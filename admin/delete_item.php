<?php
// admin/delete_item.php
require_once __DIR__ . '/../includes/admin.php';
ensure_admin_logged_in();

$db = get_db();
$id = (int) ($_GET['id'] ?? 0);

if ($id > 0) {
    try {
        // Buscar nome do produto para o log
        $stmt = $db->prepare("SELECT nome, imagem_url FROM produto WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $produto = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($produto) {
            // Deletar imagem se existir
            if (!empty($produto['imagem_url']) && file_exists(__DIR__ . '/../' . $produto['imagem_url'])) {
                unlink(__DIR__ . '/../' . $produto['imagem_url']);
            }
            
            // Deletar produto
            $stmt = $db->prepare("DELETE FROM produto WHERE id = :id");
            $stmt->execute([':id' => $id]);
            
            registrar_log('excluir_produto', "Excluiu produto: " . $produto['nome']);
            header('Location: index.php?success=item_deleted');
            exit;
        } else {
            header('Location: index.php?error=Produto não encontrado');
            exit;
        }
    } catch (PDOException $e) {
        header('Location: index.php?error=Erro ao excluir: ' . $e->getMessage());
        exit;
    }
} else {
    header('Location: index.php?error=ID inválido');
    exit;
}
?>