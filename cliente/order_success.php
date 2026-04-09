<?php
session_start();
include '../includes/config.php';
checkLogin();

$pedido_id = $_GET['pedido_id'] ?? 0;
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Pedido Confirmado - Cliente</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <header>
        <h1>Pedido Confirmado</h1>
        <nav>
            <a href="index.php">Cardápio</a>
            <a href="../logout.php">Sair</a>
        </nav>
    </header>

    <main>
        <p>Seu pedido #<?php echo $pedido_id; ?> foi confirmado com sucesso!</p>
        <p>Você receberá notificações sobre o status.</p>
    </main>
</body>
</html>