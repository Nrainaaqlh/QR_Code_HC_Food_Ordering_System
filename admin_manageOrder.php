<?php
session_start();
include 'db.php'; // Include your database connection file

// Mapping arrays for options, size, and takeaway
$optionsMapping = [
    1 => 'Normal',
    2 => 'Less Sweet',
    3 => 'No Sweet'
    // Add other options as needed
];

$sizeMapping = [
    1 => 'Normal',
    2 => 'Big'
    // Add other sizes as needed
];

$takeawayMapping = [
    1 => 'Takeaway',
    2 => 'Dine In'
    // Add other takeaway options as needed
];

// Function to fetch order items based on order ID
function fetchOrderItems($orderID) {
    global $con, $optionsMapping, $sizeMapping, $takeawayMapping;

    $orderItems = [];
    $sql = "SELECT order_items.*, menu_item.itemName 
            FROM order_items 
            INNER JOIN menu_item ON order_items.itemID = menu_item.itemID 
            WHERE order_items.orderID = ?";
    $stmt = $con->prepare($sql);
    $stmt->bind_param("i", $orderID);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        // Map options, size, and takeaway to human-readable format
        $row['options'] = isset($optionsMapping[$row['options']]) ? $optionsMapping[$row['options']] : 'Unknown';
        $row['size'] = isset($sizeMapping[$row['size']]) ? $sizeMapping[$row['size']] : 'Unknown';
        $row['takeaway'] = isset($takeawayMapping[$row['takeaway']]) ? $takeawayMapping[$row['takeaway']] : 'Unknown';
        
        $orderItems[] = $row;
    }

    return $orderItems;
}

// Function to fetch orders based on status
function fetchOrders($status) {
    global $con;

    $orders = [];
    $sql = "SELECT * FROM orders WHERE orderStatus = ?";
    $stmt = $con->prepare($sql);
    $stmt->bind_param("s", $status);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $row['orderItems'] = fetchOrderItems($row['orderID']); // Fetch order items for each order
        $orders[] = $row;
    }

    return $orders;
}

