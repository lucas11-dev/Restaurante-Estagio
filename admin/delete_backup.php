<?php
require_once __DIR__ . '/../includes/admin.php';
ensure_admin_logged_in();

$db = get_db();
$id = (int) ($_GET['id'] ?? 0);

if ($id > 0) {
    // Buscar nome do arquivo
    $stmt = $db->prepare("SELECT nome_arquivo FROM backup_sistema WHERE id = :id");
    $stmt->execute([':id' => $id]);
    $backup = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($backup) {
        $filepath = __DIR__ . '/../backups/' . $backup['nome_arquivo'];
        if (file_exists($filepath)) {
            unlink($filepath);
        }
        
        $stmt = $db->prepare("DELETE FROM backup_sistema WHERE id = :id");
        $stmt->execute([':id' => $id]);
        
        registrar_log('excluir_backup', "Excluiu backup: {$backup['nome_arquivo']}");
    }
}

header('Location: index.php');
exit;
?>