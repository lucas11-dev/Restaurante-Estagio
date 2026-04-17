-- Backup do Banco de Dados
-- Data: 2026-04-17 07:34:25
-- Gerado por: Administrador

SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS `backup_sistema`;
CREATE TABLE `backup_sistema` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nome_arquivo` varchar(255) NOT NULL,
  `tamanho` varchar(50) DEFAULT NULL,
  `tipo` varchar(50) DEFAULT NULL,
  `criado_por` int(11) NOT NULL,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `criado_por` (`criado_por`),
  CONSTRAINT `backup_sistema_ibfk_1` FOREIGN KEY (`criado_por`) REFERENCES `utilizador` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `cardapio`;
CREATE TABLE `cardapio` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(100) NOT NULL,
  `ativo` tinyint(1) NOT NULL DEFAULT 1,
  `criado_por` int(11) NOT NULL,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `criado_por` (`criado_por`),
  CONSTRAINT `cardapio_ibfk_1` FOREIGN KEY (`criado_por`) REFERENCES `utilizador` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `carrinho`;
CREATE TABLE `carrinho` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cliente_id` int(11) NOT NULL,
  `produto_id` int(11) NOT NULL,
  `quantidade` int(11) DEFAULT 1,
  `preco_unitario` decimal(10,2) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_carrinho` (`cliente_id`,`produto_id`),
  KEY `produto_id` (`produto_id`),
  CONSTRAINT `carrinho_ibfk_1` FOREIGN KEY (`cliente_id`) REFERENCES `cliente` (`id`) ON DELETE CASCADE,
  CONSTRAINT `carrinho_ibfk_2` FOREIGN KEY (`produto_id`) REFERENCES `produto` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `categoria`;
CREATE TABLE `categoria` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(50) NOT NULL,
  `descricao` text DEFAULT NULL,
  `icone` varchar(10) DEFAULT NULL,
  `ordem` int(11) DEFAULT 0,
  `status` enum('ativo','inativo') DEFAULT 'ativo',
  `ativo` tinyint(1) DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nome` (`nome`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `categoria` (`id`, `nome`, `descricao`, `icone`, `ordem`, `status`, `ativo`) VALUES
('1', 'Pratos Principais', 'Nossos melhores pratos', '🍽️', '1', 'ativo', '1'),
('2', 'Bebidas', 'Refrigerantes, sucos e bebidas', '🥤', '2', 'ativo', '1'),
('3', 'Bolos e Sobremesas', 'Doces especiais', '🍰', '3', 'ativo', '1');

DROP TABLE IF EXISTS `cliente`;
CREATE TABLE `cliente` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `utilizador_id` int(11) NOT NULL,
  `telefone` varchar(20) DEFAULT NULL,
  `endereco` text DEFAULT NULL,
  `cidade` varchar(100) DEFAULT NULL,
  `pontos` int(11) DEFAULT 0,
  `foto_perfil` varchar(255) DEFAULT NULL,
  `data_nascimento` date DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `utilizador_id` (`utilizador_id`),
  CONSTRAINT `cliente_ibfk_1` FOREIGN KEY (`utilizador_id`) REFERENCES `utilizador` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `cliente` (`id`, `utilizador_id`, `telefone`, `endereco`, `cidade`, `pontos`, `foto_perfil`, `data_nascimento`) VALUES
('1', '1', '+244972890553', NULL, NULL, '0', NULL, NULL),
('2', '2', '+244930124569', NULL, NULL, '0', NULL, NULL),
('3', '3', '+244930124564', NULL, NULL, '0', NULL, NULL);

DROP TABLE IF EXISTS `configuracoes`;
CREATE TABLE `configuracoes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `chave` varchar(100) NOT NULL,
  `valor` text DEFAULT NULL,
  `descricao` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `chave` (`chave`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `favoritos`;
CREATE TABLE `favoritos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cliente_id` int(11) NOT NULL,
  `produto_id` int(11) NOT NULL,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_favorito` (`cliente_id`,`produto_id`),
  KEY `produto_id` (`produto_id`),
  CONSTRAINT `favoritos_ibfk_1` FOREIGN KEY (`cliente_id`) REFERENCES `cliente` (`id`) ON DELETE CASCADE,
  CONSTRAINT `favoritos_ibfk_2` FOREIGN KEY (`produto_id`) REFERENCES `produto` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `historico_visualizacao`;
CREATE TABLE `historico_visualizacao` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cliente_id` int(11) NOT NULL,
  `produto_id` int(11) NOT NULL,
  `visualizado_em` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `cliente_id` (`cliente_id`),
  KEY `produto_id` (`produto_id`),
  CONSTRAINT `historico_visualizacao_ibfk_1` FOREIGN KEY (`cliente_id`) REFERENCES `cliente` (`id`) ON DELETE CASCADE,
  CONSTRAINT `historico_visualizacao_ibfk_2` FOREIGN KEY (`produto_id`) REFERENCES `produto` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `item_cardapio`;
CREATE TABLE `item_cardapio` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `categoria_id` int(11) NOT NULL,
  `nome` varchar(120) NOT NULL,
  `descricao` text DEFAULT NULL,
  `preco` decimal(10,2) NOT NULL CHECK (`preco` >= 0),
  `foto_url` varchar(500) DEFAULT NULL,
  `disponivel` tinyint(1) NOT NULL DEFAULT 1,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp(),
  `atualizado_em` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `categoria_id` (`categoria_id`),
  CONSTRAINT `item_cardapio_ibfk_1` FOREIGN KEY (`categoria_id`) REFERENCES `categoria` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `item_pedido`;
CREATE TABLE `item_pedido` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pedido_id` int(11) NOT NULL,
  `produto_id` int(11) NOT NULL,
  `quantidade` int(11) NOT NULL,
  `preco_unitario` decimal(10,2) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  `observacoes` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `pedido_id` (`pedido_id`),
  KEY `produto_id` (`produto_id`),
  CONSTRAINT `item_pedido_ibfk_1` FOREIGN KEY (`pedido_id`) REFERENCES `pedido` (`id`) ON DELETE CASCADE,
  CONSTRAINT `item_pedido_ibfk_2` FOREIGN KEY (`produto_id`) REFERENCES `produto` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `logs_actividade`;
CREATE TABLE `logs_actividade` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `utilizador_id` int(11) NOT NULL,
  `accao` varchar(100) NOT NULL,
  `descricao` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `utilizador_id` (`utilizador_id`),
  CONSTRAINT `logs_actividade_ibfk_1` FOREIGN KEY (`utilizador_id`) REFERENCES `utilizador` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `logs_actividade` (`id`, `utilizador_id`, `accao`, `descricao`, `ip_address`, `criado_em`) VALUES
('1', '4', 'adicionar_produto', 'Adicionou produto: Arroz com feijão', '::1', '2026-04-17 02:09:00'),
('2', '4', 'criar_backup', 'Criou backup: backup_2026-04-17_07-33-22.sql', '::1', '2026-04-17 02:33:22'),
('3', '4', 'excluir_backup', 'Excluiu backup: backup_2026-04-17_07-33-22.sql', '::1', '2026-04-17 02:34:12');

DROP TABLE IF EXISTS `mesa`;
CREATE TABLE `mesa` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `numero` int(11) NOT NULL,
  `capacidade` int(11) DEFAULT 4,
  `status` enum('livre','ocupada','reservada') DEFAULT 'livre',
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `numero` (`numero`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `mesa` (`id`, `numero`, `capacidade`, `status`, `criado_em`) VALUES
('1', '1', '4', 'livre', '2026-04-17 01:46:21'),
('2', '2', '4', 'livre', '2026-04-17 01:46:21'),
('3', '3', '6', 'livre', '2026-04-17 01:46:21'),
('4', '4', '2', 'livre', '2026-04-17 01:46:21'),
('5', '5', '8', 'livre', '2026-04-17 01:46:21'),
('6', '6', '4', 'livre', '2026-04-17 01:46:21'),
('7', '7', '4', 'livre', '2026-04-17 01:46:21'),
('8', '8', '2', 'livre', '2026-04-17 01:46:21');

DROP TABLE IF EXISTS `notificacao`;
CREATE TABLE `notificacao` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `utilizador_id` int(11) NOT NULL,
  `pedido_id` int(11) DEFAULT NULL,
  `tipo` varchar(30) NOT NULL,
  `mensagem` text NOT NULL,
  `lida` tinyint(1) NOT NULL DEFAULT 0,
  `enviado_em` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `utilizador_id` (`utilizador_id`),
  KEY `pedido_id` (`pedido_id`),
  CONSTRAINT `notificacao_ibfk_1` FOREIGN KEY (`utilizador_id`) REFERENCES `utilizador` (`id`) ON DELETE CASCADE,
  CONSTRAINT `notificacao_ibfk_2` FOREIGN KEY (`pedido_id`) REFERENCES `pedido` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `notificacao_pedido`;
CREATE TABLE `notificacao_pedido` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pedido_id` int(11) NOT NULL,
  `tipo` varchar(50) DEFAULT 'novo_pedido',
  `mensagem` text NOT NULL,
  `lida` tinyint(1) DEFAULT 0,
  `destinatario_tipo` enum('cozinha','garcom','admin') DEFAULT 'cozinha',
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `pedido_id` (`pedido_id`),
  CONSTRAINT `notificacao_pedido_ibfk_1` FOREIGN KEY (`pedido_id`) REFERENCES `pedido` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `pagamento`;
CREATE TABLE `pagamento` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pedido_id` int(11) NOT NULL,
  `metodo` enum('cartao','multicaixa','dinheiro','simulacao') NOT NULL,
  `status` enum('pendente','aprovado','recusado','estornado') NOT NULL DEFAULT 'pendente',
  `valor` decimal(10,2) NOT NULL,
  `referencia_ext` varchar(200) DEFAULT NULL,
  `processado_em` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `pedido_id` (`pedido_id`),
  CONSTRAINT `pagamento_ibfk_1` FOREIGN KEY (`pedido_id`) REFERENCES `pedido` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `pedido`;
CREATE TABLE `pedido` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cliente_id` int(11) NOT NULL,
  `mesa_id` int(11) DEFAULT NULL,
  `garcom_id` int(11) DEFAULT NULL,
  `numero_pedido` varchar(20) NOT NULL,
  `status` enum('pendente','confirmado','preparando','pronto','entregue','cancelado') DEFAULT 'pendente',
  `tipo_pedido` enum('local','entrega') DEFAULT 'local',
  `endereco_entrega` text DEFAULT NULL,
  `observacoes` text DEFAULT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  `total` decimal(10,2) NOT NULL,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `numero_pedido` (`numero_pedido`),
  KEY `cliente_id` (`cliente_id`),
  KEY `mesa_id` (`mesa_id`),
  KEY `garcom_id` (`garcom_id`),
  CONSTRAINT `pedido_ibfk_1` FOREIGN KEY (`cliente_id`) REFERENCES `cliente` (`id`) ON DELETE CASCADE,
  CONSTRAINT `pedido_ibfk_2` FOREIGN KEY (`mesa_id`) REFERENCES `mesa` (`id`) ON DELETE SET NULL,
  CONSTRAINT `pedido_ibfk_3` FOREIGN KEY (`garcom_id`) REFERENCES `utilizador` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `produto`;
CREATE TABLE `produto` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `categoria_id` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `descricao` text DEFAULT NULL,
  `preco` decimal(10,2) NOT NULL,
  `preco_promocional` decimal(10,2) DEFAULT NULL,
  `imagem` varchar(10) DEFAULT NULL,
  `imagem_url` varchar(255) DEFAULT NULL,
  `destaque` tinyint(1) DEFAULT 0,
  `disponivel` tinyint(1) DEFAULT 1,
  `ordem` int(11) DEFAULT 0,
  `imagem_urls` varchar(255) DEFAULT NULL,
  `imagems` varchar(10) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `categoria_id` (`categoria_id`),
  CONSTRAINT `produto_ibfk_1` FOREIGN KEY (`categoria_id`) REFERENCES `categoria` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `produto` (`id`, `categoria_id`, `nome`, `descricao`, `preco`, `preco_promocional`, `imagem`, `imagem_url`, `destaque`, `disponivel`, `ordem`, `imagem_urls`, `imagems`) VALUES
('2', '1', 'Arroz com feijão', 'aconpanha com peixe e frango', '2300.00', NULL, NULL, 'uploads/produtos/produto_1776402540_6282.png', '0', '1', '0', NULL, NULL);

DROP TABLE IF EXISTS `publicidade`;
CREATE TABLE `publicidade` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `titulo` varchar(100) NOT NULL,
  `descricao` text DEFAULT NULL,
  `imagem` varchar(10) DEFAULT NULL,
  `link` varchar(255) DEFAULT NULL,
  `ordem` int(11) DEFAULT 0,
  `ativo` tinyint(1) DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `publicidade` (`id`, `titulo`, `descricao`, `imagem`, `link`, `ordem`, `ativo`) VALUES
('1', '🍖 Prato do Dia', 'Picanha Grelhada com 20% OFF!', '🥩', NULL, '1', '1'),
('2', '🥤 Bebidas Especiais', 'Compre 2 bebidas e ganhe 1 grátis', '🍺', NULL, '2', '1'),
('3', '🍰 Sobremesa Especial', 'Brownie com sorvete - Promoção', '🍫', NULL, '3', '1');

DROP TABLE IF EXISTS `utilizador`;
CREATE TABLE `utilizador` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(120) NOT NULL,
  `email` varchar(100) NOT NULL,
  `senha` varchar(255) NOT NULL,
  `tipo` enum('cliente','admin','garcom','cozinha','gerente') NOT NULL DEFAULT 'cliente',
  `status` enum('ativo','inativo','bloqueado') NOT NULL DEFAULT 'ativo',
  `foto_url` varchar(255) DEFAULT NULL,
  `telefone_func` varchar(20) DEFAULT NULL,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `utilizador` (`id`, `nome`, `email`, `senha`, `tipo`, `status`, `foto_url`, `telefone_func`, `criado_em`) VALUES
('1', 'Flausia Henriques', 'flausia@gmail.com', 'flausia', 'cliente', 'ativo', NULL, NULL, '2026-04-10 09:28:53'),
('2', 'Rodrigo Muandumba', 'rodrigomuandumba@gmail.com', 'rodrigo', 'cliente', 'ativo', NULL, NULL, '2026-04-14 10:31:35'),
('3', 'Rodrigo Muandumba', 'rodrigomuandumbas@gmail.com', 'rodrigos', 'cliente', 'ativo', NULL, NULL, '2026-04-14 10:40:44'),
('4', 'Administrador', 'admin@restaurante.com', 'admin123', 'admin', 'ativo', NULL, NULL, '2026-04-14 23:34:05');

SET FOREIGN_KEY_CHECKS = 1;
