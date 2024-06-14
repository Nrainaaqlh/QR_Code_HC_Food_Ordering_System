<?php
session_start();
include 'db.php';

global $con;

// Fetch total number of orders for this month
$currentMonth = date('m');
$currentYear = date('Y');
$sqlTotalOrders = "SELECT COUNT(*) as totalOrders FROM orders WHERE MONTH(orderDate) = ? AND YEAR(orderDate) = ?";
$statementTotalOrders = $con->prepare($sqlTotalOrders);
$statementTotalOrders->bind_param('ii', $currentMonth, $currentYear);
$statementTotalOrders->execute();
$resultTotalOrders = $statementTotalOrders->get_result();
$totalOrders = ($resultTotalOrders->num_rows > 0) ? $resultTotalOrders->fetch_assoc()['totalOrders'] : 0;
$statementTotalOrders->close();

// Fetch total sales
$currentMonth = date('m');
$currentYear = date('Y');
$sqlTotalSales = "SELECT SUM(totalAmount) AS total_sales FROM orders WHERE MONTH(orderDate) = ? AND YEAR(orderDate) = ?";
$statement = $con->prepare($sqlTotalSales);
$statement->bind_param('ii', $currentMonth, $currentYear);
$statement->execute();
$resultTotalSales = $statement->get_result();
$totalSales = ($resultTotalSales->num_rows > 0) ? $resultTotalSales->fetch_assoc()['total_sales'] : 0;
$statement->close();


// Fetch total number of menu items
$sqlTotalItems = "SELECT COUNT(*) as totalItems FROM menu_item";
$resultTotalItems = $con->query($sqlTotalItems);
$totalItems = ($resultTotalItems->num_rows > 0) ? $resultTotalItems->fetch_assoc()['totalItems'] : 0;

// Fetch total number of categories
$sqlTotalCategories = "SELECT COUNT(*) as totalCategories FROM menu_category";
$resultTotalCategories = $con->query($sqlTotalCategories);
$totalCategories = ($resultTotalCategories->num_rows > 0) ? $resultTotalCategories->fetch_assoc()['totalCategories'] : 0;

// Fetch total number of customers
$sqlTotalCustomer = "SELECT COUNT(*) as totalCustomer FROM customers";
$resultTotalCustomer = $con->query($sqlTotalCustomer);
$totalCustomer = ($resultTotalCustomer->num_rows > 0) ? $resultTotalCustomer->fetch_assoc()['totalCustomer'] : 0;

// Fetch average rating
$sqlAverageRating = "SELECT AVG(rating) as averageRating FROM reviews";
$resultAverageRating = $con->query($sqlAverageRating);
$averageRating = ($resultAverageRating->num_rows > 0) ? number_format($resultAverageRating->fetch_assoc()['averageRating'], 1) : 0;

// Fetch total sales grouped by day within a date range
$start_date = date('Y-m-d', strtotime('-7 days')); // Default start date (7 days ago)
$end_date = date('Y-m-d', strtotime('+1 days')); // Default end date (today)

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['start_date']) && isset($_POST['end_date'])) {
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
}

$sqlTotalSalesByDay = "SELECT DATE(orderDate) AS orderDay, SUM(totalAmount) AS totalSales
                       FROM orders
                       WHERE orderDate BETWEEN ? AND ?
                       GROUP BY DATE(orderDate)";
$stmtTotalSalesByDay = $con->prepare($sqlTotalSalesByDay);
$stmtTotalSalesByDay->bind_param("ss", $start_date, $end_date);
$stmtTotalSalesByDay->execute();
$resultTotalSalesByDay = $stmtTotalSalesByDay->get_result();

// Initialize arrays to store data for the chart
$salesData = [];
while ($row = $resultTotalSalesByDay->fetch_assoc()) {
    $salesData[$row['orderDay']] = $row['totalSales'];
}

$stmtTotalSalesByDay->close();

// Fetch total number of customers per day
$sqlTotalOrdersByDay = "SELECT DATE(orderDate) AS orderDay, COUNT(DISTINCT orderID) AS totalOrders
                          FROM orders
                          WHERE orderDate BETWEEN ? AND ?
                          GROUP BY DATE(orderDate)";
$stmtTotalOrdersByDay = $con->prepare($sqlTotalOrdersByDay);
$stmtTotalOrdersByDay->bind_param("ss", $start_date, $end_date);
$stmtTotalOrdersByDay->execute();
$resultTotalCustomersByDay = $stmtTotalOrdersByDay->get_result();

// Initialize arrays to store data for the chart
$orderData = [];
while ($row = $resultTotalCustomersByDay->fetch_assoc()) {
    $orderData[$row['orderDay']] = $row['totalOrders'];
}

