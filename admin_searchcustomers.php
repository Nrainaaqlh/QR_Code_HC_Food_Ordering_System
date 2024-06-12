<?php
session_start();
include 'db.php';

global $con;

$searchKeyword = isset($_GET['search']) ? $_GET['search'] : '';
$sql = "SELECT * FROM customers WHERE custName LIKE '%$searchKeyword%' OR custID LIKE '%$searchKeyword'";
$result = $con->query($sql);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo '<tr>';
        echo '<td>' . $row['custID'] . '</td>';
        echo '<td>' . $row['custName'] . '</td>';
        echo '<td>' . $row['custPhoneNum'] . '</td>';
        echo '<td>' . $row['custEmail'] . '</td>';
        echo '<td>
                <div class="btn-group">
                    <button type="button" class="btn btn-warning mr-2" data-toggle="modal" data-target="#editModal' . $row['custID'] . '">Edit</button>
                    <form method="post" class="actions-form">
                        <input type="hidden" name="delete" value="' . $row['custID'] . '">
                        <button type="submit" class="btn btn-danger">Delete</button>
                    </form>
                </div>
            </td>';
        echo '</tr>';
    }
} else {
    echo '<tr><td colspan="4">No customers found</td></tr>';
}
?>
