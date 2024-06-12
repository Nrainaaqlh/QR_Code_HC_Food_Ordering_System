<?php
session_start();
include 'db.php';

// Check if cart is empty
if (empty($_SESSION['cart']) || count($_SESSION['cart']) == 0) {
    die("Your cart is empty.");
}

// Assuming the user is logged in and you have their ID stored in a session
$custID = isset($_SESSION['custID']) ? $_SESSION['custID'] : null;

// Check if custID is available
if (!$custID) {
    die("Error: Customer ID not found in session.");
}

// Calculate the total price
$totalPrice = 0;
foreach ($_SESSION['cart'] as $item) {
    $totalPrice += $item['finalPrice'] * $item['quantity'];
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Summary</title>
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
            margin: 10px auto;
            padding: 20px;
            background-color: white;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            border-radius: 8px;
        }

        .container h2 {
            margin-top: 5px;
            margin-left:60px;
        }


        .cart-item {
            border-bottom: 1px solid #dee2e6;
            padding: 10px 0;
            display: flex;
            align-items: center;
        }

        .cart-item h4 {
            margin: 0;
            font-size: 16px;
        }

        .cart-item p {
            margin: 5px 0;
            font-size: 16px;
        }

        .btn-place-order {
            background-color: #411900;
            color: white;
            border: none;
            padding: 10px 20px;
            font-size: 18px;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
            margin-top: 20px;
            display: block;
            width: 100%;
            text-align: center;
        }

        .btn-place-order:hover {
            background-color:  #6d4c41;
        }

        .payment-method {
            margin-top: 20px;
        }

        .payment-method label {
            display: block;
            margin-bottom: 10px;
        }

        .payment-method input[type="radio"] {
            margin-right: 10px;
        }

        .back-button {
            display: inline-block;
            margin-top: 10px;
            text-decoration: none;
            color: black;
            font-size: 50px;
            position: absolute;
            top: 0;
        }

        .price-container {
            display: flex;
            justify-content: space-between; /* Aligns items with space between them */
            align-items: center; /* Centers items vertically */
        }



        @media (max-width: 768px) {
            .container {
                padding: 10px;
                width: 90%;
            }

            .back-button {
            top: 0px;
            left: 20px;
            size:30px
            }

            
            .cart-item h4 {
                margin: 0;
                size: 12px;
            }
            .container h2 {
                size:8px;
                margin-top: 10px;
        }
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Order Summary</h2>
    <a href="customer_addTocart.php" class="back-button">&#x2B05;&#xFE0E;</a>
    <div id="order-summary">
        <?php foreach ($_SESSION['cart'] as $item) : ?>
            <div class="cart-item">
                <h4><?php echo $item['name']; ?> x <?php echo $item['quantity']; ?></h4>
                <hr><span><p>RM<?php echo $item['finalPrice']; ?></p>
                
            </div>
        <?php endforeach; ?>
    </div>
    <div class="price-container">
        <p><b>Total Price:</b></p>
        <p id="total"> RM<?php echo $totalPrice; ?></p>
    </div>

    <div class="payment-method">
        <b><hr></b>
        <h3>Payment Method</h3>
        <form id="payment-form">
            <label><input type="radio" name="payment-method" value="stripe" checked> Stripe (fpx, card, grabpay)</label>
            <button type="button" id="proceed-to-payment" class="btn-place-order">Pay</button>
        </form>
    </div>
</div>

<script src="https://js.stripe.com/v3/"></script>
<script>
document.getElementById('proceed-to-payment').addEventListener('click', function() {
    var totalPrice = <?php echo $totalPrice; ?>;
    var xhr = new XMLHttpRequest();
    xhr.open('POST', 'customer_payment.php', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.onreadystatechange = function() {
        if (xhr.readyState === XMLHttpRequest.DONE) {
            if (xhr.status === 200) {
                var response = JSON.parse(xhr.responseText);
                if (response.url) {
                    window.location.href = response.url;
                } else if (response.error) {
                    alert(response.error);
                }
            } else {
                console.error('Error processing payment', xhr.status, xhr.statusText);
            }
        }
    };
    xhr.send('totalPrice=' + totalPrice);
});

</script>

</body>
</html>