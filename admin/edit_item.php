<<<<<<< HEAD
<?php
// admin/edit_item.php
=======
﻿<?php
>>>>>>> a5598ee33df2db43f75f8f6e777ac6881e159abc
require_once __DIR__ . '/../includes/admin.php';
ensure_admin_logged_in();

$db = get_db();
<<<<<<< HEAD
$id = (int) ($_GET['id'] ?? 0);

if ($id <= 0) {
    header('Location: index.php?error=ID inválido');
    exit;
}

// Buscar produto
$stmt = $db->prepare("SELECT * FROM produto WHERE id = :id");
$stmt->execute([':id' => $id]);
$produto = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$produto) {
    header('Location: index.php?error=Produto não encontrado');
    exit;
}

$categorias = get_active_categories($db);
$errors = [];
$mensagem = '';

// Processar formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome'] ?? '');
    $descricao = trim($_POST['descricao'] ?? '');
    $preco = trim($_POST['preco'] ?? '');
    $categoria_id = (int) ($_POST['categoria_id'] ?? 0);
    $disponivel = isset($_POST['disponivel']) ? 1 : 0;
    $destaque = isset($_POST['destaque']) ? 1 : 0;
    
    if ($categoria_id <= 0) {
        $errors['categoria_id'] = 'Escolha uma categoria válida.';
    }
    if (empty($nome)) {
        $errors['nome'] = 'Nome do item é obrigatório.';
    }
    if (empty($descricao)) {
        $errors['descricao'] = 'A descrição é obrigatória.';
    }
    if (empty($preco) || !is_numeric(str_replace(',', '.', $preco))) {
        $errors['preco'] = 'Preço inválido.';
    }
    
    // Processar upload da nova imagem
    $imagem_url = $produto['imagem_url'];
    if (isset($_FILES['imagem']) && $_FILES['imagem']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['imagem'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        
        if (in_array($ext, $allowed)) {
            // Deletar imagem antiga
            if (!empty($imagem_url) && file_exists(__DIR__ . '/../' . $imagem_url)) {
                unlink(__DIR__ . '/../' . $imagem_url);
            }
            
            $filename = 'produto_' . time() . '_' . rand(1000, 9999) . '.' . $ext;
            $filepath = 'uploads/produtos/' . $filename;
            $fullpath = __DIR__ . '/../' . $filepath;
            
            if (move_uploaded_file($file['tmp_name'], $fullpath)) {
                $imagem_url = $filepath;
            } else {
                $errors['imagem'] = 'Erro ao fazer upload da imagem.';
            }
        } else {
            $errors['imagem'] = 'Formato de imagem não permitido.';
        }
    }
    
    // Processar emoji
    $imagem_emoji = trim($_POST['imagem_emoji'] ?? '');
    
    if (empty($errors)) {
        $preco = str_replace(',', '.', $preco);
        
        $stmt = $db->prepare("UPDATE produto SET 
                              categoria_id = :categoria_id, 
                              nome = :nome, 
                              descricao = :descricao, 
                              preco = :preco, 
                              imagem_url = :imagem_url, 
                              imagem = :imagem, 
                              disponivel = :disponivel, 
                              destaque = :destaque 
                              WHERE id = :id");
        
        $stmt->execute([
            ':categoria_id' => $categoria_id,
            ':nome' => $nome,
            ':descricao' => $descricao,
            ':preco' => $preco,
            ':imagem_url' => $imagem_url,
            ':imagem' => $imagem_emoji ?: null,
            ':disponivel' => $disponivel,
            ':destaque' => $destaque,
            ':id' => $id
        ]);
        
        registrar_log('editar_produto', "Editou produto: $nome");
=======
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

>>>>>>> a5598ee33df2db43f75f8f6e777ac6881e159abc
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
<<<<<<< HEAD
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f7fafc; color: #1f2937; }
        header { background: #111827; color: white; padding: 20px 24px; }
        header h1 { font-size: 24px; }
        header nav { margin-top: 14px; display: flex; gap: 16px; flex-wrap: wrap; }
=======
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f8fafc; color: #111827; }
        header { background: #111827; color: white; padding: 20px 24px; }
        header h1 { font-size: 24px; }
        header nav { margin-top: 12px; display: flex; gap: 16px; flex-wrap: wrap; }
>>>>>>> a5598ee33df2db43f75f8f6e777ac6881e159abc
        header nav a { color: #d1d5db; text-decoration: none; }
        header nav a:hover { color: white; }
        main { max-width: 900px; margin: 28px auto; padding: 0 20px; }
        .card { background: white; border-radius: 20px; padding: 28px; box-shadow: 0 16px 36px rgba(15, 23, 42, 0.08); }
        .card h2 { margin-bottom: 20px; color: #0f172a; }
        .form-group { margin-bottom: 18px; }
        label { display: block; margin-bottom: 10px; font-weight: 600; }
<<<<<<< HEAD
        input[type="text"], input[type="number"], textarea, select { 
            width: 100%; 
            padding: 12px 16px; 
            border: 1px solid #cbd5e1; 
            border-radius: 12px; 
            font-size: 15px; 
        }
        input[type="file"] { width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 12px; }
        textarea { min-height: 100px; resize: vertical; }
        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        .current-image { margin: 10px 0; }
        .current-image img { max-width: 150px; border-radius: 8px; }
        .error { color: #b91c1c; font-size: 13px; margin-top: 5px; }
        button { background: #1e40af; color: white; padding: 14px 24px; border: none; border-radius: 12px; cursor: pointer; font-size: 16px; font-weight: 700; width: 100%; }
        button:hover { background: #2563eb; }
        .back-link { display: inline-block; margin-top: 20px; color: #1e40af; text-decoration: none; }
        .preview-img { max-width: 150px; margin-top: 10px; border-radius: 8px; display: none; }
=======
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
>>>>>>> a5598ee33df2db43f75f8f6e777ac6881e159abc
    </style>
</head>
<body>
    <header>
<<<<<<< HEAD
        <h1>✏️ Editar Item</h1>
        <nav>
            <a href="index.php">← Voltar ao painel</a>
            <a href="produtos.php"> Ver produtos</a>
        </nav>
    </header>
    <main>
        <div class="card">
            <h2>Editando: <?php echo htmlspecialchars($produto['nome']); ?></h2>
            
            <form method="POST" action="" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="nome">Nome do item </label>
                    <input type="text" id="nome" name="nome" value="<?php echo htmlspecialchars($produto['nome']); ?>" required>
                    <?php if (!empty($errors['nome'])): ?><div class="error">❌ <?php echo htmlspecialchars($errors['nome']); ?></div><?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="descricao">Descrição </label>
                    <textarea id="descricao" name="descricao" required><?php echo htmlspecialchars($produto['descricao']); ?></textarea>
                    <?php if (!empty($errors['descricao'])): ?><div class="error">❌ <?php echo htmlspecialchars($errors['descricao']); ?></div><?php endif; ?>
=======
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
>>>>>>> a5598ee33df2db43f75f8f6e777ac6881e159abc
                </div>

                <div class="form-row">
                    <div class="form-group">
<<<<<<< HEAD
                        <label for="preco">Preço (Kz) </label>
                        <input type="text" id="preco" name="preco" value="<?php echo $produto['preco']; ?>" required>
                        <?php if (!empty($errors['preco'])): ?><div class="error">❌ <?php echo htmlspecialchars($errors['preco']); ?></div><?php endif; ?>
                    </div>
                    <div class="form-group">
                        <label for="categoria_id">Categoria </label>
                        <select id="categoria_id" name="categoria_id" required>
                            <option value="">Selecione</option>
                            <?php foreach ($categorias as $cat): ?>
                                <option value="<?php echo $cat['id']; ?>" <?php echo $produto['categoria_id'] == $cat['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($cat['icone'] ?? ''); ?> <?php echo htmlspecialchars($cat['nome']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <?php if (!empty($errors['categoria_id'])): ?><div class="error">❌ <?php echo htmlspecialchars($errors['categoria_id']); ?></div><?php endif; ?>
=======
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
>>>>>>> a5598ee33df2db43f75f8f6e777ac6881e159abc
                    </div>
                </div>

                <div class="form-group">
<<<<<<< HEAD
                    <label>Imagem atual</label>
                    <div class="current-image">
                        <?php if (!empty($produto['imagem_url'])): ?>
                            <img src="../<?php echo $produto['imagem_url']; ?>" alt="Imagem atual" style="max-width: 150px; border-radius: 8px;">
                        <?php elseif (!empty($produto['imagem'])): ?>
                            <span style="font-size: 60px;"><?php echo $produto['imagem']; ?></span>
                        <?php else: ?>
                            <span>Nenhuma imagem</span>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="form-group">
                    <label for="imagem">Nova imagem (opcional)</label>
                    <input type="file" id="imagem" name="imagem" accept="image/*" onchange="previewImagem(this)">
                    <img id="preview" class="preview-img" alt="Pré-visualização">
                    <div class="note">📁 Selecione uma nova imagem para substituir a atual</div>
                    <?php if (!empty($errors['imagem'])): ?><div class="error">❌ <?php echo htmlspecialchars($errors['imagem']); ?></div><?php endif; ?>
                </div>

                <div class="form-group">
                    <label>
                        <input type="checkbox" name="disponivel" value="1" <?php echo $produto['disponivel'] ? 'checked' : ''; ?>> 
                        ✅ Item disponível no cardápio
                    </label>
                </div>

                <div class="form-group">
                    <label>
                        <input type="checkbox" name="destaque" value="1" <?php echo $produto['destaque'] ? 'checked' : ''; ?>> 
                        ⭐ Destacar produto
                    </label>
                </div>

                <button type="submit">💾 Salvar alterações</button>
            </form>
            
            <a href="index.php" class="back-link">← Cancelar e voltar</a>
        </div>
    </main>

    <script>
        function previewImagem(input) {
            var preview = document.getElementById('preview');
            if (input.files && input.files[0]) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    preview.style.display = 'block';
                    preview.src = e.target.result;
                };
                reader.readAsDataURL(input.files[0]);
            }
        }
    </script>
</body>
</html>
=======
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
>>>>>>> a5598ee33df2db43f75f8f6e777ac6881e159abc
