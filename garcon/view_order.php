<?php
session_start();
include '../includes/config.php';
checkLogin();
if ($_SESSION['tipo'] != 'garcon') {
    header("Location: ../login.php");
    exit();
}

$pedido_id = $_GET['id'];
$conn = connectDB();

// Get order
$stmt = $conn->prepare("SELECT p.*, c.telefone FROM pedido p JOIN cliente c ON p.cliente_id = c.id WHERE p.id = ?");
$stmt->bind_param("i", $pedido_id);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();

if (!$order) {
    header("Location: index.php");
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
    <title>Ver Pedido - Garçon</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <header>
        <h1>Pedido #<?php echo $order['id']; ?></h1>
        <nav>
            <a href="index.php">Pedidos</a>
        </nav>
    </header>

    <main>
        <p>Cliente: <?php echo $order['telefone']; ?></p>
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

        <form action="update_status.php" method="post">
            <input type="hidden" name="pedido_id" value="<?php echo $order['id']; ?>">
            <label>Novo Status:
                <select name="status">
                    <option value="em_preparacao" <?php if ($order['status'] == 'em_preparacao') echo 'selected'; ?>>Em Preparação</option>
                    <option value="pronto" <?php if ($order['status'] == 'pronto') echo 'selected'; ?>>Pronto</option>
                    <option value="entregue" <?php if ($order['status'] == 'entregue') echo 'selected'; ?>>Entregue</option>
                </select>
            </label>
            <button type="submit">Atualizar</button>
        </form>
    </main>
</body>
</html>