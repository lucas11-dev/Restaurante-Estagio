<?php
session_start();
include '../includes/config.php';
checkLogin();
if ($_SESSION['tipo'] != 'cliente') {
    header("Location: ../login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $item_id = $_POST['item_id'];
    $quantidade = $_POST['quantidade'];

    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    // Get item details
    $conn = connectDB();
    $stmt = $conn->prepare("SELECT nome, preco FROM item_cardapio WHERE id = ? AND disponivel = 1");
    $stmt->bind_param("i", $item_id);
    $stmt->execute();
    $item = $stmt->get_result()->fetch_assoc();
    $conn->close();

    if ($item) {
        $_SESSION['cart'][$item_id] = [
            'nome' => $item['nome'],
            'preco' => $item['preco'],
            'quantidade' => (isset($_SESSION['cart'][$item_id]) ? $_SESSION['cart'][$item_id]['quantidade'] + $quantidade : $quantidade)
        ];
    }
}

header("Location: index.php");
exit();
?>