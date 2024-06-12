<?php
session_start();
include 'db.php';

global $con;

$searchKeyword = isset($_GET['search']) ? $_GET['search'] : '';
$sql = "SELECT * FROM menu_category WHERE categoryName LIKE '%$searchKeyword%' OR categoryDesc LIKE '%$searchKeyword%'";
$result = $con->query($sql);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo '<tr>';
        echo '<td>' . $row['categoryID'] . '</td>';
        echo '<td>' . $row['categoryName'] . '</td>';
        echo '<td>' . $row['categoryDesc'] . '</td>';
        echo '<td>
                <div class="btn-group">
                    <button type="button" class="btn btn-warning mr-2" data-toggle="modal" data-target="#editModal' . $row['categoryID'] . '">Edit</button>
                    <form method="post" class="actions-form">
                        <input type="hidden" name="delete" value="' . $row['categoryID'] . '">
                        <button type="submit" class="btn btn-danger">Delete</button>
                    </form>
                </div>
            </td>';
        echo '</tr>';
    }
} else {
    echo '<tr><td colspan="4">No categories found</td></tr>';
}
?>