$stmtTotalOrdersByDay->close();

$con->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" type="text/css" href="admin_style.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        #dashboard-container {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-around;
            margin-top: 20px;
        }

        .dashboard-item {
            background-color: white;
            padding: 20px;
            margin: 10px;
            border-radius: 10px;
            box-shadow: 0px 2px 5px rgba(0, 0, 0, 0.2);
            text-align: center;
            width: calc(30% - 40px);
            min-width: 100px;
            margin-bottom: 5px;
            transition: transform 0.2s;
        }

        .dashboard-item:hover {
            transform: scale(1.05);
        }

        .dashboard-item h3 {
            font-size: 15px;
            font-weight: bold;
            margin-bottom: 20px;
            color: black;
            text-decoration: underline;
        }

        .dashboard-item p {
            font-size: 15px;
            color: black;
        }

        .chart-container {
            width: 90%;
            max-width: 500px; /* Adjust width as needed */
            margin-top: 20px;
            margin-right: 20px;
            display: flex;
            justify-content: space-around; /* Ensure charts are evenly spaced */
        }
        
        .chart {
            width: 100%;
            height: 500px; /* Adjust height as needed */
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

    <!-- Main content area -->
    <div class="container-fluid page-wrapper">
        <main role="main" class="col-md-9 ml-sm-auto col-lg-10 px-4">
            <div class="p-3">
                <h2>Admin Dashboard</h2>

                <div id="dashboard-container" class="dashboard-container">
                    <a href="admin_totalOrders.php" class="dashboard-item">
                        <h3>This Month Orders</h3>
                        <p><?php echo $totalOrders; ?></p>
                    </a>

                    <a href="admin_manageCustomer.php" class="dashboard-item">
                        <h3>Total Customers</h3>
                        <p><?php echo $totalCustomer; ?></p>
                    </a>

                    <a href="admin_totalSales.php" class="dashboard-item">
                        <h3>This Month Sales</h3>
                        <p><?php echo 'RM ' . number_format($totalSales, 2); ?></p>
                    </a>

                    <a href="admin_manageMenu.php" class="dashboard-item">
                        <h3>Total Items</h3>
                        <p><?php echo $totalItems; ?></p>
                    </a>

                    <a href="admin_manageCategory.php" class="dashboard-item">
                        <h3>Total Categories</h3>
                        <p><?php echo $totalCategories; ?></p>
                    </a>

                    <a href="admin_averageRating.php" class="dashboard-item">
                        <h3>Average Rating</h3>
                        <p><?php echo $averageRating; ?></p>
                    </a>
                </div>

                <div class="chart-container">
                    <!-- Sales Chart -->
                    <canvas id="salesChart" class="chart"></canvas>

                    <!-- Customer Chart -->
                    <canvas id="customerChart" class="chart"></canvas>
                </div>
            </div>
        </main>
    </div>

    <script>
        var salesData = <?php echo json_encode($salesData); ?>;
      
        var ctx = document.getElementById('salesChart').getContext('2d');
        var salesChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: Object.keys(salesData),
                datasets: [{
                    label: 'Total Sales (RM)',
                    data: Object.values(salesData),
                    backgroundColor: 'rgba(54, 162, 235, 0.5)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    xAxes: [{
                        stacked: true,
                        ticks: {
                            autoSkip: true,
                            maxTicksLimit: 20
                        }
                    }],
                    yAxes: [{
                        ticks: {
                            beginAtZero: true,
                            callback: function(value) { return 'RM ' + value.toFixed(2); }
                        }
                    }]
                },
                tooltips: {
                    mode: 'index',
                    intersect: false
                }
            }
        });

         // Customer data from PHP
         var orderData = <?php echo json_encode($orderData); ?>;
        
        // Chart initialization
        var ctx = document.getElementById('customerChart').getContext('2d');
        var customerChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: Object.keys(orderData), // Array of dates
                datasets: [{
                    label: 'Total Orders',
                    data: Object.values(orderData), // Array of customer counts
                    backgroundColor: 'rgba(255, 99, 132, 0.5)',
                    borderColor: 'rgba(255, 99, 132, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    xAxes: [{
                        stacked: true,
                        ticks: {
                            autoSkip: true,
                            maxTicksLimit: 20
                        }
                    }],
                    yAxes: [{
                        ticks: {
                            beginAtZero: true,
                            stepSize: 1
                        }
                    }]
                },
                tooltips: {
                    mode: 'index',
                    intersect: false
                }
            }
        });
    </script>
</body>
</html>
