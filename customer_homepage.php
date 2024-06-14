<?php
session_start();
include 'db.php';

$totalPrice = 0; // Initialize total price variable
$totalItems = 0; // Initialize total items variable

if (!empty($_SESSION['cart']) && count($_SESSION['cart']) > 0) {
    foreach ($_SESSION['cart'] as $item) {
        $totalPrice += $item['finalPrice'] * $item['quantity']; // Calculate subtotal for each item and accumulate to total price
        $totalItems += $item['quantity']; // Calculate total items in cart
    }
}

$flashMessage = isset($_SESSION['flash_message']) ? $_SESSION['flash_message'] : "";
unset($_SESSION['flash_message']); // Clear the flash message once displayed

// Close connection
$con->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="customer_style.css">
    <title>HC Cafe</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 0;
            background-color: white;
            color: #343a40;
        }

        header {
            background-color: #411900;
            color: #ffffff;
            padding: 10px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin: 0;
        }

        .header .logo {
            font-size: 24px;
            font-weight: bold;
        }
        .header .icons {
            font-size: 20px;
            display: flex;
            gap: 10px;
        }
        .header .icons span {
            cursor: pointer; 
        }

        .header .icons .icon-container {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 35px; 
            height: 35px; 
            border-radius: 50%; 
            background-color: #ffffff; 
            opacity: 70%;
        }

        .header .icons a {
            text-decoration: none; 
        }

        .flash-message {
            background-color: #4CAF50;
            color: white;
            text-align: center;
            padding: 10px;
            margin-bottom: 15px;
        }

        .hero {
            background-color: #f8f9fa;
            padding: 50px 0;
            text-align: center;
        }

        h2{
            margin-top: 5px;
            font-size: 20px;
        }
        .featured-products {
            padding: 10px 0;
            text-align: center;
        }
        .product {
            display: inline-block;
            margin: 0 10px 20px;
            background-color: #ffffff;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            height: 250px; 
            width: 200px;
    
        }

        .product img {
            max-width: 100%;
            height: 50%; 
            object-fit: cover; 
            border-radius: 8px;
        }

        .price {
            color: #007bff;
            font-size: 16px;
            margin: 10px 0;
        }

        .product h3 {
            font-size: 18px;
            line-height: 1.2; 
            max-height: 3.6em; 
            overflow: hidden; 
            text-overflow: ellipsis; 
            display: -webkit-box;
            -webkit-line-clamp: 3; 
            -webkit-box-orient: vertical;
        }

        button {
            background-color: #411900;
            color: #ffffff;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        button:hover {
            background-color: #6d4c41;
        }

        .search-container {
            display: flex;
            width: 80%;
            max-width: 600px;
            margin: 0 auto;
            margin-bottom: 20px;
            margin-top: 0px;
            gap: 0px;
            height: 30px
        }

        .search-container input[type=text] {
            width: 100%;
            padding: 12px 20px;
            box-sizing: border-box;
            border: 1px solid #ccc;
            border-radius: 25px 0px 0px 25px;
            font-size: 15px;
            flex:1;
        }

        .searchbtn {
            padding: 8px 16px;
            border: none;
            background-color: #411900;
            border-radius: 0px 25px 25px 0px;
            color: white;
            cursor: pointer;
            height: 30px;
        }

        .search-container button:hover {
            background-color: #6d4c41;
        }

        .cart-button {
            position: fixed;
            background-color: #411900;
            width: 100%;
            left: 50%;
            transform: translateX(-50%);
            bottom: 0px; 
            padding: 15px 30px ;
            z-index: 999; 
        }

        #greeting {
            position: absolute;
            top: 10px;
            right: 10px;
            font-size: 1.2em;
        }

        .dropdown {
            position: relative;
            display: inline-block;
        }

        .dropbtn {
            padding: 8px 16px;
            border: none;
            background-color: #411900;
            border-radius: 25px;
            color: white;
            cursor: pointer;
            height: 30px;
            min-width: 110px;
            margin-left: 10px;
        }

        .dropdown-content {
            display: none;
            position: absolute;
            background-color: #f9f9f9;
            min-width: 160px;
            box-shadow: 0px 8px 16px 0px rgba(0,0,0,0.2);
            z-index: 1;
        }

        .dropdown-content a {
            color: black;
            padding: 10px 16px;
            text-decoration: none;
            display: block;
        }

        .dropdown-content a:hover {
            background-color: #f1f1f1;
        }

        .dropdown:hover .dropdown-content {
            display: block;
        }

        .dropdown:hover .dropbtn {
            background-color:  #6d4c411;
        }

        .modal {
        display: none; 
        position: fixed;
        z-index: 1; 
        left: 0;
        top: 0;
        border-radius: 8px;
        width: 100%; 
        height: 100%; 
        overflow: auto; 
        background-color: rgba(0,0,0,0.4); 
    }

    .modal-content {
        background-color: #fefefe;
        margin: 15% auto; 
        padding: 20px;
        border: 1px solid #888;
        width: 80%; 
        max-width: 300px;
        text-align: center;
    }

    .close {
        color: #aaa;
        float: right;
        font-size: 28px;
        font-weight: bold;
    }

    .close:hover,
    .close:focus {
        color: black;
        text-decoration: none;
        cursor: pointer;
    }

    .modal-content button {
        background-color: #411900;
        color: white;
        border: none;
        border-radius: 4px;
        padding: 10px;
        margin: 5px;
        cursor: pointer;
    }

    .modal-content button:hover {
        background-color: #6d4c41;
    }
        @media (max-width: 768px){
        
            .header .logo {
                font-size: 18px;
            }
            .header .icons {
                font-size: 15px;
                gap: 10px;
            }

            .header .icons .icon-container {
                width: 25px; 
                height: 25px; 
            }

            h2{
                font-size: 18px;
            }

            .product{
                height: 210px; 
                width: 100px;
            }

            .price {
            color: #007bff;
            font-size: 12px;
            margin: 5px 0;
            }

            .product h3 {
                font-size: 14px;
                max-height: 2.4em; 
            }

            button {
                font-size: 10px;
                padding: 10px 20px;
            }

            .search-container {
                max-width: 400px;
            }

            .cart-button {
                padding: 15px 30px;
                bottom: 0px;
            }
        }

    </style>
