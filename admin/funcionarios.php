<?php
// admin/funcionarios.php
require_once __DIR__ . '/../includes/admin.php';
ensure_admin_logged_in();

$db = get_db();
$mensagem = '';
$erro = '';

// Processar ações
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'cadastrar') {
        $nome = trim($_POST['nome'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $senha = $_POST['senha'] ?? '';
        $tipo = $_POST['tipo'] ?? '';
        $telefone = $_POST['telefone'] ?? '';
        $status = $_POST['status'] ?? 'ativo';
        
        if (empty($nome) || empty($email) || empty($senha) || empty($tipo)) {
            $erro = 'Preencha todos os campos obrigatórios.';
        } else {
            // Verificar se email já existe
            $stmt = $db->prepare("SELECT id FROM utilizador WHERE email = :email");
            $stmt->execute([':email' => $email]);
            if ($stmt->fetch()) {
                $erro = 'Email já cadastrado.';
            } else {
                $stmt = $db->prepare("INSERT INTO utilizador (nome, email, senha, tipo, status, telefone_func) 
                                      VALUES (:nome, :email, :senha, :tipo, :status, :telefone)");
                $stmt->execute([
                    ':nome' => $nome,
                    ':email' => $email,
                    ':senha' => $senha,
                    ':tipo' => $tipo,
                    ':status' => $status,
                    ':telefone' => $telefone
                ]);
                registrar_log('cadastrar_funcionario', "Cadastrou funcionário: $nome ($tipo)");
                $mensagem = 'Funcionário cadastrado com sucesso!';
            }
        }
    }
    
    if ($action === 'editar') {
        $id = (int) $_POST['id'];
        $nome = trim($_POST['nome'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $tipo = $_POST['tipo'] ?? '';
        $telefone = $_POST['telefone'] ?? '';
        $status = $_POST['status'] ?? 'ativo';
        $senha = $_POST['senha'] ?? '';
        
        if (!empty($senha)) {
            $stmt = $db->prepare("UPDATE utilizador SET nome = :nome, email = :email, tipo = :tipo, 
                                  telefone_func = :telefone, status = :status, senha = :senha WHERE id = :id");
            $stmt->execute([
                ':nome' => $nome, ':email' => $email, ':tipo' => $tipo,
                ':telefone' => $telefone, ':status' => $status, ':senha' => $senha, ':id' => $id
            ]);
        } else {
            $stmt = $db->prepare("UPDATE utilizador SET nome = :nome, email = :email, tipo = :tipo, 
                                  telefone_func = :telefone, status = :status WHERE id = :id");
            $stmt->execute([
                ':nome' => $nome, ':email' => $email, ':tipo' => $tipo,
                ':telefone' => $telefone, ':status' => $status, ':id' => $id
            ]);
        }
        registrar_log('editar_funcionario', "Editou funcionário ID: $id");
        $mensagem = 'Funcionário atualizado com sucesso!';
    }
    
    if ($action === 'excluir') {
        $id = (int) $_POST['id'];
        $stmt = $db->prepare("DELETE FROM utilizador WHERE id = :id AND tipo IN ('garcom', 'cozinha', 'gerente')");
        $stmt->execute([':id' => $id]);
        registrar_log('excluir_funcionario', "Excluiu funcionário ID: $id");
        $mensagem = 'Funcionário excluído com sucesso!';
    }
}

// Buscar funcionários
$stmt = $db->prepare("SELECT * FROM utilizador WHERE tipo IN ('garcom', 'cozinha', 'gerente') ORDER BY tipo, nome");
$stmt->execute();
$funcionarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Estatísticas
$stmt = $db->query("SELECT COUNT(*) as total FROM utilizador WHERE tipo = 'garcom'");
$total_garcons = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

$stmt = $db->query("SELECT COUNT(*) as total FROM utilizador WHERE tipo = 'gerente'");
$total_gerentes = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

$stmt = $db->query("SELECT COUNT(*) as total FROM utilizador WHERE tipo = 'cozinha'");
$total_cozinha = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestão de Funcionários - Admin</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f4f6fb; }
        header { background: #1e40af; color: white; padding: 20px 32px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; }
        header h1 { font-size: 24px; }
        header nav a { color: white; text-decoration: none; margin-left: 20px; }
        .container { max-width: 1400px; margin: 30px auto; padding: 0 24px; }
        .stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 30px; }
        .stat-card { background: white; padding: 20px; border-radius: 15px; text-align: center; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
        .stat-card h3 { font-size: 32px; color: #1e40af; }
        .card { background: white; border-radius: 15px; padding: 25px; margin-bottom: 30px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
        .card h2 { margin-bottom: 20px; color: #1e3c72; border-left: 4px solid #ffd700; padding-left: 15px; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: 600; }
        input, select { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 8px; }
        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        button { background: #27ae60; color: white; padding: 10px 20px; border: none; border-radius: 8px; cursor: pointer; }
        button:hover { background: #219a52; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #eee; }
        th { background: #f8f9fa; }
        .btn-edit { background: #3498db; }
        .btn-delete { background: #e74c3c; }
        .btn-small { padding: 5px 10px; font-size: 12px; margin: 0 2px; }
        .mensagem { background: #d4edda; color: #155724; padding: 12px; border-radius: 8px; margin-bottom: 20px; }
        .erro { background: #f8d7da; color: #721c24; padding: 12px; border-radius: 8px; margin-bottom: 20px; }
        .modal { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center; }
        .modal-content { background: white; width: 500px; border-radius: 15px; padding: 25px; }
        .modal-header { display: flex; justify-content: space-between; margin-bottom: 20px; }
        .close { cursor: pointer; font-size: 24px; }
        @media (max-width: 768px) { .form-row { grid-template-columns: 1fr; } }
    </style>
</head>
<body>
    <header>
        <h1>👥 Gestão de Funcionários</h1>
        <nav>
            <a href="#" class="active" onclick="showTab('dashboard')">Dashboard</a>
            <a href="#" onclick="showTab('produtos')">Produtos</a>
            <a href="funcionarios.php">Funcionários</a>
            <a href="#" onclick="showTab('configuracoes')">Configurações</a>
            <a href="#" onclick="showTab('backup')">Backup</a>
            <a href="../logout.php">Sair</a>
        </nav>
    </header>

    <div class="container">
        <?php if ($mensagem): ?>
            <div class="mensagem">✅ <?php echo htmlspecialchars($mensagem); ?></div>
        <?php endif; ?>
        <?php if ($erro): ?>
            <div class="erro">❌ <?php echo htmlspecialchars($erro); ?></div>
        <?php endif; ?>

        <div class="stats">
            <div class="stat-card"><h3><?php echo $total_garcons; ?></h3><p>👨‍🍳 Garçons</p></div>
            <div class="stat-card"><h3><?php echo $total_gerentes; ?></h3><p>👔 Gerentes</p></div>
            <div class="stat-card"><h3><?php echo $total_garcons + $total_cozinha + $total_gerentes; ?></h3><p>Total</p></div>
        </div>

        <div class="card">
            <h2>➕ Cadastrar Novo Funcionário</h2>
            <form method="POST">
                <input type="hidden" name="action" value="cadastrar">
                <div class="form-row">
                    <div class="form-group"><label>Nome completo </label><input type="text" name="nome" required></div>
                    <div class="form-group"><label>Email </label><input type="email" name="email" required></div>
                </div>
                <div class="form-row">
                    <div class="form-group"><label>Senha </label><input type="password" name="senha" required placeholder="Mínimo 6 caracteres"></div>
                    <div class="form-group"><label>Tipo </label>
                        <select name="tipo" required>
                            <option value="garcom">👨‍🍳 Garçom</option>
                            <option value="gerente">👔 Gerente</option>
                        </select>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group"><label>Telefone</label><input type="text" name="telefone" placeholder="+244 923 456 789"></div>
                    <div class="form-group"><label>Status</label>
                        <select name="status">
                            <option value="ativo">Ativo</option>
                            <option value="inativo">Inativo</option>
                        </select>
                    </div>
                </div>
                <button type="submit"> Cadastrar Funcionário</button>
            </form>
        </div>

        <div class="card">
            <h2>📋 Lista de Funcionários</h2>
            <div style="overflow-x: auto;">
                <table>
                    <thead>
                        <tr><th>Nome</th><th>Email</th><th>Tipo</th><th>Telefone</th><th>Status</th><th>Ações</th>
                    </thead>
                    <tbody>
                        <?php foreach ($funcionarios as $func): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($func['nome']); ?></td>
                            <td><?php echo htmlspecialchars($func['email']); ?></td>
                            <td>
                                <?php 
                                    if($func['tipo'] == 'garcom') echo '👨‍🍳 Garçom';
                                    
                                    else echo '👔 Gerente';
                                ?>
                            </td>
                            <td><?php echo htmlspecialchars($func['telefone_func'] ?? '-'); ?></td>
                            <td><?php echo $func['status'] == 'ativo' ? '🟢 Ativo' : '🔴 Inativo'; ?></td>
                            <td>
                                <button class="btn-edit btn-small" onclick="editarFuncionario(<?php echo $func['id']; ?>, '<?php echo addslashes($func['nome']); ?>', '<?php echo $func['email']; ?>', '<?php echo $func['tipo']; ?>', '<?php echo $func['telefone_func']; ?>', '<?php echo $func['status']; ?>')">✏️</button>
                                <button class="btn-delete btn-small" onclick="excluirFuncionario(<?php echo $func['id']; ?>)">🗑️</button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal Editar -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>✏️ Editar Funcionário</h3>
                <span class="close" onclick="fecharModal()">&times;</span>
            </div>
            <form method="POST">
                <input type="hidden" name="action" value="editar">
                <input type="hidden" name="id" id="edit_id">
                <div class="form-group"><label>Nome</label><input type="text" name="nome" id="edit_nome" required></div>
                <div class="form-group"><label>Email</label><input type="email" name="email" id="edit_email" required></div>
                <div class="form-group"><label>Tipo</label>
                    <select name="tipo" id="edit_tipo">
                        <option value="garcom">👨‍🍳 Garçom</option>
                        <option value="gerente">👔 Gerente</option>
                    </select>
                </div>
                <div class="form-group"><label>Telefone</label><input type="text" name="telefone" id="edit_telefone"></div>
                <div class="form-group"><label>Status</label>
                    <select name="status" id="edit_status">
                        <option value="ativo">Ativo</option>
                        <option value="inativo">Inativo</option>
                    </select>
                </div>
                <div class="form-group"><label>Nova Senha (opcional)</label><input type="password" name="senha" placeholder="Deixe em branco para manter"></div>
                <button type="submit">💾 Salvar Alterações</button>
            </form>
        </div>
    </div>

    <!-- Form para excluir -->
    <form id="deleteForm" method="POST" style="display:none;">
        <input type="hidden" name="action" value="excluir">
        <input type="hidden" name="id" id="delete_id">
    </form>

    <script>
        function editarFuncionario(id, nome, email, tipo, telefone, status) {
            document.getElementById('edit_id').value = id;
            document.getElementById('edit_nome').value = nome;
            document.getElementById('edit_email').value = email;
            document.getElementById('edit_tipo').value = tipo;
            document.getElementById('edit_telefone').value = telefone || '';
            document.getElementById('edit_status').value = status;
            document.getElementById('editModal').style.display = 'flex';
        }
        
        function excluirFuncionario(id) {
            if(confirm('Tem certeza que deseja excluir este funcionário?')) {
                document.getElementById('delete_id').value = id;
                document.getElementById('deleteForm').submit();
            }
        }
        
        function fecharModal() {
            document.getElementById('editModal').style.display = 'none';
        }
        
        window.onclick = function(event) {
            if(event.target.classList.contains('modal')) {
                event.target.style.display = 'none';
            }
        }
    </script>
</body>
</html>