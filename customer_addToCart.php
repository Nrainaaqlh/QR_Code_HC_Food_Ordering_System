<?php
session_start();
include 'db.php';

// Check connection
if ($con->connect_error) {
    die("Connection failed: " . $con->connect_error);
}

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

    // Check if item already exists in cart with the same options and size, update quantity
    $itemFound = false;
    if (!empty($_SESSION['cart'])) {
        foreach ($_SESSION['cart'] as $key => $item) {
            if ($item['id'] == $itemID && $item['options'] == $options && $item['size'] == $size && $item['takeaway'] == $takeaway && $item['remark'] == $remark) {
                $_SESSION['cart'][$key]['quantity'] += $quantity;
                $_SESSION['cart'][$key]['finalPrice'] = $finalPrice; 
                $itemFound = true;
                break;
            }
        }
    }

    // If item is not found in cart, add it as a new item
    if (!$itemFound) {
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
    }

    // Close statement
    $stmt_check_item->close();
}

// Close connection
$con->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cart</title>
    <link rel="stylesheet" href="customer_style.css">
    <style>
        body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f8f9fa; 
            color: #343a40;
            justify-content: center; 
            align-items: center; 
        }

        .container {
            width: 50%;
            height: 100%;
            margin: 10px auto;
            padding: 20px;
            background-color: white;
            box-shadow: 0 0 10px rgba(0,0,0,0.1); /* Soft shadow */
            border-radius: 8px;
        }

        .container h2 {
            margin-top: 15px;
            margin-left:60px;
        }

        .back-button {
            display: inline-block;
            margin-bottom: 10px;
            margin-top: 0px;
            padding:0px;
            text-decoration: none;
            color: black; /* Blue color for link */
            font-size: 50px;
        }

        .cart-item {
            border-bottom: 1px solid #dee2e6; /* Lighter grey border */
            width:100%;
            padding: 15px 0;
            display: flex;
            align-items: center;
        }

        .cart-item img {
            max-width: 100px;
            max-height: 100px;
            margin-right: 20px;
            border-radius: 6px;
        }

        .cart-item .item-details {
            flex: 1;
        }

        .cart-item h3 {
            margin: 0;
            font-size: 18px;
        }

        .cart-item p {
            margin: 5px 0;
            font-size: 14px;
        }

        .quantity-input {
            width: 50px;
            padding: 5px;
            text-align: center;
        }

        .remove-button {
            background-color: #dc3545; /* Red color for remove button */
            color: white;
            border: none;
            padding: 8px 12px;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .remove-button:hover {
            background-color: #c82333; /* Darker red on hover */
        }

        .empty-cart {
            text-align: center;
            font-size: 20px;
            margin-top: 50px;
        }

        .btn-place-order {
            background-color: #411900; /* Green background color */
            color: white; /* White text color */
            border: none; /* Remove default border */
            padding: 10px 20px; /* Padding for better click area */
            font-size: 18px; /* Increase font size */
            border-radius: 5px; /* Rounded corners */
            cursor: pointer; /* Pointer cursor on hover */
            transition: background-color 0.3s; /* Smooth transition for hover effect */
            margin-top: 20px; /* Space above the button */
            display: block; /* Make button a block element */
            width: 100%; /* Full-width button */
            text-align: center; /* Center the text */
        }

        .btn-place-order:hover {
            background-color:  #6d4c41; /* Darker green on hover */
        }

        .back-button {
            display: inline-block;
            margin-top: 20px;
            margin-left: 5px;
            text-decoration: none;
            color: black; /* Blue color for link */
            font-size: 50px;
            position: absolute;
            top: 0;
           
        }

        @media (max-width: 768px) {

            #container h2 {
                size:8px;
        }

            .back-button {
            top: 0px;
            left: 20px;
            size:30px
            }

            .container {
                padding: 10px;
                width:90%;
            }


        }
    </style>
</head>
<body>

