<?php
include 'db.php';

// Retrieve the order ID from the URL parameter
$orderID = isset($_GET['order_id']) ? $_GET['order_id'] : null;

if (!$orderID) {
    echo 'Error: Missing order ID.';
    exit();
}

// Query the database for the order status
$orderSql = "SELECT orderStatus FROM orders WHERE orderID = ?";
$orderStmt = $con->prepare($orderSql);
$orderStmt->bind_param("i", $orderID);
$orderStmt->execute();
$orderStmt->bind_result($orderStatus);
$orderStmt->fetch();
$orderStmt->close();

if ($orderStatus) {
    echo htmlspecialchars($orderStatus);
} else {
    echo 'Error: Order not found.';
}
?>
