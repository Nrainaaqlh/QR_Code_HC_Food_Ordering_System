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
$sales = array();

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $sales[] = $row;
    }
} else {
    echo '<tr><td colspan="6">No items found</td></tr>';
}

foreach ($sales as $sale) {
    echo '<tr>';
    echo '<td>' . $sale['orderID'] . '</td>';
    echo '<td>' . $sale['custID'] . '</td>';
    echo '<td>' . $sale['totalAmount'] . '</td>';
    echo '<td>' . $sale['orderDate'] . '</td>';
    echo '<td>' . $sale['paymentStatus'] . '</td>';
    echo '<td>' . $sale['orderStatus'] . '</td>';
    echo '</tr>';
}
?>
