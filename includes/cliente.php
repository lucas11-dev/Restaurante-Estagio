<?php
// includes/cliente.php
session_start();

// Verificar se o cliente está logado
function ensure_cliente_logged_in() {
    if (!isset($_SESSION['utilizador_id']) || $_SESSION['utilizador_tipo'] !== 'cliente') {
        header('Location: ../login.php');
        exit;
    }
}

// Obter conexão com o banco de dados
function get_db() {
    $host = 'localhost';
    $dbname = 'restaurante_conect';
    $username = 'root';
    $password = '';
    
    try {
        $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $pdo;
    } catch(PDOException $e) {
        die("Erro de conexão: " . $e->getMessage());
    }
}

// Função para formatar dinheiro
if (!function_exists('format_money')) {
    function format_money($value) {
        return number_format($value, 0, ',', '.');
    }
}

// Obter ID do cliente
function get_cliente_id($db) {
    $stmt = $db->prepare("SELECT id FROM cliente WHERE utilizador_id = :user_id");
    $stmt->execute([':user_id' => $_SESSION['utilizador_id']]);
    $cliente = $stmt->fetch(PDO::FETCH_ASSOC);
    return $cliente ? $cliente['id'] : null;
}

// Carrinho (usando sessão)
function get_cart() {
    if (!isset($_SESSION['carrinho'])) {
        $_SESSION['carrinho'] = [];
    }
    return $_SESSION['carrinho'];
}

function save_cart($cart) {
    $_SESSION['carrinho'] = $cart;
}

function clear_cart() {
    $_SESSION['carrinho'] = [];
}
?>