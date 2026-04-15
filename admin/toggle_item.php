<?php
require_once __DIR__ . '/../includes/admin.php';
ensure_admin_logged_in();

$db = get_db();
$itemId = (int) ($_GET['id'] ?? 0);
if ($itemId > 0) {
    $stmt = $db->prepare('UPDATE item_cardapio SET disponivel = NOT disponivel WHERE id = :id');
    $stmt->execute([':id' => $itemId]);
}
header('Location: index.php?success=item_toggled');
exit;
