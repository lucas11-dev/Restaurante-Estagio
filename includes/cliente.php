<?php
session_start();
require_once __DIR__ . '/database.php';

function get_db() {
    $database = new Database();
    return $database->getConnection();
}

function ensure_cliente_logged_in() {
    if (!isset($_SESSION['utilizador_id']) || ($_SESSION['utilizador_tipo'] ?? '') !== 'cliente') {
        header('Location: ../login.php');
        exit;
    }
}

function get_cliente_id(PDO $db) {
    $stmt = $db->prepare('SELECT id FROM cliente WHERE utilizador_id = :utilizador_id');
    $stmt->execute([':utilizador_id' => $_SESSION['utilizador_id']]);
    $cliente = $stmt->fetch(PDO::FETCH_ASSOC);
    return $cliente ? (int) $cliente['id'] : null;
}

function get_cart() {
    return $_SESSION['cart'] ?? [];
}

function save_cart(array $cart) {
    $_SESSION['cart'] = $cart;
}

function clear_cart() {
    unset($_SESSION['cart']);
}

function format_money($value) {
    return number_format((float) $value, 2, ',', '.');
}