// Fetch orders based on different statuses
$ordersIncome = fetchOrders('Income');
$ordersProceed = fetchOrders('Processed');
$ordersReady = fetchOrders('Ready');
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Manage Orders</title>
    <link rel="stylesheet" type="text/css" href="admin_style.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        /* Add custom styles for your admin manage order page */
        .order-list {
            margin-top: 20px;
        }

        .order-list h3 {
            margin-bottom: 10px;
        }

        .order-list table {
            width: 100%;
            border-collapse: collapse;
        }

        .order-list th,
        .order-list td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }

        .order-list th {
            background-color: #f2f2f2;
        }
    </style>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
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
                <h2>Manage Orders</h2>

                <!-- Tab navigation for different order statuses -->
                <ul class="nav nav-tabs" id="myTab" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" id="income-tab" data-toggle="tab" href="#income" role="tab" aria-controls="income" aria-selected="true">Order Receive</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="proceed-tab" data-toggle="tab" href="#proceed" role="tab" aria-controls="proceed" aria-selected="false">Order Processed</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="completed-tab" data-toggle="tab" href="#ready" role="tab" aria-controls="ready" aria-selected="false">Order Ready</a>
                    </li>
                </ul>

                <!-- Tab content for different order statuses -->
                <div class="tab-content" id="myTabContent">
                    <div class="tab-pane fade show active" id="income" role="tabpanel" aria-labelledby="income-tab">
                        <div class="order-list">
                            <h3>Order Receive</h3>
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Order ID</th>
                                        <th>Customer ID</th>
                                        <th>Order items</th>
                                        <th>Total Amount</th>
                                        <th>Order Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Display Orders and Order Items -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="proceed" role="tabpanel" aria-labelledby="proceed-tab">
                        <div class="order-list">
                            <h3>Order Processed</h3>
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Order ID</th>
                                        <th>Customer ID</th>
                                        <th>Order items</th>
                                        <th>Total Amount</th>
                                        <th>Order Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Display Orders and Order Items -->
                                    <?php foreach ($ordersProceed as $order): ?>
                                        <?php foreach ($order['orderItems'] as $index => $item): ?>
                                            <tr>
                                                <?php if ($index === 0): ?>
                                                    <td rowspan="<?php echo count($order['orderItems']); ?>"><?php echo $order['orderID']; ?></td>
                                                    <td rowspan="<?php echo count($order['orderItems']); ?>"><?php echo $order['custID']; ?></td>
                                                    <td><?php echo $item['itemName']; ?> - Size: <?php echo $item['size']; ?>, Quantity: <?php echo $item['quantity']; ?>, Options: <?php echo $item['options']; ?>, Remark: <?php echo $item['remark']; ?>, Takeaway: <?php echo $item['takeaway']; ?></td>
                                                    <td rowspan="<?php echo count($order['orderItems']); ?>"><?php echo $order['totalAmount']; ?></td>
                                                    <td rowspan="<?php echo count($order['orderItems']); ?>"><?php echo $order['orderDate']; ?></td>
                                                    <td rowspan="<?php echo count($order['orderItems']); ?>">
                                                        <!-- Example Action: Update Order Status -->
                                                        <a href="admin_proceedOrder.php?order_id=<?php echo $order['orderID']; ?>">Update Status</a>
                                                    </td>
                                                <?php else: ?>
                                                    <td><?php echo $item['itemName']; ?> - Size: <?php echo $item['size']; ?>, Quantity: <?php echo $item['quantity']; ?>, Options: <?php echo $item['options']; ?>, Remark: <?php echo $item['remark']; ?>, Takeaway: <?php echo $item['takeaway']; ?></td>
                                                <?php endif; ?>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="ready" role="tabpanel" aria-labelledby="ready-tab">
                        <div class="order-list">
                            <h3>Order Ready</h3>
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Order ID</th>
                                        <th>Customer ID</th>
                                        <th>Order items</th>
                                        <th>Total Amount</th>
                                        <th>Order Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Display Orders and Order Items -->
                                    <?php foreach ($ordersReady as $order): ?>
                                        <?php foreach ($order['orderItems'] as $index => $item): ?>
                                            <tr>
                                                <?php if ($index === 0): ?>
                                                    <td rowspan="<?php echo count($order['orderItems']); ?>"><?php echo $order['orderID']; ?></td>
                                                    <td rowspan="<?php echo count($order['orderItems']); ?>"><?php echo $order['custID']; ?></td>
                                                    <td><?php echo $item['itemName']; ?> - Size: <?php echo $item['size']; ?>, Quantity: <?php echo $item['quantity']; ?>, Options: <?php echo $item['options']; ?>, Remark: <?php echo $item['remark']; ?>, Takeaway: <?php echo $item['takeaway']; ?></td>
                                                    <td rowspan="<?php echo count($order['orderItems']); ?>"><?php echo $order['totalAmount']; ?></td>
                                                    <td rowspan="<?php echo count($order['orderItems']); ?>"><?php echo $order['orderDate']; ?></td>
                                                    <td rowspan="<?php echo count($order['orderItems']); ?>">
                                                        <!-- Example Action: Update Order Status -->
                                                        <a href="admin_readyOrder.php?order_id=<?php echo $order['orderID']; ?>">Update Status</a>
                                                    </td>
                                                <?php else: ?>
                                                    <td><?php echo $item['itemName']; ?> - Size: <?php echo $item['size']; ?>, Quantity: <?php echo $item['quantity']; ?>, Options: <?php echo $item['options']; ?>, Remark: <?php echo $item['remark']; ?>, Takeaway: <?php echo $item['takeaway']; ?></td>
                                                <?php endif; ?>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
   
         function playNotificationSound() {
            console.log('Notification sound triggered.'); // Optional: Log for debugging
            var audio = new Audio('noti.mp3'); // Path to your notification sound file
            audio.play();
        }
        
    $(document).ready(function () {
        // Function to fetch order receive data via AJAX
        function fetchOrderReceiveData() {
            $.ajax({
                url: 'admin_fetchNewOrders.php', // PHP script to fetch order receive data
                method: 'POST',
                data: { status: 'Income' }, // Specify the status to fetch
                success: function (data) {
                    $('#income tbody').html(data); // Replace content in the tab with updated data
    
                }
            });
        }

        // Initial fetch on page load
        fetchOrderReceiveData();

        // Set interval to fetch data every 30 seconds
        setInterval(fetchOrderReceiveData, 3000); // 30000 milliseconds = 30 seconds
    });
</script>
</body>

</html>

