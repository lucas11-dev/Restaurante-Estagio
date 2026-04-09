<?php
session_start();
include '../includes/config.php';
checkLogin();
if ($_SESSION['tipo'] != 'cliente') {
    header("Location: ../login.php");
    exit();
}

$cart = $_SESSION['cart'] ?? [];
$total = 0;
foreach ($cart as $item) {
    $total += $item['preco'] * $item['quantidade'];
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Carrinho - Cliente</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <header>
        <h1>Carrinho</h1>
        <nav>
            <a href="index.php">Cardápio</a>
            <a href="../logout.php">Sair</a>
        </nav>
    </header>

    <main>
        <?php if (empty($cart)): ?>
            <p>Seu carrinho está vazio.</p>
        <?php else: ?>
            <table>
                <tr><th>Item</th><th>Quantidade</th><th>Preço Unitário</th><th>Total</th><th>Ação</th></tr>
                <?php foreach ($cart as $id => $item): ?>
                    <tr>
                        <td><?php echo $item['nome']; ?></td>
                        <td><?php echo $item['quantidade']; ?></td>
                        <td>R$ <?php echo number_format($item['preco'], 2, ',', '.'); ?></td>
                        <td>R$ <?php echo number_format($item['preco'] * $item['quantidade'], 2, ',', '.'); ?></td>
                        <td><a href="remove_from_cart.php?id=<?php echo $id; ?>">Remover</a></td>
                    </tr>
                <?php endforeach; ?>
                <tr><td colspan="3">Total</td><td>R$ <?php echo number_format($total, 2, ',', '.'); ?></td><td></td></tr>
            </table>
            <form action="checkout.php" method="post">
                <label>Tipo: 
                    <select name="tipo">
                        <option value="mesa">Mesa</option>
                        <option value="takeaway">Takeaway</option>
                    </select>
                </label><br>
                <label>Observação: <textarea name="observacao"></textarea></label><br>
                <button type="submit">Finalizar Pedido</button>
            </form>
        <?php endif; ?>
    </main>
</body>
</html>