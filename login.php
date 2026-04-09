<?php
session_start();
include 'includes/config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $senha = $_POST['senha'];

    $conn = connectDB();
    $stmt = $conn->prepare("SELECT id, senha, tipo FROM utilizador WHERE email = ? AND ativo = 1");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();
        if (password_verify($senha, $user['senha'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['tipo'] = $user['tipo'];
            // Redirect based on type
            if ($user['tipo'] == 'cliente') {
                header("Location: cliente/index.php");
            } elseif ($user['tipo'] == 'garcon') {
                header("Location: garcon/index.php");
            } elseif ($user['tipo'] == 'admin' || $user['tipo'] == 'gerente') {
                header("Location: admin/index.php");
            }
            exit();
        } else {
            $error = "Senha incorreta.";
        }
    } else {
        $error = "Usuário não encontrado.";
    }
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Login - Restaurante Conect</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <h1>Login</h1>
        <?php if (isset($error)) echo "<p class='error'>$error</p>"; ?>
        <form method="post">
            <label>Email: <input type="email" name="email" required></label><br>
            <label>Senha: <input type="password" name="senha" required></label><br>
            <button type="submit">Entrar</button>
        </form>
        <p><a href="register.php">Registrar-se</a></p>
    </div>
</body>
</html>