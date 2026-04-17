<?php
require_once __DIR__ . '/../includes/admin.php';
ensure_admin_logged_in();

$db = get_db();
$id = (int) ($_GET['id'] ?? 0);

if ($id > 0) {
    $stmt = $db->prepare("SELECT nome_arquivo FROM backup_sistema WHERE id = :id");
    $stmt->execute([':id' => $id]);
    $backup = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($backup) {
        $filepath = __DIR__ . '/../backups/' . $backup['nome_arquivo'];
        
        if (file_exists($filepath)) {
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="' . $backup['nome_arquivo'] . '"');
            header('Content-Length: ' . filesize($filepath));
            readfile($filepath);
            exit;
        }
    }
}

header('Location: index.php?error=download_failed');
exit;
?>