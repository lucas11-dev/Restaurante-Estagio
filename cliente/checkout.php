<?php
require_once __DIR__ . '/../includes/cliente.php';
ensure_cliente_logged_in();
$db = get_db();
$cart = get_cart();

if (empty($cart)) {
    header('Location: cart.php');
    exit;
}

$cliente_id = get_cliente_id($db);
if (!$cliente_id) {
    die('Cliente não encontrado.');
}

$erro = '';
$sucesso = false;
$observacao = '';
$metodo_pagamento = 'dinheiro';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $metodo_pagamento = in_array($_POST['metodo_pagamento'] ?? 'dinheiro', ['dinheiro', 'multicaixa']) ? $_POST['metodo_pagamento'] : 'dinheiro';
    $observacao = trim($_POST['observacao'] ?? '');

    $total = 0;
    $itensPedidos = [];

    foreach ($cart as $item) {
        $stmtItem = $db->prepare('SELECT id, nome, preco, disponivel FROM produto WHERE id = :id');
        $stmtItem->execute([':id' => $item['id']]);
        $dadosItem = $stmtItem->fetch(PDO::FETCH_ASSOC);
        
        if (!$dadosItem || !$dadosItem['disponivel']) {
            $erro = 'Um dos itens do carrinho não está mais disponível. Atualize seu carrinho.';
            break;
        }
        
        $quantidade = (int) $item['quantidade'];
        if ($quantidade < 1) {
            $erro = 'Quantidade inválida no carrinho.';
            break;
        }
        
        $subtotal = $dadosItem['preco'] * $quantidade;
        $total += $subtotal;
        $itensPedidos[] = [
            'produto_id' => $dadosItem['id'],
            'quantidade' => $quantidade,
            'preco_unit' => $dadosItem['preco'],
        ];
    }

    if (empty($erro)) {
        try {
            $db->beginTransaction();

            // Gerar número do pedido
            $numero_pedido = 'PED-' . date('Ymd') . '-' . rand(1000, 9999);
            
            // Inserir pedido
            $stmtPedido = $db->prepare('INSERT INTO pedido (cliente_id, numero_pedido, status, tipo_pedido, observacoes, subtotal, total) 
                                        VALUES (:cliente_id, :numero_pedido, :status, :tipo_pedido, :observacoes, :subtotal, :total)');
            $stmtPedido->execute([
                ':cliente_id' => $cliente_id,
                ':numero_pedido' => $numero_pedido,
                ':status' => 'pendente',
                ':tipo_pedido' => 'local',
                ':observacoes' => $observacao,
                ':subtotal' => $total,
                ':total' => $total,
            ]);
            $pedidoId = $db->lastInsertId();

            // Inserir itens do pedido
            $stmtItemPedido = $db->prepare('INSERT INTO item_pedido (pedido_id, produto_id, quantidade, preco_unitario, subtotal) 
                                            VALUES (:pedido_id, :produto_id, :quantidade, :preco_unit, :subtotal)');
            foreach ($itensPedidos as $itemPedido) {
                $stmtItemPedido->execute([
                    ':pedido_id' => $pedidoId,
                    ':produto_id' => $itemPedido['produto_id'],
                    ':quantidade' => $itemPedido['quantidade'],
                    ':preco_unit' => $itemPedido['preco_unit'],
                    ':subtotal' => $itemPedido['preco_unit'] * $itemPedido['quantidade'],
                ]);
            }

            $db->commit();
            clear_cart();
            header('Location: order_success.php?order_id=' . $pedidoId);
            exit;
            
        } catch (PDOException $exception) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            $erro = 'Erro ao processar pedido. Tente novamente.';
        }
    }
}

