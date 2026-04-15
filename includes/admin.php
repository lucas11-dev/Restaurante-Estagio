<?php
session_start();
require_once __DIR__ . '/database.php';

function get_db() {
    $database = new Database();
    return $database->getConnection();
}

function ensure_admin_logged_in() {
    if (!isset($_SESSION['utilizador_id']) || ($_SESSION['utilizador_tipo'] ?? '') !== 'admin') {
        header('Location: ../login.php');
        exit;
    }
}

function format_money($value) {
    return number_format((float) $value, 2, ',', '.');
}

function get_active_categories(PDO $db) {
    $stmt = $db->prepare('SELECT id, nome FROM categoria WHERE ativo = 1 ORDER BY ordem ASC, nome ASC');
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
