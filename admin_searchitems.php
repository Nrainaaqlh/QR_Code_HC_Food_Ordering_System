<?php
session_start();
include 'db.php'; 

global $con;

$searchKeyword = isset($_GET['search']) ? $_GET['search'] : '';
$sql = "SELECT * FROM menu_item WHERE itemName LIKE '%$searchKeyword%' OR itemDesc LIKE '%$searchKeyword%'";
$result = $con->query($sql);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        
        echo '<tr>';
        echo '<td>' . $row['itemID'] . '</td>';
        echo '<td><img src="' . $row['itemImage'] . '" alt="" style="max-width: 100px; max-height: 100px;"></td>';
        echo '<td>' . $row['itemName'] . '</td>';
        echo '<td>' . $row['itemPrice'] . '</td>';
        echo '<td>' . $row['itemDesc'] . '</td>';
        echo '<td>' . $row['categoryID'] . '</td>';
        echo '<td>' . ($row['itemStatus'] ? 'Active' : 'Inactive') . '</td>';
        echo '<td>
                <div class="btn-group">
                    <button type="button" class="btn btn-warning mr-2" data-toggle="modal" data-target="#editModal' . $row['itemID'] . '">Edit</button>
                    <form method="post" class="actions-form">
                        <input type="hidden" name="delete" value="' . $row['itemID'] . '">
                        <button type="submit" class="btn btn-danger">Delete</button>
                    </form>
                </div>
              </td>';
        echo '</tr>';
    }
} else {
    echo '<tr><td colspan="7">No items found</td></tr>';
}
?>
