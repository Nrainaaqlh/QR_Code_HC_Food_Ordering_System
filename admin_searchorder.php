<?php
session_start();
include 'db.php';

global $con;
$searchKeyword = isset($_GET['search']) ? $_GET['search'] : '';
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 25;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;
$month = isset($_GET['month']) ? $_GET['month'] : date('Y-m');

$sql = "SELECT * FROM orders WHERE (orderID LIKE '%$searchKeyword%' OR custID LIKE '%$searchKeyword%' OR totalAmount LIKE '%$searchKeyword%' OR orderDate LIKE '%$searchKeyword%' OR paymentStatus LIKE '%$searchKeyword%' OR orderStatus LIKE '%$searchKeyword%') AND DATE_FORMAT(orderDate, '%Y-%m') = '$month' LIMIT $limit OFFSET $offset";
$result = $con->query($sql);
$orders = array();

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $orders[] = $row;
    }
} else {
    echo '<tr><td colspan="7">No items found</td></tr>';
}

foreach ($orders as $order) {
    echo '<tr>';
    echo '<td>' . $order['orderID'] . '</td>';
    echo '<td>' . $order['custID'] . '</td>';
    echo '<td>' . $order['totalAmount'] . '</td>';
    echo '<td>' . $order['orderDate'] . '</td>';
    echo '<td>' . $order['paymentStatus'] . '</td>';
    echo '<td>' . $order['orderStatus'] . '</td>';
    echo '</tr>';
    }

?>
