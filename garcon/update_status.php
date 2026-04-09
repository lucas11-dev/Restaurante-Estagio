<?php
session_start();
include '../includes/config.php';
checkLogin();
if ($_SESSION['tipo'] != 'garcon') {
    header("Location: ../login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $pedido_id = $_POST['pedido_id'];
    $status = $_POST['status'];

    $conn = connectDB();
    $stmt = $conn->prepare("UPDATE pedido SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $status, $pedido_id);
    $stmt->execute();

    // Send notification to client
    $stmt2 = $conn->prepare("SELECT c.utilizador_id FROM pedido p JOIN cliente c ON p.cliente_id = c.id WHERE p.id = ?");
    $stmt2->bind_param("i", $pedido_id);
    $stmt2->execute();
    $cliente = $stmt2->get_result()->fetch_assoc();
    sendNotification($cliente['utilizador_id'], 'status_alterado', "Status do pedido #$pedido_id alterado para $status.", $pedido_id);

    $conn->close();
}

header("Location: index.php");
exit();
?>