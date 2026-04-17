<?php
// includes/admin.php
session_start();

// Verificar se o admin está logado (sem redirecionamento infinito)
function ensure_admin_logged_in() {
    // Se não estiver logado, redirecionar para login
    if (!isset($_SESSION['utilizador_id'])) {
        header('Location: ../login.php');
        exit;
    }
    
    // Verificar se o tipo é admin ou gerente
    if (!isset($_SESSION['utilizador_tipo']) || ($_SESSION['utilizador_tipo'] !== 'admin' && $_SESSION['utilizador_tipo'] !== 'gerente')) {
        header('Location: ../login.php');
        exit;
    }
    
    return true;
}

// Obter conexão com o banco de dados
function get_db() {
    $host = 'localhost';
    $dbname = 'restaurante_conect';
    $username = 'root';
    $password = '';
    
    try {
        $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $pdo;
    } catch(PDOException $e) {
        die("Erro de conexão: " . $e->getMessage());
    }
}

// Função para formatar dinheiro
if (!function_exists('format_money')) {
    function format_money($value) {
        return number_format($value, 0, ',', '.');
    }
}

// Função para registrar logs
if (!function_exists('registrar_log')) {
    function registrar_log($accao, $descricao) {
        $db = get_db();
        try {
            $stmt = $db->prepare("INSERT INTO logs_actividade (utilizador_id, accao, descricao, ip_address) 
                                  VALUES (:user_id, :accao, :descricao, :ip)");
            $stmt->execute([
                ':user_id' => $_SESSION['utilizador_id'],
                ':accao' => $accao,
                ':descricao' => $descricao,
                ':ip' => $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0'
            ]);
        } catch (PDOException $e) {
            // Tabela pode não existir, ignorar erro
        }
    }
}

// Buscar categorias ativas
function get_active_categories($db = null) {
    if ($db === null) {
        $db = get_db();
    }
    $stmt = $db->prepare("SELECT * FROM categoria WHERE status = 'ativo' OR status IS NULL ORDER BY ordem ASC");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>