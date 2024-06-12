<?php
// Include database connection
include 'db.php';

// Check connection
if ($con->connect_error) {
    die("Connection failed: " . $con->connect_error);
}

// Fetch search query and category ID from GET parameters
$searchQuery = isset($_GET['query']) ? $con->real_escape_string($_GET['query']) : '';
$categoryID = isset($_GET['categoryID']) ? intval($_GET['categoryID']) : 0;

// Define the SQL query to fetch items
$sql = "SELECT * FROM menu_item WHERE itemStatus = 1";

// Add conditions based on search query and category ID
if ($categoryID > 0) {
    $sql .= " AND categoryID = $categoryID";
}

if (!empty($searchQuery)) {
    $sql .= " AND itemName LIKE '%$searchQuery%'";
}

// Execute the query to fetch items
$result = $con->query($sql);

// Check if items are found
if ($result->num_rows > 0) {
    // Loop through each item
    while ($row = $result->fetch_assoc()) {
        ?>
        <div class="product">
            <img src="<?php echo $row['itemImage']; ?>" alt="<?php echo htmlspecialchars($row['itemName']); ?>">
            <h3><?php echo htmlspecialchars($row['itemName']); ?></h3>
            <p class="itemPrice">RM<?php echo $row['itemPrice']; ?></p>
            <button onclick="addToCart('<?php echo $row['itemID']; ?>')">Add to Cart</button>
        </div>
        <?php
    }
} else {
    echo "No items found.";
}

// Close connection
$con->close();
?>
