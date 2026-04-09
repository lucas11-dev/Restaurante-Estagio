<?php
session_start();
include '../includes/config.php';
checkLogin();

$id = $_GET['id'];
if (isset($_SESSION['cart'][$id])) {
    unset($_SESSION['cart'][$id]);
}

header("Location: cart.php");
exit();
?>