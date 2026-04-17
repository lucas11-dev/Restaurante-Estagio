<?php
// cliente/index.php
require_once __DIR__ . '/../includes/cliente.php';
ensure_cliente_logged_in();

// Conexão com o banco de dados
$host = 'localhost';
$dbname = 'restaurante_conect';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Erro de conexão: " . $e->getMessage());
}

// Buscar dados do cliente com foto
$stmt = $pdo->prepare("SELECT c.*, u.nome, u.email 
                       FROM cliente c 
                       INNER JOIN utilizador u ON c.utilizador_id = u.id 
                       WHERE u.id = :user_id");
$stmt->execute([':user_id' => $_SESSION['utilizador_id']]);
$cliente = $stmt->fetch(PDO::FETCH_ASSOC);

$mensagem = '';
$erro = '';

if (isset($_GET['success'])) {
    $mensagem = 'Item adicionado ao carrinho com sucesso.';
}
if (isset($_GET['error'])) {
    $erro = htmlspecialchars($_GET['error']);
}

// Consulta usando a tabela produto
$query = "SELECT c.id AS categoria_id, c.nome AS categoria_nome, c.icone,
                 p.id AS produto_id, p.nome AS produto_nome, p.descricao, p.preco, p.imagem_url, p.imagem
          FROM categoria c
          INNER JOIN produto p ON p.categoria_id = c.id
          WHERE (c.status = 'ativo' OR c.status IS NULL)
            AND p.disponivel = 1
          ORDER BY c.ordem ASC, p.nome ASC";

$stmt = $pdo->prepare($query);
$stmt->execute();
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Organizar itens por categoria
$categorias = [];
foreach ($items as $item) {
    $cat_id = $item['categoria_id'];
    if (!isset($categorias[$cat_id])) {
        $categorias[$cat_id] = [
            'nome' => $item['categoria_nome'],
            'icone' => $item['icone'] ?? '🍽️',
            'itens' => []
        ];
    }
    $categorias[$cat_id]['itens'][] = $item;
}

// Obter carrinho
$cart = get_cart();
$cartCount = array_sum(array_column($cart, 'quantidade'));

// Buscar notificações não lidas
try {
    $stmt = $pdo->prepare("SELECT id FROM cliente WHERE utilizador_id = :user_id");
    $stmt->execute([':user_id' => $_SESSION['utilizador_id']]);
    $cliente_data = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($cliente_data) {
        $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM notificacao_pedido n 
                               INNER JOIN pedido p ON n.pedido_id = p.id 
                               WHERE p.cliente_id = :cliente_id AND n.lida = 0 AND n.destinatario_tipo = 'cliente'");
        $stmt->execute([':cliente_id' => $cliente_data['id']]);
        $notificacoes = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    } else {
        $notificacoes = 0;
    }
} catch (PDOException $e) {
    $notificacoes = 0;
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cardápio - FOODNET</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Arial, sans-serif; background: #f8f9fa; color: #333; }
        
        header { background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); color: white; padding: 20px 30px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .topbar { display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; gap: 20px; max-width: 1200px; margin: 0 auto; }
        .user-info { display: flex; align-items: center; gap: 15px; }
        .user-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: rgba(255,255,255,0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            cursor: pointer;
            transition: transform 0.3s;
        }
        .user-avatar:hover { transform: scale(1.05); }
        .user-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .user-avatar span { font-size: 28px; }
        .user-text { text-align: right; }
        .user-text h1 { margin: 0; font-size: 20px; }
        .user-text p { margin: 5px 0 0; opacity: 0.9; font-size: 12px; }
        
        .topbar nav { display: flex; gap: 20px; align-items: center; flex-wrap: wrap; }
        .topbar nav a { color: white; text-decoration: none; font-weight: 500; transition: opacity 0.3s; }
        .topbar nav a:hover { opacity: 0.8; }
        
        .badge { background: #e74c3c; padding: 4px 10px; border-radius: 20px; font-size: 12px; margin-left: 8px; }
        .notif-badge { background: #e74c3c; padding: 2px 8px; border-radius: 20px; font-size: 11px; margin-left: 5px; }
        
        main { max-width: 1200px; margin: 40px auto; padding: 0 20px; }
        
        .mensagem, .erro { padding: 15px 20px; border-radius: 10px; margin-bottom: 30px; text-align: center; }
        .mensagem { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .erro { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        
        .secao { margin-bottom: 50px; }
        .secao h2 { 
            font-size: 28px; 
            margin-bottom: 25px; 
            color: #1e3c72;
            border-left: 4px solid #ffd700;
            padding-left: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .item-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 25px; }
        
        .item-card { 
            background: white; 
            border-radius: 15px; 
            overflow: hidden; 
            box-shadow: 0 5px 20px rgba(0,0,0,0.08); 
            transition: transform 0.3s, box-shadow 0.3s;
        }
        .item-card:hover { transform: translateY(-5px); box-shadow: 0 10px 30px rgba(0,0,0,0.15); }
        
        .item-card img { 
            width: 100%; 
            height: 200px; 
            object-fit: cover; 
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        
        .item-card .conteudo { padding: 20px; }
        .item-card h3 { font-size: 20px; margin-bottom: 10px; color: #333; }
        .item-card p { color: #666; line-height: 1.5; margin-bottom: 15px; }
        .item-card .preco { font-size: 24px; font-weight: bold; color: #1e3c72; margin-bottom: 15px; }
        .item-card .preco small { font-size: 12px; font-weight: normal; color: #999; }
        
        .item-card button { 
            width: 100%; 
            padding: 12px; 
            border: none; 
            border-radius: 8px; 
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            color: white; 
            font-size: 16px;
            font-weight: 600;
            cursor: pointer; 
            transition: all 0.3s;
        }
        .item-card button:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(30,60,114,0.3); }
        
        .rodape { 
            text-align: center; 
            padding: 30px; 
            background: #2d3748; 
            color: white; 
            margin-top: 60px;
        }
        
        @media (max-width: 768px) {
            .topbar { flex-direction: column; text-align: center; }
            .user-info { flex-direction: column; text-align: center; }
            .user-text { text-align: center; }
            .item-grid { grid-template-columns: 1fr; }
            .secao h2 { font-size: 24px; }
        }
    </style>
</head>
<body>
    <header>
        <div class="topbar">
            <div class="user-info">
                <a href="perfil.php" class="user-avatar">
                    <?php if (!empty($cliente['foto_perfil']) && file_exists(__DIR__ . '/../' . $cliente['foto_perfil'])): ?>
                        <img src="../<?php echo $cliente['foto_perfil']; ?>" alt="Foto de perfil">
                    <?php else: ?>
                        <span>👤</span>
                    <?php endif; ?>
                </a>
                <div class="user-text">
                    <h1>Olá, <?php echo htmlspecialchars($_SESSION['utilizador_nome']); ?>! </h1>
                    <p>Bem-vindo ao FOODNET</p>
                </div>
            </div>
            <nav>
                <a href="perfil.php">👤 Meu Perfil</a>
                <a href="notificacoes.php">
                    🔔 Notificações
                    <?php if ($notificacoes > 0): ?>
                        <span class="notif-badge"><?php echo $notificacoes; ?></span>
                    <?php endif; ?>
                </a>
                <a href="cart.php"> Carrinho<?php if ($cartCount > 0): ?><span class="badge"><?php echo $cartCount; ?></span><?php endif; ?></a>
                <a href="orders.php"> Meus Pedidos</a>
                <a href="../logout.php"> <- Sair</a>
            </nav>
        </div>
    </header>

    <main>
        <?php if ($mensagem): ?>
            <div class="mensagem">✅ <?php echo htmlspecialchars($mensagem); ?></div>
        <?php endif; ?>
        
        <?php if ($erro): ?>
            <div class="erro">❌ <?php echo htmlspecialchars($erro); ?></div>
        <?php endif; ?>

        <?php if (empty($categorias)): ?>
            <div class="erro">Nenhum item disponível no cardápio no momento. Volte em breve!</div>
        <?php endif; ?>

        <?php foreach ($categorias as $categoria): ?>
            <section class="secao">
                <h2>
                    <span><?php echo htmlspecialchars($categoria['icone']); ?></span>
                    <?php echo htmlspecialchars($categoria['nome']); ?>
                </h2>
                <div class="item-grid">
                    <?php foreach ($categoria['itens'] as $item): ?>
                        <article class="item-card">
                            <?php if (!empty($item['imagem_url'])): ?>
                                <img src="../<?php echo htmlspecialchars($item['imagem_url']); ?>" alt="<?php echo htmlspecialchars($item['produto_nome']); ?>">
                            <?php elseif (!empty($item['imagem'])): ?>
                                <div style="display: flex; align-items: center; justify-content: center; font-size: 80px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); height: 200px;">
                                    <?php echo htmlspecialchars($item['imagem']); ?>
                                </div>
                            <?php else: ?>
                                <div style="display: flex; align-items: center; justify-content: center; font-size: 80px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); height: 200px;">
                                    
                                </div>
                            <?php endif; ?>
                            <div class="conteudo">
                                <h3><?php echo htmlspecialchars($item['produto_nome']); ?></h3>
                                <p><?php echo htmlspecialchars($item['descricao']); ?></p>
                                <div class="preco">
                                    Kz <?php echo format_money($item['preco']); ?>
                                    <small>/ porção</small>
                                </div>
                                <form method="POST" action="add_to_cart.php">
                                    <input type="hidden" name="item_id" value="<?php echo (int) $item['produto_id']; ?>">
                                    <button type="submit">🛒 Adicionar ao carrinho</button>
                                </form>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php endforeach; ?>
    </main>

    <footer class="rodape">
        <p> &copy; <?php echo date('Y'); ?> FOODNET - Seu restaurante digitalizado e conectado</p>
    </footer>
</body>
</html>