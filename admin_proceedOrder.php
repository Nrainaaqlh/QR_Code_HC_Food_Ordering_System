<?php
session_start();
include 'db.php'; // Include your database connection file

if (isset($_GET['order_id'])) {
    $orderID = $_GET['order_id'];
    
    // Update order status to 'Order Processed'
    $sql = "UPDATE orders SET orderStatus = 'Ready' WHERE orderID = ?";
    $stmt = $con->prepare($sql);
    $stmt->bind_param("i", $orderID); // Assuming orderID is an integer
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        // Redirect back to manage orders page after successful update
        header("Location: admin_manageOrder.php");
        exit();
    } else {
        // Handle error if update fails
        echo "Failed to update order status.";
    }
} else {
    // Handle case where order_id is not set
    echo "Order ID not specified.";
}
?>
