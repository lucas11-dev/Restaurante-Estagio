<?php
require_once __DIR__ . '/../includes/admin.php';
ensure_admin_logged_in();

$id = (int) ($_GET['id'] ?? 0);

if ($id > 0) {
    $db = get_db();
    $stmt = $db->prepare("UPDATE produto SET disponivel = NOT disponivel WHERE id = :id");
    $stmt->execute([':id' => $id]);
    
    registrar_log('toggle_produto', "Alternou disponibilidade do produto ID: $id");
}

header('Location: index.php?success=item_toggled');
exit;
?>