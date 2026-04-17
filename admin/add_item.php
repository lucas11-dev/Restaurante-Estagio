<<<<<<< HEAD
<?php
=======
﻿<?php
>>>>>>> a5598ee33df2db43f75f8f6e777ac6881e159abc
require_once __DIR__ . '/../includes/admin.php';
ensure_admin_logged_in();

$db = get_db();
$categories = get_active_categories($db);
$errors = [];
<<<<<<< HEAD
$values = ['nome' => '', 'descricao' => '', 'preco' => '', 'categoria_id' => '', 'disponivel' => 1];
$mensagem = '';

// Criar diretório de uploads se não existir
$upload_dir = __DIR__ . '/../uploads/produtos/';
if (!file_exists($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}
=======
$values = ['nome' => '', 'descricao' => '', 'preco' => '', 'foto_url' => '', 'categoria_id' => '', 'disponivel' => 1];
>>>>>>> a5598ee33df2db43f75f8f6e777ac6881e159abc

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $values['nome'] = trim($_POST['nome'] ?? '');
    $values['descricao'] = trim($_POST['descricao'] ?? '');
    $values['preco'] = trim($_POST['preco'] ?? '');
<<<<<<< HEAD
=======
    $values['foto_url'] = trim($_POST['foto_url'] ?? '');
>>>>>>> a5598ee33df2db43f75f8f6e777ac6881e159abc
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

<<<<<<< HEAD
    // Processar upload da imagem
    $imagem_url = null;
    $imagem_emoji = null;
    
    if (isset($_FILES['imagem']) && $_FILES['imagem']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['imagem'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        
        if (in_array($ext, $allowed)) {
            $filename = 'produto_' . time() . '_' . rand(1000, 9999) . '.' . $ext;
            $filepath = 'uploads/produtos/' . $filename;
            $fullpath = __DIR__ . '/../' . $filepath;
            
            if (move_uploaded_file($file['tmp_name'], $fullpath)) {
                $imagem_url = $filepath;
            } else {
                $errors['imagem'] = 'Erro ao fazer upload da imagem.';
            }
        } else {
            $errors['imagem'] = 'Formato de imagem não permitido. Use JPG, PNG, GIF ou WEBP.';
        }
    }
    
    // Processar emoji
    $imagem_emoji = trim($_POST['imagem_emoji'] ?? '');

    if (empty($errors)) {
        $values['preco'] = str_replace(',', '.', $values['preco']);
        
        $stmt = $db->prepare('INSERT INTO produto (categoria_id, nome, descricao, preco, imagem_url, imagem, disponivel, destaque) 
                              VALUES (:categoria_id, :nome, :descricao, :preco, :imagem_url, :imagem, :disponivel, 0)');
=======
    if (empty($errors)) {
        $values['preco'] = str_replace(',', '.', $values['preco']);
        $stmt = $db->prepare('INSERT INTO item_cardapio (categoria_id, nome, descricao, preco, foto_url, disponivel) VALUES (:categoria_id, :nome, :descricao, :preco, :foto_url, :disponivel)');
>>>>>>> a5598ee33df2db43f75f8f6e777ac6881e159abc
        $stmt->execute([
            ':categoria_id' => $values['categoria_id'],
            ':nome' => $values['nome'],
            ':descricao' => $values['descricao'],
            ':preco' => $values['preco'],
<<<<<<< HEAD
            ':imagem_url' => $imagem_url,
            ':imagem' => $imagem_emoji ?: null,
            ':disponivel' => $values['disponivel'],
        ]);

        registrar_log('adicionar_produto', "Adicionou produto: {$values['nome']}");
        $mensagem = 'Item adicionado com sucesso!';
        
        // Limpar valores
        $values = ['nome' => '', 'descricao' => '', 'preco' => '', 'categoria_id' => '', 'disponivel' => 1];
=======
            ':foto_url' => $values['foto_url'] ?: null,
            ':disponivel' => $values['disponivel'],
        ]);

        header('Location: index.php?success=item_added');
        exit;
>>>>>>> a5598ee33df2db43f75f8f6e777ac6881e159abc
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Adicionar Item - Admin</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f7fafc; color: #1f2937; }
        header { background: #111827; color: white; padding: 20px 24px; }
        header h1 { font-size: 24px; }
        header nav { margin-top: 14px; display: flex; gap: 16px; flex-wrap: wrap; }
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
            padding: 14px 16px; 
            border: 1px solid #cbd5e1; 
            border-radius: 12px; 
            font-size: 15px; 
        }
        input[type="file"] {
            width: 100%;
            padding: 12px;
            border: 1px solid #cbd5e1;
            border-radius: 12px;
            font-size: 14px;
            background: white;
        }
=======
        input[type="text"], input[type="url"], textarea, select { width: 100%; padding: 14px 16px; border: 1px solid #cbd5e1; border-radius: 12px; font-size: 15px; }
>>>>>>> a5598ee33df2db43f75f8f6e777ac6881e159abc
        textarea { min-height: 140px; resize: vertical; }
        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        .form-row .form-group { margin-bottom: 0; }
        .note { color: #475569; margin-top: 6px; font-size: 13px; }
        .error { color: #b91c1c; font-size: 13px; margin-top: 8px; }
<<<<<<< HEAD
        .success { background: #d4edda; color: #155724; padding: 12px; border-radius: 8px; margin-bottom: 20px; }
        button { 
            background: #1e40af; 
            color: white; 
            padding: 14px 24px; 
            border: none; 
            border-radius: 12px; 
            cursor: pointer; 
            font-size: 16px; 
            font-weight: 700; 
            width: 100%;
        }
        button:hover { background: #2563eb; }
        .back-link { display: inline-block; margin-top: 20px; color: #1e40af; text-decoration: none; }
        .back-link:hover { text-decoration: underline; }
        .preview-img {
            max-width: 150px;
            max-height: 150px;
            margin-top: 10px;
            border-radius: 8px;
            border: 1px solid #ddd;
            display: none;
        }
=======
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
        <h1>➕ Adicionar item ao cardápio</h1>
        <nav>
            <a href="index.php">← Voltar ao painel</a>
            <a href="produtos.php"> Ver produtos</a>
=======
        <h1>Adicionar item ao cardápio</h1>
        <nav>
            <a href="index.php">Voltar ao painel</a>
>>>>>>> a5598ee33df2db43f75f8f6e777ac6881e159abc
        </nav>
    </header>
    <main>
        <section class="card">
            <h2>Novo item</h2>
<<<<<<< HEAD
            
            <?php if ($mensagem): ?>
                <div class="success">✅ <?php echo htmlspecialchars($mensagem); ?></div>
            <?php endif; ?>
            
            <?php if (empty($categories)): ?>
                <div class="error" style="background: #f8d7da; padding: 15px; border-radius: 8px;">
                </div>
            <?php else: ?>
                <form method="POST" action="" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="nome">Nome do item </label>
                        <input type="text" id="nome" name="nome" value="<?php echo htmlspecialchars($values['nome']); ?>" required placeholder="Ex: Picanha Grelhada">
                        <?php if (!empty($errors['nome'])): ?><div class="error">❌ <?php echo htmlspecialchars($errors['nome']); ?></div><?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label for="descricao">Descrição </label>
                        <textarea id="descricao" name="descricao" required placeholder="Ex: 300g - acompanha arroz e farofa"><?php echo htmlspecialchars($values['descricao']); ?></textarea>
                        <?php if (!empty($errors['descricao'])): ?><div class="error">❌ <?php echo htmlspecialchars($errors['descricao']); ?></div><?php endif; ?>
=======
            <?php if (empty($categories)): ?>
                <p>Antes de cadastrar um item, insira categorias na tabela <strong>categoria</strong> do banco de dados.</p>
            <?php else: ?>
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
                            <input type="text" id="preco" name="preco" value="<?php echo htmlspecialchars($values['preco']); ?>" required placeholder="Ex: 2800">
                            <?php if (!empty($errors['preco'])): ?><div class="error">❌ <?php echo htmlspecialchars($errors['preco']); ?></div><?php endif; ?>
                        </div>
                        <div class="form-group">
                            <label for="categoria_id">Categoria </label>
                            <select id="categoria_id" name="categoria_id" required>
                                <option value="">Selecione uma categoria</option>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?php echo $category['id']; ?>" <?php echo $values['categoria_id'] == $category['id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($category['icone'] ?? ''); ?> <?php echo htmlspecialchars($category['nome']); ?>
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
                        <label for="imagem">Imagem do produto</label>
                        <input type="file" id="imagem" name="imagem" accept="image/*" onchange="previewImagem(this)">
                        <img id="preview" class="preview-img" alt="Pré-visualização">
                        <div class="note">📁 Selecione uma imagem do seu computador (JPG, PNG, GIF, WEBP)</div>
                        <?php if (!empty($errors['imagem'])): ?><div class="error">❌ <?php echo htmlspecialchars($errors['imagem']); ?></div><?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label>
                            <input type="checkbox" name="disponivel" value="1" <?php echo $values['disponivel'] ? 'checked' : ''; ?>> 
                            ✅ Item disponível no cardápio
                        </label>
                    </div>

                    <button type="submit">💾 Salvar item</button>
                </form>
            <?php endif; ?>
            
            <a href="index.php" class="back-link">← Voltar ao painel administrativo</a>
        </section>
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
                        <div class="note">Opcional. Use uma imagem que represente o item.</div>
                    </div>

                    <div class="form-group">
                        <label><input type="checkbox" name="disponivel" value="1" <?php echo $values['disponivel'] ? 'checked' : ''; ?>> Item disponível</label>
                    </div>

                    <button type="submit">Salvar item</button>
                </form>
            <?php endif; ?>
            <a href="index.php" class="back-link">← Voltar ao painel</a>
        </section>
    </main>
</body>
</html>
>>>>>>> a5598ee33df2db43f75f8f6e777ac6881e159abc
