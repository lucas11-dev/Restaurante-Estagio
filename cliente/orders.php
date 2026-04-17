<?php
require_once __DIR__ . '/../includes/cliente.php';
ensure_cliente_logged_in();
$db = get_db();

$stmt = $db->prepare('SELECT p.id, p.numero_pedido, p.status, p.tipo_pedido, p.total, p.criado_em
                      FROM pedido p
                      INNER JOIN cliente c ON c.id = p.cliente_id
                      WHERE c.utilizador_id = :utilizador_id
                      ORDER BY p.criado_em DESC');
$stmt->execute([':utilizador_id' => $_SESSION['utilizador_id']]);
$pedidos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meus Pedidos - Restaurante Conect</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 0; background: #f4f4f7; color: #333; }
        header { background: #2c3e50; color: white; padding: 20px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; }
        header a { color: white; text-decoration: none; margin-left: 16px; }
        main { padding: 24px; max-width: 1000px; margin: 0 auto; }
        h1 { margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; background: white; border-radius: 16px; overflow: hidden; box-shadow: 0 12px 30px rgba(0,0,0,.08); }
        th, td { padding: 16px; text-align: left; border-bottom: 1px solid #eee; }
        th { background: #f9fafb; }
        .status { padding: 6px 12px; border-radius: 20px; font-size: 12px; font-weight: 600; color: white; display: inline-block; }
        .status-pendente { background: #f39c12; }
        .status-confirmado { background: #3498db; }
        .status-preparando { background: #e74c3c; }
        .status-pronto { background: #27ae60; }
        .status-entregue { background: #2ecc71; }
        .status-cancelado { background: #95a5a6; }
        .btn-view { background: #2c3e50; color: white; padding: 8px 16px; border-radius: 8px; text-decoration: none; font-size: 13px; }
        .btn-view:hover { background: #1a252f; }
        .empty { background: white; padding: 40px; text-align: center; border-radius: 16px; }
    </style>
</head>
<body>
    <header>
        <div> Meus Pedidos</div>
        <div>
            <a href="index.php"> Cardápio</a>
            <a href="cart.php"> Carrinho</a>
            <a href="../logout.php"> <- Sair</a>
        </div>
    </header>

    <main>
        <h1>Histórico de pedidos</h1>
        
        <?php if (empty($pedidos)): ?>
            <div class="empty">
                <p>Nenhum pedido encontrado ainda.</p>
                <a href="index.php" style="color: #27ae60;">👉 Faça seu primeiro pedido</a>
            </div>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>Pedido</th>
                        <th>Status</th>
                        <th>Tipo</th>
                        <th>Total</th>
                        <th>Data</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($pedidos as $pedido): ?>
                        <tr>
                            <td>#<?php echo htmlspecialchars($pedido['numero_pedido']); ?></td>
                            <td><span class="status status-<?php echo $pedido['status']; ?>"><?php echo ucfirst($pedido['status']); ?></span></td>
                            <td><?php echo $pedido['tipo_pedido'] == 'local' ? '🏠 Mesa' : '📦 Entrega'; ?></td>
                            <td>Kz <?php echo format_money($pedido['total']); ?></td>
                            <td><?php echo date('d/m/Y H:i', strtotime($pedido['criado_em'])); ?></td>
                            <td><a class="btn-view" href="view_order.php?order_id=<?php echo (int) $pedido['id']; ?>">Ver detalhes</a></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </main>
</body>
</html>