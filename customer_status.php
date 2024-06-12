
<?php
session_start();
include 'db.php';

// Retrieve the order ID from the URL parameter
$orderID = isset($_GET['order_id']) ? $_GET['order_id'] : null;

if (!$orderID) {
    die(json_encode(array('error' => 'Missing order ID')));
}

// Query to retrieve order details and status from the database
$orderSql = "SELECT * FROM orders WHERE orderID = ?";
$orderStmt = $con->prepare($orderSql);
$orderStmt->bind_param("i", $orderID);
$orderStmt->execute();
$orderResult = $orderStmt->get_result();

if ($orderResult->num_rows > 0) {
    $orderData = $orderResult->fetch_assoc();
    $orderStatus = $orderData['orderStatus'];

    // Query to retrieve order items
    $orderItemsSql = "SELECT oi.itemID, oi.quantity, mi.itemName FROM order_items oi 
                      INNER JOIN menu_item mi ON oi.itemID = mi.itemID 
                      WHERE oi.orderID = ?";
    $orderItemsStmt = $con->prepare($orderItemsSql);
    $orderItemsStmt->bind_param("i", $orderID);
    $orderItemsStmt->execute();
    $orderItemsResult = $orderItemsStmt->get_result();
    $orderItems = array();
    
    while ($row = $orderItemsResult->fetch_assoc()) {
        // Add item details to the orderItems array
        $orderItems[] = array(
            'itemID' => $row['itemID'],
            'itemName' => $row['itemName'],
            'quantity' => $row['quantity']
        );
    }
} else {
    die(json_encode(array('error' => 'Order not found')));
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Summary</title>
    <link rel="stylesheet" href="customer_style.css">
    <style>
        body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f8f9fa;
            color: #343a40;
            display: flex;
            justify-content: center; /* Center horizontally */
            align-items: center; /* Center vertically */
        }

        .container {
            width: 50%;
            margin: 10px auto;
            padding: 20px;
            background-color: white;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            border-radius: 8px;
            display: flex;
            flex-direction: column; /* Arrange children in a column */
            align-items: center; /* Center children horizontally */
            text-align: center; /* Center text inside */
        }

        .container h2 {
            margin-top: 5px;
        }

        .container h3 {
            margin-top: 5px;
        }

        .progress-bar {
        width: 100%;
        background-color: #ddd;
        height: 20px;
        margin-top: 10px;
        margin-bottom: 20px;
        border-radius: 10px;
        overflow: hidden;
        }
        .progress {
            height: 100%;
            width: 0%;
            border-radius: 10px;
            transition: width 0.3s ease-in-out;
        }
        .progress.pending {
            background-color: #FFC107; /* Yellow */
        }
        .progress.processing {
            background-color: #2196F3; /* Blue */
        }
        .progress.completed {
            background-color: #4CAF50; /* Green */
        }

        .loading-gif {
        display: block;
        margin: 20px auto; /* Center loading GIF */
        width: 200px;
        height: 200px;
        }
   

        @media (max-width: 768px) {
            .container {
                padding: 10px;
                width: 90%;
            }
            .loading-gif {
                width: 150px; /* Adjust loading GIF size for smaller screens */
                height: 150px;
            }
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Order status</h2>

    <div class="order-summary">
        <p>Order ID: #<?php echo $orderData['orderID']; ?></p>
        <p>Order Date: <?php echo $orderData['orderDate']; ?></p>
    <hr>
    </div>
    <div class="order-items">
        <h3>Order Items</h3>
        <?php foreach ($orderItems as $item): ?>
            <div class="order-item">
                <p><?php echo $item['itemName']; ?> x <?php echo $item['quantity']; ?></p>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="order-status">
        <img class="loading-gif" src="loading1.gif" alt="Loading...">
        <div class="progress-bar">
            <div class="progress" id="order-progress"></div>
        </div>
        <div class="order-status-text" id="order-status-text">Fetching order status...</div>
    </div>
</div>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script>
$(document).ready(function() {
    // Flag to track if notification has been shown
    let notificationShown = false;

    // Function to update order status via Ajax
    function updateOrderStatus() {
        $.ajax({
            url: 'customer_getOrderStatus.php',  // PHP script to fetch order status
            type: 'GET',
            dataType: 'json',
            data: { order_id: <?php echo $orderID; ?> }, // Pass order ID to PHP script
            success: function(data) {
                // Update progress bar based on order status
                let progress = 0;
                let statusText = '';
                switch(data.orderStatus) {
                    case 'Income':
                        progress = 10;
                        statusText = 'Order submitted and awaiting processing.';
                        $('#order-progress').removeClass().addClass('progress pending');
                        break;
                    case 'Processed':
                        progress = 50;
                        statusText = 'Order is currently being processed.';
                        $('#order-progress').removeClass().addClass('progress processing');
                        break;
                    case 'Ready':
                        progress = 100;
                        statusText = 'Order is ready for pickup!';
                        $('#order-progress').removeClass().addClass('progress completed');

                        // Check if notification has already been shown
                        if (!notificationShown) {
                            // Notify user that order is completed
                            notifyUser('Order ready to pickup!');
                            notificationShown = true; // Set flag to true after showing notification
                        }

                        // Vibrate the device (if supported)
                        vibrateDevice();

                        break;
                    case 'Completed':
                        // Redirect to the review page when order is completed
                        window.location.href = 'customer_review.php?order_id=<?php echo $orderID; ?>';
                        break;
                    default:
                        progress = 0;
                        statusText = 'Order status not available.';
                        $('#order-progress').removeClass().addClass('progress');
                }
                $('#order-progress').css('width', progress + '%');
                $('#order-status-text').text(statusText);
            },
            error: function(xhr, status, error) {
                console.error('Error fetching order status');
            }
        });
    }

    // Function to show notification
    function notifyUser(message) {
        // Check if the browser supports notifications
        if ('Notification' in window) {
            // Request permission to show notifications
            Notification.requestPermission().then(function(result) {
                if (result === 'granted') {
                    // Create a notification
                    new Notification(message);
                }
            });
        } else {
            // Fallback if browser does not support notifications
            alert(message);
        }
    }

    // Function to vibrate the device (if supported)
    function vibrateDevice() {
        if ('vibrate' in navigator) {
            // Vibrate for 500ms
            navigator.vibrate(500);
        }
    }

    // Fetch order status every 5 seconds (adjust as needed)
    setInterval(updateOrderStatus, 5000);  // 5000 milliseconds = 5 seconds
});

</script>

</body>
</html>
