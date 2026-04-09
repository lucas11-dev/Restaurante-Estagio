<?php
session_start();
include '../includes/config.php';
checkLogin();
if ($_SESSION['tipo'] != 'cliente') {
    header("Location: ../login.php");
    exit();
}

$conn = connectDB();
$user_id = $_SESSION['user_id'];

// Get cliente_id
$stmt = $conn->prepare("SELECT id FROM cliente WHERE utilizador_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$cliente = $stmt->get_result()->fetch_assoc();
$cliente_id = $cliente['id'];

// Buscar cardápio ativo
$sql = "SELECT c.id as cat_id, c.nome as cat_nome, i.id, i.nome, i.descricao, i.preco, i.foto_url
        FROM categoria c
        JOIN item_cardapio i ON c.id = i.categoria_id
        WHERE c.ativo = 1 AND i.disponivel = 1
        ORDER BY c.ordem, i.nome";
$result = $conn->query($sql);

// Agrupar por categoria
$menu = [];
while ($row = $result->fetch_assoc()) {
    $menu[$row['cat_nome']][] = $row;
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Cardápio - Cliente</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <header>
        <h1>Cardápio</h1>
        <nav>
            <a href="cart.php">Carrinho</a>
            <a href="orders.php">Meus Pedidos</a>
            <a href="../logout.php">Sair</a>
        </nav>
    </header>

    <main>
        <?php foreach ($menu as $categoria => $itens): ?>
            <h2><?php echo $categoria; ?></h2>
            <div class="menu-items">
                <?php foreach ($itens as $item): ?>
                    <div class="item">
                        <?php if ($item['foto_url']): ?>
                            <img src="<?php echo $item['foto_url']; ?>" alt="<?php echo $item['nome']; ?>">
                        <?php endif; ?>
                        <h3><?php echo $item['nome']; ?></h3>
                        <p><?php echo $item['descricao']; ?></p>
                        <p>R$ <?php echo number_format($item['preco'], 2, ',', '.'); ?></p>
                        <form action="add_to_cart.php" method="post">
                            <input type="hidden" name="item_id" value="<?php echo $item['id']; ?>">
                            <input type="number" name="quantidade" value="1" min="1">
                            <button type="submit">Adicionar ao Carrinho</button>
                        </form>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endforeach; ?>
    </main>

    <script src="../js/script.js"></script>
</body>
</html>