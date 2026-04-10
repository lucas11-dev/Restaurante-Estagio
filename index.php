<?php
// index.php - Página inicial do site - Apenas com responsividade adicionada
session_start();

if (isset($_SESSION['utilizador_id'])) {
    header('Location: dashboard.php');
    exit;
}

$host = 'localhost';
$dbname = 'restaurante_conect';
$username = 'root';
$password = '';

$pratos_dia = [];

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $stmt = $pdo->query("SELECT p.*, c.nome as categoria_nome 
                         FROM produto p 
                         INNER JOIN categoria c ON p.categoria_id = c.id 
                         WHERE p.destaque = 1 AND p.disponivel = 1 
                         LIMIT 3");
    $pratos_dia = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    $pratos_dia = [];
}

if (empty($pratos_dia)) {
    $pratos_dia = [
        ['nome' => 'Picanha Grelhada', 'descricao' => '300g - acompanha arroz e farofa', 'preco' => 2800],
        ['nome' => 'Camarão no Alho', 'descricao' => '200g - molho especial da casa', 'preco' => 3500],
        ['nome' => 'Salada Caesar', 'descricao' => 'com frango grelhado e croutons', 'preco' => 1200]
    ];
}

// ============================================
// NOSSOS SERVIÇOS 
// ============================================
$servicos = [
    [
        'icone' => '<img src="assets/img/servicio-entrega-contacto-cuarentena-hombre-entrega-alimentos-bolsas-compras-aislamiento_489646-16503.jpg" alt="Encomenda" style="width: 100%; max-width: 200px; height: auto; border-radius: 10px;">',
        'titulo' => 'Encomenda',
        'descricao' => 'Entrega rápida, segura e confiável. Levamos seus pedidos até você com cuidado e pontualidade, garantindo praticidade no seu dia a dia!'
    ],
    [
        'icone' => '<img src="assets/img/231555-afinal-voce-saber-como-agradar-os-clientes-no-seu-restaurante-998x600.jpg" alt="Atendimento ao Cliente" style="width: 100%; max-width: 200px; height: auto; border-radius: 10px;">',
        'titulo' => 'Atendimento ao Cliente',
        'descricao' => 'Nosso atendimento ao cliente é feito com atenção, cordialidade e rapidez. Estamos sempre prontos para esclarecer dúvidas, acompanhar pedidos e garantir que sua experiência seja prática e agradável!'
    ],
    [
        'icone' => '<img src="assets/img/dicas-e-boas-praticas-para-restaurantes-e-lanchonetes.jpg" alt="Cozinha" style="width: 100%; max-width: 200px; height: auto; border-radius: 10px;">',
        'titulo' => 'Cozinha',
        'descricao' => 'Entrega de pratos saborosos e fresquinhos direto da sua cozinha para a sua mesa. Rápido, higiênico e com todo cuidado para manter o sabor e a qualidade!'
    ],
];

// ============================================
// NOSSA EQUIPE 
// ============================================
$equipe = [
    [
        'foto' => '<img src="assets/img/copilot_image_1774602450123.jpeg" alt="Lucas Chivinda">',
        'nome' => 'Lucas Chivinda',
        'cargo' => 'Chef Executivo',
        'descricao' => '20 anos de experiência na culinária internacional, especialista em gastronomia angolana.',
        'instagram' => '#',
        'linkedin' => '#'
    ],
    [
        'foto' => '<img src="uploads/equipe/chef2.jpg" alt="António Bucula">',
        'nome' => 'António Bucula',
        'cargo' => 'Sous Chef',
        'descricao' => 'Especialista em culinária angolana e portuguesa, cria pratos tradicionais com toque moderno.',
        'instagram' => '#',
        'linkedin' => '#'
    ],
    [
        'foto' => '<img src="assets/img/copilot_image_1774602315363.jpeg" alt="António Buengue">',
        'nome' => 'António Buengue',
        'cargo' => 'Gerente Geral',
        'descricao' => 'Gestão de restaurantes há 15 anos, responsável pela excelência no atendimento.',
        'instagram' => '#',
        'linkedin' => '#'
    ],
];

