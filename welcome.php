<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="customer_style.css">
    <title>Welcome to HC Cafe</title>
    <style>
        /* Add your custom styles here */
        body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 0;
            background-color: white;
            color: #343a40;
        }

        header {
            color: white;
            background-color: #411900;
            padding: 20px 0;
            text-align: center;
            font-size: 24px;
            font-weight: bold;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        .hero {
            background-color: white;
            color: black;
            padding: 150px 0;
            text-align: center;
        }

        .hero p {
            font-size: 24px;
            margin-bottom: 20px;
            line-height: 1.5;
        }

        .start-ordering-btn {
            background-color: #411900;
            color: #ffffff;
            font-size: 18px;
            padding: 15px 30px;
            text-decoration: none;
            border-radius: 8px;
            transition: background-color 0.3s;
            display: inline-block;
        }

        .start-ordering-btn:hover {
            background-color: #004080;
        }

        /* Media queries for mobile view */
        @media (max-width: 600px) {
            .hero p {
                font-size: 18px;
            }

            .start-ordering-btn {
                font-size: 16px;
                padding: 12px 24px;
            }
        }

    </style>
</head>
<body>

    <header>
        <div class="container">
            <h1>Welcome to HC Cafe</h1>
        </div>
    </header>

    <section class="hero">
        <div class="container">
            <p>Discover our delicious menu and start your order now!</p>
            <a href="customer_login.php" class="start-ordering-btn">Start Ordering</a>
        </div>
    </section>

</body>
</html>
