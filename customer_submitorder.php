<?php
session_start();
require 'vendor/autoload.php'; // Ensure you have Stripe SDK installed via Composer
include 'db.php'; // Include your database connection file

// Retrieve the session_id and order details from the URL parameters
$session_id = isset($_GET['session_id']) ? $_GET['session_id'] : null;
$order = isset($_GET['order']) ? json_decode(urldecode($_GET['order']), true) : null;

// Verify session_id and order details are available
if (!$session_id || !$order) {
    die('Error: Missing session ID or order details.');
}

// Assuming the user is logged in and you have their ID stored in a session
$custID = isset($_SESSION['custID']) ? $_SESSION['custID'] : null;

// Check if custID is available
if (!$custID) {
    die('Error: Customer ID not found in session.');
}

// Calculate the total price
$totalAmount = array_reduce($order, function($carry, $item) {
    return $carry + ($item['finalPrice'] * $item['quantity']);
}, 0);

// Stripe API configuration
\Stripe\Stripe::setApiKey('sk_test_51PLne32LPA7wBM3DIip4cOzdJHoCo1Xba4wvxmUZ7Ppngwscj2HMm7dcxdqTjJ6MjLHoR6Odw8shjCxPLqiRitXV00jUWGpuEI'); // Replace with your actual secret key

try {
    // Retrieve the session details from Stripe
    $session = \Stripe\Checkout\Session::retrieve($session_id);

    // Check if the payment was successful
    if ($session->payment_status === 'paid') {
        // Retrieve the PaymentIntent associated with the session
        $paymentIntent = \Stripe\PaymentIntent::retrieve($session->payment_intent);

        $paymentID = $paymentIntent->id;
        
        // Determine the payment method type
        $paymentMethod = null;
        if (isset($paymentIntent->payment_method_types) && !empty($paymentIntent->payment_method_types)) {
            $paymentMethod = $paymentIntent->payment_method_types[0];
        } else {
            $paymentMethod = 'unknown';
        }

        $paymentDate = date('Y-m-d H:i:s'); // Current date and time

        // Insert order details into orders table
        $orderSql = "INSERT INTO orders (custID, totalAmount, paymentStatus, orderStatus, orderDate) VALUES (?, ?, 'Paid', 'Income', NOW())";
        $orderStmt = $con->prepare($orderSql);
        $orderStmt->bind_param("id", $custID, $totalAmount);

        if ($orderStmt->execute()) {
            // Get the last inserted order ID
            $orderID = $orderStmt->insert_id;

            // Insert each item in the order into order_items table
            foreach ($order as $item) {
                // Ensure all required fields are available
                if (isset($item['id'], $item['quantity'], $item['finalPrice'])) {
                    // Retrieve item details
                    $itemID = $item['id'];
                    $quantity = $item['quantity'];
                    $itemPrice = $item['finalPrice'];
                    $size = isset($item['size']) ? $item['size'] : '';
                    $options = isset($item['options']) ? $item['options'] : '';
                    $remark = isset($item['remark']) ? urldecode($item['remark']) : '';
                    $takeaway = isset($item['takeaway']) ? $item['takeaway'] : 0;

                    // Insert item into order_items table
                    $orderItemSql = "INSERT INTO order_items (orderID, itemID, quantity, size, options, remark, takeaway) VALUES (?, ?, ?, ?, ?, ?, ?)";
                    $orderItemStmt = $con->prepare($orderItemSql);
                    $orderItemStmt->bind_param("iiisssi", $orderID, $itemID, $quantity, $size, $options, $remark, $takeaway);
                    $orderItemStmt->execute();
                }
            }

            // Insert payment details into the payments table
            $paymentSql = "INSERT INTO payments (paymentID, orderID, custID, amount, method, paymentDate) VALUES (?, ?, ?, ?, ?, ?)";
            $paymentStmt = $con->prepare($paymentSql);
            $paymentStmt->bind_param("siidss", $paymentID, $orderID, $custID, $totalAmount, $paymentMethod, $paymentDate);
            $paymentStmt->execute();

            // Clear the cart session after order is placed
            unset($_SESSION['cart']);

            // Redirect to status page with order ID
            header('Location: customer_status.php?order_id=' . $orderID);
            exit();
        } else {
            die('Error inserting order into database.');
        }
    } else {
        die('Payment failed. Please try again.');
    }
} catch (Exception $e) {
    die('Error: ' . $e->getMessage());
}
?>
