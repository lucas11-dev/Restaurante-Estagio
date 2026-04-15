<?php
// register.php
session_start();

// Incluir configuraÃ§Ã£o do banco
require_once 'includes/database.php';

$erros = [];
$sucesso = false;
$dados = $_POST;

// Se jÃ¡ estiver logado, redireciona
if (isset($_SESSION['utilizador_id'])) {
    header('Location: cliente/index.php');
    exit;
}

// Processar formulÃ¡rio
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // VALIDAÃ‡Ã•ES
    $nome = trim($_POST['nome'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $senha = $_POST['senha'] ?? '';
    $confirmar_senha = $_POST['confirmar_senha'] ?? '';
    $telefone = trim($_POST['telefone'] ?? '');
    
    // Validar nome
    if (empty($nome)) {
        $erros['nome'] = 'Nome Ã© obrigatÃ³rio';
    } elseif (strlen($nome) < 3) {
        $erros['nome'] = 'Nome deve ter pelo menos 3 caracteres';
    } elseif (strlen($nome) > 120) {
        $erros['nome'] = 'Nome muito longo';
    }
    
    // Validar email
    if (empty($email)) {
        $erros['email'] = 'Email Ã© obrigatÃ³rio';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erros['email'] = 'Email invÃ¡lido';
    }
    
    // Validar senha
    if (empty($senha)) {
        $erros['senha'] = 'Senha Ã© obrigatÃ³ria';
    } elseif (strlen($senha) < 6) {
        $erros['senha'] = 'Senha deve ter pelo menos 6 caracteres';
    }
    
    // Validar confirmaÃ§Ã£o de senha
    if ($senha !== $confirmar_senha) {
        $erros['confirmar_senha'] = 'As senhas nÃ£o coincidem';
    }
    
    // Validar telefone (opcional)
    if (!empty($telefone)) {
        $apenas_numeros = preg_replace('/[^0-9]/', '', $telefone);
        if (strlen($apenas_numeros) === 9) {
            $telefone = '+244' . $apenas_numeros;
        } elseif (strlen($apenas_numeros) === 12 && substr($apenas_numeros, 0, 3) === '244') {
            $telefone = '+' . $apenas_numeros;
        } else {
            $erros['telefone'] = 'Digite 9 nÃºmeros (ex: 923456789)';
        }
    }
    
    // Se nÃ£o houver erros, prosseguir
    if (empty($erros)) {
        try {
            $database = new Database();
            $db = $database->getConnection();
            
            // Verificar se email jÃ¡ existe
            $check = $db->prepare("SELECT id FROM utilizador WHERE email = :email");
            $check->execute([':email' => $email]);
            
            if ($check->fetch()) {
                $erros['email'] = 'Este email jÃ¡ estÃ¡ cadastrado';
            } else {
                // Iniciar transaÃ§Ã£o
                $db->beginTransaction();
                
                // Inserir na tabela utilizador
                $sql = "INSERT INTO utilizador (nome, email, senha, tipo, ativo) 
                        VALUES (:nome, :email, :senha, 'cliente', 1)";
                $stmt = $db->prepare($sql);
                $stmt->execute([
                    ':nome' => $nome,
                    ':email' => $email,
                    ':senha' => $senha
                ]);
                
                $utilizador_id = $db->lastInsertId();
                
                // Inserir na tabela cliente
                $sql2 = "INSERT INTO cliente (utilizador_id, telefone) 
                         VALUES (:utilizador_id, :telefone)";
                $stmt2 = $db->prepare($sql2);
                $stmt2->execute([
                    ':utilizador_id' => $utilizador_id,
                    ':telefone' => $telefone ?: null
                ]);
                
                $db->commit();
                
                // Criar sessÃ£o
                $_SESSION['utilizador_id'] = $utilizador_id;
                $_SESSION['utilizador_nome'] = $nome;
                $_SESSION['utilizador_email'] = $email;
                $_SESSION['utilizador_tipo'] = 'cliente';
                
                $sucesso = true;
                
                // Redirecionar
                header('Location: cliente/index.php');
                exit;
                
            }
        } catch (PDOException $e) {
            if (isset($db)) $db->rollBack();
            $erros['geral'] = 'Erro ao cadastrar: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro - Restaurante Conect</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            overflow: hidden;
            width: 100%;
            max-width: 500px;
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .header h1 { font-size: 28px; margin-bottom: 10px; }
        .form-container { padding: 30px; }
        .form-group { margin-bottom: 20px; }
        label { display: block; margin-bottom: 8px; color: #333; font-weight: 500; }
        input {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 14px;
            transition: all 0.3s;
        }
        input:focus { outline: none; border-color: #667eea; }
        input.error { border-color: #e74c3c; }
        input.success { border-color: #27ae60; }
        .error-message {
            color: #e74c3c;
            font-size: 12px;
            margin-top: 5px;
            display: block;
        }
        .btn-submit {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s;
        }
        .btn-submit:hover { transform: translateY(-2px); }
        .login-link { text-align: center; margin-top: 20px; color: #666; }
        .login-link a { color: #667eea; text-decoration: none; font-weight: 600; }
        .error-general {
            background: #f8d7da;
            color: #721c24;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
        }
        .success-message {
            background: #d4edda;
            color: #155724;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
        }
        .telefone-container { position: relative; }
        .telefone-prefix {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #666;
            font-weight: 500;
        }
        .telefone-input { padding-left: 55px !important; }
        small { display: block; margin-top: 5px; color: #666; font-size: 11px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Restaurante Conect</h1>
            <p>Crie sua conta e faÃ§a seu pedido</p>
        </div>
        
        <div class="form-container">
            <?php if (isset($erros['geral'])): ?>
                <div class="error-general">âŒ <?php echo htmlspecialchars($erros['geral']); ?></div>
            <?php endif; ?>
            
            <?php if ($sucesso): ?>
                <div class="success-message">âœ… Cadastro realizado! Redirecionando...</div>
            <?php endif; ?>
            
            <form method="POST" action="" id="registerForm">
                <div class="form-group">
                    <label for="nome">Nome completo </label>
                    <input type="text" id="nome" name="nome" value="<?php echo htmlspecialchars($dados['nome'] ?? ''); ?>">
                    <span class="error-message" id="nome-error"><?php echo $erros['nome'] ?? ''; ?></span>
                </div>
                
                <div class="form-group">
                    <label for="email">Email </label>
                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($dados['email'] ?? ''); ?>">
                    <span class="error-message" id="email-error"><?php echo $erros['email'] ?? ''; ?></span>
                </div>
                
                <div class="form-group">
                    <label for="senha">Senha </label>
                    <input type="password" id="senha" name="senha">
                    <span class="error-message" id="senha-error"><?php echo $erros['senha'] ?? ''; ?></span>
                </div>
                
                <div class="form-group">
                    <label for="confirmar_senha">Confirmar senha </label>
                    <input type="password" id="confirmar_senha" name="confirmar_senha">
                    <span class="error-message" id="confirmar-error"><?php echo $erros['confirmar_senha'] ?? ''; ?></span>
                </div>
                
                <div class="form-group">
                    <label for="telefone">Telefone</label>
                    <div class="telefone-container">
                        <span class="telefone-prefix">+244</span>
                        <input type="tel" id="telefone" name="telefone" class="telefone-input" 
                             maxlength="9" value="<?php echo htmlspecialchars($dados['telefone'] ?? ''); ?>">
                    </div>
                    <!-- <small>Digite apenas os 9 nÃºmeros</small> -->
                    <span class="error-message" id="telefone-error"><?php echo $erros['telefone'] ?? ''; ?></span>
                </div>
                
                <button type="submit" class="btn-submit" id="submitBtn">Criar conta</button>
            </form>
            
            <div class="login-link">
                JÃ¡ tem uma conta? <a href="login.php">FaÃ§a login</a>
            </div>
        </div>
    </div>
    
    <script>
        // ValidaÃ§Ã£o em tempo real
        const form = document.getElementById('registerForm');
        
        function validarNome() {
            const nome = document.getElementById('nome');
            const error = document.getElementById('nome-error');
            if (nome.value.trim().length < 3) {
                error.textContent = 'Nome deve ter pelo menos 3 caracteres';
                nome.classList.add('error');
                return false;
            } else if (nome.value.trim().length === 0) {
                error.textContent = 'Nome Ã© obrigatÃ³rio';
                nome.classList.add('error');
                return false;
            } else {
                error.textContent = '';
                nome.classList.remove('error');
                nome.classList.add('success');
                return true;
            }
        }
        
        function validarEmail() {
            const email = document.getElementById('email');
            const error = document.getElementById('email-error');
            const regex = /^[^\s@]+@([^\s@]+\.)+[^\s@]+$/;
            if (!regex.test(email.value)) {
                error.textContent = 'Email invÃ¡lido';
                email.classList.add('error');
                return false;
            } else if (email.value.trim().length === 0) {
                error.textContent = 'Email Ã© obrigatÃ³rio';
                email.classList.add('error');
                return false;
            } else {
                error.textContent = '';
                email.classList.remove('error');
                email.classList.add('success');
                return true;
            }
        }
        
        function validarSenha() {
            const senha = document.getElementById('senha');
            const error = document.getElementById('senha-error');
            if (senha.value.length < 6) {
                error.textContent = 'Senha deve ter pelo menos 6 caracteres';
                senha.classList.add('error');
                return false;
            } else if (senha.value.length === 0) {
                error.textContent = 'Senha Ã© obrigatÃ³ria';
                senha.classList.add('error');
                return false;
            } else {
                error.textContent = '';
                senha.classList.remove('error');
                senha.classList.add('success');
                validarConfirmarSenha();
                return true;
            }
        }
        
        function validarConfirmarSenha() {
            const senha = document.getElementById('senha').value;
            const confirmar = document.getElementById('confirmar_senha');
            const error = document.getElementById('confirmar-error');
            if (confirmar.value !== senha) {
                error.textContent = 'As senhas nÃ£o coincidem';
                confirmar.classList.add('error');
                return false;
            } else if (confirmar.value.length === 0) {
                error.textContent = 'Confirme sua senha';
                confirmar.classList.add('error');
                return false;
            } else {
                error.textContent = '';
                confirmar.classList.remove('error');
                confirmar.classList.add('success');
                return true;
            }
        }
        
        function validarTelefone() {
            const telefone = document.getElementById('telefone');
            const error = document.getElementById('telefone-error');
            let valor = telefone.value.replace(/[^0-9]/g, '');
            
            if (valor.length === 0) {
                error.textContent = '';
                telefone.classList.remove('error', 'success');
                return true;
            }
            
            if (valor.length === 9) {
                error.textContent = '';
                telefone.classList.remove('error');
                telefone.classList.add('success');
                return true;
            } else {
                error.textContent = 'Digite 9 nÃºmeros';
                telefone.classList.add('error');
                return false;
            }
        }
        
        document.getElementById('nome').addEventListener('input', validarNome);
        document.getElementById('email').addEventListener('input', validarEmail);
        document.getElementById('senha').addEventListener('input', validarSenha);
        document.getElementById('confirmar_senha').addEventListener('input', validarConfirmarSenha);
        document.getElementById('telefone').addEventListener('input', function() {
            let valor = this.value.replace(/[^0-9]/g, '');
            if (valor.length > 9) valor = valor.substring(0, 9);
            this.value = valor;
            validarTelefone();
        });
        
        form.addEventListener('submit', function(e) {
            if (!validarNome() || !validarEmail() || !validarSenha() || !validarConfirmarSenha() || !validarTelefone()) {
                e.preventDefault();
            }
        });
    </script>
</body>
</html>
