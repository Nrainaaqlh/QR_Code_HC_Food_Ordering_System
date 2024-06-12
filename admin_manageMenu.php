<?php
session_start();
include 'db.php';

global $con;
$searchKeyword = isset($_GET['search']) ? $_GET['search'] : '';
$sql = "SELECT * FROM menu_item WHERE itemName LIKE '%$searchKeyword%' OR itemDesc LIKE '%$searchKeyword%'";
$result = $con->query($sql);
$menuItems = array();

if (isset($_SESSION['flash_message1'])) {
    $flashMessage1 = $_SESSION['flash_message1'];
    unset($_SESSION['flash_message1']); 
}

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $menuItems[] = $row;
    }
}

$sqlCategories = "SELECT * FROM menu_category";
$resultCategories = $con->query($sqlCategories);

if ($resultCategories->num_rows > 0) {
    while ($row = $resultCategories->fetch_assoc()) {
        $categories[] = $row;
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST["add"])) {
       
        if (isset($_FILES['imageFile']) && $_FILES['imageFile']['error'] == UPLOAD_ERR_OK) {
            $uploadDir = 'img/';
            $uploadFile = $uploadDir . basename($_FILES['imageFile']['name']);
    
            if (move_uploaded_file($_FILES['imageFile']['tmp_name'], $uploadFile)) {
                $itemImage = $uploadFile;
            } else {
                echo "Error uploading file.";
                exit();
            }
        }

        $itemName = $_POST["name"];
        $itemPrice = $_POST["price"];
        $itemDesc = $_POST["description"];
        $categoryID = $_POST["categoryID"];
     
        $itemImage = mysqli_real_escape_string($con, $itemImage);
        $itemName = mysqli_real_escape_string($con, $itemName);
        $itemDesc = mysqli_real_escape_string($con, $itemDesc);
    
        $sqlAdd = "INSERT INTO menu_item (itemImage, itemName, itemPrice, itemDesc, itemStatus, categoryID)
           VALUES ('$itemImage', '$itemName', $itemPrice, '$itemDesc', 1, $categoryID)";

        if ($con->query($sqlAdd)) {
            $_SESSION['flash_message1'] = "Item added successfully!";
            header("Location: {$_SERVER['REQUEST_URI']}");
            exit();
        } else {
            echo "Error: " . $con->error;
        }

    }  elseif (isset($_POST["edit"])) {
        
        if (isset($_FILES['imageFile']) && $_FILES['imageFile']['error'] == UPLOAD_ERR_OK) {
            $uploadDir = 'img/'; 
            $uploadFile = $uploadDir . basename($_FILES['imageFile']['name']);
        
            if (move_uploaded_file($_FILES['imageFile']['tmp_name'], $uploadFile)) {
                $itemImage = $uploadFile;
            } else {
                echo "Error uploading file.";
                exit();
            }
        } else {
            $itemImage = $_POST['currentImage'];
        }

        $itemID = $_POST["edit"];
       
        $itemName = $_POST["name"];
        $itemPrice = $_POST["price"];
        $itemDesc = $_POST["description"];
        $itemStatus = $_POST["status"];
        $categoryID = $_POST["categoryID"];

        $itemImage = mysqli_real_escape_string($con, $itemImage);
        $itemName = mysqli_real_escape_string($con, $itemName);
        $itemDesc = mysqli_real_escape_string($con, $itemDesc);

        $sqlEdit = "UPDATE menu_item SET itemImage = '$itemImage', itemName = '$itemName', itemPrice = $itemPrice, 
             itemDesc = '$itemDesc', itemStatus = '$itemStatus', categoryID = $categoryID
             WHERE itemID = $itemID";

        if ($con->query($sqlEdit)) {
            $_SESSION['flash_message1'] = "Item edited successfully!";
            header("Location: {$_SERVER['REQUEST_URI']}");
            exit();
        }

    } elseif (isset($_POST["delete"])) {
        
        $id = $_POST["delete"];

        $sqlDelete = "DELETE FROM menu_item WHERE itemID = $id";

        if ($con->query($sqlDelete)) {
              $sqlUpdateIDs = "SET @counter = 0;
              UPDATE menu_item SET itemID = @counter := @counter + 1;
              ALTER TABLE menu_item AUTO_INCREMENT = 1;";

            $con->multi_query($sqlUpdateIDs);
        
            $_SESSION['flash_message1'] = "Item deleted successfully!";
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
    <title>Admin Manage Menu</title>
    <link rel="stylesheet" type="text/css" href="admin_style.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>

    <style>
        .flash-message1 {
            position: fixed;
            top: 20px; /* Adjust top position as needed */
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
            
                <?php if (isset($flashMessage1)) : ?>
                        <div id="flashMessage1" class="alert alert-success alert-dismissible fade show flash-message1" role="alert">
                            <?php echo $flashMessage1; ?>
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <script>
                            $(document).ready(function(){
                                
                                $('#flashMessage1').fadeIn().delay(3000).fadeOut('slow');
                            });
                        </script>
                <?php endif; ?>

                <div class="menu-list">
                    <h3>Menu List</h3>

                    <div class="row mb-3">
                    <div class="col-md-6">
                    <form id="searchForm" method="get" enctype="multipart/form-data">
                            <div class="input-group">
                                <input type="text" name="search" id="search" class="form-control"
                                       placeholder="Search items"
                                       value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                                <div class="input-group-append">
                                    <button type="submit" id="searchButton" class="btn btn-primary">Search</button>
                                </div>
                            </div>
                        </form>
                    </div>

                    <div class="col-md-6 text-right">       
                        <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#addModal">Add New</button>  
                    </div>
                </div>
                      
                    <table id="itemTable" class="table table-bordered">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Image</th>
                            <th>Name</th>
                            <th>Price (RM)</th>
                            <th>Description</th>
                            <th>Category</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="itemTableBody">
                        <?php
                            foreach ($menuItems as $item) :
                        ?>

                        <tr>
                            <td><?php echo $item['itemID']; ?></td>
                            <td><img src="<?php echo $item['itemImage']; ?>" alt="" style="max-width: 100px; max-height: 100px;"></td>
                            <td><?php echo $item['itemName']; ?></td>
                            <td><?php echo $item['itemPrice']; ?></td>
                            <td><?php echo $item['itemDesc']; ?></td>
                            <td><?php echo $item['categoryID']; ?></td>
                            <td><?php echo $item['itemStatus'] ? 'Active' : 'Inactive'; ?></td>
                        <td>
                        <div class="btn-group">
                            
                            <button type="button" class="btn btn-warning mr-2" data-toggle="modal" data-target="#editModal<?php echo $item['itemID']; ?>">Edit</button>

                            <div class="actions-form">
                                    <button type="submit" class="btn btn-danger delete-button" 
                                    data-toggle="modal" 
                                    data-target="#deleteModal<?php echo $item['itemID']; ?>">Delete
                                    </button>
                            </div>
                        </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                    </table>
                            </div>
                </div>
            </div>

        <div class="modal fade" id="addModal" tabindex="-1" role="dialog" aria-labelledby="addModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addModalLabel">Add New Menu Item</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span></button>
                    </div>
                    <div class="modal-body">
                        <form method="post" enctype="multipart/form-data">
                        <div class="form-group">
                            <label>Category:</label>
                            <select name="categoryID" class="form-control" required>
                                <?php foreach ($categories as $category) : ?>
                                    <option value="<?php echo $category['categoryID']; ?>"><?php echo $category['categoryName']; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>    
                        <div class="form-group">
                                <label>Image URL:</label>
                                <input type="file" name="imageFile" class="form-control-file" accept="image/*" >
                            </div>
                            <div class="form-group">
                                <label>Name:</label>
                                <input type="text" name="name" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label>Price:</label>
                                <input type="number" name="price" class="form-control" required>
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

        <?php foreach ($menuItems as $item) :   ?>
        <div class="modal fade" id="editModal<?php echo $item['itemID']; ?>" tabindex="-1" role="dialog" aria-labelledby="editModalLabel<?php echo $item['itemID']; ?>" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editModalLabel">Edit Menu Item</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span></button>
                    </div>
                        <div class="modal-body">   
                            <form method="post" enctype="multipart/form-data">
                                <input type="hidden" name="edit" value="<?php echo $item['itemID']; ?>">
                                
                                <div class="form-group">
                                    <label>Category:</label>
                                    <select name="categoryID" class="form-control" required>
                                        <?php foreach ($categories as $category) : ?>
                                            <option value="<?php echo $category['categoryID']; ?>" <?php echo $item['categoryID'] == $category['categoryID'] ? 'selected' : ''; ?>><?php echo $category['categoryName']; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label>Image URL:</label>
                                    <input type="hidden" name="currentImage" value="<?php echo $item['itemImage'];?>">
                                    <input type="file" name="imageFile" class="form-control-file" accept="image/*">
                                </div>
                                <div class="form-group">
                                    <label>Name:</label>
                                    <input type="text" name="name" class="form-control" value="<?php echo $item['itemName']; ?>" required>
                                </div>
                                <div class="form-group">
                                    <label>Price:</label>
                                    <input type="number" name="price" class="form-control" value="<?php echo $item['itemPrice']; ?>" required>
                                </div>
                                <div class="form-group">
                                    <label>Description:</label>
                                    <textarea name="description" class="form-control" required><?php echo $item['itemDesc']; ?></textarea>
                                </div>
                                <div class="form-group">     
                                <label>Status:</label>
                                    <select name="status" class="form-control" required>
                                        <option value="1" <?php echo $item['itemStatus'] == '1' ? 'selected' : ''; ?>>Active</option>
                                        <option value="0" <?php echo $item['itemStatus'] == '0' ? 'selected' : ''; ?>>Inactive</option>
                                    </select>
                                </div>

                                <button type="submit" class="btn btn-primary">Save Changes</button>
                            </form>
                        </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>   

        <?php foreach ($menuItems as $item) : ?>
            <div class="modal fade" id="deleteModal<?php echo $item['itemID']; ?>" tabindex="-1" role="dialog"
                    aria-labelledby="deleteModalLabel<?php echo $item['itemID']; ?>" aria-hidden="true">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="deleteModalLabel<?php echo $item['itemID']; ?>">Confirm
                                    Deletion</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                <p>Are you sure you want to delete this item?</p>
                            </div>
                            <div class="modal-footer">
                                <form method="post">
                                    <input type="hidden" name="delete" value="<?php echo $item['itemID']; ?>">
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
                    url: 'admin_searchitems.php', 
                    type: 'GET',
                    data: {
                        search: searchKeyword
                    },
                    success: function (response) {
                        $('#itemTableBody').html(response);
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
