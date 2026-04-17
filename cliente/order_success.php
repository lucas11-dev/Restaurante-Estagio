<?php
require_once __DIR__ . '/../includes/cliente.php';
ensure_cliente_logged_in();
$db = get_db();
$orderId = filter_input(INPUT_GET, 'order_id', FILTER_VALIDATE_INT);
$order = null;

if ($orderId) {
    $stmt = $db->prepare('SELECT p.id, p.numero_pedido, p.status, p.tipo_pedido, p.total, p.observacoes, p.criado_em
                          FROM pedido p
                          INNER JOIN cliente c ON c.id = p.cliente_id
                          WHERE p.id = :pedido_id AND c.utilizador_id = :utilizador_id');
    $stmt->execute([':pedido_id' => $orderId, ':utilizador_id' => $_SESSION['utilizador_id']]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pedido Confirmado - FOODNET</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 0; background: #f4f4f7; color: #333; }
        header { background: #2c3e50; color: white; padding: 20px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; }
        header a { color: white; text-decoration: none; margin-left: 16px; }
        main { padding: 24px; max-width: 800px; margin: 0 auto; }
        .box { background: white; padding: 32px; border-radius: 18px; box-shadow: 0 12px 30px rgba(0,0,0,.08); text-align: center; }
        .box h1 { margin-bottom: 18px; color: #27ae60; }
        .order-info { margin-top: 20px; text-align: left; background: #f8f9fa; padding: 20px; border-radius: 12px; }
        .order-info p { margin: 10px 0; }
        .actions { margin-top: 24px; display: flex; justify-content: center; gap: 14px; flex-wrap: wrap; }
        .btn { padding: 14px 18px; border-radius: 12px; text-decoration: none; font-weight: 700; color: white; }
        .btn-primary { background: #27ae60; }
        .btn-secondary { background: #7f8c8d; }
    </style>
</head>
<body>
    <header>
        <div>🎉 Pedido Confirmado</div>
        <div>
            <a href="index.php">🍽️ Cardápio</a>
            <a href="orders.php">📦 Meus pedidos</a>
            <a href="../logout.php">🚪 Sair</a>
        </div>
    </header>

    <main>
        <div class="box">
            <?php if (!$order): ?>
                <h1>✅ Pedido realizado!</h1>
                <p>Seu pedido foi registrado com sucesso.</p>
                <p>Visite a página de pedidos para acompanhar o status.</p>
            <?php else: ?>
                <h1>🎉 Pedido #<?php echo htmlspecialchars($order['numero_pedido']); ?> Confirmado!</h1>
                <p>Obrigado pelo seu pedido. Ele já está em processamento.</p>
                <div class="order-info">
                    <p><strong>📋 Número:</strong> <?php echo htmlspecialchars($order['numero_pedido']); ?></p>
                    <p><strong>📌 Status:</strong> <?php echo ucfirst(htmlspecialchars($order['status'])); ?></p>
                    <p><strong>🍽️ Tipo:</strong> <?php echo htmlspecialchars($order['tipo_pedido']); ?></p>
                    <p><strong>💰 Total:</strong> Kz <?php echo format_money($order['total']); ?></p>
                    <p><strong>📅 Data:</strong> <?php echo date('d/m/Y H:i', strtotime($order['criado_em'])); ?></p>
                    <?php if (!empty($order['observacoes'])): ?>
                        <p><strong>📝 Observação:</strong> <?php echo nl2br(htmlspecialchars($order['observacoes'])); ?></p>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
            <div class="actions">
                <a class="btn btn-primary" href="index.php">🍽️ Voltar ao cardápio</a>
                <a class="btn btn-secondary" href="orders.php">📦 Ver meus pedidos</a>
            </div>
        </div>
    </main>
</body>
</html>