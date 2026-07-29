<?php
session_start();
// if (!isset($_SESSION['id'])) {
//     header('Location: index.php');
//     exit();
// }

include('includes/config.php');


// Extract the necessary information from the form submission
$fullname = $_POST['fullname'];
$services = $_POST['Ride'];
$amount = $_POST['amount']*10; // Amount in paisa
$id = $_POST['id'];
echo $fullname;

// Generate a unique purchase_order_id
$purchase_order_id = uniqid("pay_");

// Prepare the request payload
$data = array(
    'return_url' => 'http://localhost/carrental/message.php', // Update with your return URL
    'website_url' => 'http://localhost/',
    'amount' => $amount,
    'purchase_order_id' => $purchase_order_id,
    'purchase_order_name' => 'Fee',
    'customer_info' => array(
        'name' => $fullname,
        'email' => 'test1@gmail.com', // Replace with actual user email
        'phone' => '9863940406' // Replace with actual user phone
    ),
    'amount_breakdown' => array(
        array('label' => 'Fee', 'amount' => $amount)
    ),
    'product_details' => array(
        array(
            'identity' => '1234567890',
            'name' => 'Fee',
            'total_price' => $amount,
            'quantity' => 1,
            'unit_price' => $amount
        )
    ),
 
    $_SESSION['payment_data'] = array(
        'Ride' => $services,
        'id' => $id,
        'amount' => $amount,
        'fullname' => $fullname,
    ),

);

$payload = json_encode($data);

// Initialize CURL
$ch = curl_init();
curl_setopt_array($ch, array(
    CURLOPT_URL => 'https://a.khalti.com/api/v2/epayment/initiate/',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => $payload,
    CURLOPT_HTTPHEADER => array(
        'Authorization: key live_secret_key_68791341fdd94846a146f0457ff7b455', // Replace with your live or test key
        'Content-Type: application/json',
    ),
));

// Execute CURL request
$response = curl_exec($ch);
curl_close($ch);

$response_data = json_decode($response, true);

if (isset($response_data['payment_url'])) {
    // Redirect to Khalti payment page
    header('Location: ' . $response_data['payment_url']);
    exit();
} else {
    echo 'Error initiating payment. Please try again.';
}
?>
