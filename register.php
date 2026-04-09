<?php
session_start();
include 'includes/config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nome = $_POST['nome'];
    $email = $_POST['email'];
    $senha = password_hash($_POST['senha'], PASSWORD_DEFAULT);
    $telefone = $_POST['telefone'];
    $endereco = $_POST['endereco'];

    $conn = connectDB();
    // Insert utilizador
    $stmt = $conn->prepare("INSERT INTO utilizador (nome, email, senha, tipo) VALUES (?, ?, ?, 'cliente')");
    $stmt->bind_param("sss", $nome, $email, $senha);
    if ($stmt->execute()) {
        $utilizador_id = $stmt->insert_id;
        // Insert cliente
        $stmt2 = $conn->prepare("INSERT INTO cliente (utilizador_id, telefone, endereco) VALUES (?, ?, ?)");
        $stmt2->bind_param("iss", $utilizador_id, $telefone, $endereco);
        $stmt2->execute();
        $_SESSION['user_id'] = $utilizador_id;
        $_SESSION['tipo'] = 'cliente';
        header("Location: cliente/index.php");
        exit();
    } else {
        $error = "Erro ao registrar: " . $conn->error;
    }
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Registrar - Restaurante Conect</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <h1>Registrar</h1>
        <?php if (isset($error)) echo "<p class='error'>$error</p>"; ?>
        <form method="post">
            <label>Nome: <input type="text" name="nome" required></label><br>
            <label>Email: <input type="email" name="email" required></label><br>
            <label>Senha: <input type="password" name="senha" required></label><br>
            <label>Telefone: <input type="text" name="telefone"></label><br>
            <label>Endereço: <textarea name="endereco"></textarea></label><br>
            <button type="submit">Registrar</button>
        </form>
        <p><a href="login.php">Já tem conta? Faça login</a></p>
    </div>
</body>
</html>