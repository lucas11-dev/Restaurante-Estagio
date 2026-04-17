<?php
require_once __DIR__ . '/../includes/cliente.php';
ensure_cliente_logged_in();
$db = get_db();

$orderId = filter_input(INPUT_GET, 'order_id', FILTER_VALIDATE_INT);
if (!$orderId) {
    header('Location: orders.php');
    exit;
}

$stmt = $db->prepare('SELECT p.id, p.numero_pedido, p.status, p.tipo_pedido, p.total, p.observacoes, p.criado_em
                      FROM pedido p
                      INNER JOIN cliente c ON c.id = p.cliente_id
                      WHERE p.id = :pedido_id AND c.utilizador_id = :utilizador_id');
$stmt->execute([':pedido_id' => $orderId, ':utilizador_id' => $_SESSION['utilizador_id']]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$order) {
    header('Location: orders.php');
    exit;
}

$stmtItems = $db->prepare('SELECT ip.quantidade, ip.preco_unitario, pr.nome
                           FROM item_pedido ip
                           INNER JOIN produto pr ON pr.id = ip.produto_id
                           WHERE ip.pedido_id = :pedido_id');
$stmtItems->execute([':pedido_id' => $orderId]);
$items = $stmtItems->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pedido #<?php echo htmlspecialchars($order['numero_pedido']); ?> - Restaurante Conect</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 0; background: #f4f4f7; color: #333; }
        header { background: #2c3e50; color: white; padding: 20px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; }
        header a { color: white; text-decoration: none; margin-left: 16px; }
        main { padding: 24px; max-width: 900px; margin: 0 auto; }
        .box { background: white; padding: 28px; border-radius: 18px; box-shadow: 0 12px 30px rgba(0,0,0,.08); }
        .section { margin-bottom: 25px; }
        .section h2 { margin-bottom: 15px; color: #1e3c72; font-size: 18px; border-left: 3px solid #ffd700; padding-left: 12px; }
        .info p { margin: 10px 0; }
        .status { display: inline-block; padding: 8px 16px; border-radius: 20px; font-weight: 600; color: white; }
        .status-pendente { background: #f39c12; }
        .status-confirmado { background: #3498db; }
        .status-preparando { background: #e74c3c; }
        .status-pronto { background: #27ae60; }
        .status-entregue { background: #2ecc71; }
        .status-cancelado { background: #95a5a6; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #eee; }
        th { background: #f8f9fa; }
        .btn-back { background: #7f8c8d; color: white; padding: 10px 20px; border-radius: 8px; text-decoration: none; display: inline-block; margin-top: 15px; }
    </style>
</head>
<body>
    <header>
        <div>📋 Pedido #<?php echo htmlspecialchars($order['numero_pedido']); ?></div>
        <div>
            <a href="orders.php">← Voltar</a>
            <a href="../logout.php">🚪 Sair</a>
        </div>
    </header>

    <main>
        <div class="box">
            <div class="section info">
                <h2>📌 Informações do pedido</h2>
                <p><strong>Status:</strong> <span class="status status-<?php echo $order['status']; ?>"><?php echo ucfirst($order['status']); ?></span></p>
                <p><strong>Número:</strong> #<?php echo htmlspecialchars($order['numero_pedido']); ?></p>
                <p><strong>Tipo:</strong> <?php echo $order['tipo_pedido'] == 'local' ? '🏠 Mesa' : '📦 Entrega'; ?></p>
                <p><strong>Total:</strong> Kz <?php echo format_money($order['total']); ?></p>
                <p><strong>Data:</strong> <?php echo date('d/m/Y H:i', strtotime($order['criado_em'])); ?></p>
                <?php if (!empty($order['observacoes'])): ?>
                    <p><strong>Observação:</strong> <?php echo nl2br(htmlspecialchars($order['observacoes'])); ?></p>
                <?php endif; ?>
            </div>
            
            <div class="section itens">
                <h2>🍽️ Itens do pedido</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Produto</th>
                            <th>Quantidade</th>
                            <th>Preço unitário</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($items as $item): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($item['nome']); ?></td>
                                <td><?php echo (int) $item['quantidade']; ?></td>
                                <td>Kz <?php echo format_money($item['preco_unitario']); ?></td>
                                <td>Kz <?php echo format_money($item['preco_unitario'] * $item['quantidade']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <a href="orders.php" class="btn-back">← Voltar para meus pedidos</a>
        </div>
    </main>
</body>
</html>