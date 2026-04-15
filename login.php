<?php
// login.php
session_start();

$host = 'localhost';
$dbname = 'restaurante_conect';
$username = 'root';
$password = '';

$erro = '';

// Se jÃ¡ estiver logado, redireciona baseado no tipo
if (isset($_SESSION['utilizador_id'])) {
    redirecionarPorTipo($_SESSION['utilizador_tipo']);
    exit;
}

function redirecionarPorTipo($tipo) {
    switch($tipo) {
        case 'admin':
        case 'gerente':
            header('Location: admin/index.php');
            break;
        case 'garcom':
            header('Location: garcon/index.php');
            break;
        default:
            header('Location: ./cliente/index.php');
            break;
    }
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $senha = $_POST['senha'] ?? '';
    
    if (empty($email) || empty($senha)) {
        $erro = 'Preencha todos os campos';
    } else {
        try {
            $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            $query = "SELECT id, nome, email, senha, tipo, ativo FROM utilizador WHERE email = :email";
            $stmt = $pdo->prepare($query);
            $stmt->execute([':email' => $email]);
            $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($usuario && $usuario['senha'] === $senha) {
                if ($usuario['status'] !== 'ativo') {
                    $erro = 'Sua conta estÃ¡ inativa. Contate o administrador.';
                } else {
                    $_SESSION['utilizador_id'] = $usuario['id'];
                    $_SESSION['utilizador_nome'] = $usuario['nome'];
                    $_SESSION['utilizador_email'] = $usuario['email'];
                    $_SESSION['utilizador_tipo'] = $usuario['tipo'];
                    
                    // Redirecionar baseado no tipo
                    redirecionarPorTipo($usuario['tipo']);
                    exit;
                }
            } else {
                $erro = 'Email ou senha incorretos';
            }
        } catch (PDOException $e) {
            $erro = 'Erro ao fazer login. Tente novamente.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Restaurante Conect</title>
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
            max-width: 450px;
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
        }
        input:focus { outline: none; border-color: #667eea; }
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
        }
        .btn-submit:hover { transform: translateY(-2px); }
        .register-link { text-align: center; margin-top: 20px; color: #666; }
        .register-link a { color: #667eea; text-decoration: none; font-weight: 600; }
        .error-message {
            background: #f8d7da;
            color: #721c24;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
        }
        .info-box {
            background: #d4edda;
            color: #155724;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Restaurante Conect</h1>
            <p>FaÃ§a login para continuar</p>
        </div>
        
        <div class="form-container">
            
            <?php if ($erro): ?>
                <div class="error-message">âŒ <?php echo htmlspecialchars($erro); ?></div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" required>
                </div>
                
                <div class="form-group">
                    <label for="senha">Senha</label>
                    <input type="password" id="senha" name="senha" required>
                </div>
                
                <button type="submit" class="btn-submit">Entrar</button>
            </form>
            
            <div class="register-link">
                NÃ£o tem uma conta? <a href="register.php">Cadastre-se</a>
            </div>
        </div>
    </div>
</body>
</html>


