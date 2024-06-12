<?php
session_start();
include 'db.php';

global $con;
$searchKeyword = isset($_GET['search']) ? $_GET['search'] : '';
$sql = "SELECT * FROM menu_category WHERE categoryName LIKE '%$searchKeyword%' OR categoryDesc LIKE '%$searchKeyword%'";
$result = $con->query($sql);
$menuCategorys = array();

if (isset($_SESSION['flash_message'])) {
    $flashMessage = $_SESSION['flash_message'];
    unset($_SESSION['flash_message']); // Clear the session after displaying the message
}

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $menuCategorys[] = $row;
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST["add"])) {
        $categoryName = $_POST["name"];
        $categoryDesc= $_POST["description"]; 

        $categoryName = mysqli_real_escape_string($con, $categoryName);
        $categoryDesc = mysqli_real_escape_string($con, $categoryDesc);

        $sqlAdd = "INSERT INTO menu_category (categoryName, categoryDesc) VALUES ('$categoryName', '$categoryDesc')";

        if ($con->query($sqlAdd)) {
            $_SESSION['flash_message'] = "Category added successfully!";
            header("Location: {$_SERVER['REQUEST_URI']}");
            exit();
        } else {
            echo "Error: " . $con->error;
        }

    } elseif (isset($_POST["edit"])) {
        $categoryID = $_POST["edit"];
        $categoryName = $_POST["name"];
        $categoryDesc= $_POST["description"]; 

        $categoryName = mysqli_real_escape_string($con, $categoryName);
        $categoryDesc = mysqli_real_escape_string($con, $categoryDesc);

        $sqlEdit = "UPDATE menu_category SET categoryName = '$categoryName', categoryDesc = '$categoryDesc' WHERE categoryID = $categoryID";

        if ($con->query($sqlEdit)) {
            $_SESSION['flash_message'] = "Category edited successfully!";
            header("Location: {$_SERVER['REQUEST_URI']}");
            exit();
        }

    } elseif (isset($_POST["delete"])) {
        $id = $_POST["delete"];

        $sqlDelete = "DELETE FROM menu_category WHERE categoryID = $id";

        if ($con->query($sqlDelete)) {
            $sqlUpdateIDs = "SET @counter = 0;
            UPDATE menu_category SET categoryID = @counter := @counter + 1;
            ALTER TABLE menu_category AUTO_INCREMENT = 1;";

            $con->multi_query($sqlUpdateIDs);

            $_SESSION['flash_message'] = "Category deleted successfully!";
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
    <title>Admin Manage Categories</title>
    <link rel="stylesheet" type="text/css" href="admin_style.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <style>
        .flash-message {
            position: fixed;
            top: 20px; /* Adjust top position as needed */
            left: 85%; /* Center horizontally */
            transform: translateX(-50%); /* Center horizontally */
            z-index: 10000; /* Ensure it's above other content */
            display: none; /* Initially hidden */
            width: 300px; /* Adjust width as needed */
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
        <?php if (isset($flashMessage)) : ?>
                        <div id="flashMessage" class="alert alert-success alert-dismissible fade show flash-message" role="alert">
                            <?php echo $flashMessage; ?>
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <script>
                            $(document).ready(function(){
                                // Show the flash message
                                $('#flashMessage').fadeIn().delay(3000).fadeOut('slow');
                            });
                        </script>
                    <?php endif; ?>
            
                <div class="category-list">
                <h3>Category List</h3>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <form id="searchForm" method="get" enctype="multipart/form-data">
                            <div class="input-group">
                                <input type="text" name="search" id="search" class="form-control"
                                       placeholder="Search categories"
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


                <table id="categoryTable" class="table table-bordered">
                    <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Description</th>
                        <th>Actions</th>
                    </tr>
                    </thead>
                    <tbody id="categoryTableBody">
                    <?php foreach ($menuCategorys as $category) : ?>
                        <tr>
                            <td><?php echo $category['categoryID']; ?></td>
                            <td><?php echo $category['categoryName']; ?></td>
                            <td><?php echo $category['categoryDesc']; ?></td>
                            <td>
                                <div class="btn-group">
                                    <button type="button" class="btn btn-warning mr-2"
                                            data-toggle="modal"
                                            data-target="#editModal<?php echo $category['categoryID']; ?>">Edit
                                    </button>

                                    <div class="actions-form">
                                    <button type="submit" class="btn btn-danger delete-button" 
                                    data-toggle="modal" 
                                    data-target="#deleteModal<?php echo $category['categoryID']; ?>">Delete
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
                <h5 class="modal-title" id="addModalLabel">Add New Menu Category</h5>
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
                        <label>Description:</label>
                        <textarea name="description" class="form-control" required></textarea>
                    </div>
                    <button type="submit" name="add" class="btn btn-primary">Add</button>
                </form>
            </div>
        </div>
    </div>
</div>


<?php foreach ($menuCategorys as $category) : ?>
    <div class="modal fade" id="editModal<?php echo $category['categoryID']; ?>" tabindex="-1" role="dialog"
         aria-labelledby="editModalLabel<?php echo $category['categoryID']; ?>" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editModalLabel<?php echo $category['categoryID']; ?>">Edit Menu
                        Category</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">

                    <form method="post">
                        <input type="hidden" name="edit" value="<?php echo $category['categoryID']; ?>">
                        <div class="form-group">
                            <label>Name:</label>
                            <input type="text" name="name" class="form-control"
                                   value="<?php echo $category['categoryName']; ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Description:</label>
                            <textarea name="description" class="form-control"
                                      required><?php echo $category['categoryDesc']; ?></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>

    
    <?php foreach ($menuCategorys as $category) : ?>
<div class="modal fade" id="deleteModal<?php echo $category['categoryID']; ?>" tabindex="-1" role="dialog"
         aria-labelledby="deleteModalLabel<?php echo $category['categoryID']; ?>" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteModalLabel<?php echo $category['categoryID']; ?>">Confirm
                        Deletion</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete this category?</p>
                </div>
                <div class="modal-footer">
                    <form method="post">
                        <input type="hidden" name="delete" value="<?php echo $category['categoryID']; ?>">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-danger">Delete</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
<?php endforeach; ?>

<script>
    $(document).ready(function () {

        function handleSearch() {
            var searchKeyword = $('#search').val();
            $.ajax({
                url: 'admin_searchcategories.php', 
                type: 'GET',
                data: {
                    search: searchKeyword
                },
                success: function (response) {
                    $('#categoryTableBody').html(response);
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

<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>

</html>
