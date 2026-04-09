<?php
session_start();
include '../includes/config.php';
checkLogin();
if ($_SESSION['tipo'] != 'garcon') {
    header("Location: ../login.php");
    exit();
}

$conn = connectDB();

// Get pending orders
$sql = "SELECT p.id, c.telefone, p.status, p.total, p.criado_em FROM pedido p JOIN cliente c ON p.cliente_id = c.id WHERE p.status IN ('pendente', 'em_preparacao') ORDER BY p.criado_em DESC";
$orders = $conn->query($sql);

$conn->close();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Painel Garçon</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <header>
        <h1>Pedidos - Garçon</h1>
        <nav>
            <a href="../logout.php">Sair</a>
        </nav>
    </header>

    <main>
        <h2>Pedidos Ativos</h2>
        <table>
            <tr><th>ID</th><th>Cliente</th><th>Status</th><th>Total</th><th>Data</th><th>Ação</th></tr>
            <?php while ($order = $orders->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $order['id']; ?></td>
                    <td><?php echo $order['telefone']; ?></td>
                    <td><?php echo $order['status']; ?></td>
                    <td>R$ <?php echo number_format($order['total'], 2, ',', '.'); ?></td>
                    <td><?php echo $order['criado_em']; ?></td>
                    <td>
                        <a href="view_order.php?id=<?php echo $order['id']; ?>">Ver</a>
                        <?php if ($order['status'] == 'pendente'): ?>
                            <a href="update_status.php?id=<?php echo $order['id']; ?>&status=em_preparacao">Iniciar Preparação</a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endwhile; ?>
        </table>
    </main>
</body>
</html>