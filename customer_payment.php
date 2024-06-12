<?php
session_start();
require 'vendor/autoload.php'; // Ensure you have Stripe SDK installed via Composer
include 'db.php';

// Check if cart is empty
if (empty($_SESSION['cart']) || count($_SESSION['cart']) == 0) {
    die(json_encode(['error' => 'Your cart is empty.']));
}

// Assuming the user is logged in and you have their ID stored in a session
$custID = isset($_SESSION['custID']) ? $_SESSION['custID'] : null;

// Check if custID is available
if (!$custID) {
    die(json_encode(['error' => 'Error: Customer ID not found in session.']));
}

// Calculate the total price
$totalPrice = isset($_POST['totalPrice']) ? (int)$_POST['totalPrice'] : 0;
if ($totalPrice <= 0) {
    die(json_encode(['error' => 'Invalid total price.']));
}

// Stripe API configuration
\Stripe\Stripe::setApiKey('sk_test_51PLne32LPA7wBM3DIip4cOzdJHoCo1Xba4wvxmUZ7Ppngwscj2HMm7dcxdqTjJ6MjLHoR6Odw8shjCxPLqiRitXV00jUWGpuEI'); // Replace with your actual secret key

try {
    // Create Stripe Checkout session
    $line_items = array_map(function($item) {
        $product_data = [
            'name' => $item['name'],
        ];
        if (!empty($item['description'])) {
            $product_data['description'] = $item['description'];
        }
        if (!empty($item['image'])) {
            $product_data['images'] = [$item['image']];
        }

        return [
            'price_data' => [
                'currency' => 'myr',
                'product_data' => $product_data,
                'unit_amount' => $item['finalPrice'] * 100,
            ],
            'quantity' => $item['quantity'],
        ];
    }, $_SESSION['cart']);

    $session = \Stripe\Checkout\Session::create([
        'payment_method_types' => ['card','fpx','grabpay'],
        'line_items' => $line_items,
        'mode' => 'payment',
        'success_url' => 'http://localhost/project_PSM/customer_submitorder.php?session_id={CHECKOUT_SESSION_ID}&order=' . urlencode(json_encode($_SESSION['cart'])),
        'cancel_url' => 'http://localhost/project_PSM/customer_placeholder.php',
    ]);

    // Return JSON response with Stripe Checkout URL
    echo json_encode(['url' => $session->url]);
} catch (Exception $e) {
    // Return JSON response with error message
    echo json_encode(['error' => $e->getMessage()]);
}
?>