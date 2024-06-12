<?php
session_start();

$cartData = isset($_GET['cart']) ? json_decode(urldecode($_GET['cart']), true) : [];
// Check if all required parameters are set in the URL
if (isset($_GET['itemID'], $_GET['options'], $_GET['size'], $_GET['takeaway'], $_GET['remark'])) {
    // Retrieve parameters from the URL
    $itemID = $_GET['itemID'];
    $options = $_GET['options'];
    $size = $_GET['size'];
    $takeaway = $_GET['takeaway'];
    $remark = urldecode($_GET['remark']);

    // Include database connection
    include 'db.php';

    // Fetch item details based on itemID from cart session
    $itemToUpdate = null;
    if (!empty($_SESSION['cart'])) {
        foreach ($_SESSION['cart'] as $key => $item) {
            // Compare item attributes with parameters from the URL
            if ($item['id'] == $itemID && $item['options'] == $options && $item['size'] == $size && $item['takeaway'] == $takeaway && $item['remark'] == $remark) {
                $itemToUpdate = $_SESSION['cart'][$key];
                break;
            }
        }
    }

    // Handle case where item is not found in cart
    if (!$itemToUpdate) {
        die("Error: Item not found in cart.");
    }

    // Fetch additional item details from the database if needed
    $stmt = $con->prepare("SELECT itemImage, itemName, itemPrice FROM menu_item WHERE itemID = ?");
    $stmt->bind_param("i", $itemID);
    $stmt->execute();
    $stmt->bind_result($itemImage, $itemName, $itemPrice);
    $stmt->fetch();
    $stmt->close();

    // Display the form to edit item details with pre-filled values
    // Ensure HTML and form fields are correctly populated with $itemToUpdate values
} else {
    // Handle case where required parameters are missing
    die("Error: Required parameters are missing.");
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Item Detail</title>
    <link rel="stylesheet" href="customer_style.css">
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f8f9fa;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center; 
            align-items: center; 
            
        }

        .item-details h1 {
                size:8px;
                text-align:left;
                margin-left:60px;
                margin-top:0px;
        }

        .item-details {
            background-color: #ffffff;
            border: 1px solid #dee2e6;
            width: 50%; 
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .item-details h2 {
            font-size: 24px;
            color: #343a40;
            margin-bottom: 10px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-bottom: 10px;
        }

        .item-details p {
            font-size: 18px;
            color: #6c757d;
            margin-bottom: 15px;
        }

        .item-details form {
            margin-top: 20px;
        }

        .item-details label {
            font-weight: bold;
            color: #343a40;
            margin-bottom: 10px;
            display: block;
        }

        .item-details img {
            height: 50px;
            width: auto;
            display: block;
            margin-bottom: 15px;
            margin-top: 20px;
        }

        .item-details input[type="radio"] {
            margin-bottom: 10px;
        }

        .item-details input[type="checkbox"] {
            margin-bottom: 10px;
        }

        .item-details input[type="text"] {
            width: calc(100% - 20px);
            padding: 10px;
            margin-bottom: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 16px;
            box-sizing: border-box;
        }

        .quantity-selector {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
            width: 100%;
        }

        .quantity-selector label {
            margin-right: 10px;
            font-size: 16px;
        }

        .quantity-selector input[type="text"] {
            width: 80px;
            text-align: center;
            margin: 0 10px;
        }

        .quantity-selector button {
            background-color: #411900;
            color: #ffffff;
            border: none;
            border-radius: 0px;
            cursor: pointer;
            font-size: 16px;
            padding: 8px 12px;
        }

        .item-details button {
            background-color: #411900;;
            color: #ffffff;
            padding: 12px 24px;
            border: none;
            border-radius: 50px;
            cursor: pointer;
            font-size: 16px;
            margin-top: 15px;
        }

        .item-details button:hover {
            background-color:  #411900;;
        }

       
        .item-details button[type="submit"] {
            background-color: #411900;
            color: #ffffff;
            padding: 12px 24px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            margin-top: 15px;
            width: 100%; 
        }

        .item-details button[type="submit"]:hover {
            background-color: #411900;;
        }

        .back-button {
            display: inline-block;
            margin-top: 0px;
           
            text-decoration: none;
            color: black; /* Blue color for link */
            font-size: 50px;
            position: absolute;
            top: 0;
           
        }

        @media (max-width: 768px) {

            .back-button {
            top: 10px;
            left: 20px;
            size:30px;
            }

            .item-details h1 {
                size:8px;
                text-align:left;
                margin-left:40px;
                margin-top:0px;
            }
            .container {
                width:90%;
                padding: 10px;
            }

            .item-details {
                padding: 15px;
                width: 90%; 
                margin: 10px auto;
            }

            .item-details h2 {
                font-size: 20px;
            }

            .item-details p {
                font-size: 16px;
            }
            .item-details input[type="radio"]
            .item-details input[type="checkbox"],
            .item-details input[type="text"] {
                font-size: 14px;
            }

            .item-details button {
                font-size: 14px;
            }
        
        }
    </style>
</head>
<body>

<section class="item-details">
    <div class="container">
    <a href="customer_homepage.php?cart=<?php echo urlencode(json_encode($_SESSION['cart'])); ?>" class="back-button">&#x2B05;&#xFE0E;</a>
    <h1>Item details</h1>
        <img src="<?php echo $itemImage; ?>" alt="Item Image" style="width: 100%; height: auto;">
        <form action="customer_updatecart.php?itemID=<?php echo $item['id']; ?>&options=<?php echo $item['options']; ?>&size=<?php echo $item['size']; ?>&takeaway=<?php echo $item['takeaway']; ?>&remark=<?php echo urlencode($item['remark']); ?>" method="post">
            <h2>
                <input type="hidden" name="itemID" value="<?php echo $itemID; ?>">
                <?php echo $itemName; ?>
                <span>Price: RM<span id="itemPrice"><?php echo $itemPrice; ?></span></span>
            </h2><hr>
            <div>
                <label for="options">Options:</label>
                <!-- Replace with your option inputs, pre-selected based on $itemToUpdate['options'] -->
                <div><input type="radio" name="options[]" value="1" <?php if ($itemToUpdate['options'] == '1') echo 'checked'; ?>> Normal</div>
                <div><input type="radio" name="options[]" value="2" <?php if ($itemToUpdate['options'] == '2') echo 'checked'; ?>> Less Sweet</div>
                <div><input type="radio" name="options[]" value="3" <?php if ($itemToUpdate['options'] == '3') echo 'checked'; ?>> No sweet</div>
            </div><hr>

            <div>
                <label for="size">Size:</label>
                <!-- Replace with your size inputs, pre-selected based on $itemToUpdate['size'] -->
                <div><input type="radio" name="size" value="1" <?php if ($itemToUpdate['size'] == '1') echo 'checked'; ?>> Normal</div>
                <div><input type="radio" name="size" value="2" <?php if ($itemToUpdate['size'] == '2') echo 'checked'; ?>> Big (+RM1)</div>
            </div><hr>

            <div class="quantity-selector">
                <label for="quantity">Quantity:</label>
                <hr><span><button type="button" id="decreaseQuantity">-</button>
               <input type="text" name="quantity" id="quantity" value="<?php echo $itemToUpdate['quantity']; ?>" readonly>
                <button type="button" id="increaseQuantity">+</button></span>
            </div><hr>

            <div>
                <label for="takeaway">Takeaway:</label>
                <input type="hidden" name="takeaway" value="1">
                <input type="checkbox" name="takeaway" value="2" <?php if ($itemToUpdate['takeaway'] == '2') echo 'checked'; ?>> FOR DINE IN CUSTOMER ONLY
            </div><hr>

            <div>
                <label for="remark">Remark:</label>
                <input type="text" name="remark" value="<?php echo $itemToUpdate['remark']; ?>">
            </div>

            <input type="hidden" name="finalPrice" id="finalPrice" value="<?php echo $itemToUpdate['finalPrice']; ?>">
            <button name="submit" type="submit">Update Item</button>
            <script>
        document.addEventListener('DOMContentLoaded', function() {
            const decreaseBtn = document.getElementById('decreaseQuantity');
            const increaseBtn = document.getElementById('increaseQuantity');
            const quantityInput = document.getElementById('quantity');
            const sizeRadios = document.querySelectorAll('input[name="size"]');
            const itemPriceSpan = document.getElementById('itemPrice');
            const finalPriceInput = document.getElementById('finalPrice');

            const basePrice = <?php echo $itemPrice; ?>;

            function updatePrice() {
                let finalPrice = basePrice;
                sizeRadios.forEach(radio => {
                    if (radio.checked && radio.value === '2') {
                        finalPrice += 1; // Adjust this calculation based on your actual pricing logic
                    }
                });

                itemPriceSpan.textContent = finalPrice.toFixed(2);
                finalPriceInput.value = finalPrice.toFixed(2);
            }

            sizeRadios.forEach(radio => {
                radio.addEventListener('change', updatePrice);
            });

            decreaseBtn.addEventListener('click', function() {
                let currentQuantity = parseInt(quantityInput.value);
                if (currentQuantity > 1) {
                    quantityInput.value = currentQuantity - 1;
                    updatePrice(); // Update price when quantity changes
                }
            });

            increaseBtn.addEventListener('click', function() {
                let currentQuantity = parseInt(quantityInput.value);
                quantityInput.value = currentQuantity + 1;
                updatePrice(); // Update price when quantity changes
            });

            // Initial price update
            updatePrice();
        });
    </script>
        </form>
        <br>
    </div>
</section>

</body>
</html>
