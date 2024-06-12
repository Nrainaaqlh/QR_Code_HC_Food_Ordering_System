<?php
session_start();
include 'db.php';

global $con;
$searchKeyword = isset($_GET['search']) ? $_GET['search'] : '';
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 25;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;
$startDate = isset($_GET['startDate']) ? $_GET['startDate'] : date('Y-m-01');
$endDate = isset($_GET['endDate']) ? $_GET['endDate'] : date('Y-m-d');

$sql = "SELECT * FROM reviews WHERE (reviewID LIKE '%$searchKeyword%' OR orderID LIKE '%$searchKeyword%' OR rating LIKE '%$searchKeyword%' OR reviewDate LIKE '%$searchKeyword%') AND reviewDate BETWEEN '$startDate' AND '$endDate' LIMIT $limit OFFSET $offset";
$result = $con->query($sql);
$reviews = array();

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $reviews[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Manage Reviews</title>
    <link rel="stylesheet" type="text/css" href="admin_style.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <style>
        .flash-message {
            position: fixed;
            top: 20px;
            left: 85%;
            transform: translateX(-50%);
            z-index: 10000;
            display: none;
            width: 300px;
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

<div class="container-fluid page-wrapper">
    <main role="main" class="col-md-9 ml-sm-auto col-lg-10 px-4">
        <div class="p-3">
            <div class="review-list">
                <h3>Review List</h3>

                <div class="row mb-3">
                    <div class="col-md-4">
                        <form id="searchForm" method="get" enctype="multipart/form-data">
                            <div class="input-group">
                                <input type="text" name="search" id="search" class="form-control" placeholder="Search reviews" value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                                <div class="input-group-append">
                                    <button type="submit" id="searchButton" class="btn btn-primary">Search</button>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="col-md-4">
                        <input type="date" id="startDate" class="form-control" value="<?php echo $startDate; ?>">
                        <input type="date" id="endDate" class="form-control mt-2" value="<?php echo $endDate; ?>">
                    </div>
                    <div class="col-md-4 text-right">
                        <select id="limit" class="form-control">
                            <option value="25" <?php echo $limit == 25 ? 'selected' : ''; ?>>25</option>
                            <option value="50" <?php echo $limit == 50 ? 'selected' : ''; ?>>50</option>
                            <option value="100" <?php echo $limit == 100 ? 'selected' : ''; ?>>100</option>
                        </select>
                    </div>
                </div>

                <table id="reviewTable" class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Review ID</th>
                            <th>Order ID</th>
                            <th>Review Rating</th>
                            <th>Review Date</th>
                        </tr>
                    </thead>
                    <tbody id="reviewTableBody">
                        <?php foreach ($reviews as $review) : ?>
                            <tr>
                                <td><?php echo $review['reviewID']; ?></td>
                                <td><?php echo $review['orderID']; ?></td>
                                <td><?php echo $review['rating']; ?></td>
                                <td><?php echo $review['reviewDate']; ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>

<script>
    $(document).ready(function() {
        function handleSearch() {
            var searchKeyword = $('#search').val();
            var limit = $('#limit').val();
            var startDate = $('#startDate').val();
            var endDate = $('#endDate').val();
            $.ajax({
                url: 'admin_searchReview.php',
                type: 'GET',
                data: {
                    search: searchKeyword,
                    limit: limit,
                    startDate: startDate,
                    endDate: endDate
                },
                success: function(response) {
                    $('#reviewTableBody').html(response);
                },
                error: function(xhr, status, error) {
                    console.error(error);
                }
            });
        }

        $('#search').on('input', function() {
            handleSearch();
        });

        $('#searchButton').click(function(e) {
            e.preventDefault();
            handleSearch();
        });

        $('#search').keypress(function(e) {
            if (e.which === 13) {
                e.preventDefault();
                handleSearch();
            }
        });

        $('#limit').change(function() {
            handleSearch();
        });

        $('#startDate').change(function() {
            handleSearch();
        });

        $('#endDate').change(function() {
            handleSearch();
        });
    });
</script>

<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>

</html>
