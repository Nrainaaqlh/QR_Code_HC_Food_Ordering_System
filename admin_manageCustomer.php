<?php
session_start();
include 'db.php';

global $con;
$searchKeyword = isset($_GET['search']) ? $_GET['search'] : '';
$sql = "SELECT * FROM customers WHERE custName LIKE '%$searchKeyword%' OR custPhoneNum LIKE '%$searchKeyword%'";
$result = $con->query($sql);
$customers = array();

if (isset($_SESSION['flash_message'])) {
    $flashMessage = $_SESSION['flash_message'];
    unset($_SESSION['flash_message']); // Clear the session after displaying the message
}

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $customers[] = $row;
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST["add"])) {
        $custName = $_POST["name"];
        $custPhoneNum = $_POST["phone"]; 
        $custEmail = $_POST["email"]; 

        $custName = mysqli_real_escape_string($con, $custName);
        $custPhoneNum = mysqli_real_escape_string($con, $custPhoneNum);

        $sqlAdd = "INSERT INTO customers (custName, custEmail, custPhoneNum) VALUES ('$custName', '$custEmail', '$custPhoneNum')";

        if ($con->query($sqlAdd)) {
            $_SESSION['flash_message'] = "Customer added successfully!";
            header("Location: {$_SERVER['REQUEST_URI']}");
            exit();
        } else {
            echo "Error: " . $con->error;
        }

    } elseif (isset($_POST["edit"])) {
        $custID = $_POST["edit"];
        $custName = $_POST["name"];
        $custPhoneNum = $_POST["phone"]; 
        $custEmail = $_POST["email"]; 

        $custName = mysqli_real_escape_string($con, $custName);
        $custPhoneNum = mysqli_real_escape_string($con, $custPhoneNum);

        $sqlEdit = "UPDATE customers SET custName = '$custName', custEmail = '$custEmail', custPhoneNum = '$custPhoneNum' WHERE custID = $custID";

        if ($con->query($sqlEdit)) {
            $_SESSION['flash_message'] = "Customer edited successfully!";
            header("Location: {$_SERVER['REQUEST_URI']}");
            exit();
        }

    } elseif (isset($_POST["delete"])) {
        $custID = $_POST["delete"];

        $sqlDelete = "DELETE FROM customers WHERE custID = $custID";

        if ($con->query($sqlDelete)) {
            $sqlUpdateIDs = "SET @counter = 0;
            UPDATE customers SET custID = @counter := @counter + 1;
            ALTER TABLE customers AUTO_INCREMENT = 1;";

            $con->multi_query($sqlUpdateIDs);

            $_SESSION['flash_message'] = "Customer deleted successfully!";
            header("Location: {$_SERVER['REQUEST_URI']}");
            exit();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Manage Customers</title>
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
           
                <script> <?php if (isset($flashMessage)) : ?>
                <div id="flashMessage" class="alert alert-success alert-dismissible fade show flash-message" role="alert">
                    <?php echo $flashMessage; ?>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                    $(document).ready(function () {
                        $('#flashMessage').fadeIn().delay(3000).fadeOut('slow');
                    });
                </script>
            <?php endif; ?>

            <div class="customer-list">
                <h3>Customer List</h3>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <form id="searchForm" method="get" enctype="multipart/form-data">
                            <div class="input-group">
                                <input type="text" name="search" id="search" class="form-control"
                                       placeholder="Search customers"
                                       value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                                <div class="input-group-append">
                                    <button type="submit" id="searchButton" class="btn btn-primary">Search</button>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="col-md-6 text-right">
                        <button type="button" class="btn btn-primary" data-toggle="modal"
                                data-target="#addModal">Add New
                        </button>
                    </div>
                </div>

                <table id="customerTable" class="table table-bordered">
                    <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Phone</th>
                        <th>Email</th>
                        <th>Actions</th>
                    </tr>
                    </thead>
                    <tbody id="customerTableBody">
                    <?php foreach ($customers as $customer) : ?>
                        <tr>
                            <td><?php echo $customer['custID']; ?></td>
                            <td><?php echo $customer['custName']; ?></td>
                            <td><?php echo $customer['custPhoneNum']; ?></td>
                            <td><?php echo $customer['custEmail']; ?></td>
                            <td>
                                <div class="btn-group">
                                    <button type="button" class="btn btn-warning mr-2"
                                            data-toggle="modal"
                                            data-target="#editModal<?php echo $customer['custID']; ?>">Edit
                                    </button>

                                    <div class="actions-form">
                                    <button type="submit" class="btn btn-danger delete-button" 
                                    data-toggle="modal" 
                                    data-target="#deleteModal<?php echo $customer['custID']; ?>">Delete
                                    </button>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>

<div class="modal fade" id="addModal" tabindex="-1" role="dialog" aria-labelledby="addModalLabel"
     aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addModalLabel">Add New Customer</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form method="post">
                    <div class="form-group">
                        <label>Name:</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Phone:</label>
                        <input type="text" name="phone" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Email:</label>
                        <input type="text" name="email" class="form-control" required>
                    </div>
                    <button type="submit" name="add" class="btn btn-primary">Add</button>
                </form>
            </div>
        </div>
    </div>
</div>


<!-- Edit Modal -->
<?php foreach ($customers as $customer) : ?>
<div class="modal fade" id="editModal<?php echo $customer['custID']; ?>" tabindex="-1" role="dialog" aria-labelledby="editModalLabel<?php echo $customer['custID']; ?>" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editModalLabel<?php echo $customer['custID']; ?>">Edit Customer</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form method="post">
                    <input type="hidden" name="edit" value="<?php echo $customer['custID']; ?>">
                    <div class="form-group">
                        <label>Name:</label>
                        <input type="text" name="name" class="form-control" value="<?php echo $customer['custName']; ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Phone:</label>
                        <input type="text" name="phone" class="form-control" value="<?php echo $customer['custPhoneNum']; ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Email:</label>
                        <input type="text" name="email" class="form-control" value="<?php echo $customer['custEmail']; ?>" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </form>
            </div>
        </div>
    </div>
</div>
<?php endforeach; ?>

<!-- Delete Modal -->
<?php foreach ($customers as $customer) : ?>
<div class="modal fade" id="deleteModal<?php echo $customer['custID']; ?>" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel<?php echo $customer['custID']; ?>" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel<?php echo $customer['custID']; ?>">Delete Customer</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                Are you sure you want to delete this customer?
            </div>
            <div class="modal-footer">
                <form method="post">
                    <input type="hidden" name="delete" value="<?php echo $customer['custID']; ?>">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>
<?php endforeach; ?>

<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<script>
$(document).ready(function () {
    // Flash message timeout
    $('#flashMessage').fadeIn().delay(3000).fadeOut('slow');

    // Delete confirmation
    $('.delete-button').on('click', function () {
        var custId = $(this).data('id');
        $('#deleteCustomerId').val(custId);
        $('#deleteModal').modal('show');
    });

    function handleSearch() {
            var searchKeyword = $('#search').val();
            $.ajax({
                url: 'admin_searchcustomers.php', 
                type: 'GET',
                data: {
                    search: searchKeyword
                },
                success: function (response) {
                    $('#customerTableBody').html(response);
                },
                error: function (xhr, status, error) {
                    console.error(error);
                }
            });
        }

        $('#search').on('input', function () {
            handleSearch();
        });

        $('#searchButton').click(function (e) {
            e.preventDefault();
            handleSearch();
        });

        $('#search').keypress(function (e) {
            if (e.which === 13) { 
                e.preventDefault();
                handleSearch();
            }
        });

});


</script>
</body>
</html>

