<?php
require_once __DIR__ . '/../includes/admin.php';
ensure_admin_logged_in();

$db = get_db();
$itemId = (int) ($_GET['id'] ?? 0);
if ($itemId <= 0) {
    header('Location: index.php');
    exit;
}

$categories = get_active_categories($db);
$errors = [];

$stmt = $db->prepare('SELECT * FROM item_cardapio WHERE id = :id');
$stmt->execute([':id' => $itemId]);
$item = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$item) {
    header('Location: index.php');
    exit;
}

$values = [
    'nome' => $item['nome'],
    'descricao' => $item['descricao'],
    'preco' => $item['preco'],
    'foto_url' => $item['foto_url'],
    'categoria_id' => $item['categoria_id'],
    'disponivel' => $item['disponivel'],
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $values['nome'] = trim($_POST['nome'] ?? '');
    $values['descricao'] = trim($_POST['descricao'] ?? '');
    $values['preco'] = trim($_POST['preco'] ?? '');
    $values['foto_url'] = trim($_POST['foto_url'] ?? '');
    $values['categoria_id'] = (int) ($_POST['categoria_id'] ?? 0);
    $values['disponivel'] = isset($_POST['disponivel']) ? 1 : 0;

    if ($values['categoria_id'] <= 0) {
        $errors['categoria_id'] = 'Escolha uma categoria válida.';
    }
    if ($values['nome'] === '') {
        $errors['nome'] = 'Nome do item é obrigatório.';
    }
    if ($values['descricao'] === '') {
        $errors['descricao'] = 'A descrição é obrigatória.';
    }
    if ($values['preco'] === '' || !is_numeric(str_replace(',', '.', $values['preco']))) {
        $errors['preco'] = 'Preço inválido.';
    }

    if (empty($errors)) {
        $values['preco'] = str_replace(',', '.', $values['preco']);
        $update = $db->prepare('UPDATE item_cardapio SET categoria_id = :categoria_id, nome = :nome, descricao = :descricao, preco = :preco, foto_url = :foto_url, disponivel = :disponivel, atualizado_em = CURRENT_TIMESTAMP WHERE id = :id');
        $update->execute([
            ':categoria_id' => $values['categoria_id'],
            ':nome' => $values['nome'],
            ':descricao' => $values['descricao'],
            ':preco' => $values['preco'],
            ':foto_url' => $values['foto_url'] ?: null,
            ':disponivel' => $values['disponivel'],
            ':id' => $itemId,
        ]);

        header('Location: index.php?success=item_updated');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Item - Admin</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f8fafc; color: #111827; }
        header { background: #111827; color: white; padding: 20px 24px; }
        header h1 { font-size: 24px; }
        header nav { margin-top: 12px; display: flex; gap: 16px; flex-wrap: wrap; }
        header nav a { color: #d1d5db; text-decoration: none; }
        header nav a:hover { color: white; }
        main { max-width: 900px; margin: 28px auto; padding: 0 20px; }
        .card { background: white; border-radius: 20px; padding: 28px; box-shadow: 0 16px 36px rgba(15, 23, 42, 0.08); }
        .card h2 { margin-bottom: 20px; color: #0f172a; }
        .form-group { margin-bottom: 18px; }
        label { display: block; margin-bottom: 10px; font-weight: 600; }
        input[type="text"], input[type="url"], textarea, select { width: 100%; padding: 14px 16px; border: 1px solid #cbd5e1; border-radius: 12px; font-size: 15px; }
        textarea { min-height: 140px; resize: vertical; }
        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        .form-row .form-group { margin-bottom: 0; }
        .note { color: #475569; margin-top: 6px; font-size: 13px; }
        .error { color: #b91c1c; font-size: 13px; margin-top: 8px; }
        button { background: #1e40af; color: white; padding: 14px 24px; border: none; border-radius: 12px; cursor: pointer; font-size: 16px; font-weight: 700; }
        button:hover { background: #2563eb; }
        .back-link { display: inline-block; margin-top: 14px; color: #1e40af; text-decoration: none; }
        .back-link:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <header>
        <h1>Editar item</h1>
        <nav>
            <a href="index.php">Voltar ao painel</a>
        </nav>
    </header>
    <main>
        <section class="card">
            <h2>Atualize os dados do item</h2>
            <form method="POST" action="">
                <div class="form-group">
                    <label for="nome">Nome</label>
                    <input type="text" id="nome" name="nome" value="<?php echo htmlspecialchars($values['nome']); ?>" required>
                    <?php if (!empty($errors['nome'])): ?><div class="error"><?php echo htmlspecialchars($errors['nome']); ?></div><?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="descricao">Descrição</label>
                    <textarea id="descricao" name="descricao" required><?php echo htmlspecialchars($values['descricao']); ?></textarea>
                    <?php if (!empty($errors['descricao'])): ?><div class="error"><?php echo htmlspecialchars($errors['descricao']); ?></div><?php endif; ?>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="preco">Preço (Kz)</label>
                        <input type="text" id="preco" name="preco" value="<?php echo htmlspecialchars($values['preco']); ?>" required>
                        <?php if (!empty($errors['preco'])): ?><div class="error"><?php echo htmlspecialchars($errors['preco']); ?></div><?php endif; ?>
                    </div>
                    <div class="form-group">
                        <label for="categoria_id">Categoria</label>
                        <select id="categoria_id" name="categoria_id" required>
                            <option value="">Selecione</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?php echo $category['id']; ?>" <?php echo $values['categoria_id'] == $category['id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($category['nome']); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <?php if (!empty($errors['categoria_id'])): ?><div class="error"><?php echo htmlspecialchars($errors['categoria_id']); ?></div><?php endif; ?>
                    </div>
                </div>

                <div class="form-group">
                    <label for="foto_url">URL da imagem</label>
                    <input type="url" id="foto_url" name="foto_url" value="<?php echo htmlspecialchars($values['foto_url']); ?>" placeholder="https://...">
                    <div class="note">Opcional. Caso não use imagem, o item será exibido sem foto.</div>
                </div>

                <div class="form-group">
                    <label><input type="checkbox" name="disponivel" value="1" <?php echo $values['disponivel'] ? 'checked' : ''; ?>> Item disponível</label>
                </div>

                <button type="submit">Salvar alterações</button>
            </form>
            <a href="index.php" class="back-link">← Voltar ao painel</a>
        </section>
    </main>
</body>
</html>
