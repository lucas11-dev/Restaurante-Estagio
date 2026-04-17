<?php
require_once __DIR__ . '/../includes/cliente.php';
ensure_cliente_logged_in();
$db = get_db();

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST['item_id'])) {
    header('Location: index.php');
    exit;
}

$itemId = filter_var($_POST['item_id'], FILTER_VALIDATE_INT);
if (!$itemId) {
    header('Location: index.php?error=' . urlencode('Item inválido.'));
    exit;
}

// Usando tabela produto
$stmt = $db->prepare('SELECT id, nome, preco, imagem_url, disponivel FROM produto WHERE id = :id');
$stmt->execute([':id' => $itemId]);
$item = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$item || !$item['disponivel']) {
    header('Location: index.php?error=' . urlencode('Este item não está disponível.'));
    exit;
}

$cart = get_cart();
$quantity = isset($cart[$itemId]['quantidade']) ? $cart[$itemId]['quantidade'] : 0;
$cart[$itemId] = [
    'id' => $item['id'],
    'nome' => $item['nome'],
    'preco' => $item['preco'],
    'imagem_url' => $item['imagem_url'],
    'quantidade' => $quantity + 1,
];
save_cart($cart);

header('Location: index.php?success=1');
exit;