<?php
require_once __DIR__ . '/../includes/admin.php';
ensure_admin_logged_in();

// Conectar ao banco de dados
$db = get_db(); // Adicionar esta linha para definir $db

$backup_dir = __DIR__ . '/../backups/';
if (!file_exists($backup_dir)) {
    mkdir($backup_dir, 0777, true);
}

$filename = 'backup_' . date('Y-m-d_H-i-s') . '.sql';
$filepath = $backup_dir . $filename;

// Método alternativo para criar backup usando PHP puro (sem mysqldump)
try {
    // Obter todas as tabelas
    $tables = [];
    $stmt = $db->query("SHOW TABLES");
    while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
        $tables[] = $row[0];
    }
    
    $sql_content = "-- Backup do Banco de Dados\n";
    $sql_content .= "-- Data: " . date('Y-m-d H:i:s') . "\n";
    $sql_content .= "-- Gerado por: " . $_SESSION['utilizador_nome'] . "\n\n";
    $sql_content .= "SET FOREIGN_KEY_CHECKS = 0;\n\n";
    
    foreach ($tables as $table) {
        // Estrutura da tabela
        $stmt = $db->query("SHOW CREATE TABLE `$table`");
        $create = $stmt->fetch(PDO::FETCH_ASSOC);
        $sql_content .= "DROP TABLE IF EXISTS `$table`;\n";
        $sql_content .= $create['Create Table'] . ";\n\n";
        
        // Dados da tabela
        $stmt = $db->query("SELECT * FROM `$table`");
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (count($rows) > 0) {
            $columns = array_keys($rows[0]);
            $sql_content .= "INSERT INTO `$table` (`" . implode("`, `", $columns) . "`) VALUES\n";
            
            $values_array = [];
            foreach ($rows as $row) {
                $row_values = [];
                foreach ($row as $value) {
                    if ($value === null) {
                        $row_values[] = "NULL";
                    } else {
                        $row_values[] = "'" . addslashes($value) . "'";
                    }
                }
                $values_array[] = "(" . implode(", ", $row_values) . ")";
            }
            $sql_content .= implode(",\n", $values_array) . ";\n\n";
        }
    }
    
    $sql_content .= "SET FOREIGN_KEY_CHECKS = 1;\n";
    
    // Salvar arquivo
    file_put_contents($filepath, $sql_content);
    $tamanho = round(filesize($filepath) / 1024, 2);
    
    // Registrar no banco
    $stmt = $db->prepare("INSERT INTO backup_sistema (nome_arquivo, tamanho, tipo, criado_por) VALUES (:nome, :tamanho, 'manual', :criado_por)");
    $stmt->execute([
        ':nome' => $filename,
        ':tamanho' => $tamanho . ' KB',
        ':criado_por' => $_SESSION['utilizador_id']
    ]);
    
    registrar_log('criar_backup', "Criou backup: $filename");
    header('Location: index.php?success=backup_created');
    
} catch (Exception $e) {
    registrar_log('erro_backup', "Erro ao criar backup: " . $e->getMessage());
    header('Location: index.php?error=backup_failed');
}
exit;
?>