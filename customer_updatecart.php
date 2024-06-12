<?php
session_start();
include 'db.php'; // Assuming this file contains your database connection

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Assuming the user is logged in and you have their ID stored in a session
    $custID = isset($_SESSION['custID']) ? $_SESSION['custID'] : null;

    // Check if custID is available
    if (!$custID) {
        die("Error: Customer ID not found in session.");
    }

    // Retrieve form data
    $itemID = isset($_POST['itemID']) ? $_POST['itemID'] : '';
    $quantity = isset($_POST['quantity']) ? $_POST['quantity'] : '';
    $options = isset($_POST['options']) ? implode(",", $_POST['options']) : "";
    $size = isset($_POST['size']) ? $_POST['size'] : '';
    $remark = isset($_POST['remark']) ? $_POST['remark'] : "";
    $takeaway = isset($_POST['takeaway']) ? $_POST['takeaway'] : "";
    $finalPrice = isset($_POST['finalPrice']) ? $_POST['finalPrice'] : '';

    // Fetch item details from the database
    $stmt = $con->prepare("SELECT itemImage, itemName, itemPrice FROM menu_item WHERE itemID = ?");
    $stmt->bind_param("i", $itemID);
    $stmt->execute();
    $stmt->bind_result($itemImage, $itemName, $itemPrice);
    $stmt->fetch();
    $stmt->close();

    // Check if the item already exists in the cart
    $itemFound = false;
    if (!empty($_SESSION['cart'])) {
        foreach ($_SESSION['cart'] as $key => $item) {
            // Compare item attributes with parameters from the form
            if ($item['id'] == $itemID) {
                if ($item['options'] == $options && $item['size'] == $size && $item['remark'] == $remark && $item['takeaway'] == $takeaway) {
                    // Update quantity and final price if all attributes match
                    $_SESSION['cart'][$key]['quantity'] = $quantity;
                    $_SESSION['cart'][$key]['finalPrice'] = $finalPrice;
                    $itemFound = true;
                    break;
                } elseif ($item['size'] == $size && $item['remark'] == $remark && $item['takeaway'] == $takeaway) {
                    // Update options, quantity, and final price if size, remark, and takeaway match
                    $_SESSION['cart'][$key]['options'] = $options;
                    $_SESSION['cart'][$key]['quantity'] = $quantity;
                    $_SESSION['cart'][$key]['finalPrice'] = $finalPrice;
                    $itemFound = true;
                    break;
                } elseif ($item['options'] == $options && $item['remark'] == $remark && $item['takeaway'] == $takeaway) {
                    // Update size, quantity, and final price if options, remark, and takeaway match
                    $_SESSION['cart'][$key]['size'] = $size;
                    $_SESSION['cart'][$key]['quantity'] = $quantity;
                    $_SESSION['cart'][$key]['finalPrice'] = $finalPrice;
                    $itemFound = true;
                    break;
                } elseif ($item['options'] == $options && $item['size'] == $size && $item['takeaway'] == $takeaway) {
                    // Update remark, quantity, and final price if options, size, and takeaway match
                    $_SESSION['cart'][$key]['remark'] = $remark;
                    $_SESSION['cart'][$key]['quantity'] = $quantity;
                    $_SESSION['cart'][$key]['finalPrice'] = $finalPrice;
                    $itemFound = true;
                    break;
                } elseif ($item['options'] == $options && $item['size'] == $size && $item['remark'] == $remark) {
                    // Update takeaway, quantity, and final price if options, size, and remark match
                    $_SESSION['cart'][$key]['takeaway'] = $takeaway;
                    $_SESSION['cart'][$key]['quantity'] = $quantity;
                    $_SESSION['cart'][$key]['finalPrice'] = $finalPrice;
                    $itemFound = true;
                    break;
                }
            }
        }
    }

    // If the item was not found in the cart, add it
    if (!$itemFound) {
        // Prepare SQL statement to check if itemID exists in menu_item table
        $stmt_check_item = $con->prepare("SELECT itemName, itemPrice, itemImage FROM menu_item WHERE itemID = ?");
        if (!$stmt_check_item) {
            die("Error preparing statement: " . $con->error);
        }

        $stmt_check_item->bind_param("i", $itemID);
        $stmt_check_item->execute();
        $stmt_check_item->bind_result($itemName, $itemPrice, $itemImage);
        $stmt_check_item->store_result();

        // Check if itemID exists in the database
        if ($stmt_check_item->num_rows == 0) {
            die("Error: Item with ID $itemID not found in menu_item table.");
        }

        // Fetch item details
        $stmt_check_item->fetch();

        // Add item to cart with details
        $_SESSION['cart'][] = array(
            'id' => $itemID,
            'name' => $itemName,
            'price' => $itemPrice,
            'image' => $itemImage,
            'quantity' => $quantity,
            'options' => $options,
            'size' => $size,
            'remark' => $remark,
            'takeaway' => $takeaway,
            'finalPrice' => $finalPrice
        );

        // Close statement
        $stmt_check_item->close();
    }

    // Redirect back to cart page after updating or adding item
    header("Location: customer_addToCart.php");
    exit();
}

// Close connection
$con->close();
?>
