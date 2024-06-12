<?php
session_start();
include 'db.php';

$orderID = isset($_GET['order_id']) ? $_GET['order_id'] : (isset($_SESSION['orderID']) ? $_SESSION['orderID'] : null);

if (!$orderID) {
    die('Order ID is missing.'); 
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['rating'])) {
    $rating = $_POST['rating'];
    $reviewDate = date('Y-m-d H:i:s'); 

    $insertReviewSql = "INSERT INTO reviews (orderID, rating, reviewDate) VALUES (?, ?, ?)";
    $stmt = $con->prepare($insertReviewSql);
    $stmt->bind_param("iis", $orderID, $rating, $reviewDate);
    $stmt->execute();
    
    $_SESSION['flash_message'] = 'Thank you for your order and review!';
    header('Location: customer_homepage.php');
    exit();
}

$orderDetailsSql = "SELECT * FROM orders WHERE orderID = ?";
$orderStmt = $con->prepare($orderDetailsSql);
$orderStmt->bind_param("i", $orderID);
$orderStmt->execute();
$orderResult = $orderStmt->get_result();


if ($orderResult->num_rows > 0) {
    $orderData = $orderResult->fetch_assoc();
} else {
    die('Order not found.'); }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Review Order</title>
    <link rel="stylesheet" href="customer_style.css">
    <style>
        body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f8f9fa;
            color: #343a40;
            display: flex;
            justify-content: center; 
            align-items: center; 
        }

        .container {
            max-width: 800px;
            width: 50%;
            margin: 10px auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        h2 {
            text-align: center;
            color: #333;
        }

        .order-summary {
            margin-bottom: 20px;
            padding: 10px;
            background-color: #f9f9f9;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
        }

        .order-summary p {
            margin: 5px 0;
            color: #666;
        }

        .star-rating {
            display: flex;
            flex-direction: row-reverse;
            justify-content: center;
            font-size: 36px;
            margin: 20px 0;
        }

        .star-rating input[type="radio"] {
            display: none;
        }

        .star-rating label {
            cursor: pointer;
            padding: 5px;
            color: #ccc;
            transition: color 0.2s;
        }

        .star-rating input[type="radio"]:checked ~ label {
            color: #ffc107;
        }

        .star-rating label:hover,
        .star-rating label:hover ~ label {
            color: #ffdf00;
        }

        .submit-button {
            display: block;
            width: 100%;
            padding: 10px;
            background-color: #28a745;
            color: #fff;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .submit-button:hover {
            background-color: #218838;
        }

        @media (max-width: 768px) {
            .container {
                padding: 10px;
                width: 90%;
            }

            .star-rating {
                font-size: 28px;
            }

            .submit-button {
                font-size: 14px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Review Your Order</h2>
        <div class="order-summary">
            <p>Order ID: #<?php echo $orderData['orderID']; ?></p>
            <p>Order Date: <?php echo $orderData['orderDate']; ?></p>
        </div>
        <form id="reviewForm" method="post">
            <div class="star-rating">
                <input type="radio" id="star5" name="rating" value="5" required />
                <label for="star5">★</label>
                <input type="radio" id="star4" name="rating" value="4" />
                <label for="star4">★</label>
                <input type="radio" id="star3" name="rating" value="3" />
                <label for="star3">★</label>
                <input type="radio" id="star2" name="rating" value="2" />
                <label for="star2">★</label>
                <input type="radio" id="star1" name="rating" value="1" />
                <label for="star1">★</label>
            </div>
            <button type="submit" class="submit-button">Submit Review</button>
        </form>
    </div>

    <script>
        document.querySelectorAll('.star-rating input[type="radio"]').forEach(function(radio) {
            radio.addEventListener('change', function() {
                var formData = new FormData(document.getElementById('reviewForm'));
                fetch('', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        alert('Thank you for your order and review!');
                        window.location.href = 'customer_homepage.php';
                    } else {
                        alert('An error occurred. Please try again.');
                    }
                });
            });
        });

        var logoutTimer = setTimeout(function() {
            window.location.href = 'customer_logout.php';
        }, 18000);

        document.addEventListener('mousemove', resetLogoutTimer);
        document.addEventListener('keypress', resetLogoutTimer);

        function resetLogoutTimer() {
            clearTimeout(logoutTimer);
            logoutTimer = setTimeout(function() {
                window.location.href = 'customer_logout.php';
            }, 18000);
        }
    </script>
</body>
</html>