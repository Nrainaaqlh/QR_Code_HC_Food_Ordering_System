<?php
session_start();

// Set response header to JSON
header('Content-Type: application/json');

$response = ['status' => 'error', 'message' => 'Invalid request'];

// Check if the required parameters are set
if (isset($_GET['id']) && isset($_GET['options']) && isset($_GET['size']) && isset($_GET['remark']) && isset($_GET['takeaway'])) {
    $itemID = $_GET['id'];
    $options = $_GET['options'];
    $size = $_GET['size'];
    $remark = $_GET['remark'];
    $takeaway = $_GET['takeaway'];

    // Loop through the cart to find the matching item
    foreach ($_SESSION['cart'] as $key => $item) {
        if ($item['id'] == $itemID && $item['options'] == $options && $item['size'] == $size && $item['remark'] == $remark && $item['takeaway'] == $takeaway) {
            // Remove the item from the cart
            unset($_SESSION['cart'][$key]);
            // Re-index the array to prevent gaps in the keys
            $_SESSION['cart'] = array_values($_SESSION['cart']);
            $response = ['status' => 'success', 'message' => 'Item removed successfully'];
            break;
        }
    }
}

echo json_encode($response);
exit();
?>