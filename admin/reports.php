<?php
session_start();
include '../includes/config.php';
checkLogin();
if ($_SESSION['tipo'] != 'admin' && $_SESSION['tipo'] != 'gerente') {
    header("Location: ../login.php");
    exit();
}

$conn = connectDB();

// Total orders
$total_orders = $conn->query("SELECT COUNT(*) as total FROM pedido")->fetch_assoc()['total'];

// Revenue
$revenue = $conn->query("SELECT SUM(total) as revenue FROM pedido WHERE status = 'entregue'")->fetch_assoc()['revenue'] ?? 0;

// Top items
$top_items = $conn->query("SELECT ic.nome, SUM(ip.quantidade) as qty FROM item_pedido ip JOIN item_cardapio ic ON ip.item_cardapio_id = ic.id GROUP BY ic.id ORDER BY qty DESC LIMIT 5");

$conn->close();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Relatórios - Admin</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <header>
        <h1>Relatórios</h1>
        <nav>
            <a href="index.php">Voltar</a>
        </nav>
    </header>

    <main>
        <p>Total de Pedidos: <?php echo $total_orders; ?></p>
        <p>Receita Total (Entregues): R$ <?php echo number_format($revenue, 2, ',', '.'); ?></p>

        <h2>Itens Mais Vendidos</h2>
        <table>
            <tr><th>Item</th><th>Quantidade</th></tr>
            <?php while ($item = $top_items->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $item['nome']; ?></td>
                    <td><?php echo $item['qty']; ?></td>
                </tr>
            <?php endwhile; ?>
        </table>
    </main>
</body>
</html>