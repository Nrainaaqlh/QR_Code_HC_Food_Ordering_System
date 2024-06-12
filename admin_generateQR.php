<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QR Generation Form</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="admin_style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            display: flex;
        }
     
        .form-group {
            margin-top: 20px;
            margin-bottom: 20px;
        }
        input[type="submit"] {
            width: auto;
        }
        .qr-frame {
            border: 2px solid #6c757d;
            padding: 15px;
            display: inline-block;
            margin-top: 20px;
            background-color: #411900;
            color: white;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            border-radius: 10px;
            text-align: center;
        }
        .qr-frame img {
            max-width: 80%;
            height: auto;
        }
        .cafe-name {
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 20px;
        }
        .cafe-design {
            margin-top: 20px;
            font-size: 10px;
        }
       
        .print-btn {
            margin-left: 10px;
        }
        @media print {
            body * {
                visibility: hidden;
            }
            .qr-frame, .qr-frame * {
                visibility: visible;
            }
            .qr-frame {
                position: absolute;
                top: 0;
                left: 0;
                width: 100%;
                height: 90%;
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                page-break-before: always;
            }
            .qr-frame img {
                width: 70%;
                height: auto;
                max-width: none;
            }
            .cafe-name {
                font-size: 80px;
                font-weight: bold;
                margin-bottom: 40px;
            }
            .cafe-design {
                margin-top: 30px;
                font-size: 40px;
            }
        }
    </style>
</head>
<body class="fix-header">
    <div class="left-sidebar">
        <div class="unscroll-sidebar">
            <div class="header">
                <h2>HC Cafe</h2>
            </div>
            <div class="sidebar-nav">
                <a href="admin_dashboard.php"><i class="dashboard"></i> Dashboard</a>
                <a href="admin_manageCustomer.php"><i class="manageCustomer"></i> Manage Customers</a>
                <a href="admin_manageCategory.php"><i class="manageCategory"></i> Manage Category</a>
                <a href="admin_manageMenu.php"><i class="manageMenu"></i> Manage Menu</a>
                <a href="admin_manageOrder.php"><i class="manageOrder"></i> Manage Order</a>
                <a href="admin_generateReport.php"><i class="generateReport"></i> Generate Report</a>
                <a href="admin_generateQR.php"><i class="generateQR"></i> Generate QR</a>
                <a href="admin_logout.php"><i class="logout"></i> Log out</a>
            </div>
        </div>
    </div>

    <!-- Main content area -->
    <div class="container-fluid page-wrapper">
        <div class="p-3">
            <h2>QR Generation Form</h2>
                <form id="qrform">
                    <div class="form-group">
                        <label>QR Link</label>
                        <input type="text" name="qrtext" id="qrtext" placeholder="Enter QR Text Link" required data-parsley-pattern="^[a-zA-Z]+$" data-parsley-trigger="keyup" class="form-control" />
                    </div>
                    <div class="form-group d-flex justify-content-start">
                        <input type="submit" name="sbt-btn" value="Generate QR" class="btn btn-success" />
                        <button type="button" onclick="printQR()" class="btn btn-primary print-btn">Print QR Code</button>
                    </div>
                </form>
            <div class="qr-result">
                <!-- QR code and print button will be inserted here -->
            </div>
        </div>

        <?php
        if(isset($_REQUEST['sbt-btn'])) {
            require_once 'db.php';
            require_once 'phpqrcode/qrlib.php';
            $path = 'img/';
            $qrtext = $_REQUEST['qrtext'];
            $qrcode = $path.time().".png";
            $qrimage = time().".png";

            $query = mysqli_query($con, "insert into qrcode set qrtext='$qrtext', qrimage='$qrimage'");
            if($query) {
        ?>

        <?php
            }
            QRcode::png($qrtext, $qrcode, 'H', 4, 4);
            echo "<div class='text-center qr-frame'><div class='cafe-name'>HC Cafe</div><img src='".$qrcode."' alt='QR Code'/><div class='cafe-design'>Enjoy your drink!</div></div>";
        }
        ?>
    </div>
    <script>
        function printQR() {
            var printContents = document.querySelector('.qr-frame').outerHTML;
            var originalContents = document.body.innerHTML;
            document.body.innerHTML = printContents;
            window.print();
            document.body.innerHTML = originalContents;
            location.reload();
        }
    </script>
</body>
</html>