</head>
<body>
<header class="header">
    <div class="logo">HC Cafe</div>
    <div class="icons">
        <div class="icon-container">
            <a href="customer_addToCart.php"><span>&#x1F6D2;</span></a>
        </div>

        <?php if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) { ?>
        <div class="icon-container">
            <a href="customer_logout.php"><span>&#x1F6AA;</span></a> <!-- Door emoji for logout -->
        </div>
        <?php } ?>
    </div>
</header>

<section class="featured-products">
    <div class="container">
        <h2>Menu</h2>

        <?php if ($flashMessage): ?>
            <div class="flash-message">
                <?php echo $flashMessage; ?>
            </div>
        <?php endif; ?>

        <div class="search-container">
    <input type="text" id="searchInput" placeholder="Search...">
    <button class="searchbtn" type="button" onclick="handleSearch()">Search</button>
    <div class="dropdown">
        <button id="dropdownButton" class="dropbtn">Select Category</button>
        <div id="dropdownContent" class="dropdown-content">
            <a href="#" onclick="loadFullMenu(); updateDropdownButton('All')">All</a>
            <?php
            include 'db.php';
            $categorySql = "SELECT mc.categoryID, mc.categoryName
                            FROM menu_category mc
                            JOIN menu_item mi ON mc.categoryID = mi.categoryID
                            WHERE mi.itemStatus = 1
                            GROUP BY mc.categoryID, mc.categoryName";
            $categoryResult = $con->query($categorySql);
            if ($categoryResult->num_rows > 0) {
                while ($categoryRow = $categoryResult->fetch_assoc()) {
                    echo "<a href='#' onclick='filterByCategory(" . $categoryRow['categoryID'] . "); updateDropdownButton(\"" . $categoryRow['categoryName'] . "\")'>" . $categoryRow['categoryName'] . "</a>";
                }
            }
            ?>
        </div>
    </div>
