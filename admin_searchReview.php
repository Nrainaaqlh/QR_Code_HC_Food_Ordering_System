<?php
session_start();
include 'db.php';

global $con;
$searchKeyword = isset($_GET['search']) ? $_GET['search'] : '';
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 25;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;
$startDate = isset($_GET['startDate']) ? $_GET['startDate'] : date('Y-m-01');
$endDate = isset($_GET['endDate']) ? $_GET['endDate'] : date('Y-m-d');

$sql = "SELECT * FROM reviews WHERE (reviewID LIKE '%$searchKeyword%' OR orderID LIKE '%$searchKeyword%' OR rating LIKE '%$searchKeyword%' OR reviewDate LIKE '%$searchKeyword%') AND reviewDate BETWEEN '$startDate' AND '$endDate' LIMIT $limit OFFSET $offset";
$result = $con->query($sql);
$reviews = array();

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $reviews[] = $row;
    }
} else {
    echo '<tr><td colspan="4">No items found</td></tr>';
}


foreach ($reviews as $review) {
    echo '<tr>';
    echo '<td>' . $review['reviewID'] . '</td>';
    echo '<td>' . $review['orderID'] . '</td>';
    echo '<td>' . $review['rating'] . '</td>';
    echo '<td>' . $review['reviewDate'] . '</td>';
    echo '</tr>';
}
?>
