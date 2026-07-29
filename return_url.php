<?php
session_start();
// if (!isset($_SESSION['id'])) {
//     header('Location: ../index.php');
//     exit();
// }


// Extract the payment response parameters
$pidx = $_GET['pidx']; // Payment ID
$purchase_order_id = $_GET['purchase_order_id']; // The unique ID generated previously

header('Location: index.php')

?>
