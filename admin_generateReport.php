<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Generate Report</title>
    <link rel="stylesheet" type="text/css" href="admin_style.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
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
                <h2>Generate Report</h2>
                <form action="admin_generateReport.php" method="post">
                    <div class="form-group">
                        <label for="start_date">Start Date:</label>
                        <input type="date" id="start_date" name="start_date" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="end_date">End Date:</label>
                        <input type="date" id="end_date" name="end_date" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="report_type">Report Type:</label>
                        <select id="report_type" name="report_type" class="form-control" required>
                            <option value="sales">Sales Report</option>
                            <option value="orders">Order Report</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary">Generate Report</button>
                </form>
                
                <div id="report-result" class="mt-5">
                <?php
                include 'db.php';

                if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                    $start_date = $_POST['start_date'];
                    $end_date = $_POST['end_date'];
                    $report_type = $_POST['report_type'];

                    if (empty($start_date) || empty($end_date) || empty($report_type)) {
                        echo "<div class='alert alert-danger'>All fields are required.</div>";
                    } else {
                        if ($con->connect_error) {
                            die("Connection failed: " . $con->connect_error);
                        }

                        if ($report_type == 'sales') {
                            $sql = "SELECT oi.itemID, m.itemName, SUM(oi.quantity) AS total_quantity, SUM(totalAmount) AS total_sales
                                    FROM order_items oi
                                    JOIN orders o ON oi.orderID = o.orderID
                                    JOIN menu_item m ON oi.itemID = m.itemID
                                    WHERE o.orderDate BETWEEN ? AND DATE_ADD(?, INTERVAL 1 DAY) - INTERVAL 1 SECOND
                                    GROUP BY oi.itemID, m.itemName";
                        } else if ($report_type == 'orders') {
                            $sql = "SELECT o.orderID, o.custID, c.custName, o.totalAmount, o.orderDate, o.paymentStatus
                                    FROM orders o
                                    JOIN customers c ON o.custID = c.custID
                                    WHERE o.orderDate BETWEEN ? AND DATE_ADD(?, INTERVAL 1 DAY) - INTERVAL 1 SECOND";
                        }

                        $stmt = $con->prepare($sql);
                        $stmt->bind_param("ss", $start_date, $end_date);
                        $stmt->execute();
                        $result = $stmt->get_result();

                        if ($result->num_rows > 0) {
                            if ($report_type == 'sales') {
                                echo "<h2>Sales Report from " . htmlspecialchars($start_date) . " to " . htmlspecialchars($end_date) . "</h2>";
                                echo "<div id='report-content'>";
                                echo "<table class='table table-striped'>";
                                echo "<thead><tr><th>Item ID</th><th>Item Name</th><th>Total Quantity Sold</th><th>Total Sales</th></tr></thead>";
                                echo "<tbody>";

                                $total_sales_sum = 0;
                                while ($row = $result->fetch_assoc()) {
                                    echo "<tr>";
                                    echo "<td>" . htmlspecialchars($row['itemID']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['itemName']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['total_quantity']) . "</td>";
                                    echo "<td>RM " . htmlspecialchars($row['total_sales']) . "</td>";
                                    echo "</tr>";
                                    $total_sales_sum += $row['total_sales'];
                                }

                                echo "</tbody>";
                                echo "<tfoot>";
                                echo "<tr><th colspan='3'>Total Sales</th><th>RM " . htmlspecialchars($total_sales_sum) . "</th></tr>";
                                echo "</tfoot>";
                                echo "</table>";
                                echo "</div>";
                            } else if ($report_type == 'orders') {
                                echo "<h2>Order Report from " . htmlspecialchars($start_date) . " to " . htmlspecialchars($end_date) . "</h2>";
                                echo "<div id='report-content'>";
                                echo "<table class='table table-striped'>";
                                echo "<thead><tr><th>Order ID</th><th>Customer ID</th><th>Customer Name</th><th>Total Amount</th><th>Order Date</th><th>Payment Status</th></tr></thead>";
                                echo "<tbody>";

                                $total_orders = 0;
                                while ($row = $result->fetch_assoc()) {
                                    echo "<tr>";
                                    echo "<td>" . htmlspecialchars($row['orderID']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['custID']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['custName']) . "</td>";
                                    echo "<td>RM " . htmlspecialchars($row['totalAmount']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['orderDate']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['paymentStatus']) . "</td>";
                                    echo "</tr>";
                                    $total_orders++;
                                }

                                echo "</tbody>";
                                echo "<tfoot>";
                                echo "<tr><th colspan='5'>Total Orders</th><th>" . htmlspecialchars($total_orders) . "</th></tr>";
                                echo "</tfoot>";
                                echo "</table>";
                                echo "</div>";
                            }
                            echo "<button onclick='printReport()' class='btn btn-secondary mt-3'>Print Report</button>";
                        } else {
                            echo "<div class='alert alert-warning'>No records found for the specified date range.</div>";
                        }

                        $stmt->close();
                        $con->close();
                    }
                }
                ?>
                </div>
            </div>
        </main>
    </div>

    <script>
        function printReport() {
            var reportContent = document.getElementById('report-content').innerHTML;
            var title = document.getElementById('report-result').getElementsByTagName('h2')[0].innerText;
            var originalContent = document.body.innerHTML;
            document.body.innerHTML = '<h1>' + title + '</h1>' + reportContent;
            window.print();
            document.body.innerHTML = originalContent;
        }
    </script>
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>

</html>
