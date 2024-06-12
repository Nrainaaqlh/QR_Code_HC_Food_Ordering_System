<?php
session_start();
include 'db.php';

// Check if rating is set
if (isset($_POST['rating'])) {
    $rating = (int)$_POST['rating'];
    $comment = isset($_POST['comment']) ? $_POST['comment'] : null;
    
    // Assuming you have a user ID stored in the session
    $custID = isset($_SESSION['custID']) ? $_SESSION['custID'] : null;
    
    if ($custID && $rating > 0 && $rating <= 5) {
        // Save the rating and comment to the database
        $stmt = $conn->prepare("INSERT INTO review (customer_id, rating, comment) VALUES (?, ?, ?)");
        $stmt->bind_param("iis", $custID, $rating, $comment);
        
        if ($stmt->execute()) {
            echo "Review saved successfully!";
        } else {
            echo "Error saving review: " . $stmt->error;
        }
        
        $stmt->close();
    } else {
        echo "Invalid customer ID or rating.";
    }
} 
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
    <title>Star Rating</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            min-height: 50vh;
            display: flex;
            align-items: center;
            text-align: center;
            justify-content: center;
            background: hsl(137, 46%, 24%);
            font-family: "Poppins", sans-serif;
        }
        .card {
            max-width: 33rem;
            background: #fff;
            margin: 0 1rem;
            padding: 1rem;
            box-shadow: 0 0 5px rgba(0, 0, 0, 0.2);
            width: 100%;
            border-radius: 0.5rem;
        }
        .star {
            font-size: 10vh;
            cursor: pointer;
        }
        .one { color: rgb(255, 0, 0); }
        .two { color: rgb(255, 106, 0); }
        .three { color: rgb(251, 255, 120); }
        .four { color: rgb(255, 255, 0); }
        .five { color: rgb(24, 159, 14); }
        .comment-box {
            margin-top: 1rem;
            width: 100%;
            padding: 0.5rem;
            border-radius: 0.5rem;
            border: 1px solid #ccc;
        }
    </style>
</head>
<body>
    <div class="card">
        <h1>JavaScript Star Rating</h1>
        <br />
        <span onclick="gfg(1)" class="star">★</span>
        <span onclick="gfg(2)" class="star">★</span>
        <span onclick="gfg(3)" class="star">★</span>
        <span onclick="gfg(4)" class="star">★</span>
        <span onclick="gfg(5)" class="star">★</span>
        <h3 id="output">Rating is: 0/5</h3>
        <textarea id="comment" class="comment-box" placeholder="Leave a comment..."></textarea>
    </div>
    <script>
        let stars = document.getElementsByClassName("star");
        let output = document.getElementById("output");

        // Function to update rating
        function gfg(n) {
            remove();
            for (let i = 0; i < n; i++) {
                let cls;
                if (n == 1) cls = "one";
                else if (n == 2) cls = "two";
                else if (n == 3) cls = "three";
                else if (n == 4) cls = "four";
                else if (n == 5) cls = "five";
                stars[i].className = "star " + cls;
            }
            output.innerText = "Rating is: " + n + "/5";
            saveRating(n);
        }

        // To remove the pre-applied styling
        function remove() {
            let i = 0;
            while (i < 5) {
                stars[i].className = "star";
                i++;
            }
        }

        // Function to send rating and comment to the server
        function saveRating(rating) {
            const xhr = new XMLHttpRequest();
            xhr.open("POST", "save_rating.php", true);
            xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
            xhr.onreadystatechange = function () {
                if (xhr.readyState === 4 && xhr.status === 200) {
                    console.log(xhr.responseText);
                }
            };
            const comment = document.getElementById("comment").value;
            xhr.send("rating=" + rating + "&comment=" + encodeURIComponent(comment));
        }
    </script>
</body>
</html>