</div>

        <div id="searchResults"></div>
    </div>
    <button class="cart-button" onclick="window.location.href='customer_addToCart.php'">
    Total Items: <?php echo $totalItems; ?> | Total Price: RM <?php echo number_format($totalPrice, 2); ?>
</button>

</form>


    <div id="authModal" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <p>Are you already registered?</p>
        <button onclick="redirectToLogin()">Yes, Log In</button>
        <button onclick="redirectToRegister()">No, Register</button>
    </div>
</div>

<script>

document.addEventListener("DOMContentLoaded", function() {
            // Function to toggle the dropdown content when the dropdown button is clicked
            function toggleDropdown() {
                var dropdownContent = document.getElementById("dropdownContent");
                if (dropdownContent.style.display === "block") {
                    dropdownContent.style.display = "none";
                } else {
                    dropdownContent.style.display = "block";
                }
            }
            
            // Attach click event listener to the dropdown button
            document.getElementById("dropdownButton").addEventListener("click", function() {
                toggleDropdown();
            });

            // Close the dropdown if the user clicks outside of it
            window.onclick = function(event) {
                if (!event.target.matches('.dropbtn')) {
                    var dropdowns = document.getElementsByClassName("dropdown-content");
                    for (var i = 0; i < dropdowns.length; i++) {
                        var openDropdown = dropdowns[i];
                        if (openDropdown.style.display === "block") {
                            openDropdown.style.display = "none";
                        }
                    }
                }
            }
        });

function filterByCategory(categoryID) {
    let xhr = new XMLHttpRequest();
    xhr.open('GET', 'customer_search.php?categoryID=' + categoryID, true);

    xhr.onload = function() {
        if (xhr.status == 200) {
            document.getElementById('searchResults').innerHTML = xhr.responseText;
        } else {
            console.error('Error filtering by category: ' + xhr.status);
        }
    };

    xhr.send();
}

function addToCart(itemID) {
    var isLoggedIn = <?php echo isset($_SESSION['custID']) ? 'true' : 'false'; ?>;

    if (!isLoggedIn) {
        showModal();
    } else {
        window.location.href = 'customer_itemdetail.php?itemID=' + itemID;
    }
}

function showModal() {
    var modal = document.getElementById("authModal");
    modal.style.display = "block";
}

function closeModal() {
    var modal = document.getElementById("authModal");
    modal.style.display = "none";
}

function redirectToLogin() {
    window.location.href = 'customer_login.php';
}

function redirectToRegister() {
    window.location.href = 'customer_register.php';
}

function updateDropdownButton(categoryName) {
    document.getElementById('dropdownButton').innerText = categoryName;
}

window.onclick = function(event) {
    var modal = document.getElementById("authModal");
    if (event.target == modal) {
        modal.style.display = "none";
    }
}

document.querySelector(".close").onclick = function() {
    closeModal();
}


function showTotalPrice() {
    window.location.href = 'customer_addToCart.php';
}

function loadFullMenu() {
    let xhr = new XMLHttpRequest();
    xhr.open('GET', 'customer_search.php', true);

    xhr.onload = function() {
        if (xhr.status == 200) {
            document.getElementById('searchResults').innerHTML = xhr.responseText;
        } else {
            console.error('Error fetching full menu: ' + xhr.status);
        }
    };

    xhr.send();
}

document.getElementById('searchInput').addEventListener('input', function() {
    let searchQuery = this.value.trim();
    if (searchQuery.length > 0) {
        let xhr = new XMLHttpRequest();
        xhr.open('GET', 'customer_search.php?query=' + encodeURIComponent(searchQuery), true);

        xhr.onload = function() {
            if (xhr.status == 200) {
                document.getElementById('searchResults').innerHTML = xhr.responseText;
            } else {
                console.error('Error fetching search results: ' + xhr.status);
            }
        };

        xhr.send();
    } else {
        loadFullMenu();
    }
});

window.onload = loadFullMenu;

    setTimeout(function() {
    var flashMessage = document.querySelector('.flash-message');
    if (flashMessage) {
        flashMessage.style.display = 'none';
    }
    }, 3000);  // 30 seconds in milliseconds


</script>
</body>
</html>