<?php
// painel_garcom.php
session_start();

if (!isset($_SESSION['utilizador_id']) || $_SESSION['utilizador_tipo'] !== 'garcom') {
    header('Location: login.php');
    exit;
}

$host = 'localhost';
$dbname = 'restaurante_conect';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Buscar pedidos
    $stmt = $pdo->prepare("SELECT p.*, m.numero as mesa_numero, 
                                  (SELECT COUNT(*) FROM item_pedido WHERE pedido_id = p.id) as total_itens
                           FROM pedido p 
                           LEFT JOIN mesa m ON p.mesa_id = m.id
                           WHERE p.status IN ('pendente', 'confirmado', 'preparando', 'pronto')
                           ORDER BY FIELD(p.status, 'pendente', 'confirmado', 'preparando', 'pronto'), p.criado_em ASC");
    $stmt->execute();
    $pedidos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    $erro = $e->getMessage();
    $pedidos = [];
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Garçon - FOODNET</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f0f2f5; }
        header { background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); color: white; padding: 20px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; }
        header h1 { font-size: 24px; }
        header nav a { color: white; text-decoration: none; margin-left: 20px; }
        .container { max-width: 1200px; margin: 30px auto; padding: 0 20px; }
        .stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 30px; }
        .stat-card { background: white; padding: 20px; border-radius: 15px; text-align: center; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
        .stat-card h3 { font-size: 32px; color: #1e3c72; }
        .pedidos-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(380px, 1fr)); gap: 20px; }
        .pedido-card { background: white; border-radius: 15px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.05); transition: transform 0.3s; }
        .pedido-card:hover { transform: translateY(-3px); box-shadow: 0 5px 20px rgba(0,0,0,0.1); }
        .pedido-header { background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); color: white; padding: 15px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; }
        .pedido-numero { font-weight: bold; font-size: 16px; }
        .status { display: inline-block; padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: 600; }
        .status-pendente { background: #f39c12; color: white; }
        .status-confirmado { background: #3498db; color: white; }
        .status-preparando { background: #e74c3c; color: white; }
        .status-pronto { background: #27ae60; color: white; }
        .pedido-body { padding: 15px; }
        .pedido-info { margin-bottom: 15px; }
        .pedido-info p { margin: 8px 0; }
        .btn-group { display: flex; gap: 10px; flex-wrap: wrap; margin-top: 15px; }
        .btn { padding: 8px 15px; border: none; border-radius: 8px; cursor: pointer; font-size: 13px; font-weight: 600; transition: all 0.3s; }
        .btn-primary { background: #27ae60; color: white; }
        .btn-primary:hover { background: #219a52; }
        .btn-warning { background: #f39c12; color: white; }
        .btn-warning:hover { background: #e67e22; }
        .btn-danger { background: #e74c3c; color: white; }
        .btn-danger:hover { background: #c0392b; }
        .btn-info { background: #3498db; color: white; }
        .btn-info:hover { background: #2980b9; }
        .empty { text-align: center; padding: 40px; background: white; border-radius: 15px; }
        .toast {
            position: fixed;
            bottom: 20px;
            right: 20px;
            padding: 12px 24px;
            border-radius: 8px;
            z-index: 1000;
            animation: slideIn 0.3s ease;
            color: white;
            font-weight: 500;
        }
        @keyframes slideIn {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
        @media (max-width: 768px) { .pedidos-grid { grid-template-columns: 1fr; } }
    </style>
</head>
<body>
    <header>
        <h1><i class="fas fa-concierge-bell"></i>Garçom</h1>
        <div>
            <span><i class="fas fa-user"></i> <?php echo htmlspecialchars($_SESSION['utilizador_nome']); ?></span>
            <nav>
                <a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Sair</a>
            </nav>
        </div>
    </header>

    <div class="container">
        <div class="stats">
            <div class="stat-card"><h3 id="totalPedidos"><?php echo count($pedidos); ?></h3><p> Pedidos Ativos</p></div>
            <div class="stat-card"><h3 id="totalPreparando"><?php echo count(array_filter($pedidos, function($p) { return $p['status'] == 'preparando'; })); ?></h3><p>👨‍🍳 Em Preparação</p></div>
            <div class="stat-card"><h3 id="totalProntos"><?php echo count(array_filter($pedidos, function($p) { return $p['status'] == 'pronto'; })); ?></h3><p>✅ Prontos para Entrega</p></div>
        </div>

        <h2 style="margin-bottom: 20px;"> Pedidos em Andamento</h2>
        
        <div class="pedidos-grid" id="pedidosGrid">
            <?php if (empty($pedidos)): ?>
                <div class="empty">🎉 Nenhum pedido ativo no momento!</div>
            <?php endif; ?>
            
            <?php foreach ($pedidos as $pedido): ?>
                <div class="pedido-card" data-pedido-id="<?php echo $pedido['id']; ?>">
                    <div class="pedido-header">
                        <span class="pedido-numero"><i class="fas fa-receipt"></i> Pedido #<?php echo $pedido['numero_pedido']; ?></span>
                        <span class="status status-<?php echo $pedido['status']; ?>">
                            <?php 
                                if($pedido['status'] == 'pendente') echo '⏳ Pendente';
                                elseif($pedido['status'] == 'confirmado') echo '✅ Confirmado';
                                elseif($pedido['status'] == 'preparando') echo 'Preparando';
                                else echo 'Pronto';
                            ?>
                        </span>
                    </div>
                    <div class="pedido-body">
                        <div class="pedido-info">
                            <p><i class="fas fa-chair"></i> <strong>Mesa:</strong> <?php echo $pedido['mesa_numero'] ?? 'Delivery'; ?></p>
                            <p><i class="fas fa-box"></i> <strong>Itens:</strong> <?php echo $pedido['total_itens']; ?> produtos</p>
                            <p><i class="fas fa-money-bill"></i> <strong>Total:</strong> Kz <?php echo number_format($pedido['total'], 0, ',', '.'); ?></p>
                            <?php if (!empty($pedido['observacoes'])): ?>
                                <p><i class="fas fa-comment"></i> <strong>Obs:</strong> <?php echo htmlspecialchars($pedido['observacoes']); ?></p>
                            <?php endif; ?>
                        </div>
                        <div class="btn-group">
                            <?php if ($pedido['status'] == 'pendente'): ?>
                                <button class="btn btn-primary" onclick="atualizarStatus(<?php echo $pedido['id']; ?>, 'confirmado', this)">
                                    <i class="fas fa-check"></i> Confirmar Pedido
                                </button>
                            <?php elseif ($pedido['status'] == 'confirmado'): ?>
                                <button class="btn btn-warning" onclick="atualizarStatus(<?php echo $pedido['id']; ?>, 'preparando', this)">
                                    <i class="fas fa-utensils"></i> Enviar para Cozinha
                                </button>
                            <?php elseif ($pedido['status'] == 'preparando'): ?>
                                <button class="btn btn-info" onclick="atualizarStatus(<?php echo $pedido['id']; ?>, 'pronto', this)">
                                    <i class="fas fa-check-circle"></i> Marcar como Pronto
                                </button>
                            <?php elseif ($pedido['status'] == 'pronto'): ?>
                                <button class="btn btn-primary" onclick="atualizarStatus(<?php echo $pedido['id']; ?>, 'entregue', this)">
                                    <i class="fas fa-hand-peace"></i> Entregar à Mesa
                                </button>
                            <?php endif; ?>
                            <button class="btn btn-danger" onclick="atualizarStatus(<?php echo $pedido['id']; ?>, 'cancelado', this)">
                                <i class="fas fa-times"></i> Cancelar
                            </button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <script>
        function mostrarToast(mensagem, tipo = 'success') {
            const toast = document.createElement('div');
            toast.className = 'toast';
            toast.style.backgroundColor = tipo === 'success' ? '#27ae60' : '#e74c3c';
            toast.innerHTML = (tipo === 'success' ? '✅ ' : '❌ ') + mensagem;
            document.body.appendChild(toast);
            setTimeout(() => toast.remove(), 3000);
        }

        function atualizarStatus(pedidoId, novoStatus, button) {
            // Desabilitar botão
            if (button) {
                button.disabled = true;
                button.style.opacity = '0.6';
                const originalText = button.innerHTML;
                button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processando...';
            }
            
            console.log('Enviando requisição...');
            console.log('Pedido ID:', pedidoId);
            console.log('Novo Status:', novoStatus);
            
            // Criar FormData para enviar
            const formData = new URLSearchParams();
            formData.append('id', pedidoId);
            formData.append('status', novoStatus);
            
            // URL absoluta para evitar problemas de caminho
            const url = window.location.origin + '/RestauranteEstagio/api/atualizar_pedido.php';
            console.log('URL:', url);
            
            fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: formData.toString()
            })
            .then(response => {
                console.log('Resposta status:', response.status);
                if (!response.ok) {
                    throw new Error('HTTP error ' + response.status);
                }
                return response.json();
            })
            .then(data => {
                console.log('Resposta data:', data);
                if (data.success) {
                    mostrarToast(data.message || 'Status atualizado com sucesso!', 'success');
                    setTimeout(() => location.reload(), 1500);
                } else {
                    mostrarToast(data.message || 'Erro ao atualizar status', 'error');
                    if (button) {
                        button.disabled = false;
                        button.style.opacity = '1';
                        if (novoStatus === 'confirmado') button.innerHTML = '<i class="fas fa-check"></i> Confirmar Pedido';
                        else if (novoStatus === 'preparando') button.innerHTML = '<i class="fas fa-utensils"></i> Enviar para Cozinha';
                        else if (novoStatus === 'pronto') button.innerHTML = '<i class="fas fa-check-circle"></i> Marcar como Pronto';
                        else if (novoStatus === 'entregue') button.innerHTML = '<i class="fas fa-hand-peace"></i> Entregar à Mesa';
                        else button.innerHTML = '<i class="fas fa-times"></i> Cancelar';
                    }
                }
            })
            .catch(error => {
                console.error('Erro na requisição:', error);
                mostrarToast('Erro de conexão: ' + error.message, 'error');
                if (button) {
                    button.disabled = false;
                    button.style.opacity = '1';
                    if (novoStatus === 'confirmado') button.innerHTML = '<i class="fas fa-check"></i> Confirmar Pedido';
                    else if (novoStatus === 'preparando') button.innerHTML = '<i class="fas fa-utensils"></i> Enviar para Cozinha';
                    else if (novoStatus === 'pronto') button.innerHTML = '<i class="fas fa-check-circle"></i> Marcar como Pronto';
                    else if (novoStatus === 'entregue') button.innerHTML = '<i class="fas fa-hand-peace"></i> Entregar à Mesa';
                    else button.innerHTML = '<i class="fas fa-times"></i> Cancelar';
                }
            });
        }
        
        // Auto-refresh a cada 30 segundos
        setInterval(() => location.reload(), 30000);
    </script>
</body>
</html>