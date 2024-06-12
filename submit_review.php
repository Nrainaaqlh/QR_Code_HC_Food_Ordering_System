<?php
session_start();
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $orderID = $_POST['order_id'];
    $rating = $_POST['rating'];

    // Validate input
    if (!is_numeric($orderID) || !in_array($rating, array(1, 2, 3, 4, 5))) {
        die(json_encode(array('error' => 'Invalid data submitted')));
    }

    // Check if a review already exists for this order
    $checkReviewSql = "SELECT * FROM reviews WHERE orderID = ?";
    $checkReviewStmt = $con->prepare($checkReviewSql);
    $checkReviewStmt->bind_param("i", $orderID);
    $checkReviewStmt->execute();
    $checkReviewResult = $checkReviewStmt->get_result();

    if ($checkReviewResult->num_rows > 0) {
        die(json_encode(array('error' => 'Review already submitted for this order')));
    }

    // Insert new review
    $insertReviewSql = "INSERT INTO reviews (orderID, rating, reviewDate) VALUES (?, ?, NOW())";
    $insertReviewStmt = $con->prepare($insertReviewSql);
    $insertReviewStmt->bind_param("ii", $orderID, $rating);

    if ($insertReviewStmt->execute()) {
        // Successfully inserted review
        echo json_encode(array('success' => true));

        // Optionally, set a session timeout or any other action after successful review submission
        $_SESSION['timeout'] = time() + (5 * 60); // Example: Session timeout set to 5 minutes

        // Log out session and redirect to customer homepage
        session_unset();    // Unset all session variables
        session_destroy();  // Destroy the session data
        header("Location: customer_homepage.php"); // Redirect to customer homepage
        exit(); // Ensure no further output is sent
    } else {
        // Failed to insert review
        echo json_encode(array('error' => 'Failed to submit review'));
    }
}
?>
