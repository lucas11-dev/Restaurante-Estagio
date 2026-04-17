<?php
require_once __DIR__ . '/../includes/admin.php';
ensure_admin_logged_in();

$db = get_db();

$configs = [
    'nome_restaurante' => $_POST['nome_restaurante'] ?? '',
    'telefone' => $_POST['telefone'] ?? '',
    'email' => $_POST['email'] ?? '',
    'endereco' => $_POST['endereco'] ?? '',
    'horario_funcionamento' => $_POST['horario_funcionamento'] ?? '',
    'taxa_entrega' => $_POST['taxa_entrega'] ?? '500'
];

foreach ($configs as $chave => $valor) {
    $stmt = $db->prepare("UPDATE configuracoes SET valor = :valor WHERE chave = :chave");
    $stmt->execute([':valor' => $valor, ':chave' => $chave]);
}

registrar_log('salvar_configuracoes', 'Atualizou as configurações do sistema');
header('Location: index.php?success=config_saved');
exit;
?>