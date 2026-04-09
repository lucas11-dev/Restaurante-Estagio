<?php
session_start();
include '../includes/config.php';
checkLogin();
if ($_SESSION['tipo'] != 'admin' && $_SESSION['tipo'] != 'gerente') {
    header("Location: ../login.php");
    exit();
}

$id = $_GET['id'];
$conn = connectDB();
$stmt = $conn->prepare("UPDATE item_cardapio SET disponivel = NOT disponivel WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$conn->close();

header("Location: index.php");
exit();
?>