<?php
session_start();
include 'db.php';

global $con;
$searchKeyword = isset($_GET['search']) ? $_GET['search'] : '';
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 25;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;
$month = isset($_GET['month']) ? $_GET['month'] : date('Y-m');

$sql = "SELECT * FROM orders WHERE (orderID LIKE '%$searchKeyword%' OR custID LIKE '%$searchKeyword%' OR totalAmount LIKE '%$searchKeyword%' OR orderDate LIKE '%$searchKeyword%' OR paymentStatus LIKE '%$searchKeyword%' OR orderStatus LIKE '%$searchKeyword%') AND DATE_FORMAT(orderDate, '%Y-%m') = '$month' LIMIT $limit OFFSET $offset";
$result = $con->query($sql);
$orders = array();

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $orders[] = $row;
    }
}

$sqlChart = "SELECT DATE(orderDate) as orderDay, COUNT(*) as orderCount FROM orders WHERE DATE_FORMAT(orderDate, '%Y-%m') = '$month' GROUP BY DATE(orderDate) ORDER BY orderDay DESC LIMIT 30";
$resultChart = $con->query($sqlChart);
$chartData = array();

if ($resultChart->num_rows > 0) {
    while ($rowChart = $resultChart->fetch_assoc()) {
        $chartData[$rowChart['orderDay']] = $rowChart['orderCount'];
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Manage Orders</title>
    <link rel="stylesheet" type="text/css" href="admin_style.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .flash-message {
            position: fixed;
            top: 20px;
            left: 85%;
            transform: translateX(-50%);
            z-index: 10000;
            display: none;
            width: 300px;
        }
    </style>
</head>

<body class="fix-header">

<div class="left-sidebar">
    <div class="unscroll-sidebar">
        <div class="header">
            <h2>HC Cafe</h2>
        </div>
        <div class="sidebar-nav">
            <a href="admin_dashboard.php"><i class="dashboard"></i> Dashboard</a>
            <a href="admin_manageCustomer.php"><i class="manageCustomer"></i> Manage Customers</a>
            <a href="admin_manageCategory.php"><i class="manageCategory"></i> Manage Category</a>
            <a href="admin_manageMenu.php"><i class="manageMenu"></i> Manage Menu</a>
            <a href="admin_manageOrder.php"><i class="manageOrder"></i> Manage Order</a>
            <a href="admin_generateReport.php"><i class="generateReport"></i> Generate Report</a>
            <a href="admin_generateQR.php"><i class="generateQR"></i> Generate QR</a>
            <a href="admin_logout.php"><i class="logout"></i> Log out</a>
        </div>
    </div>
</div>

<div class="container-fluid page-wrapper">
    <main role="main" class="col-md-9 ml-sm-auto col-lg-10 px-4">
        <div class="p-3">
            <div class="order-list">
                <h3>Order List</h3>

                <div class="row mb-3">
                    <div class="col-md-4">
                        <form id="searchForm" method="get" enctype="multipart/form-data">
                            <div class="input-group">
                                <input type="text" name="search" id="search" class="form-control" placeholder="Search orders" value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                                <div class="input-group-append">
                                    <button type="submit" id="searchButton" class="btn btn-primary">
                                    Search</button>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="col-md-4">
                        <input type="month" id="monthPicker" class="form-control" value="<?php echo $month; ?>">
                    </div>
                    <div class="col-md-4 text-right">
                        <select id="limit" class="form-control">
                            <option value="25" <?php echo $limit == 25 ? 'selected' : ''; ?>>25</option>
                            <option value="50" <?php echo $limit == 50 ? 'selected' : ''; ?>>50</option>
                            <option value="100" <?php echo $limit == 100 ? 'selected' : ''; ?>>100</option>
                        </select>
                    </div>
                </div>

                <table id="orderTable" class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Customer ID</th>
                            <th>Total Amount</th>
                            <th>Order Date</th>
                            <th>Payment Status</th>
                            <th>Order Status</th>
                        </tr>
                    </thead>
                    <tbody id="orderTableBody">
                        <?php foreach ($orders as $order) : ?>
                            <tr>
                                <td><?php echo $order['orderID']; ?></td>
                                <td><?php echo $order['custID']; ?></td>
                                <td><?php echo $order['totalAmount']; ?></td>
                                <td><?php echo $order['orderDate']; ?></td>
                                <td><?php echo $order['paymentStatus']; ?></td>
                                <td><?php echo $order['orderStatus']; ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="mt-5">
                <canvas id="orderChart" width="400" height="200"></canvas>
            </div>
        </div>
    </main>
</div>

<script>
    $(document).ready(function() {
        var ctx = document.getElementById('orderChart').getContext('2d');
        var myChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: [<?php echo implode(',', array_map(function($date) {
                    return "'$date'";
                }, array_keys($chartData))); ?>],
                datasets: [{
                    label: 'Number of Orders',
                    data: [<?php echo implode(',', array_values($chartData)); ?>],
                    backgroundColor: 'rgba(54, 162, 235, 0.2)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        function handleSearch() {
            var searchKeyword = $('#search').val();
            var limit = $('#limit').val();
            var month = $('#monthPicker').val();
            $.ajax({
                url: 'admin_searchOrder.php',
                type: 'GET',
                data: {
                    search: searchKeyword,
                    limit: limit,
                    month: month
                },
                success: function(response) {
                    $('#orderTableBody').html(response);
                    // Update chart
                    updateChart(month);
                },
                error: function(xhr, status, error) {
                    console.error(error);
                }
            });
        }

        function updateChart(month) {
            $.ajax({
                url: 'admin_fetchChartData.php',
                type: 'GET',
                data: { month: month },
                success: function(data) {
                    var chartLabels = [];
                    var chartData = [];
                    data = JSON.parse(data);
                    for (var day in data) {
                        chartLabels.push(day);
                        chartData.push(data[day]);
                    }
                    myChart.data.labels = chartLabels;
                    myChart.data.datasets[0].data = chartData;
                    myChart.update();
                },
                error: function(xhr, status, error) {
                    console.error(error);
                }
            });
        }

        $('#search').on('input', function() {
            handleSearch();
        });

        $('#searchButton').click(function(e) {
            e.preventDefault();
            handleSearch();
        });

        $('#search').keypress(function(e) {
            if (e.which === 13) {
                e.preventDefault();
                handleSearch();
            }
        });

        $('#limit').change(function() {
            handleSearch();
        });

        $('#monthPicker').change(function() {
            handleSearch();
        });
    });
</script>

<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>

</html>
