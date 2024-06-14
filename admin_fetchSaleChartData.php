<?php
include 'db.php';
$month = isset($_GET['month']) ? $_GET['month'] : date('Y-m');
$sqlChart = "SELECT DATE(orderDate) as saleDay, SUM(totalAmount) as saleCount FROM orders WHERE DATE_FORMAT(orderDate, '%Y-%m') = '$month' GROUP BY DATE(orderDate) ORDER BY saleDay DESC LIMIT 30";
$resultChart = $con->query($sqlChart);
$chartData = array();

if ($resultChart->num_rows > 0) {
    while ($rowChart = $resultChart->fetch_assoc()) {
        $chartData[$rowChart['saleDay']] = $rowChart['saleCount'];
    }
}

echo json_encode($chartData);
?>
