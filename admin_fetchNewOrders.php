<?php
session_start();
include 'db.php'; // Include your database connection file

// Mapping arrays for options, size, and takeaway
$optionsMapping = [
    1 => 'Normal',
    2 => 'Less Sweet',
    3 => 'No Sweet'
    // Add other options as needed
];

$sizeMapping = [
    1 => 'Normal',
    2 => 'Big'
    // Add other sizes as needed
];

$takeawayMapping = [
    1 => 'Yes',
    2 => 'No'
    // Add other takeaway options as needed
];

// Function to fetch order items based on order ID
function fetchOrderItems($orderID) {
    global $con, $optionsMapping, $sizeMapping, $takeawayMapping;

    $orderItems = [];
    $sql = "SELECT order_items.*, menu_item.itemName 
            FROM order_items 
            INNER JOIN menu_item ON order_items.itemID = menu_item.itemID 
            WHERE order_items.orderID = ?";
    $stmt = $con->prepare($sql);
    $stmt->bind_param("i", $orderID);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        // Map options, size, and takeaway to human-readable format
        $row['options'] = isset($optionsMapping[$row['options']]) ? $optionsMapping[$row['options']] : 'Unknown';
        $row['size'] = isset($sizeMapping[$row['size']]) ? $sizeMapping[$row['size']] : 'Unknown';
        $row['takeaway'] = isset($takeawayMapping[$row['takeaway']]) ? $takeawayMapping[$row['takeaway']] : 'Unknown';
        
        $orderItems[] = $row;
    }

    return $orderItems;
}

// Function to fetch orders based on status
function fetchOrders($status) {
    global $con;

    $orders = [];
    $sql = "SELECT * FROM orders WHERE orderStatus = ?";
    $stmt = $con->prepare($sql);
    $stmt->bind_param("s", $status);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $row['orderItems'] = fetchOrderItems($row['orderID']); // Fetch order items for each order
        $orders[] = $row;
    }

    return $orders;
}

// Fetch orders based on 'Order income' status
$ordersIncome = fetchOrders('Income');

// Generate HTML for displaying orders
$html = '';
foreach ($ordersIncome as $order) {
    foreach ($order['orderItems'] as $index => $item) {
        $html .= '<tr>';
        if ($index === 0) { // First item in the order, include order details
            $html .= '<td rowspan="' . count($order['orderItems']) . '">' . $order['orderID'] . '</td>';
            $html .= '<td rowspan="' . count($order['orderItems']) . '">' . $order['custID'] . '</td>';
        }
        $html .= '<td>' . $item['itemName'] . ' - Size: ' . $item['size'] . ', Quantity: ' . $item['quantity'] . ', Options: ' . $item['options'] . ', Remark: ' . $item['remark'] . ', Takeaway: ' . $item['takeaway'] . '</td>';
        if ($index === 0) { // First item in the order, include order details
            $html .= '<td rowspan="' . count($order['orderItems']) . '">' . $order['totalAmount'] . '</td>';
            $html .= '<td rowspan="' . count($order['orderItems']) . '">' . $order['orderDate'] . '</td>';
            $html .= '<td rowspan="' . count($order['orderItems']) . '"><a href="admin_receiveOrder.php?order_id=' . $order['orderID'] . '">Update Status</a></td>';
        }
        $html .= '</tr>';
    }
}


// Output the generated HTML
echo $html;
?>
