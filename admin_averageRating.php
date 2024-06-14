<?php
session_start();
include 'db.php';

global $con;
$searchKeyword = isset($_GET['search']) ? $_GET['search'] : '';
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 25;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

$sql = "SELECT * FROM reviews WHERE orderID LIKE '%$searchKeyword%' OR rating LIKE '%$searchKeyword%' OR reviewDate LIKE '%$searchKeyword%'";
$result = $con->query($sql);
$reviews = array();

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $reviews[] = $row;
    }
}

// Fetch data for the chart (example: number of reviews per rating for last 30 days)
$sqlChart = "SELECT rating, COUNT(*) as count FROM reviews GROUP BY rating";
$resultChart = $con->query($sqlChart);
$chartData = array();

if ($resultChart->num_rows > 0) {
    while ($rowChart = $resultChart->fetch_assoc()) {
        $chartData[$rowChart['rating']] = $rowChart['count'];
    }
}

// Calculate total count of reviews for percentage calculation
$totalReviews = array_sum($chartData);

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Manage Reviews</title>
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

        #reviewChart {
            max-width: 400px; /* Adjust max-width to your preference */
            margin: 0 auto; /* Center the chart horizontally */
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
            <div class="review-list">
                <h3>Review List</h3>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <form id="searchForm" method="get" enctype="multipart/form-data">
                            <div class="input-group">
                                <input type="text" name="search" id="search" class="form-control"
                                       placeholder="Search reviews"
                                       value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                                <div class="input-group-append">
                                    <button type="submit" id="searchButton" class="btn btn-primary">Search</button>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="col-md-6 text-right">
                        <select id="limit" class="form-control">
                            <option value="25" <?php echo $limit == 25 ? 'selected' : ''; ?>>25</option>
                            <option value="50" <?php echo $limit == 50 ? 'selected' : ''; ?>>50</option>
                            <option value="100" <?php echo $limit == 100 ? 'selected' : ''; ?>>100</option>
                        </select>
                    </div>
                </div>

                <table id="reviewTable" class="table table-bordered">
                    <thead>
                    <tr>
                        <th>Review ID</th>
                        <th>Order ID</th>
                        <th>Rating</th>
                        <th>Review Date</th>
                    </tr>
                    </thead>
                    <tbody id="reviewTableBody">
                    <?php foreach ($reviews as $review) : ?>
                        <tr>
                            <td><?php echo $review['reviewID']; ?></td>
                            <td><?php echo $review['orderID']; ?></td>
                            <td><?php echo $review['rating']; ?></td>
                            <td><?php echo $review['reviewDate']; ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="mt-5">
                <canvas id="reviewChart" width="400" height="200"></canvas>
            </div>
        </div>
    </main>
</div>

<script>
    $(document).ready(function() {
        var ctx = document.getElementById('reviewChart').getContext('2d');
        var reviewChart = new Chart(ctx, {
            type: 'pie',
            data: {
                labels: [<?php echo implode(',', array_map(function($rating) {
                    return "'$rating ★'";
                }, array_keys($chartData))); ?>],
                datasets: [{
                    label: 'Number of Reviews',
                    data: [<?php
                        // Calculate percentages based on total reviews
                        foreach ($chartData as $rating => $count) {
                            $percentage = ($count / $totalReviews) * 100;
                            echo number_format($percentage, 2) . ',';
                        }
                        ?>],
                    backgroundColor: [
                        'rgba(255, 99, 132, 0.8)',
                        'rgba(54, 162, 235, 0.8)',
                        'rgba(255, 206, 86, 0.8)',
                        'rgba(75, 192, 192, 0.8)',
                        'rgba(153, 102, 255, 0.8)',
                        'rgba(255, 159, 64, 0.8)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    tooltip: {
                        callbacks: {
                            label: function(tooltipItem) {
                                var dataset = tooltipItem.dataset;
                                var total = dataset.data.reduce(function(previousValue, currentValue, currentIndex, array) {
                                    return previousValue + currentValue;
                                });
                                var currentValue = dataset.data[tooltipItem.dataIndex];
                                var percentage = Math.round((currentValue / total) * 100);
                                return percentage + '%';
                            }
                        }
                    }
                }
            }
        });

        // Function to update reviews based on search and limit
        function handleSearch() {
            var searchKeyword = $('#search').val();
            var limit = $('#limit').val();
            $.ajax({
                url: 'admin_searchReview.php',
                type: 'GET',
                data: {
                    search: searchKeyword,
                    limit: limit
                },
                success: function(response) {
                    $('#reviewTableBody').html(response);
                },
                error: function(xhr, status, error) {
                    console.error(error);
                }
            });
        }

        // Initial search and chart update
        handleSearch();

        // Event listeners
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
    });
</script>

<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>

</html>
