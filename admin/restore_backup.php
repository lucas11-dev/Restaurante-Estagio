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
            $sql = file_get_contents($filepath);
            
            // Executar SQL
            try {
                $db->exec("SET FOREIGN_KEY_CHECKS = 0");
                
                // Dividir as queries
                $queries = explode(";\n", $sql);
                foreach ($queries as $query) {
                    $query = trim($query);
                    if (!empty($query)) {
                        $db->exec($query);
                    }
                }
                
                $db->exec("SET FOREIGN_KEY_CHECKS = 1");
                
                registrar_log('restaurar_backup', "Restaurou backup: {$backup['nome_arquivo']}");
                header('Location: index.php?success=backup_restored');
            } catch (Exception $e) {
                registrar_log('erro_restore', "Erro ao restaurar backup: " . $e->getMessage());
                header('Location: index.php?error=restore_failed');
            }
        } else {
            header('Location: index.php?error=file_not_found');
        }
    } else {
        header('Location: index.php?error=backup_not_found');
    }
} else {
    header('Location: index.php');
}
exit;
?>