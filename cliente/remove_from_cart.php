<?php
require_once __DIR__ . '/../includes/cliente.php';
ensure_cliente_logged_in();

$itemId = filter_input(INPUT_GET, 'item_id', FILTER_VALIDATE_INT);
$cart = get_cart();

if ($itemId && isset($cart[$itemId])) {
    unset($cart[$itemId]);
    save_cart($cart);
}

header('Location: cart.php');
exit;