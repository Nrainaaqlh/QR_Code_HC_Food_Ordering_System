<?php
session_start();
include 'db.php';

// Check if cart is empty
if (empty($_SESSION['cart']) || count($_SESSION['cart']) == 0) {
    die("Your cart is empty.");
}

// Assuming the user is logged in and you have their ID stored in a session
$custID = isset($_SESSION['custID']) ? $_SESSION['custID'] : null;

// Check if custID is available
if (!$custID) {
    die("Error: Customer ID not found in session.");
}

// Calculate the total price
$totalPrice = 0;
foreach ($_SESSION['cart'] as $item) {
    $totalPrice += $item['finalPrice'] * $item['quantity'];
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
            justify-content: center;
            align-items: center;
        }

        .container {
            width: 50%;
            margin: 10px auto;
            padding: 20px;
            background-color: white;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            border-radius: 8px;
        }

        .container h2 {
            margin-top: 5px;
            margin-left:60px;
        }


        .cart-item {
            border-bottom: 1px solid #dee2e6;
            padding: 10px 0;
            display: flex;
            align-items: center;
        }

        .cart-item h4 {
            margin: 0;
            font-size: 16px;
        }

        .cart-item p {
            margin: 5px 0;
            font-size: 16px;
        }

        .btn-place-order {
            background-color: #411900;
            color: white;
            border: none;
            padding: 10px 20px;
            font-size: 18px;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
            margin-top: 20px;
            display: block;
            width: 100%;
            text-align: center;
        }

        .btn-place-order:hover {
            background-color:  #6d4c41;
        }

        .payment-method {
            margin-top: 20px;
        }

        .payment-method label {
            display: block;
            margin-bottom: 10px;
        }

        .payment-method input[type="radio"] {
            margin-right: 10px;
        }

        .back-button {
            display: inline-block;
            margin-top: 10px;
            text-decoration: none;
            color: black;
            font-size: 50px;
            position: absolute;
            top: 0;
        }

        .price-container {
            display: flex;
            justify-content: space-between; /* Aligns items with space between them */
            align-items: center; /* Centers items vertically */
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
            <h3>Order ID: <?php echo $orderData['orderID']; ?></h3>
            <p>Order Date: <?php echo $orderData['orderDate']; ?></p>   
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
                        window.location.href = 'customer_review.php';
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
