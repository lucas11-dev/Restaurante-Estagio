<?php
// cliente/perfil.php
require_once __DIR__ . '/../includes/cliente.php';
ensure_cliente_logged_in();

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

// Buscar dados do cliente
$stmt = $pdo->prepare("SELECT c.*, u.nome, u.email 
                       FROM cliente c 
                       INNER JOIN utilizador u ON c.utilizador_id = u.id 
                       WHERE u.id = :user_id");
$stmt->execute([':user_id' => $_SESSION['utilizador_id']]);
$cliente = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$cliente) {
    // Criar registro de cliente se não existir
    $stmt = $pdo->prepare("INSERT INTO cliente (utilizador_id) VALUES (:user_id)");
    $stmt->execute([':user_id' => $_SESSION['utilizador_id']]);
    
    // Buscar novamente
    $stmt = $pdo->prepare("SELECT c.*, u.nome, u.email 
                           FROM cliente c 
                           INNER JOIN utilizador u ON c.utilizador_id = u.id 
                           WHERE u.id = :user_id");
    $stmt->execute([':user_id' => $_SESSION['utilizador_id']]);
    $cliente = $stmt->fetch(PDO::FETCH_ASSOC);
}

$mensagem = '';
$erro = '';

// Processar atualização do perfil
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome'] ?? '');
    $telefone = trim($_POST['telefone'] ?? '');
    $endereco = trim($_POST['endereco'] ?? '');
    $cidade = trim($_POST['cidade'] ?? '');
    $data_nascimento = trim($_POST['data_nascimento'] ?? '');
    
    if (empty($nome)) {
        $erro = 'Nome é obrigatório';
    } else {
        try {
            // Atualizar nome na tabela utilizador
            $stmt = $pdo->prepare("UPDATE utilizador SET nome = :nome WHERE id = :id");
            $stmt->execute([':nome' => $nome, ':id' => $_SESSION['utilizador_id']]);
            $_SESSION['utilizador_nome'] = $nome;
            
            // Atualizar dados do cliente
            $stmt = $pdo->prepare("UPDATE cliente SET 
                                    telefone = :telefone,
                                    endereco = :endereco,
                                    cidade = :cidade,
                                    data_nascimento = :data_nascimento
                                  WHERE utilizador_id = :user_id");
            $stmt->execute([
                ':telefone' => $telefone ?: null,
                ':endereco' => $endereco ?: null,
                ':cidade' => $cidade ?: null,
                ':data_nascimento' => $data_nascimento ?: null,
                ':user_id' => $_SESSION['utilizador_id']
            ]);
            
            // Processar upload da foto
            if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
                $upload_dir = __DIR__ . '/../uploads/perfil/';
                if (!file_exists($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }
                
                $ext = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
                $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                
                if (in_array($ext, $allowed)) {
                    // Deletar foto antiga se existir
                    if (!empty($cliente['foto_perfil']) && file_exists(__DIR__ . '/../' . $cliente['foto_perfil'])) {
                        unlink(__DIR__ . '/../' . $cliente['foto_perfil']);
                    }
                    
                    $filename = 'perfil_' . $_SESSION['utilizador_id'] . '_' . time() . '.' . $ext;
                    $filepath = 'uploads/perfil/' . $filename;
                    $fullpath = __DIR__ . '/../' . $filepath;
                    
                    if (move_uploaded_file($_FILES['foto']['tmp_name'], $fullpath)) {
                        $stmt = $pdo->prepare("UPDATE cliente SET foto_perfil = :foto WHERE utilizador_id = :user_id");
                        $stmt->execute([':foto' => $filepath, ':user_id' => $_SESSION['utilizador_id']]);
                        $cliente['foto_perfil'] = $filepath;
                    }
                } else {
                    $erro = 'Formato de imagem não permitido. Use JPG, PNG, GIF ou WEBP.';
                }
            }
            
            if (empty($erro)) {
                $mensagem = 'Perfil atualizado com sucesso!';
                // Recarregar dados
                $stmt = $pdo->prepare("SELECT c.*, u.nome, u.email 
                                       FROM cliente c 
                                       INNER JOIN utilizador u ON c.utilizador_id = u.id 
                                       WHERE u.id = :user_id");
                $stmt->execute([':user_id' => $_SESSION['utilizador_id']]);
                $cliente = $stmt->fetch(PDO::FETCH_ASSOC);
            }
        } catch (PDOException $e) {
            $erro = 'Erro ao atualizar: ' . $e->getMessage();
        }
    }
}

// Buscar carrinho
$cart = get_cart();
$cartCount = array_sum(array_column($cart, 'quantidade'));
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meu Perfil - Restaurante Conect</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Arial, sans-serif; background: #f8f9fa; color: #333; }
        
        header { background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); color: white; padding: 20px 30px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .topbar { display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; gap: 20px; max-width: 1200px; margin: 0 auto; }
        .topbar h1 { margin: 0; font-size: 24px; }
        .topbar nav { display: flex; gap: 20px; align-items: center; flex-wrap: wrap; }
        .topbar nav a { color: white; text-decoration: none; font-weight: 500; transition: opacity 0.3s; }
        .topbar nav a:hover { opacity: 0.8; }
        
        .badge { background: #e74c3c; padding: 4px 10px; border-radius: 20px; font-size: 12px; margin-left: 8px; }
        
        main { max-width: 800px; margin: 40px auto; padding: 0 20px; }
        
        .mensagem, .erro { padding: 15px 20px; border-radius: 10px; margin-bottom: 30px; text-align: center; }
        .mensagem { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .erro { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        
        .perfil-card {
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
        }
        
        .perfil-header {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            padding: 30px;
            text-align: center;
            position: relative;
        }
        
        .foto-perfil {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            background: white;
            margin: 0 auto 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
            cursor: pointer;
            transition: transform 0.3s;
        }
        
        .foto-perfil:hover {
            transform: scale(1.05);
        }
        
        .foto-perfil img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .foto-perfil span {
            font-size: 60px;
            opacity: 0.7;
        }
        
        .upload-btn {
            background: rgba(255,255,255,0.2);
            color: white;
            border: none;
            padding: 8px 20px;
            border-radius: 20px;
            cursor: pointer;
            font-size: 14px;
            margin-top: 10px;
        }
        
        .upload-btn:hover {
            background: rgba(255,255,255,0.3);
        }
        
        .perfil-body {
            padding: 30px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
        }
        
        input, textarea {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 15px;
            transition: border-color 0.3s;
        }
        
        input:focus, textarea:focus {
            outline: none;
            border-color: #1e3c72;
        }
        
        textarea {
            resize: vertical;
            min-height: 80px;
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        
        .btn-salvar {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            color: white;
            border: none;
            padding: 14px 30px;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            width: 100%;
            transition: transform 0.3s;
        }
        
        .btn-salvar:hover {
            transform: translateY(-2px);
        }
        
        .info-text {
            font-size: 13px;
            color: #666;
            margin-top: 5px;
        }
        
        @media (max-width: 768px) {
            .form-row {
                grid-template-columns: 1fr;
                gap: 15px;
            }
            .foto-perfil {
                width: 120px;
                height: 120px;
            }
        }
    </style>
</head>
<body>
    <header>
        <div class="topbar">
            <div>
                <h1>👤 Meu Perfil</h1>
            </div>
            <nav>
                <a href="index.php">🍽️ Cardápio</a>
                <a href="cart.php">🛒 Carrinho<?php if ($cartCount > 0): ?><span class="badge"><?php echo $cartCount; ?></span><?php endif; ?></a>
                <a href="orders.php">📦 Meus Pedidos</a>
                <a href="../logout.php">🚪 Sair</a>
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

        <div class="perfil-card">
            <div class="perfil-header">
                <form method="POST" enctype="multipart/form-data" id="perfilForm">
                    <div class="foto-perfil" onclick="document.getElementById('fotoInput').click()">
                        <?php if (!empty($cliente['foto_perfil']) && file_exists(__DIR__ . '/../' . $cliente['foto_perfil'])): ?>
                            <img src="../<?php echo $cliente['foto_perfil']; ?>" alt="Foto de perfil" id="fotoPreview">
                        <?php else: ?>
                            <span id="fotoPreview">👤</span>
                        <?php endif; ?>
                    </div>
                    <input type="file" id="fotoInput" name="foto" accept="image/*" style="display: none;" onchange="previewFoto(this)">
                    <div class="info-text">Clique na foto para alterar</div>
            </div>
            
            <div class="perfil-body">
                    <div class="form-group">
                        <label>Nome completo</label>
                        <input type="text" name="nome" value="<?php echo htmlspecialchars($cliente['nome']); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" value="<?php echo htmlspecialchars($cliente['email']); ?>" disabled style="background: #f5f5f5;">
                        <div class="info-text">O email não pode ser alterado</div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>Telefone</label>
                            <input type="tel" name="telefone" value="<?php echo htmlspecialchars($cliente['telefone'] ?? ''); ?>" placeholder="+244 923 456 789">
                        </div>
                        <div class="form-group">
                            <label>Data de Nascimento</label>
                            <input type="date" name="data_nascimento" value="<?php echo htmlspecialchars($cliente['data_nascimento'] ?? ''); ?>">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>Endereço</label>
                        <textarea name="endereco" placeholder="Seu endereço completo"><?php echo htmlspecialchars($cliente['endereco'] ?? ''); ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label>Cidade</label>
                        <input type="text" name="cidade" value="<?php echo htmlspecialchars($cliente['cidade'] ?? ''); ?>" placeholder="Luanda, Benguela, Huíla...">
                    </div>
                    
                    <button type="submit" class="btn-salvar">💾 Salvar Alterações</button>
                </form>
            </div>
        </div>
        
        <div style="text-align: center; margin-top: 20px;">
            <a href="index.php" style="color: #1e3c72; text-decoration: none;">← Voltar ao cardápio</a>
        </div>
    </main>

    <script>
        function previewFoto(input) {
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const preview = document.getElementById('fotoPreview');
                    if (preview.tagName === 'SPAN') {
                        const img = document.createElement('img');
                        img.id = 'fotoPreview';
                        img.src = e.target.result;
                        img.style.width = '100%';
                        img.style.height = '100%';
                        img.style.objectFit = 'cover';
                        preview.parentNode.replaceChild(img, preview);
                    } else {
                        preview.src = e.target.result;
                    }
                };
                reader.readAsDataURL(input.files[0]);
            }
        }
    </script>
</body>
</html>