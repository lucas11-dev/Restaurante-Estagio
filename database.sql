-- Criar banco de dados
CREATE DATABASE IF NOT EXISTS restaurante_conect;
USE restaurante_conect;

-- Tabela utilizador
CREATE TABLE utilizador (
  id               INT PRIMARY KEY AUTO_INCREMENT NOT NULL UNIQUE,
  nome             VARCHAR(120) NOT NULL UNIQUE,
  email            VARCHAR(150) NOT NULL UNIQUE,
  senha            VARCHAR(255) NOT NULL,
  tipo             ENUM('cliente','garcon','gerente','admin') NOT NULL,
  ativo            BOOLEAN NOT NULL DEFAULT TRUE,
  criado_em        TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  atualizado_em    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabela cliente
CREATE TABLE cliente (
  id               INT PRIMARY KEY AUTO_INCREMENT NOT NULL UNIQUE,
  utilizador_id    INT NOT NULL UNIQUE,
  telefone         VARCHAR(20),
  endereco         TEXT,
  criado_em        TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (utilizador_id) REFERENCES utilizador(id) ON DELETE CASCADE
);

-- Tabela cardapio
CREATE TABLE cardapio (
  id               INT PRIMARY KEY AUTO_INCREMENT NOT NULL UNIQUE,
  nome             VARCHAR(100) NOT NULL,
  ativo            BOOLEAN NOT NULL DEFAULT TRUE,
  criado_por       INT NOT NULL,
  criado_em        TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (criado_por) REFERENCES utilizador(id)
);

-- Tabela categoria
CREATE TABLE categoria (
  id               INT PRIMARY KEY AUTO_INCREMENT NOT NULL UNIQUE,
  cardapio_id      INT NOT NULL,
  nome             VARCHAR(80) NOT NULL,
  ordem            SMALLINT NOT NULL DEFAULT 0,
  ativo            BOOLEAN NOT NULL DEFAULT TRUE,
  FOREIGN KEY (cardapio_id) REFERENCES cardapio(id) ON DELETE CASCADE
);

-- Tabela item_cardapio
CREATE TABLE item_cardapio (
  id               INT PRIMARY KEY AUTO_INCREMENT NOT NULL UNIQUE,
  categoria_id     INT NOT NULL,
  nome             VARCHAR(120) NOT NULL,
  descricao        TEXT,
  preco            DECIMAL(10,2) NOT NULL CHECK (preco >= 0),
  foto_url         VARCHAR(500),
  disponivel       BOOLEAN NOT NULL DEFAULT TRUE,
  criado_em        TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  atualizado_em    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (categoria_id) REFERENCES categoria(id) ON DELETE CASCADE
);

-- Tabela pedido
CREATE TABLE pedido (
  id               INT PRIMARY KEY AUTO_INCREMENT NOT NULL UNIQUE,
  cliente_id       INT NOT NULL,
  status           ENUM('pendente','em_preparacao','pronto','entregue','cancelado') NOT NULL DEFAULT 'pendente',
  tipo             ENUM('mesa','takeaway') NOT NULL DEFAULT 'mesa',
  total            DECIMAL(10,2) NOT NULL DEFAULT 0,
  observacao       TEXT,
  criado_em        TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  atualizado_em    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (cliente_id) REFERENCES cliente(id)
);

-- Tabela item_pedido
CREATE TABLE item_pedido (
  id               INT PRIMARY KEY AUTO_INCREMENT NOT NULL UNIQUE,
  pedido_id        INT NOT NULL,
  item_cardapio_id INT NOT NULL,
  quantidade       SMALLINT NOT NULL CHECK (quantidade > 0),
  preco_unit       DECIMAL(10,2) NOT NULL,
  observacao       TEXT,
  FOREIGN KEY (pedido_id) REFERENCES pedido(id) ON DELETE CASCADE,
  FOREIGN KEY (item_cardapio_id) REFERENCES item_cardapio(id)
);

-- Tabela pagamento
CREATE TABLE pagamento (
  id               INT PRIMARY KEY AUTO_INCREMENT NOT NULL UNIQUE,
  pedido_id        INT NOT NULL UNIQUE,
  metodo           ENUM('cartao','multicaixa','dinheiro','simulacao') NOT NULL,
  status           ENUM('pendente','aprovado','recusado','estornado') NOT NULL DEFAULT 'pendente',
  valor            DECIMAL(10,2) NOT NULL,
  referencia_ext   VARCHAR(200),
  processado_em    TIMESTAMP NULL,
  FOREIGN KEY (pedido_id) REFERENCES pedido(id)
);

-- Tabela notificacao
CREATE TABLE notificacao (
  id               INT PRIMARY KEY AUTO_INCREMENT NOT NULL UNIQUE,
  utilizador_id    INT NOT NULL,
  pedido_id        INT NULL,
  tipo             VARCHAR(30) NOT NULL,
  mensagem         TEXT NOT NULL,
  lida             BOOLEAN NOT NULL DEFAULT FALSE,
  enviado_em       TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (utilizador_id) REFERENCES utilizador(id) ON DELETE CASCADE,
  FOREIGN KEY (pedido_id) REFERENCES pedido(id) ON DELETE SET NULL
);

-- Inserir usuário admin padrão
INSERT INTO utilizador (nome, email, senha, tipo) VALUES ('Admin', 'admin@restaurante.com', '$2y$10$examplehashedpassword', 'admin');

-- Inserir cardápio padrão
INSERT INTO cardapio (nome, criado_por) VALUES ('Cardápio Principal', 1);

-- Inserir categorias
INSERT INTO categoria (cardapio_id, nome, ordem) VALUES (1, 'Entradas', 1), (1, 'Pratos Principais', 2), (1, 'Sobremesas', 3), (1, 'Bebidas', 4);

-- Inserir itens de exemplo
INSERT INTO item_cardapio (categoria_id, nome, descricao, preco) VALUES
(1, 'Salada Caesar', 'Alface, croutons, parmesão, molho Caesar', 15.00),
(2, 'Pizza Margherita', 'Molho de tomate, queijo, manjericão', 25.00),
(3, 'Tiramisu', 'Sobremesa italiana com café', 12.00),
(4, 'Refrigerante', 'Coca-Cola 350ml', 5.00);