<div class="container">
    <a href="customer_homepage.php" class="back-button">&#x2B05;&#xFE0E;</a>
    <h2>Shopping Cart</h2>

    <div id="cart-items">
    <?php
        // Mapping arrays for options, sizes, and takeaway
        $optionsMapping = [
            1 => 'Normal',
            2 => 'Less Sweet',
            3 => 'No Sweet'
            // Add other options as needed
        ];

        $sizeMapping = [
            1 => 'Normal',
            2 => 'Big'
            // Add other sizes as needed
        ];

        $takeawayMapping = [
            1 => 'Takeaway',
            2 => 'Dine In'
            // Add other takeaway options as needed
        ];

        if (!empty($_SESSION['cart']) && count($_SESSION['cart']) > 0) {
            foreach ($_SESSION['cart'] as $key => $item) {
                // Convert options, size, and takeaway to their corresponding descriptions
                $itemOptions = array_map(function($option) use ($optionsMapping) {
                    return $optionsMapping[$option];
                }, explode(",", $item['options']));
                
                $itemSize = isset($sizeMapping[$item['size']]) ? $sizeMapping[$item['size']] : 'Unknown';
                $itemTakeaway = isset($takeawayMapping[$item['takeaway']]) ? $takeawayMapping[$item['takeaway']] : 'Unknown';
                
                ?>
                <div class="cart-item" data-id="<?php echo $item['id']; ?>" data-options="<?php echo $item['options']; ?>" data-size="<?php echo $item['size']; ?>" data-remark="<?php echo $item['remark']; ?>" data-takeaway="<?php echo $item['takeaway']; ?>">
                <img src="<?php echo $item['image']; ?>" alt="<?php echo $item['name']; ?>">
                <div class="item-details">
                    <h3><?php echo $item['name']; ?></h3>
                    <p>Price: RM<?php echo $item['finalPrice']; ?></p>
                    <p>Options: <?php echo implode(", ", $itemOptions); ?></p>
                    <p>Size: <?php echo $itemSize; ?></p>
                    <p>Quantity: <?php echo $item['quantity']; ?></p>
                    <p>Takeaway: <?php echo $itemTakeaway; ?></p>
                    <a href="customer_edititemdetail.php?cart=<?php echo urlencode(json_encode($_SESSION['cart'])); ?>&itemID=<?php echo $item['id']; ?>&options=<?php echo $item['options']; ?>&size=<?php echo $item['size']; ?>&takeaway=<?php echo $item['takeaway']; ?>&remark=<?php echo urlencode($item['remark']); ?>">edit</a>
                    </div>
                    <button class="remove-button">Remove</button>
                </div>
                <?php
            }
            // Order placement button
            ?>
            <form action="customer_placeorder.php" method="post">
                <button type="submit" class="btn-place-order">Place Order</button>
            </form>
            <?php
        } else {
            ?>
            <p class="empty-cart">Your cart is empty</p>
            <?php
        }
        ?>
    </div>
</div>

<script>
function removeFromCart(itemId, options, size, remark, takeaway) {
    // Construct URL with item ID, options, size, remark, and takeaway
    var url = 'customer_removecart.php?id=' + itemId + '&options=' + options + '&size=' + size + '&remark=' + encodeURIComponent(remark) + '&takeaway=' + takeaway;

    // Send AJAX request to remove item from cart
    var xhr = new XMLHttpRequest();
    xhr.onreadystatechange = function() {
        if (xhr.readyState === XMLHttpRequest.DONE) {
            if (xhr.status === 200) {
                var response = JSON.parse(xhr.responseText);
                if (response.status === 'success') {
                    // Remove item element from the cart
                    var itemElement = document.querySelector('.cart-item[data-id="' + itemId + '"][data-options="' + options + '"][data-size="' + size + '"][data-remark="' + remark + '"][data-takeaway="' + takeaway + '"]');
                    if (itemElement) {
                        itemElement.remove();
                    }
                    // Check if cart is empty
                    if (document.querySelectorAll('.cart-item').length === 0) {
                        document.getElementById('cart-items').innerHTML = '<p class="empty-cart">Your cart is empty</p>';
                    }
                } else {
                    console.error(response.message);
                }
            } else {
                console.error('Error removing item from cart');
            }
        }
    };
    xhr.open('GET', url, true);
    xhr.send();
}

// Add event listeners to remove buttons
document.querySelectorAll('.remove-button').forEach(function(button) {
    button.addEventListener('click', function() {
        var itemElement = button.closest('.cart-item');
        var itemId = itemElement.getAttribute('data-id');
        var options = itemElement.getAttribute('data-options');
        var size = itemElement.getAttribute('data-size');
        var remark = itemElement.getAttribute('data-remark');
        var takeaway = itemElement.getAttribute('data-takeaway');
        removeFromCart(itemId, options, size, remark, takeaway);
    });
});
</script>

</body>
</html>
