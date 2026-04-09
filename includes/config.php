<?php
// Configurações do banco de dados
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'restaurante_conect');

// Conectar ao banco
function connectDB() {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if ($conn->connect_error) {
        die("Conexão falhou: " . $conn->connect_error);
    }
    $conn->set_charset("utf8");
    return $conn;
}

// Função para verificar login
function checkLogin() {
    if (!isset($_SESSION['user_id'])) {
        header("Location: ../login.php");
        exit();
    }
}

// Função para enviar notificação
function sendNotification($user_id, $tipo, $mensagem, $pedido_id = null) {
    $conn = connectDB();
    $stmt = $conn->prepare("INSERT INTO notificacao (utilizador_id, pedido_id, tipo, mensagem) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("iiss", $user_id, $pedido_id, $tipo, $mensagem);
    $stmt->execute();
    $conn->close();
}
?>