$total = array_reduce($cart, function ($carry, $item) {
    return $carry + ($item['preco'] * $item['quantidade']);
}, 0);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - Restaurante Conect</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 0; background: #f4f4f7; color: #333; }
        header { background: #2c3e50; color: white; padding: 20px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; }
        header a { color: white; text-decoration: none; margin-left: 16px; }
        main { padding: 24px; max-width: 900px; margin: 0 auto; }
        .box { background: white; padding: 28px; border-radius: 18px; box-shadow: 0 12px 30px rgba(0,0,0,.08); }
        .erro { background: #f8d7da; color: #721c24; padding: 16px; border-radius: 12px; margin-bottom: 20px; }
        .form-group { margin-bottom: 20px; }
        label { display: block; margin-bottom: 8px; font-weight: 700; }
        select, textarea { width: 100%; padding: 12px 14px; border-radius: 10px; border: 1px solid #ccc; font-size: 15px; }
        textarea { min-height: 100px; resize: vertical; }
        .payment-options { display: flex; gap: 20px; margin-top: 10px; }
        .payment-option { display: flex; align-items: center; gap: 10px; padding: 15px; border: 2px solid #ddd; border-radius: 12px; cursor: pointer; flex: 1; }
        .payment-option.selected { border-color: #27ae60; background: #e8f5e9; }
        .payment-option input { margin: 0; width: 20px; height: 20px; }
        .payment-option label { margin: 0; cursor: pointer; font-weight: normal; }
        .resumo { margin-top: 24px; padding-top: 20px; border-top: 1px solid #eee; }
        .total { font-size: 24px; font-weight: 700; margin-top: 14px; color: #1e3c72; }
        .btn-confirmar { background: #27ae60; color: white; border: none; padding: 16px 20px; border-radius: 14px; cursor: pointer; font-size: 16px; font-weight: 700; width: 100%; margin-top: 20px; }
        .btn-confirmar:hover { background: #219a52; }
    </style>
</head>
<body>
    <header>
        <div>✅ Finalizar Pedido</div>
        <div>
            <a href="cart.php">← Voltar ao carrinho</a>
            <a href="../logout.php">🚪 Sair</a>
        </div>
    </header>

    <main>
        <div class="box">
            <?php if ($erro): ?><div class="erro">❌ <?php echo htmlspecialchars($erro); ?></div><?php endif; ?>
            
            <form method="POST" action="checkout.php">
                <div class="form-group">
                    <label>💳 Método de Pagamento</label>
                    <div class="payment-options">
                        <div class="payment-option <?php echo $metodo_pagamento == 'dinheiro' ? 'selected' : ''; ?>" onclick="selectPayment('dinheiro')">
                            <input type="radio" name="metodo_pagamento" value="dinheiro" id="pag_dinheiro" <?php echo $metodo_pagamento == 'dinheiro' ? 'checked' : ''; ?>>
                            <label for="pag_dinheiro">💰 Dinheiro</label>
                        </div>
                        <div class="payment-option <?php echo $metodo_pagamento == 'multicaixa' ? 'selected' : ''; ?>" onclick="selectPayment('multicaixa')">
                            <input type="radio" name="metodo_pagamento" value="multicaixa" id="pag_multicaixa" <?php echo $metodo_pagamento == 'multicaixa' ? 'checked' : ''; ?>>
                            <label for="pag_multicaixa">💳 Multicaixa</label>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="observacao">📝 Observação</label>
                    <textarea id="observacao" name="observacao" placeholder="Digite algo para a cozinha (ex: sem cebola, bem passado...)"><?php echo htmlspecialchars($observacao); ?></textarea>
                </div>

                <div class="resumo">
                    <h3>📋 Resumo do pedido</h3>
                    <?php foreach ($cart as $item): ?>
                        <p><?php echo htmlspecialchars($item['nome']); ?> x <?php echo (int) $item['quantidade']; ?> = Kz <?php echo format_money($item['preco'] * $item['quantidade']); ?></p>
                    <?php endforeach; ?>
                    <div class="total">💰 Total: Kz <?php echo format_money($total); ?></div>
                </div>

                <button type="submit" class="btn-confirmar">✅ Confirmar pedido</button>
            </form>
        </div>
    </main>

    <script>
        function selectPayment(method) {
            document.getElementById('pag_' + method).checked = true;
            document.querySelectorAll('.payment-option').forEach(opt => opt.classList.remove('selected'));
            event.currentTarget.classList.add('selected');
        }
    </script>
</body>
</html>