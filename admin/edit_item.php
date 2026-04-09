<?php
session_start();
include '../includes/config.php';
checkLogin();
if ($_SESSION['tipo'] != 'admin' && $_SESSION['tipo'] != 'gerente') {
    header("Location: ../login.php");
    exit();
}

$id = $_GET['id'];
$conn = connectDB();

// Get item
$stmt = $conn->prepare("SELECT * FROM item_cardapio WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$item = $stmt->get_result()->fetch_assoc();

// Get categories
$categories = $conn->query("SELECT id, nome FROM categoria WHERE ativo = 1");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $categoria_id = $_POST['categoria_id'];
    $nome = $_POST['nome'];
    $descricao = $_POST['descricao'];
    $preco = $_POST['preco'];
    $foto_url = $_POST['foto_url'];

    $stmt2 = $conn->prepare("UPDATE item_cardapio SET categoria_id=?, nome=?, descricao=?, preco=?, foto_url=? WHERE id=?");
    $stmt2->bind_param("issdsi", $categoria_id, $nome, $descricao, $preco, $foto_url, $id);
    $stmt2->execute();
    header("Location: index.php");
    exit();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Editar Item - Admin</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <header>
        <h1>Editar Item</h1>
        <nav>
            <a href="index.php">Voltar</a>
        </nav>
    </header>

    <main>
        <form method="post">
            <label>Categoria:
                <select name="categoria_id" required>
                    <?php while ($cat = $categories->fetch_assoc()): ?>
                        <option value="<?php echo $cat['id']; ?>" <?php if ($cat['id'] == $item['categoria_id']) echo 'selected'; ?>><?php echo $cat['nome']; ?></option>
                    <?php endwhile; ?>
                </select>
            </label><br>
            <label>Nome: <input type="text" name="nome" value="<?php echo $item['nome']; ?>" required></label><br>
            <label>Descrição: <textarea name="descricao"><?php echo $item['descricao']; ?></textarea></label><br>
            <label>Preço: <input type="number" step="0.01" name="preco" value="<?php echo $item['preco']; ?>" required></label><br>
            <label>URL da Foto: <input type="url" name="foto_url" value="<?php echo $item['foto_url']; ?>"></label><br>
            <button type="submit">Salvar</button>
        </form>
    </main>
</body>
</html>