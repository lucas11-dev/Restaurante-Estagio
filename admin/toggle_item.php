<<<<<<< HEAD
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
=======
﻿<?php
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
>>>>>>> a5598ee33df2db43f75f8f6e777ac6881e159abc
