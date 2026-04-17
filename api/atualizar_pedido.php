<?php
// api/atualizar_pedido.php
session_start();
header('Content-Type: application/json');

// Configuração do banco
$host = 'localhost';
$dbname = 'restaurante_conect';
$username = 'root';
$password = '';

// Log para debug
error_log("=== API atualizar_pedido.php chamada ===");
error_log("POST: " . print_r($_POST, true));
error_log("GET: " . print_r($_GET, true));

// Verificar se o usuário está logado
if (!isset($_SESSION['utilizador_id'])) {
    echo json_encode(['success' => false, 'message' => 'Usuário não autenticado']);
    exit;
}

// Receber os dados - tentar POST normal ou JSON
$pedido_id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
$status = isset($_POST['status']) ? $_POST['status'] : '';

// Se não veio via POST, tentar via JSON
if ($pedido_id == 0) {
    $input = json_decode(file_get_contents('php://input'), true);
    if ($input) {
        $pedido_id = isset($input['id']) ? (int)$input['id'] : 0;
        $status = isset($input['status']) ? $input['status'] : '';
        error_log("Dados via JSON: " . print_r($input, true));
    }
}

error_log("Pedido ID: $pedido_id, Status: $status");

if ($pedido_id == 0 || empty($status)) {
    echo json_encode(['success' => false, 'message' => 'Dados inválidos. ID: ' . $pedido_id . ', Status: ' . $status]);
    exit;
}

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Buscar informações do pedido
    $stmt = $pdo->prepare("SELECT p.numero_pedido, p.cliente_id, c.utilizador_id as cliente_user_id 
                           FROM pedido p 
                           LEFT JOIN cliente c ON p.cliente_id = c.id 
                           WHERE p.id = :id");
    $stmt->execute([':id' => $pedido_id]);
    $pedido = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$pedido) {
        echo json_encode(['success' => false, 'message' => 'Pedido não encontrado']);
        exit;
    }
    
    // Atualizar status do pedido
    $stmt = $pdo->prepare("UPDATE pedido SET status = :status WHERE id = :id");
    $result = $stmt->execute([':status' => $status, ':id' => $pedido_id]);
    
    if ($result) {
        error_log("Pedido #{$pedido['numero_pedido']} atualizado para status: $status");
        
        // Mensagens para o cliente
        $mensagens = [
            'confirmado' => "✅ Seu pedido #{$pedido['numero_pedido']} foi CONFIRMADO! Em breve será preparado.",
            'preparando' => "👨‍🍳 Seu pedido #{$pedido['numero_pedido']} está sendo PREPARADO na cozinha!",
            'pronto' => "🍽️ Seu pedido #{$pedido['numero_pedido']} está PRONTO para ser entregue!",
            'entregue' => "🎉 Seu pedido #{$pedido['numero_pedido']} foi ENTREGUE! Bom apetite!",
            'cancelado' => "❌ Seu pedido #{$pedido['numero_pedido']} foi CANCELADO."
        ];
        
        // Criar notificação para o cliente
        if (isset($mensagens[$status]) && $pedido['cliente_id']) {
            try {
                // Verificar se tabela existe
                $stmt = $pdo->query("SHOW TABLES LIKE 'notificacao_pedido'");
                if ($stmt->rowCount() > 0) {
                    $stmt = $pdo->prepare("INSERT INTO notificacao_pedido (pedido_id, tipo, mensagem, destinatario_tipo, lida) 
                                          VALUES (:pedido_id, 'status_update', :mensagem, 'cliente', 0)");
                    $stmt->execute([
                        ':pedido_id' => $pedido_id,
                        ':mensagem' => $mensagens[$status]
                    ]);
                    error_log("Notificação criada para o cliente");
                }
            } catch (PDOException $e) {
                error_log("Erro ao criar notificação: " . $e->getMessage());
            }
        }
        
        echo json_encode(['success' => true, 'message' => 'Status atualizado com sucesso!']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Erro ao atualizar pedido']);
    }
    
} catch (PDOException $e) {
    error_log("Erro PDO: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Erro no banco de dados: ' . $e->getMessage()]);
}
?>