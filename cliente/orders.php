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

// Get orders
$sql = "SELECT p.id, p.status, p.total, p.criado_em FROM pedido p WHERE p.cliente_id = ? ORDER BY p.criado_em DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $cliente_id);
$stmt->execute();
$orders = $stmt->get_result();

$conn->close();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Meus Pedidos - Cliente</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <header>
        <h1>Meus Pedidos</h1>
        <nav>
            <a href="index.php">Cardápio</a>
            <a href="../logout.php">Sair</a>
        </nav>
    </header>

    <main>
        <table>
            <tr><th>ID</th><th>Status</th><th>Total</th><th>Data</th><th>Ação</th></tr>
            <?php while ($order = $orders->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $order['id']; ?></td>
                    <td><?php echo $order['status']; ?></td>
                    <td>R$ <?php echo number_format($order['total'], 2, ',', '.'); ?></td>
                    <td><?php echo $order['criado_em']; ?></td>
                    <td><a href="view_order.php?id=<?php echo $order['id']; ?>">Ver</a></td>
                </tr>
            <?php endwhile; ?>
        </table>
    </main>
</body>
</html>