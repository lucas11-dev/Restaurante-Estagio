<?php
require_once __DIR__ . '/../includes/cliente.php';
ensure_cliente_logged_in();
$db = get_db();

// Buscar notificações
$stmt = $db->prepare("SELECT n.*, p.numero_pedido 
                      FROM notificacao_pedido n
                      INNER JOIN pedido p ON n.pedido_id = p.id
                      INNER JOIN cliente c ON p.cliente_id = c.id
                      WHERE c.utilizador_id = :user_id AND n.destinatario_tipo = 'cliente'
                      ORDER BY n.criado_em DESC");
$stmt->execute([':user_id' => $_SESSION['utilizador_id']]);
$notificacoes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Marcar como lidas
$stmt = $db->prepare("UPDATE notificacao_pedido n
                      INNER JOIN pedido p ON n.pedido_id = p.id
                      INNER JOIN cliente c ON p.cliente_id = c.id
                      SET n.lida = 1 
                      WHERE c.utilizador_id = :user_id AND n.destinatario_tipo = 'cliente'");
$stmt->execute([':user_id' => $_SESSION['utilizador_id']]);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notificações - FOODNET</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f4f7; }
        header { background: #2c3e50; color: white; padding: 20px; }
        header a { color: white; text-decoration: none; margin-left: 16px; }
        .container { max-width: 800px; margin: 30px auto; padding: 0 20px; }
        .notificacao { background: white; border-radius: 10px; padding: 15px; margin-bottom: 10px; border-left: 4px solid #27ae60; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        .notificacao .data { font-size: 12px; color: #999; margin-top: 5px; }
        .empty { text-align: center; padding: 40px; background: white; border-radius: 10px; }
        .btn-back { background: #3498db; color: white; padding: 10px 20px; border-radius: 8px; text-decoration: none; display: inline-block; margin-top: 20px; }
    </style>
</head>
<body>
    <header>
        <div>🔔 Notificações</div>
        <div>
            <a href="index.php">← Voltar</a>
            <a href="../logout.php">Sair</a>
        </div>
    </header>
    <div class="container">
        <h1>Minhas Notificações</h1>
        <?php if (empty($notificacoes)): ?>
            <div class="empty">📭 Nenhuma notificação no momento.</div>
        <?php else: ?>
            <?php foreach ($notificacoes as $notif): ?>
                <div class="notificacao">
                    <p><?php echo htmlspecialchars($notif['mensagem']); ?></p>
                    <div class="data"><?php echo date('d/m/Y H:i', strtotime($notif['criado_em'])); ?></div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
        <a href="index.php" class="btn-back">← Voltar ao cardápio</a>
    </div>
</body>
</html>