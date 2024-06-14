<?php
session_start();
include 'db.php';

// Fetch data for the chart (example: number of reviews per rating for last 30 days)
$sqlChart = "SELECT rating, COUNT(rating) as count FROM reviews GROUP BY rating";
$resultChart = $con->query($sqlChart);
$chartData = array();

if ($resultChart->num_rows > 0) {
    while ($rowChart = $resultChart->fetch_assoc()) {
        $chartData[$rowChart['rating']] = $rowChart['count'];
    }
}

// Prepare JSON response
$response = array(
    'ratings' => $ratings
);

?>