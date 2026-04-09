<?php
session_start();
include '../includes/config.php';
checkLogin();
if ($_SESSION['tipo'] != 'admin' && $_SESSION['tipo'] != 'gerente') {
    header("Location: ../login.php");
    exit();
}

$conn = connectDB();

// Get menu items
$sql_items = "SELECT i.*, c.nome as categoria FROM item_cardapio i JOIN categoria cat ON i.categoria_id = cat.id JOIN cardapio c ON cat.cardapio_id = c.id WHERE c.ativo = 1";
$items = $conn->query($sql_items);

// Get orders
$sql_orders = "SELECT p.id, u.nome as cliente, p.status, p.total, p.criado_em FROM pedido p JOIN cliente cl ON p.cliente_id = cl.id JOIN utilizador u ON cl.utilizador_id = u.id ORDER BY p.criado_em DESC LIMIT 10";
$orders = $conn->query($sql_orders);

$conn->close();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Painel Admin</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <header>
        <h1>Painel Administrativo</h1>
        <nav>
            <a href="../logout.php">Sair</a>
        </nav>
    </header>

    <main>
        <h2>Gerenciar Cardápio</h2>
        <a href="add_item.php">Adicionar Item</a>
        <table>
            <tr><th>ID</th><th>Nome</th><th>Categoria</th><th>Preço</th><th>Disponível</th><th>Ações</th></tr>
            <?php while ($item = $items->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $item['id']; ?></td>
                    <td><?php echo $item['nome']; ?></td>
                    <td><?php echo $item['categoria']; ?></td>
                    <td>R$ <?php echo number_format($item['preco'], 2, ',', '.'); ?></td>
                    <td><?php echo $item['disponivel'] ? 'Sim' : 'Não'; ?></td>
                    <td>
                        <a href="edit_item.php?id=<?php echo $item['id']; ?>">Editar</a>
                        <a href="toggle_item.php?id=<?php echo $item['id']; ?>"><?php echo $item['disponivel'] ? 'Desabilitar' : 'Habilitar'; ?></a>
                    </td>
                </tr>
            <?php endwhile; ?>
        </table>

        <h2>Pedidos Recentes</h2>
        <table>
            <tr><th>ID</th><th>Cliente</th><th>Status</th><th>Total</th><th>Data</th></tr>
            <?php while ($order = $orders->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $order['id']; ?></td>
                    <td><?php echo $order['cliente']; ?></td>
                    <td><?php echo $order['status']; ?></td>
                    <td>R$ <?php echo number_format($order['total'], 2, ',', '.'); ?></td>
                    <td><?php echo $order['criado_em']; ?></td>
                </tr>
            <?php endwhile; ?>
        </table>

        <h2>Relatórios</h2>
        <a href="reports.php">Ver Relatórios</a>
    </main>
</body>
</html>