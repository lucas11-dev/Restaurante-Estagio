<?php
require_once __DIR__ . '/../includes/admin.php';
ensure_admin_logged_in();

$db = get_db();

$alert = '';
if (isset($_GET['success'])) {
    switch ($_GET['success']) {
        case 'item_added':
            $alert = 'Item cadastrado com sucesso!';
            break;
        case 'item_updated':
            $alert = 'Item atualizado com sucesso!';
            break;
        case 'item_toggled':
            $alert = 'Disponibilidade do item atualizada!';
            break;
        default:
            $alert = '';
    }
}

$totalItems = (int) $db->query('SELECT COUNT(*) FROM item_cardapio')->fetchColumn();
$totalCategories = (int) $db->query('SELECT COUNT(*) FROM categoria')->fetchColumn();
$totalOrders = (int) $db->query("SELECT COUNT(*) FROM pedido WHERE status IN ('pendente','em_preparacao','pronto')")->fetchColumn();
$totalRevenue = $db->query("SELECT COALESCE(SUM(total), 0) FROM pedido WHERE status IN ('pronto','entregue')")->fetchColumn();

$itemsStmt = $db->prepare(
    'SELECT i.id, i.nome, i.descricao, i.preco, i.foto_url, i.disponivel, c.nome AS categoria_nome
     FROM item_cardapio i
     JOIN categoria c ON c.id = i.categoria_id
     ORDER BY c.nome ASC, i.nome ASC'
);
$itemsStmt->execute();
$items = $itemsStmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel do Admin - Restaurante Conect</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f4f6fb; color: #2d3748; }
        header { background: #1e40af; color: white; padding: 24px 32px; }
        header .top { display: flex; justify-content: space-between; flex-wrap: wrap; gap: 16px; max-width: 1200px; margin: auto; }
        header h1 { font-size: 28px; }
        header nav a { color: white; text-decoration: none; margin-left: 18px; font-weight: 500; }
        header nav a:hover { opacity: 0.85; }
        main { max-width: 1200px; margin: 24px auto; padding: 0 24px; }
        .cards { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 18px; margin-bottom: 28px; }
        .card { background: white; border-radius: 18px; padding: 22px; box-shadow: 0 14px 40px rgba(15, 23, 42, 0.08); }
        .card h2 { font-size: 18px; color: #1e3a8a; margin-bottom: 10px; }
        .card p { font-size: 34px; font-weight: 700; color: #111827; margin: 0; }
        .actions { display: flex; gap: 12px; flex-wrap: wrap; margin-bottom: 20px; }
        .actions a { background: #1e40af; color: white; padding: 12px 18px; border-radius: 12px; text-decoration: none; transition: background .2s ease-in-out; }
        .actions a:hover { background: #1d4ed8; }
        .alert { margin-bottom: 20px; padding: 16px 20px; border-radius: 14px; background: #d1fae5; color: #065f46; border: 1px solid #a7f3d0; }
        table { width: 100%; border-collapse: collapse; background: white; border-radius: 18px; overflow: hidden; box-shadow: 0 14px 40px rgba(15, 23, 42, 0.08); }
        th, td { padding: 16px 18px; text-align: left; border-bottom: 1px solid #e2e8f0; }
        th { background: #eef2ff; color: #1e3a8a; font-weight: 700; }
        tr:last-child td { border-bottom: none; }
        .badge { display: inline-flex; align-items: center; justify-content: center; padding: 6px 12px; border-radius: 999px; font-size: 12px; font-weight: 700; }
        .badge-yes { background: #d1fae5; color: #065f46; }
        .badge-no { background: #fee2e2; color: #991b1b; }
        .table-actions a { margin-right: 8px; font-weight: 600; color: #1e40af; text-decoration: none; }
        .table-actions a:hover { text-decoration: underline; }
        @media (max-width: 800px) { .cards { grid-template-columns: 1fr; } table, th, td { display: block; } th { position: absolute; top: -9999px; left: -9999px; } td { border: none; position: relative; padding-left: 50%; } td:before { position: absolute; top: 16px; left: 18px; width: calc(50% - 36px); white-space: nowrap; font-weight: 700; color: #334155; } td:nth-of-type(1):before { content: 'Item'; } td:nth-of-type(2):before { content: 'Categoria'; } td:nth-of-type(3):before { content: 'Preço'; } td:nth-of-type(4):before { content: 'Disponível'; } td:nth-of-type(5):before { content: 'Ações'; }}
    </style>
</head>
<body>
    <header>
        <div class="top">
            <div>
                <h1>Painel do Admin</h1>
                <p>Bem-vindo, <?php echo htmlspecialchars($_SESSION['utilizador_nome']); ?>.</p>
            </div>
            <nav>
                <a href="index.php">Dashboard</a>
                <a href="add_item.php">Adicionar Item</a>
                <a href="reports.php">Relatórios</a>
                <a href="../logout.php">Sair</a>
            </nav>
        </div>
    </header>
    <main>
        <?php if ($alert): ?>
            <div class="alert"><?php echo htmlspecialchars($alert); ?></div>
        <?php endif; ?>

        <div class="cards">
            <div class="card">
                <h2>Items no cardápio</h2>
                <p><?php echo $totalItems; ?></p>
            </div>
            <div class="card">
                <h2>Categorias</h2>
                <p><?php echo $totalCategories; ?></p>
            </div>
            <div class="card">
                <h2>Pedidos abertos</h2>
                <p><?php echo $totalOrders; ?></p>
            </div>
            <div class="card">
                <h2>Receita estimada</h2>
                <p>Kz <?php echo format_money($totalRevenue); ?></p>
            </div>
        </div>

        <div class="actions">
            <a href="add_item.php">+ Novo item</a>
            <a href="reports.php">Ver relatórios</a>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Item</th>
                    <th>Categoria</th>
                    <th>Preço</th>
                    <th>Disponível</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($items)): ?>
                    <tr><td colspan="5" style="text-align:center; padding: 24px;">Nenhum item cadastrado ainda.</td></tr>
                <?php endif; ?>
                <?php foreach ($items as $item): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($item['nome']); ?></td>
                        <td><?php echo htmlspecialchars($item['categoria_nome']); ?></td>
                        <td>Kz <?php echo format_money($item['preco']); ?></td>
                        <td><span class="badge <?php echo $item['disponivel'] ? 'badge-yes' : 'badge-no'; ?>"><?php echo $item['disponivel'] ? 'Sim' : 'Não'; ?></span></td>
                        <td class="table-actions">
                            <a href="edit_item.php?id=<?php echo $item['id']; ?>">Editar</a>
                            <a href="toggle_item.php?id=<?php echo $item['id']; ?>"><?php echo $item['disponivel'] ? 'Desativar' : 'Ativar'; ?></a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </main>
</body>
</html>
