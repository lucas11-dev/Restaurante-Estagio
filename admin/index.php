<?php
// admin/index.php
require_once __DIR__ . '/../includes/admin.php';
ensure_admin_logged_in();

$db = get_db();

// Garantir que o usuário é admin (verificação extra)
$stmt = $db->prepare("SELECT tipo FROM utilizador WHERE id = :id");
$stmt->execute([':id' => $_SESSION['utilizador_id']]);
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$usuario || $usuario['tipo'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

$alert = '';
if (isset($_GET['success'])) {
    switch ($_GET['success']) {
        case 'item_added':
            $alert = 'Item cadastrado com sucesso!';
            break;
        case 'item_updated':
            $alert = 'Item atualizado com sucesso!';
            break;
        case 'item_deleted':
            $alert = 'Item removido com sucesso!';
            break;
        case 'item_toggled':
            $alert = 'Disponibilidade do item atualizada!';
            break;
        case 'config_saved':
            $alert = 'Configurações salvas com sucesso!';
            break;
        case 'backup_created':
            $alert = 'Backup criado com sucesso!';
            break;
        case 'backup_restored':
            $alert = 'Backup restaurado com sucesso!';
            break;
    }
}

// Estatísticas - usando tabelas corretas
try {
    $totalItems = (int) $db->query('SELECT COUNT(*) FROM produto')->fetchColumn();
    $totalCategories = (int) $db->query('SELECT COUNT(*) FROM categoria')->fetchColumn();
    $totalOrders = (int) $db->query("SELECT COUNT(*) FROM pedido WHERE status IN ('pendente','confirmado','preparando')")->fetchColumn();
    $totalRevenue = $db->query("SELECT COALESCE(SUM(total), 0) FROM pedido WHERE status IN ('pronto','entregue')")->fetchColumn();
    $totalClients = (int) $db->query("SELECT COUNT(*) FROM utilizador WHERE tipo = 'cliente'")->fetchColumn();
    $totalFuncionarios = (int) $db->query("SELECT COUNT(*) FROM utilizador WHERE tipo IN ('garcom', 'cozinha', 'gerente', 'admin')")->fetchColumn();
} catch (PDOException $e) {
    $totalItems = 0;
    $totalCategories = 0;
    $totalOrders = 0;
    $totalRevenue = 0;
    $totalClients = 0;
    $totalFuncionarios = 0;
}

// Buscar produtos
try {
    $itemsStmt = $db->prepare(
        'SELECT p.id, p.nome, p.descricao, p.preco, p.imagem_url, p.imagem, p.disponivel, c.nome AS categoria_nome
         FROM produto p
         JOIN categoria c ON c.id = p.categoria_id
         ORDER BY c.nome ASC, p.nome ASC'
    );
    $itemsStmt->execute();
    $items = $itemsStmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $items = [];
}

// Buscar configurações
try {
    $configStmt = $db->query("SELECT * FROM configuracoes");
    $configs = [];
    while ($row = $configStmt->fetch(PDO::FETCH_ASSOC)) {
        $configs[$row['chave']] = $row['valor'];
    }
} catch (PDOException $e) {
    $configs = [];
}

// Buscar backups
try {
    $backupStmt = $db->query("SELECT b.*, u.nome as criado_por_nome FROM backup_sistema b JOIN utilizador u ON b.criado_por = u.id ORDER BY b.criado_em DESC LIMIT 20");
    $backups = $backupStmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $backups = [];
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Restaurante Conect</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f4f6fb; color: #2d3748; }
        header { background: #1e40af; color: white; padding: 20px 32px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; }
        header h1 { font-size: 24px; }
        header nav a { color: white; text-decoration: none; margin-left: 20px; }
        header nav a:hover { opacity: 0.85; }
        .container { max-width: 1400px; margin: 30px auto; padding: 0 24px; }
        .cards { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 18px; margin-bottom: 28px; }
        .card { background: white; border-radius: 18px; padding: 22px; box-shadow: 0 14px 40px rgba(15, 23, 42, 0.08); }
        .card h2 { font-size: 14px; color: #1e3a8a; margin-bottom: 10px; text-transform: uppercase; letter-spacing: 1px; }
        .card p { font-size: 34px; font-weight: 700; color: #111827; margin: 0; }
        .actions { display: flex; gap: 12px; flex-wrap: wrap; margin-bottom: 20px; }
        .actions a { background: #1e40af; color: white; padding: 12px 18px; border-radius: 12px; text-decoration: none; transition: background .2s; }
        .actions a:hover { background: #1d4ed8; }
        .alert { margin-bottom: 20px; padding: 16px 20px; border-radius: 14px; background: #d1fae5; color: #065f46; border: 1px solid #a7f3d0; }
        table { width: 100%; border-collapse: collapse; background: white; border-radius: 18px; overflow: hidden; box-shadow: 0 14px 40px rgba(15, 23, 42, 0.08); }
        th, td { padding: 16px 18px; text-align: left; border-bottom: 1px solid #e2e8f0; }
        th { background: #eef2ff; color: #1e3a8a; font-weight: 700; }
        tr:last-child td { border-bottom: none; }
        .badge { display: inline-flex; padding: 6px 12px; border-radius: 999px; font-size: 12px; font-weight: 700; }
        .badge-yes { background: #d1fae5; color: #065f46; }
        .badge-no { background: #fee2e2; color: #991b1b; }
        .table-actions a { margin-right: 8px; font-weight: 600; color: #1e40af; text-decoration: none; }
        .table-actions a:hover { text-decoration: underline; }
        .tab-content { display: none; }
        .tab-content.active { display: block; }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 8px; font-weight: 600; }
        .form-group input, .form-group textarea { width: 100%; padding: 12px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 14px; }
        .btn-save { background: #27ae60; color: white; padding: 12px 24px; border: none; border-radius: 8px; cursor: pointer; font-weight: 600; }
        .btn-save:hover { background: #219a52; }
        .backup-list { margin-top: 20px; }
        .btn-backup { background: #8b5cf6; color: white; padding: 8px 16px; border-radius: 8px; text-decoration: none; font-size: 12px; }
        .btn-backup:hover { background: #7c3aed; }
        @media (max-width: 800px) { .cards { grid-template-columns: 1fr; } }
    </style>
</head>
<body>
    <header>
        <div>
            <h1> FOODNET - Admin</h1>
            <p>Bem-vindo, <?php echo htmlspecialchars($_SESSION['utilizador_nome']); ?>!</p>
        </div>
        <nav>
            <a href="#" onclick="showTab('dashboard')">Dashboard</a>
            <a href="#" onclick="showTab('produtos')">Produtos</a>
            <a href="#" onclick="showTab('configuracoes')">Configurações</a>
            <a href="#" onclick="showTab('backup')">Backup</a>
            <a href="../logout.php">Sair</a>
        </nav>
    </header>
    
    <div class="container">
        <?php if ($alert): ?>
            <div class="alert">✅ <?php echo htmlspecialchars($alert); ?></div>
        <?php endif; ?>

        <!-- Tab Dashboard -->
        <div id="tab-dashboard" class="tab-content active">
            <div class="cards">
                <div class="card"><h2>Produtos</h2><p><?php echo $totalItems; ?></p></div>
                <div class="card"><h2>Categorias</h2><p><?php echo $totalCategories; ?></p></div>
                <div class="card"><h2>Pedidos abertos</h2><p><?php echo $totalOrders; ?></p></div>
                <div class="card"><h2>Receita</h2><p>Kz <?php echo number_format($totalRevenue, 0, ',', '.'); ?></p></div>
                <div class="card"><h2>Clientes</h2><p><?php echo $totalClients; ?></p></div>
                <div class="card"><h2>Funcionários</h2><p><?php echo $totalFuncionarios; ?></p></div>
            </div>

            <div class="actions">
                <a href="add_item.php">➕ Novo produto</a>
                <a href="funcionarios.php">➕ Novo Funcionário</a>
            </div>

            <h3 style="margin: 20px 0 15px;"> Últimos produtos</h3>
            <table>
                <thead><tr><th>Item</th><th>Categoria</th><th>Preço</th><th>Disponível</th><th>Ações</th></tr></thead>
                <tbody>
                    <?php if (empty($items)): ?>
                        <tr><td colspan="5" style="text-align:center; padding: 24px;">Nenhum produto cadastrado</td></tr>
                    <?php endif; ?>
                    <?php foreach (array_slice($items, 0, 10) as $item): ?>
                        <tr>
                            <td>
                                <?php if (!empty($item['imagem_url'])): ?>
                                    <img src="../<?php echo $item['imagem_url']; ?>" style="width: 40px; height: 40px; object-fit: cover; border-radius: 8px; vertical-align: middle; margin-right: 10px;">
                                <?php elseif (!empty($item['imagem'])): ?>
                                    <span style="font-size: 24px; margin-right: 10px;"><?php echo $item['imagem']; ?></span>
                                <?php endif; ?>
                                <?php echo htmlspecialchars($item['nome']); ?>
                            </td>
                            <td><?php echo htmlspecialchars($item['categoria_nome']); ?></td>
                            <td>Kz <?php echo number_format($item['preco'], 0, ',', '.'); ?></td>
                            <td><span class="badge <?php echo $item['disponivel'] ? 'badge-yes' : 'badge-no'; ?>"><?php echo $item['disponivel'] ? 'Sim' : 'Não'; ?></span></td>
                            <td class="table-actions">
                                <a href="edit_item.php?id=<?php echo $item['id']; ?>">✏️ Editar</a>
                                <a href="toggle_item.php?id=<?php echo $item['id']; ?>"><?php echo $item['disponivel'] ? '📴 Desativar' : '✅ Ativar'; ?></a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Tab Produtos -->
        <div id="tab-produtos" class="tab-content">
            <div class="actions"><a href="add_item.php">➕ Novo produto</a></div>
            <table>
                <thead><tr><th>Imagem</th><th>Item</th><th>Categoria</th><th>Preço</th><th>Disponível</th><th>Ações</th></tr></thead>
                <tbody>
                    <?php foreach ($items as $item): ?>
                        <tr>
                            <td>
                                <?php if (!empty($item['imagem_url'])): ?>
                                    <img src="../<?php echo $item['imagem_url']; ?>" style="width: 50px; height: 50px; object-fit: cover; border-radius: 8px;">
                                <?php elseif (!empty($item['imagem'])): ?>
                                    <span style="font-size: 30px;"><?php echo $item['imagem']; ?></span>
                                <?php else: ?>
                                    <span style="font-size: 30px;"></span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo htmlspecialchars($item['nome']); ?></td>
                            <td><?php echo htmlspecialchars($item['categoria_nome']); ?></td>
                            <td>Kz <?php echo number_format($item['preco'], 0, ',', '.'); ?></td>
                            <td><span class="badge <?php echo $item['disponivel'] ? 'badge-yes' : 'badge-no'; ?>"><?php echo $item['disponivel'] ? 'Sim' : 'Não'; ?></span></td>
                            <td class="table-actions">
                                <a href="edit_item.php?id=<?php echo $item['id']; ?>">✏️ Editar</a>
                                <a href="toggle_item.php?id=<?php echo $item['id']; ?>"><?php echo $item['disponivel'] ? '📴 Desativar' : '✅ Ativar'; ?></a>
                                <a href="delete_item.php?id=<?php echo $item['id']; ?>" onclick="return confirm('Excluir?')">🗑️ Excluir</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Tab Configurações -->
        <div id="tab-configuracoes" class="tab-content">
            <div style="background: white; border-radius: 18px; padding: 28px; max-width: 600px;">
                <h2 style="margin-bottom: 20px;">⚙️ Configurações</h2>
                <form method="POST" action="save_config.php">
                    <div class="form-group">
                        <label>Nome do Restaurante</label>
                        <input type="text" name="nome_restaurante" value="<?php echo htmlspecialchars($configs['nome_restaurante'] ?? 'Restaurante Conect'); ?>">
                    </div>
                    <div class="form-group">
                        <label>Telefone</label>
                        <input type="text" name="telefone" value="<?php echo htmlspecialchars($configs['telefone'] ?? '+244 923 456 789'); ?>">
                    </div>
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" name="email" value="<?php echo htmlspecialchars($configs['email'] ?? 'contato@restauranteconect.ao'); ?>">
                    </div>
                    <div class="form-group">
                        <label>Endereço</label>
                        <textarea name="endereco" rows="3"><?php echo htmlspecialchars($configs['endereco'] ?? 'Luanda, Angola'); ?></textarea>
                    </div>
                    <div class="form-group">
                        <label>Taxa de Entrega (Kz)</label>
                        <input type="number" name="taxa_entrega" value="<?php echo htmlspecialchars($configs['taxa_entrega'] ?? '500'); ?>">
                    </div>
                    <button type="submit" class="btn-save">💾 Salvar</button>
                </form>
            </div>
        </div>

        <!-- Tab Backup -->
        <div id="tab-backup" class="tab-content">
            <div style="background: white; border-radius: 18px; padding: 28px;">
                <h2 style="margin-bottom: 20px;">💾 Backup</h2>
                <a href="create_backup.php" class="btn-backup">📀 Criar Backup</a>
                <div class="backup-list">
                    <h3 style="margin: 20px 0 15px;">📋 Backups</h3>
                    <?php if (empty($backups)): ?>
                        <p>Nenhum backup encontrado.</p>
                    <?php else: ?>
                        <table>
                            <thead><tr><th>Arquivo</th><th>Tamanho</th><th>Criado por</th><th>Data</th><th>Ações</th></tr></thead>
                            <tbody>
                                <?php foreach ($backups as $backup): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($backup['nome_arquivo']); ?></td>
                                        <td><?php echo $backup['tamanho']; ?></td>
                                        <td><?php echo htmlspecialchars($backup['criado_por_nome']); ?></td>
                                        <td><?php echo date('d/m/Y H:i:s', strtotime($backup['criado_em'])); ?></td>
                                        <td>
                                            <a href="restore_backup.php?id=<?php echo $backup['id']; ?>" onclick="return confirm('Restaurar backup?')">🔄 Restaurar</a>
                                            <a href="download_backup.php?id=<?php echo $backup['id']; ?>">📥 Download</a>
                                            <a href="delete_backup.php?id=<?php echo $backup['id']; ?>" onclick="return confirm('Excluir?')" style="color:#e74c3c;">🗑️ Excluir</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script>
        function showTab(tabName) {
            document.querySelectorAll('.tab-content').forEach(tab => tab.classList.remove('active'));
            document.getElementById('tab-' + tabName).classList.add('active');
        }
    </script>
</body>
</html>