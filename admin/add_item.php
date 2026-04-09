<?php
session_start();
include '../includes/config.php';
checkLogin();
if ($_SESSION['tipo'] != 'admin' && $_SESSION['tipo'] != 'gerente') {
    header("Location: ../login.php");
    exit();
}

$conn = connectDB();

// Get categories
$categories = $conn->query("SELECT id, nome FROM categoria WHERE ativo = 1");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $categoria_id = $_POST['categoria_id'];
    $nome = $_POST['nome'];
    $descricao = $_POST['descricao'];
    $preco = $_POST['preco'];
    $foto_url = $_POST['foto_url'];

    $stmt = $conn->prepare("INSERT INTO item_cardapio (categoria_id, nome, descricao, preco, foto_url) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("issds", $categoria_id, $nome, $descricao, $preco, $foto_url);
    $stmt->execute();
    header("Location: index.php");
    exit();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Adicionar Item - Admin</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <header>
        <h1>Adicionar Item ao Cardápio</h1>
        <nav>
            <a href="index.php">Voltar</a>
        </nav>
    </header>

    <main>
        <form method="post">
            <label>Categoria:
                <select name="categoria_id" required>
                    <?php while ($cat = $categories->fetch_assoc()): ?>
                        <option value="<?php echo $cat['id']; ?>"><?php echo $cat['nome']; ?></option>
                    <?php endwhile; ?>
                </select>
            </label><br>
            <label>Nome: <input type="text" name="nome" required></label><br>
            <label>Descrição: <textarea name="descricao"></textarea></label><br>
            <label>Preço: <input type="number" step="0.01" name="preco" required></label><br>
            <label>URL da Foto: <input type="url" name="foto_url"></label><br>
            <button type="submit">Adicionar</button>
        </form>
    </main>
</body>
</html>