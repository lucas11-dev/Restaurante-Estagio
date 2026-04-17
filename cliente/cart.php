<?php
require_once __DIR__ . '/../includes/cliente.php';
ensure_cliente_logged_in();

$cart = get_cart();
$erro = '';
$sucesso = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $quantidades = $_POST['quantidade'] ?? [];
    foreach ($quantidades as $itemId => $qty) {
        $itemId = (int) $itemId;
        $qty = (int) $qty;
        if (isset($cart[$itemId])) {
            if ($qty > 0) {
                $cart[$itemId]['quantidade'] = $qty;
            } else {
                unset($cart[$itemId]);
            }
        }
    }
    save_cart($cart);
    $sucesso = 'Carrinho atualizado com sucesso.';
}

$total = 0;
foreach ($cart as $item) {
    $total += $item['preco'] * $item['quantidade'];
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Carrinho - Restaurante Conect</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 0; background: #f4f4f7; color: #333; }
        header { background: #2c3e50; color: white; padding: 20px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; }
        header a { color: white; text-decoration: none; margin-left: 16px; }
        main { padding: 24px; max-width: 1000px; margin: 0 auto; }
        .mensagem, .erro { padding: 16px; border-radius: 12px; margin-bottom: 20px; }
        .mensagem { background: #d4edda; color: #155724; }
        .erro { background: #f8d7da; color: #721c24; }
        table { width: 100%; border-collapse: collapse; background: white; border-radius: 16px; overflow: hidden; box-shadow: 0 12px 30px rgba(0,0,0,.08); }
        th, td { padding: 16px; text-align: left; border-bottom: 1px solid #eee; }
        th { background: #f9fafb; }
        td img { width: 60px; height: 60px; object-fit: cover; border-radius: 8px; }
        .quantidade-input { width: 70px; padding: 8px; border-radius: 10px; border: 1px solid #ccc; }
        .total { text-align: right; font-size: 20px; font-weight: 700; margin-top: 18px; }
        .footer-links { display: flex; justify-content: space-between; gap: 12px; margin-top: 24px; flex-wrap: wrap; }
        .btn-secondary, .btn-primary { padding: 14px 18px; border-radius: 12px; font-weight: 700; text-decoration: none; color: white; border: none; cursor: pointer; }
        .btn-secondary { background: #7f8c8d; }
        .btn-primary { background: #27ae60; }
        .btn-danger { background: #e74c3c; color: white; padding: 10px 14px; border-radius: 10px; text-decoration: none; display: inline-block; }
    </style>
</head>
<body>
    <header>
        <div><strong>🛒 Carrinho de Compras</strong></div>
        <div>
            <a href="index.php"> Continuar comprando</a>
            <a href="orders.php"> Meus pedidos</a>
            <a href="../logout.php"> <- Sair</a>
        </div>
    </header>

    <main>
        <?php if ($sucesso): ?><div class="mensagem">✅ <?php echo htmlspecialchars($sucesso); ?></div><?php endif; ?>
        <?php if ($erro): ?><div class="erro">❌ <?php echo htmlspecialchars($erro); ?></div><?php endif; ?>

        <?php if (empty($cart)): ?>
            <div class="erro">🛒 Seu carrinho está vazio. <a href="index.php">Adicione itens</a> ao carrinho.</div>
        <?php else: ?>
            <form method="POST" action="cart.php">
                <table>
                    <thead>
                        <tr>
                            <th>Item</th>
                            <th>Preço unitário</th>
                            <th>Quantidade</th>
                            <th>Subtotal</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($cart as $item): ?>
                            <tr>
                                <td>
                                    <div style="display:flex; align-items:center; gap:12px;">
                                        <?php if (!empty($item['imagem_url'])): ?>
                                            <img src="../<?php echo htmlspecialchars($item['imagem_url']); ?>" alt="<?php echo htmlspecialchars($item['nome']); ?>">
                                        <?php else: ?>
                                            <div style="width:60px;height:60px;background:#ececec;border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:30px;">🍽️</div>
                                        <?php endif; ?>
                                        <span><?php echo htmlspecialchars($item['nome']); ?></span>
                                    </div>
                                </td>
                                <td>Kz <?php echo format_money($item['preco']); ?></td>
                                <td>
                                    <input class="quantidade-input" type="number" name="quantidade[<?php echo (int) $item['id']; ?>]" value="<?php echo (int) $item['quantidade']; ?>" min="0">
                                </td>
                                <td>Kz <?php echo format_money($item['preco'] * $item['quantidade']); ?></td>
                                <td><a href="remove_from_cart.php?item_id=<?php echo (int) $item['id']; ?>" class="btn-danger" onclick="return confirm('Remover item?')">🗑️ Remover</a></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <div class="total">💰 Total: Kz <?php echo format_money($total); ?></div>
                <div class="footer-links">
                    <button type="submit" class="btn-secondary">🔄 Atualizar carrinho</button>
                    <a class="btn-primary" href="checkout.php">✅ Finalizar compra</a>
                </div>
            </form>
        <?php endif; ?>
    </main>
</body>
</html>