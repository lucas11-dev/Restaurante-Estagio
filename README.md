# Restaurante Conect

Sistema de eCommerce para gestão de pedidos em restaurantes. A forma mais simples de chegar ao seu prato preferido!

## Funcionalidades

- Cadastro e autenticação de usuários (cliente, garçon, admin/gerente)
- Visualização de cardápio por clientes
- Carrinho de compras e finalização de pedidos
- Gestão de pedidos por garçons
- Administração do cardápio e relatórios

## Estrutura do Projeto

- `css/`: Estilos CSS
- `js/`: Scripts JavaScript
- `images/`: Imagens
- `includes/`: Arquivos de configuração e funções
- `cliente/`: Páginas para clientes
- `garcon/`: Páginas para garçons
- `admin/`: Páginas administrativas
- `api/`: APIs (futuro)

## Instalação

1. Instale o XAMPP e inicie Apache e MySQL.
2. Copie o projeto para `c:\xampp\htdocs\RestauranteEstagio`.
3. Importe `database.sql` no phpMyAdmin.
4. Acesse `http://localhost/RestauranteEstagio`.

## Usuários de Teste

- Admin: admin@restaurante.com / admin (senha: admin, mas altere para hash seguro)
- Registre clientes e garçons conforme necessário.

## Stack

Frontend

- HTML 5 => criar e estruturar o conteúdo das páginas
- Tailwind css + Bootstrap 5 => Estilização
- JavaScript

Beckend

- PHP 8.4
- MySQL 8.0