date_default_timezone_set('Africa/Luanda');
$hora_atual = date('H:i');
$data_atual = date('d/m/Y');
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0">
    <meta name="theme-color" content="#667eea">
    <title>Restaurante Conect - Seu restaurante digitalizado e conectado</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js" integrity="sha512-v2CJ7UaYy4JwqLDIrZUI/4hqeoQieOmAZNXBeQyjo21dadnwR+8ZaIJVT8EE2iyI61OV8e6M8PP2/4hpQINQ/g==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            background: #f8f9fa;
        }

        /* Header/Hero Section */
        .hero {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 100px 20px;
            text-align: center;
        }

        .hero h1 {
            font-size: 56px;
            margin-bottom: 20px;
        }

        .hero .dev {
            font-size: 14px;
            opacity: 0.7;
            margin-bottom: 30px;
            letter-spacing: 2px;
        }

        .hero p {
            font-size: 20px;
            opacity: 0.9;
            margin-bottom: 40px;
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
        }

        .btn-group {
            display: flex;
            gap: 20px;
            justify-content: center;
            flex-wrap: wrap;
        }

        .btn {
            display: inline-block;
            padding: 14px 35px;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s;
            font-size: 16px;
        }

        .btn-primary {
            background: white;
            color: #667eea;
        }

        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.2);
        }

        .btn-outline {
            border: 2px solid white;
            color: white;
            background: transparent;
        }

        .btn-outline:hover {
            background: white;
            color: #667eea;
        }

        /* Navigation */
        .navbar {
            background: white;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .nav-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 15px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
        }

        .logo {
            font-size: 24px;
            font-weight: bold;
            color: #667eea;
            text-decoration: none;
        }

        .nav-links {
            display: flex;
            gap: 30px;
            list-style: none;
        }

        .nav-links a {
            text-decoration: none;
            color: #333;
            font-weight: 500;
            transition: color 0.3s;
        }

        .nav-links a:hover {
            color: #667eea;
        }

        /* Mobile Menu Button - NOVO */
        .mobile-menu-btn {
            display: none;
            background: none;
            border: none;
            font-size: 24px;
            color: #667eea;
            cursor: pointer;
            padding: 8px;
        }

        /* Main Content */
        .main-content {
            max-width: 1200px;
            margin: 60px auto;
            padding: 0 20px;
        }

        /* Cardápio do Dia Section */
        .cardapio-section {
            background: white;
            border-radius: 20px;
            padding: 40px;
            margin-bottom: 60px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.05);
        }

        .cardapio-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #f0f0f0;
        }

        .cardapio-header h2 {
            font-size: 28px;
            color: #333;
        }

        .mesa-info {
            background: #f8f9fa;
            padding: 8px 15px;
            border-radius: 20px;
            font-size: 14px;
            color: #667eea;
        }

        .pratos-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 30px;
            margin-bottom: 30px;
        }

        .prato-card {
            background: #f8f9fa;
            border-radius: 15px;
            padding: 25px;
            transition: transform 0.3s;
            text-align: center;
        }

        .prato-card:hover {
            transform: translateY(-5px);
        }

        .prato-nome {
            font-size: 20px;
            font-weight: bold;
            color: #333;
            margin-bottom: 10px;
        }

        .prato-descricao {
            color: #666;
            font-size: 14px;
            margin-bottom: 15px;
        }

        .prato-preco {
            font-size: 24px;
            font-weight: bold;
            color: #667eea;
        }

        .pedido-status {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            border-radius: 15px;
            text-align: center;
        }

        /* Seção Nossos Serviços */
        .servicos-section {
            background: white;
            border-radius: 20px;
            padding: 60px 40px;
            margin-bottom: 60px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.05);
        }

        .section-title {
            text-align: center;
            font-size: 36px;
            margin-bottom: 20px;
            color: #333;
        }

        .section-subtitle {
            text-align: center;
            font-size: 18px;
            color: #666;
            margin-bottom: 50px;
        }

        .servicos-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
            gap: 30px;
        }

        .servico-card {
            background: #f8f9fa;
            border-radius: 15px;
            padding: 30px;
            text-align: center;
            transition: all 0.3s;
        }

        .servico-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }

        .servico-icon {
            font-size: 50px;
            margin-bottom: 20px;
        }

        /* NOVO - Estilo para imagens nos serviços */
        .servico-icon img {
            max-width: 100%;
            height: auto;
            border-radius: 10px;
        }

        .servico-card h3 {
            font-size: 22px;
            margin-bottom: 15px;
            color: #333;
        }

        .servico-card p {
            color: #666;
            line-height: 1.6;
        }

        /* Seção Nossa Equipe - ESTILO PARA FOTOS */
        .equipe-section {
            background: white;
            border-radius: 20px;
            padding: 60px 40px;
            margin-bottom: 60px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.05);
        }

        .equipe-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 30px;
        }

        .membro-card {
            background: #f8f9fa;
            border-radius: 15px;
            overflow: hidden;
            text-align: center;
            transition: all 0.3s;
        }

        .membro-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }

        /* Estilo para a foto do membro */
        .membro-foto {
            width: 100%;
            height: 280px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .membro-foto img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s;
        }

        .membro-card:hover .membro-foto img {
            transform: scale(1.05);
        }

        /* Caso queira usar emoji em vez de imagem */
        .membro-foto-emoji {
            width: 100%;
            height: 280px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 80px;
        }

        .membro-info {
            padding: 20px;
        }

        .membro-info h3 {
            font-size: 20px;
            margin-bottom: 5px;
            color: #333;
        }

        .membro-cargo {
            color: #667eea;
            font-weight: 500;
            margin-bottom: 10px;
            font-size: 14px;
        }

        .membro-descricao {
            color: #666;
            font-size: 14px;
            margin-bottom: 15px;
        }

        .membro-social {
            display: flex;
            justify-content: center;
            gap: 15px;
        }

        .membro-social a {
            color: #667eea;
            text-decoration: none;
            font-size: 20px;
            transition: color 0.3s;
        }

        .membro-social a:hover {
            color: #764ba2;
        }

        /* Depoimentos Section */
        .depoimentos-section {
            background: white;
            border-radius: 20px;
            padding: 60px 40px;
            margin-bottom: 60px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.05);
        }

        .depoimentos-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
        }

        .depoimento-card {
            background: #f8f9fa;
            border-radius: 15px;
            padding: 25px;
            text-align: center;
        }

        .depoimento-foto {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 15px;
            font-size: 40px;
            overflow: hidden;
        }

        .depoimento-foto img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .depoimento-texto {
            font-style: italic;
            color: #666;
            margin-bottom: 15px;
        }

        .depoimento-nome {
            font-weight: bold;
            color: #333;
            margin-bottom: 5px;
        }

        .depoimento-estrelas {
            color: #f39c12;
            margin-bottom: 5px;
        }

        /* Info Bar */
        .info-bar {
            background: white;
            padding: 15px 20px;
            border-radius: 10px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
            margin-bottom: 30px;
        }

        .info-item {
            display: flex;
            align-items: center;
            gap: 10px;
            color: #666;
        }

        /* Footer */
        .footer {
            background: #2d3748;
            color: white;
            text-align: center;
            padding: 60px 20px 40px;
            margin-top: 60px;
        }

        .footer-content {
            max-width: 1200px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 40px;
            text-align: left;
            margin-bottom: 40px;
        }

        .footer-section h3 {
            margin-bottom: 20px;
            font-size: 18px;
        }

        .footer-section p {
            opacity: 0.7;
            line-height: 1.8;
        }

        .footer-bottom {
            text-align: center;
            padding-top: 40px;
            border-top: 1px solid rgba(255,255,255,0.1);
            opacity: 0.7;
        }

        /* ================================================ */
        /* MEDIA QUERIES - RESPONSIVIDADE                    */
        /* ================================================ */

        /* Tablet */
        @media (max-width: 992px) {
            .hero h1 {
                font-size: 42px;
            }
            
            .hero p {
                font-size: 18px;
            }
            
            .section-title {
                font-size: 32px;
            }
            
            .cardapio-section,
            .servicos-section,
            .equipe-section,
            .depoimentos-section {
                padding: 40px 25px;
            }
        }

        /* Mobile */
        @media (max-width: 768px) {
            /* Menu Mobile */
            .mobile-menu-btn {
                display: block;
            }
            
            .nav-links {
                position: fixed;
                top: 0;
                right: -100%;
                width: 80%;
                max-width: 300px;
                height: 100vh;
                background: white;
                flex-direction: column;
                padding: 60px 20px 20px;
                transition: right 0.3s ease;
                box-shadow: -2px 0 10px rgba(0,0,0,0.1);
                z-index: 1001;
                gap: 10px;
                overflow-y: auto;
            }
            
            .nav-links.active {
                right: 0;
            }
            
            .nav-links li {
                width: 100%;
            }
            
            .nav-links a {
                display: block;
                padding: 12px 15px;
                background: #f8f9fa;
                border-radius: 8px;
                text-align: center;
            }
            
            /* Overlay do menu */
            .menu-overlay {
                display: none;
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background: rgba(0,0,0,0.5);
                z-index: 1000;
            }
            
            .menu-overlay.active {
                display: block;
            }
            
            /* Hero */
            .hero {
                padding: 60px 15px;
            }
            
            .hero h1 {
                font-size: 32px;
            }
            
            .hero p {
                font-size: 16px;
            }
            
            .btn-group {
                flex-direction: column;
                gap: 12px;
            }
            
            .btn {
                width: 100%;
                text-align: center;
            }
            
            /* Navegação */
            .nav-container {
                padding: 10px 15px;
            }
            
            .logo {
                font-size: 20px;
            }
            
            /* Cards */
            .cardapio-header {
                flex-direction: column;
                text-align: center;
                gap: 10px;
            }
            
            .cardapio-header h2 {
                font-size: 24px;
            }
            
            .section-title {
                font-size: 26px;
            }
            
            .section-subtitle {
                font-size: 16px;
                margin-bottom: 30px;
            }
            
            /* Grids */
            .pratos-grid {
                grid-template-columns: 1fr;
                gap: 20px;
            }
            
            .servicos-grid {
                grid-template-columns: 1fr;
                gap: 20px;
            }
            
            .equipe-grid {
                grid-template-columns: 1fr;
                gap: 20px;
            }
            
            .depoimentos-grid {
                grid-template-columns: 1fr;
                gap: 20px;
            }
            
            /* Info Bar */
            .info-bar {
                flex-direction: column;
                align-items: flex-start;
            }
            
            /* Seções */
            .cardapio-section,
            .servicos-section,
            .equipe-section,
            .depoimentos-section {
                padding: 30px 15px;
            }
            
            .main-content {
                padding: 0 15px;
                margin: 40px auto;
            }
            
            /* Footer */
            .footer-content {
                grid-template-columns: 1fr;
                gap: 30px;
                text-align: center;
            }
            
            .footer-section {
                text-align: center;
            }
            
            /* Fotos da equipe - ajuste para mobile */
            .membro-foto {
                height: 250px;
            }
            
            /* Imagens dos serviços */
            .servico-icon img {
                max-width: 180px;
            }
        }

        /* Mobile pequeno */
        @media (max-width: 480px) {
            .hero h1 {
                font-size: 28px;
            }
            
            .hero p {
                font-size: 14px;
            }
            
            .section-title {
                font-size: 22px;
            }
            
            .prato-nome {
                font-size: 18px;
            }
            
            .prato-preco {
                font-size: 20px;
            }
            
            .membro-foto {
                height: 220px;
            }
            
            .depoimento-foto {
                width: 60px;
                height: 60px;
                font-size: 30px;
            }
        }

        /* Tablet específico */
        @media (min-width: 769px) and (max-width: 992px) {
            .pratos-grid,
            .servicos-grid,
            .equipe-grid,
            .depoimentos-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        /* Desktop - 3 colunas */
        @media (min-width: 993px) {
            .pratos-grid,
            .servicos-grid,
            .equipe-grid,
            .depoimentos-grid {
                grid-template-columns: repeat(3, 1fr);
            }
        }

        /* Ajustes para imagens responsivas */
        img {
            max-width: 100%;
            height: auto;
        }

        /* Botão de fechar menu (mobile) */
        .close-menu {
            display: none;
        }
        
        @media (max-width: 768px) {
            .close-menu {
                display: block;
                position: absolute;
                top: 15px;
                right: 15px;
                font-size: 24px;
                cursor: pointer;
                color: #666;
            }
        }

        /* Melhorias de toque para mobile */
        @media (hover: none) and (pointer: coarse) {
            .btn,
            .nav-links a,
            .prato-card,
            .servico-card,
            .membro-card {
                cursor: default;
            }
        }
        .btn-default{
    border: none;
    display: flex;
    align-items: center;
    justify-content: center;
    background-color: #ffcb45;
    border-radius: 12px;
    padding: 10px 14px;
    font-weight: 10px;
    box-shadow: 0px 0px 10px 2px rgba(0, 0, 0, 0.1);
    cursor: pointer;
    transition: background-color .3s ease;
}

.btn-default:hover{
    background-color: #f8d477;
}

    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar">
        <div class="nav-container">
            <a href="#" class="logo">RestauranteConect</a>
            
            <button class="mobile-menu-btn" onclick="toggleMenu()">
                <i class="fas fa-bars"></i>
            </button>
            
            <ul class="nav-links" id="navLinks">
                <li class="close-menu" onclick="toggleMenu()">
                    <i class="fas fa-times"></i>
                </li>
                <li><a href="#home" onclick="closeMenu()">Início</a></li>
                <li><a href="#cardapio" onclick="closeMenu()">Cardápio</a></li>
                <li><a href="#servicos" onclick="closeMenu()">Serviços</a></li>
                <li><a href="#equipe" onclick="closeMenu()">Equipe</a></li>
                <li><a href="#depoimentos" onclick="closeMenu()">Depoimentos</a></li>
                <li><a href="login.php">Entrar</a></li>
                <li><a href="register.php" style="color: #667eea;">Cadastrar</a></li>
            </ul>
        </div>
    </nav>
    
    <div class="menu-overlay" id="menuOverlay" onclick="toggleMenu()"></div>

    <!-- Hero Section -->
    <section class="hero" id="home">
       <!-- <div class="dev">- DEV</div> -->
        <h1>RestauranteConect</h1>
        <p>O seu restaurante, digitalizado e conectado.</p>
        <p>Gerencie pedidos, cardápio e pagamentos em tempo real.<br>Da mesa à cozinha, tudo fluindo num único sistema.</p>
        <div class="btn-group">
            <a href="register.php" class="btn btn-primary">Começar agora →</a>
            <a href="#" class="btn btn-outline"><i class="fa-solid fa-phone"></i> Entrar em contacto...</a>
        </div>
    </section>

    <div class="main-content">
        <!-- Info Bar -->
        <div class="info-bar">
            <div class="info-item">
                <i class="fas fa-temperature-high"></i>
                <span>25°C</span>
            </div>
            <div class="info-item">
                <i class="fas fa-map-marker-alt"></i>
                <span>Luanda, Angola</span>
            </div>
            <div class="info-item">
                <i class="far fa-clock"></i>
                <span><?php echo $hora_atual; ?></span>
            </div>
            <div class="info-item">
                <i class="far fa-calendar-alt"></i>
                <span><?php echo $data_atual; ?></span>
            </div>
        </div>

        <!-- Cardápio do Dia Section -->
        <section class="cardapio-section" id="cardapio">
            <div class="cardapio-header">
                <h2>Cardápio do Dia</h2>
                <div class="mesa-info">
                    <i class="fas fa-users"></i> Mesa 7 - Garçon: António
                </div>
            </div>
            
            <div class="pratos-grid">
                <?php foreach($pratos_dia as $prato): ?>
                <div class="prato-card">
                    <div class="prato-nome"><?php echo htmlspecialchars($prato['nome']); ?></div>
                    <div class="prato-descricao"><?php echo htmlspecialchars($prato['descricao']); ?></div>
                    <div class="prato-preco">Kz <?php echo number_format($prato['preco'], 0, ',', '.'); ?></div>
                </div>
                <?php endforeach; ?>
            </div>
            
            <div class="pedido-status">
                <i class="fas fa-box"></i>
                <h3>Pedido #0047</h3>
                <p>Em preparação na cozinha...</p>
                <div style="margin-top: 10px;">
                    <i class="fas fa-spinner fa-pulse"></i> Em andamento
                </div>
            </div>
        </section>

        <!-- Nossos Serviços Section -->
        <section class="servicos-section" id="servicos">
            <h2 class="section-title"> Nossos Serviços</h2>
            <p class="section-subtitle">Oferecemos as melhores soluções para o seu restaurante</p>
            <div class="servicos-grid">
                <?php foreach($servicos as $servico): ?>
                <div class="servico-card">
                    <div class="servico-icon"><?php echo $servico['icone']; ?></div>
                    <h3><?php echo htmlspecialchars($servico['titulo']); ?></h3>
                    <p><?php echo htmlspecialchars($servico['descricao']); ?></p>
                </div>
                <?php endforeach; ?>
            </div>
        </section>

        <!-- Nossa Equipe Section - COM SUPORTE A <img> -->
        <section class="equipe-section" id="equipe">
            <h2 class="section-title"> Nossa Equipe</h2>
            <p class="section-subtitle">Profissionais apaixonados por gastronomia</p>
            <div class="equipe-grid">
                <?php foreach($equipe as $membro): ?>
                <div class="membro-card">
                    <div class="membro-foto">
                        <?php 
                        // Se a foto contiver <img>, mostra a imagem, senão mostra como emoji
                        if (strpos($membro['foto'], '<img') !== false) {
                            echo $membro['foto'];
                        } else {
                            echo '<div class="membro-foto-emoji">' . $membro['foto'] . '</div>';
                        }
                        ?>
                    </div>
                    <div class="membro-info">
                        <h3><?php echo htmlspecialchars($membro['nome']); ?></h3>
                        <div class="membro-cargo"><?php echo htmlspecialchars($membro['cargo']); ?></div>
                        <div class="membro-descricao"><?php echo htmlspecialchars($membro['descricao']); ?></div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </section>

        <!-- Depoimentos Section -->
        <section class="depoimentos-section" id="depoimentos">
            <h2 class="section-title">Depoimentos</h2>
            <p class="section-subtitle">O que nossos clientes dizem sobre nós</p>
            <div class="depoimentos-grid">
                <div class="depoimento-card">
                    <div class="depoimento-foto">
                        <img src="assets/img/1770289794001.jpg" alt="Cliente">
                    </div>
                    <div class="depoimento-texto">"Melhor restaurante de Luanda! Atendimento impecável e comida maravilhosa!"</div>
                    <div class="depoimento-estrelas">
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                    </div>
                    <div class="depoimento-nome">Antónica Sunda</div>
                    <div class="depoimento-data">15/03/2026</div>
                </div>
                <div class="depoimento-card">
                    <div class="depoimento-foto">
                        <img src="assets/img/IMG-20251231-WA0004.jpg" alt="Cliente">
                    </div>
                    <div class="depoimento-texto">"Adorei o cardápio digital, muito prático. O camarão no alho é sensacional!"</div>
                    <div class="depoimento-estrelas">
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                    </div>
                    <div class="depoimento-nome">Fernanda Chivinda</div>
                    <div class="depoimento-data">10/03/2026</div>
                </div>
                <div class="depoimento-card">
                    <div class="depoimento-foto">
                        <img src="assets/img/IMG-20250805-WA0001.jpg" alt="Cliente">
                    </div>
                    <div class="depoimento-texto">"Entrega rápida e comida sempre quente. Recomendo a todos!"</div>
                    <div class="depoimento-estrelas">
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="far fa-star"></i>
                    </div>
                    <div class="depoimento-nome">Domingos Chivinda</div>
                    <div class="depoimento-data">05/03/2026</div>
                </div>
            </div>
        </section>
    </div>

    <!-- Footer -->
    <footer class="footer">
        <div class="footer-content">
            <div class="footer-section">
                <h3>RestauranteConect</h3>
                <p>O seu restaurante, digitalizado e conectado. Da mesa à cozinha, tudo fluindo num único sistema.</p>
            </div>
            <div class="footer-section">
                <h3>Contato</h3>
                <p><i class="fas fa-phone"></i> (+244) 972 890 553 / (+244) 938 188 186</p>
                <p><i class="fas fa-envelope"></i> estagiopobu@restauranteconect.ao</p>
                <p><i class="fas fa-map-marker-alt"></i> Gamek, Luanda, Angola</p>
            </div>
            <div class="footer-section">
                <h3>Horário de Funcionamento</h3>
                <p>Segunda a Sexta: 11h - 23h</p>
                <p>Sábado e Domingo: 10h - 00h</p>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; 2026 Restaurante Conect - Todos os direitos reservados</p>
        </div>
    </footer>

    <script>
        // Menu Mobile
        function toggleMenu() {
            document.getElementById('navLinks').classList.toggle('active');
            document.getElementById('menuOverlay').classList.toggle('active');
            document.body.style.overflow = document.getElementById('navLinks').classList.contains('active') ? 'hidden' : '';
        }
        
        function closeMenu() {
            document.getElementById('navLinks').classList.remove('active');
            document.getElementById('menuOverlay').classList.remove('active');
            document.body.style.overflow = '';
        }
        
        // Smooth scroll
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                const href = this.getAttribute('href');
                if (href === '#') return;
                
                e.preventDefault();
                const target = document.querySelector(href);
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });
        
        // Fechar menu ao redimensionar para desktop
        window.addEventListener('resize', function() {
            if (window.innerWidth > 768) {
                closeMenu();
            }
        });
    </script>
</body>
</html>