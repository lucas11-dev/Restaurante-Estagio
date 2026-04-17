<?php
// login.php
session_start();

$host = 'localhost';
$dbname = 'restaurante_conect';
$username = 'root';
$password = '';

$erro = '';

function redirecionarPorTipo($tipo) {
    switch($tipo) {
        case 'admin':
            header('Location: admin/index.php');
            break;
        case 'gerente':
            header('Location: admin/index.php');
            break;
        case 'garcom':
            header('Location: painel_garcom.php');
            break;
        case 'cozinha':
            header('Location: painel_cozinha.php');
            break;
        case 'cliente':
            header('Location: cliente/index.php');
            break;
        default:
            header('Location: index.php');
            break;
    }
    exit;
}

// Se já estiver logado, redireciona (mas verificar se não está na página de login)
if (isset($_SESSION['utilizador_id']) && isset($_SESSION['utilizador_tipo'])) {
    // Evitar loop: se já está na página correta, não redirecionar
    $current_file = basename($_SERVER['PHP_SELF']);
    $tipo = $_SESSION['utilizador_tipo'];
    
    if ($tipo == 'admin' && $current_file == 'login.php') {
        header('Location: admin/index.php');
        exit;
    } elseif ($tipo == 'gerente' && $current_file == 'login.php') {
        header('Location: admin/index.php');
        exit;
    } elseif ($tipo == 'garcom' && $current_file == 'login.php') {
        header('Location: painel_garcom.php');
        exit;
    } elseif ($tipo == 'cliente' && $current_file == 'login.php') {
        header('Location: cliente/index.php');
        exit;
    }
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
            
            $query = "SELECT id, nome, email, senha, tipo, status FROM utilizador WHERE email = :email";
            $stmt = $pdo->prepare($query);
            $stmt->execute([':email' => $email]);
            $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($usuario && $usuario['senha'] === $senha) {
                if ($usuario['status'] !== 'ativo') {
                    $erro = 'Sua conta está inativa. Contate o administrador.';
                } else {
                    $_SESSION['utilizador_id'] = $usuario['id'];
                    $_SESSION['utilizador_nome'] = $usuario['nome'];
                    $_SESSION['utilizador_email'] = $usuario['email'];
                    $_SESSION['utilizador_tipo'] = $usuario['tipo'];
                    
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
    <title>Login - FOODNET</title>
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
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
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
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
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
            <h1>FOODNET</h1>
            <p>Faça login para continuar</p>
        </div>
        
        <div class="form-container">
            <?php if ($erro): ?>
                <div class="error-message">❌ <?php echo htmlspecialchars($erro); ?></div>
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
                Não tem uma conta? <a href="register.php">Cadastre-se</a>
            </div>
        </div>
    </div>
</body>
</html>