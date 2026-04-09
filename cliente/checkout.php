<?php
session_start();
include '../includes/config.php';
checkLogin();
if ($_SESSION['tipo'] != 'cliente') {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$conn = connectDB();

// Get cliente_id
$stmt = $conn->prepare("SELECT id FROM cliente WHERE utilizador_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$cliente = $stmt->get_result()->fetch_assoc();
$cliente_id = $cliente['id'];

$cart = $_SESSION['cart'] ?? [];
if (empty($cart)) {
    header("Location: cart.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $tipo = $_POST['tipo'];
    $observacao = $_POST['observacao'];
    $total = 0;
    foreach ($cart as $item) {
        $total += $item['preco'] * $item['quantidade'];
    }

    // Insert pedido
    $stmt = $conn->prepare("INSERT INTO pedido (cliente_id, tipo, total, observacao) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isds", $cliente_id, $tipo, $total, $observacao);
    $stmt->execute();
    $pedido_id = $stmt->insert_id;

    // Insert item_pedido
    foreach ($cart as $item_id => $item) {
        $stmt2 = $conn->prepare("INSERT INTO item_pedido (pedido_id, item_cardapio_id, quantidade, preco_unit) VALUES (?, ?, ?, ?)");
        $stmt2->bind_param("iiid", $pedido_id, $item_id, $item['quantidade'], $item['preco']);
        $stmt2->execute();
    }

    // Insert pagamento simulação
    $stmt3 = $conn->prepare("INSERT INTO pagamento (pedido_id, metodo, status, valor) VALUES (?, 'simulacao', 'aprovado', ?)");
    $stmt3->bind_param("id", $pedido_id, $total);
    $stmt3->execute();

    // Send notification
    sendNotification($user_id, 'pedido_confirmado', "Seu pedido #$pedido_id foi confirmado.", $pedido_id);

    // Clear cart
    unset($_SESSION['cart']);

    header("Location: order_success.php?pedido_id=$pedido_id");
    exit();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Checkout - Cliente</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <header>
        <h1>Finalizar Pedido</h1>
        <nav>
            <a href="cart.php">Voltar ao Carrinho</a>
        </nav>
    </header>

    <main>
        <p>Confirme seu pedido.</p>
        <form method="post">
            <button type="submit">Confirmar</button>
        </form>
    </main>
</body>
</html>