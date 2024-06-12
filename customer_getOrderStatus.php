<?php
session_start();
include 'db.php'; // Make sure this includes the database connection logic

// Retrieve the order ID from the URL parameter
$orderID = isset($_GET['order_id']) ? $_GET['order_id'] : null;

if (!$orderID) {
    die(json_encode(array('error' => 'Missing order ID')));
}

// Query to retrieve order status from the database
$statusSql = "SELECT orderStatus FROM orders WHERE orderID = ?";
$statusStmt = $con->prepare($statusSql);
$statusStmt->bind_param("i", $orderID);
$statusStmt->execute();
$result = $statusStmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $orderStatus = $row['orderStatus'];
    // Return the order status as JSON
    echo json_encode(array('orderStatus' => $orderStatus));
} else {
    die(json_encode(array('error' => 'Order not found')));
}
?>
