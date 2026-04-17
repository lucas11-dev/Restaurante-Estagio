<?php
require_once __DIR__ . '/../includes/admin.php';
ensure_admin_logged_in();

$db = get_db();

$totalOrders = (int) $db->query('SELECT COUNT(*) FROM pedido')->fetchColumn();
$pendingOrders = (int) $db->query("SELECT COUNT(*) FROM pedido WHERE status = 'pendente'")->fetchColumn();
$preparingOrders = (int) $db->query("SELECT COUNT(*) FROM pedido WHERE status = 'em_preparacao'")->fetchColumn();
$readyOrders = (int) $db->query("SELECT COUNT(*) FROM pedido WHERE status = 'pronto'")->fetchColumn();
$deliveredOrders = (int) $db->query("SELECT COUNT(*) FROM pedido WHERE status = 'entregue'")->fetchColumn();
$cancelledOrders = (int) $db->query("SELECT COUNT(*) FROM pedido WHERE status = 'cancelado'")->fetchColumn();
$revenue = $db->query("SELECT COALESCE(SUM(total), 0) FROM pedido WHERE status IN ('pronto','entregue')")->fetchColumn();

$topItems = $db->query(
    'SELECT i.nome AS item_nome, COALESCE(SUM(ip.quantidade), 0) AS quantidade_vendida
     FROM item_pedido ip
     JOIN item_cardapio i ON i.id = ip.item_cardapio_id
     GROUP BY i.id
     ORDER BY quantidade_vendida DESC
     LIMIT 8'
)->fetchAll(PDO::FETCH_ASSOC);

$recentOrders = $db->query(
    'SELECT id, cliente_id, status, total, criado_em FROM pedido ORDER BY criado_em DESC LIMIT 10'
)->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Relatórios - Admin</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f0f4f8; color: #111827; }
        header { background: #1d4ed8; color: white; padding: 20px 24px; }
        header h1 { font-size: 24px; }
        header nav { margin-top: 12px; display: flex; gap: 16px; flex-wrap: wrap; }
        header nav a { color: #dbeafe; text-decoration: none; }
        header nav a:hover { color: white; }
        main { max-width: 1100px; margin: 28px auto; padding: 0 20px; }
        .grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px; margin-bottom: 28px; }
        .card { background: white; border-radius: 18px; padding: 24px; box-shadow: 0 16px 36px rgba(15, 23, 42, 0.08); }
        .card h2 { font-size: 18px; margin-bottom: 16px; color: #1d4ed8; }
        .card p { font-size: 30px; font-weight: 700; color: #111827; }
        .chart-list { list-style: none; margin-top: 12px; padding-left: 0; }
        .chart-list li { background: #eef6ff; border-radius: 12px; margin-bottom: 10px; padding: 14px 18px; display: flex; justify-content: space-between; align-items: center; }
        table { width: 100%; border-collapse: collapse; background: white; border-radius: 18px; overflow: hidden; box-shadow: 0 16px 36px rgba(15, 23, 42, 0.08); }
        th, td { padding: 14px 18px; text-align: left; border-bottom: 1px solid #e2e8f0; }
        th { background: #eff6ff; color: #1d4ed8; font-weight: 700; }
        tr:last-child td { border-bottom: none; }
        .label { font-size: 13px; color: #475569; }
        .back-link { display: inline-block; margin-top: 18px; color: #1d4ed8; text-decoration: none; }
        .back-link:hover { text-decoration: underline; }
        @media (max-width: 768px) { .grid { grid-template-columns: 1fr; } }
    </style>
</head>
<body>
    <header>
        <h1>Relatórios</h1>
        <nav>
            <a href="index.php">Voltar ao painel</a>
            <a href="add_item.php">Adicionar item</a>
            <a href="../logout.php">Sair</a>
        </nav>
    </header>
    <main>
        <div class="grid">
            <div class="card">
                <h2>Total de pedidos</h2>
                <p><?php echo $totalOrders; ?></p>
            </div>
            <div class="card">
                <h2>Receita estimada</h2>
                <p>Kz <?php echo format_money($revenue); ?></p>
            </div>
            <div class="card">
                <h2>Pedidos pendentes</h2>
                <p><?php echo $pendingOrders; ?></p>
            </div>
            <div class="card">
                <h2>Pedidos entregues</h2>
                <p><?php echo $deliveredOrders; ?></p>
            </div>
        </div>

        <div class="card" style="margin-bottom: 24px;">
            <h2>Pedidos por status</h2>
            <ul class="chart-list">
                <li><span>Pendente</span><strong><?php echo $pendingOrders; ?></strong></li>
                <li><span>Em preparação</span><strong><?php echo $preparingOrders; ?></strong></li>
                <li><span>Pronto</span><strong><?php echo $readyOrders; ?></strong></li>
                <li><span>Entregue</span><strong><?php echo $deliveredOrders; ?></strong></li>
                <li><span>Cancelado</span><strong><?php echo $cancelledOrders; ?></strong></li>
            </ul>
        </div>

        <div class="card" style="margin-bottom: 24px;">
            <h2>Itens mais vendidos</h2>
            <?php if (empty($topItems)): ?>
                <p class="label">Ainda não há vendas registradas.</p>
            <?php else: ?>
                <ul class="chart-list">
                    <?php foreach ($topItems as $item): ?>
                        <li>
                            <span><?php echo htmlspecialchars($item['item_nome']); ?></span>
                            <strong><?php echo (int) $item['quantidade_vendida']; ?></strong>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>

        <div class="card">
            <h2>Últimos pedidos</h2>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Cliente</th>
                        <th>Status</th>
                        <th>Total (Kz)</th>
                        <th>Criado em</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($recentOrders)): ?>
                        <tr><td colspan="5" style="padding: 18px; text-align: center;">Nenhum pedido registrado.</td></tr>
                    <?php endif; ?>
                    <?php foreach ($recentOrders as $pedido): ?>
                        <tr>
                            <td><?php echo $pedido['id']; ?></td>
                            <td><?php echo $pedido['cliente_id']; ?></td>
                            <td><?php echo htmlspecialchars($pedido['status']); ?></td>
                            <td><?php echo format_money($pedido['total']); ?></td>
                            <td><?php echo htmlspecialchars($pedido['criado_em']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <a href="index.php" class="back-link">← Voltar ao painel</a>
    </main>
</body>
</html>