<?php
session_start();
include '../includes/config.php';
checkLogin();
if ($_SESSION['tipo'] != 'cliente') {
    header("Location: ../login.php");
    exit();
}

$pedido_id = $_GET['id'];
$user_id = $_SESSION['user_id'];
$conn = connectDB();

// Get cliente_id
$stmt = $conn->prepare("SELECT id FROM cliente WHERE utilizador_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$cliente = $stmt->get_result()->fetch_assoc();
$cliente_id = $cliente['id'];

// Get order
$stmt = $conn->prepare("SELECT * FROM pedido WHERE id = ? AND cliente_id = ?");
$stmt->bind_param("ii", $pedido_id, $cliente_id);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();

if (!$order) {
    header("Location: orders.php");
    exit();
}

// Get items
$stmt2 = $conn->prepare("SELECT ip.*, ic.nome FROM item_pedido ip JOIN item_cardapio ic ON ip.item_cardapio_id = ic.id WHERE ip.pedido_id = ?");
$stmt2->bind_param("i", $pedido_id);
$items = $stmt2->get_result();

$conn->close();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Ver Pedido - Cliente</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <header>
        <h1>Pedido #<?php echo $order['id']; ?></h1>
        <nav>
            <a href="orders.php">Meus Pedidos</a>
        </nav>
    </header>

    <main>
        <p>Status: <?php echo $order['status']; ?></p>
        <p>Total: R$ <?php echo number_format($order['total'], 2, ',', '.'); ?></p>
        <p>Data: <?php echo $order['criado_em']; ?></p>
        <p>Observação: <?php echo $order['observacao']; ?></p>

        <h2>Itens</h2>
        <table>
            <tr><th>Item</th><th>Quantidade</th><th>Preço Unitário</th><th>Total</th></tr>
            <?php while ($item = $items->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $item['nome']; ?></td>
                    <td><?php echo $item['quantidade']; ?></td>
                    <td>R$ <?php echo number_format($item['preco_unit'], 2, ',', '.'); ?></td>
                    <td>R$ <?php echo number_format($item['preco_unit'] * $item['quantidade'], 2, ',', '.'); ?></td>
                </tr>
            <?php endwhile; ?>
        </table>
    </main>
</body>
</html>