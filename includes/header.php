<?php
session_start();
date_default_timezone_set('Africa/Maputo');

function usuarioLogado() {
    return isset($_SESSION['usuario_id']);
}

function ehAdmin() {
    return isset($_SESSION['papel']) && $_SESSION['papel'] === 'admin';
}

function ehEditor() {
    return isset($_SESSION['papel']) && $_SESSION['papel'] === 'editor';
}

function temPermissao($papelRequerido) {
    if (!usuarioLogado()) return false;
    $papel = $_SESSION['papel'];
    
    if ($papelRequerido === 'admin') {
        return $papel === 'admin';
    } elseif ($papelRequerido === 'editor') {
        return $papel === 'admin' || $papel === 'editor';
    }
    return true;
}

// Função para determinar o caminho base
function getBasePath() {
    $current = $_SERVER['PHP_SELF'];
    if (strpos($current, '/backend/') !== false) {
        return '../';
    }
    return '';
}

// Obter número de notificações não lidas
$notificacoesNaoLidas = 0;
if (usuarioLogado()) {
    $arquivoNotif = __DIR__ . '/../data/notificacoes.json';
    if (file_exists($arquivoNotif)) {
        $notificacoes = json_decode(file_get_contents($arquivoNotif), true);
        if (is_array($notificacoes)) {
            foreach ($notificacoes as $n) {
                if ($n['usuario_destino_id'] == $_SESSION['usuario_id'] && (!isset($n['lida']) || $n['lida'] === false)) {
                    $notificacoesNaoLidas++;
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-MZ">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blog Acadêmico Moçambique</title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" sizes="32x32" href="<?php echo getBasePath(); ?>assets/img/favicon.png">
    <link rel="apple-touch-icon" sizes="180x180" href="<?php echo getBasePath(); ?>assets/img/apple-touch-icon.png">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary-orange: #FF6B35;
            --primary-blue: #003B5C;
            --secondary-orange: #FF8C42;
            --secondary-blue: #005B8C;
            --light-bg: #F8F9FA;
            --dark-text: #212529;
            --shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background: var(--light-bg);
            color: var(--dark-text);
            padding-top: 76px;
        }
        
        /* Navbar Moderna */
        .navbar-custom {
            background: linear-gradient(135deg, var(--primary-blue) 0%, var(--secondary-blue) 100%);
            box-shadow: var(--shadow);
            padding: 1rem 0;
            position: fixed;
            top: 0;
            width: 100%;
            z-index: 1000;
        }
        
        .navbar-logo {
            width: 45px;
            height: 45px;
            object-fit: contain;
            transition: transform 0.3s ease;
        }
        
        .navbar-brand:hover .navbar-logo {
            transform: rotate(360deg);
        }
        
        .navbar-brand {
            font-weight: 700;
            font-size: 1.5rem;
            color: white !important;
            transition: transform 0.3s ease;
        }
        
        .navbar-brand:hover {
            transform: scale(1.05);
        }
        
        .nav-link {
            color: rgba(255, 255, 255, 0.9) !important;
            font-weight: 500;
            margin: 0 10px;
            transition: all 0.3s ease;
            position: relative;
        }
        
        .nav-link:hover {
            color: var(--primary-orange) !important;
            transform: translateY(-2px);
        }
        
        .nav-link::after {
            content: '';
            position: absolute;
            width: 0;
            height: 2px;
            bottom: -5px;
            left: 50%;
            background: var(--primary-orange);
            transition: all 0.3s ease;
            transform: translateX(-50%);
        }
        
        .nav-link:hover::after {
            width: 80%;
        }
        
        .btn-login, .btn-register {
            padding: 8px 20px;
            border-radius: 25px;
            font-weight: 600;
            transition: all 0.3s ease;
            margin: 0 5px;
        }
        
        .btn-login {
            background: transparent;
            border: 2px solid white;
            color: white;
        }
        
        .btn-login:hover {
            background: white;
            color: var(--primary-blue);
        }
        
        .btn-register {
            background: var(--primary-orange);
            border: 2px solid var(--primary-orange);
            color: white;
        }
        
        .btn-register:hover {
            background: var(--secondary-orange);
            border-color: var(--secondary-orange);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(255, 107, 53, 0.4);
        }
        
        /* Notificações Badge - MELHORADO */
        .notifications-bell {
            position: relative;
            color: white;
            font-size: 1.4rem;
            padding: 8px 12px;
            border-radius: 50%;
            transition: all 0.3s ease;
            cursor: pointer;
            background: rgba(255, 255, 255, 0.1);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            text-decoration: none;
            width: 45px;
            height: 45px;
        }
        
        .notifications-bell:hover {
            background: var(--primary-orange);
            transform: scale(1.1);
            color: white;
        }
        
        .notifications-bell i {
            animation: bellRing 3s ease-in-out infinite;
        }
        
        @keyframes bellRing {
            0%, 100% { transform: rotate(0deg); }
            5%, 15% { transform: rotate(15deg); }
            10%, 20% { transform: rotate(-15deg); }
            25% { transform: rotate(0deg); }
        }
        
        .notification-badge {
            position: absolute;
            top: -2px;
            right: -2px;
            background: linear-gradient(135deg, #ff3b30, #ff6b6b);
            color: white;
            border-radius: 50%;
            min-width: 22px;
            height: 22px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.7rem;
            font-weight: 800;
            border: 3px solid var(--primary-blue);
            animation: pulse 2s infinite;
            padding: 0 5px;
            box-shadow: 0 2px 8px rgba(255, 59, 48, 0.6);
        }
        
        @keyframes pulse {
            0% {
                box-shadow: 0 0 0 0 rgba(255, 59, 48, 0.7);
            }
            70% {
                box-shadow: 0 0 0 10px rgba(255, 59, 48, 0);
            }
            100% {
                box-shadow: 0 0 0 0 rgba(255, 59, 48, 0);
            }
        }
        
        /* User Dropdown */
        .user-profile-img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid var(--primary-orange);
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .user-profile-img:hover {
            transform: scale(1.1);
            box-shadow: 0 0 20px rgba(255, 107, 53, 0.6);
        }
        
        .dropdown-menu {
            border-radius: 15px;
            border: none;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            margin-top: 10px;
        }
        
        .dropdown-item {
            padding: 12px 20px;
            transition: all 0.3s ease;
        }
        
        .dropdown-item:hover {
            background: linear-gradient(135deg, var(--primary-orange), var(--secondary-orange));
            color: white;
            transform: translateX(5px);
        }
        
        .dropdown-item i {
            margin-right: 10px;
            width: 20px;
        }
        
        /* Hamburger Menu Animado - MELHORADO */
        .navbar-toggler {
            border: none;
            padding: 5px;
            width: 45px;
            height: 45px;
            position: relative;
            cursor: pointer;
            background: rgba(255, 255, 255, 0.1) !important;
            border-radius: 10px;
            transition: all 0.3s ease;
        }
        
        .navbar-toggler:hover {
            background: rgba(255, 255, 255, 0.2) !important;
        }
        
        .navbar-toggler:focus {
            box-shadow: none;
        }
        
        .hamburger {
            width: 30px;
            height: 20px;
            position: relative;
            transform: rotate(0deg);
            transition: .5s ease-in-out;
            cursor: pointer;
            display: block;
            margin: auto;
        }
        
        .hamburger span {
            display: block;
            position: absolute;
            height: 3px;
            width: 100%;
            background: white;
            border-radius: 3px;
            opacity: 1;
            left: 0;
            transform: rotate(0deg);
            transition: .25s ease-in-out;
        }
        
        .hamburger span:nth-child(1) {
            top: 0px;
        }
        
        .hamburger span:nth-child(2),
        .hamburger span:nth-child(3) {
            top: 8px;
        }
        
        .hamburger span:nth-child(4) {
            top: 16px;
        }
        
        /* Estado aberto (X) */
        .navbar-toggler:not(.collapsed) .hamburger span:nth-child(1) {
            top: 8px;
            width: 0%;
            left: 50%;
        }
        
        .navbar-toggler:not(.collapsed) .hamburger span:nth-child(2) {
            transform: rotate(45deg);
        }
        
        .navbar-toggler:not(.collapsed) .hamburger span:nth-child(3) {
            transform: rotate(-45deg);
        }
        
        .navbar-toggler:not(.collapsed) .hamburger span:nth-child(4) {
            top: 8px;
            width: 0%;
            left: 50%;
        }
        
        /* Mobile Adjustments */
        @media (max-width: 991px) {
            body {
                padding-top: 70px;
            }
            
            .navbar-collapse {
                background: var(--primary-blue);
                margin-top: 15px;
                padding: 20px;
                border-radius: 15px;
                animation: slideDown 0.3s ease;
            }
            
            @keyframes slideDown {
                from {
                    opacity: 0;
                    transform: translateY(-10px);
                }
                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }
            
            .nav-link {
                padding: 10px 0;
            }
            
            .btn-login, .btn-register {
                width: 100%;
                margin: 10px 0;
            }
            
            .notifications-bell {
                margin-right: 10px;
                margin-bottom: 0;
            }
            
            .user-dropdown-mobile {
                margin-top: 15px;
                padding-top: 15px;
                border-top: 1px solid rgba(255, 255, 255, 0.2);
            }
            
            .user-info-mobile {
                display: flex;
                align-items: center;
                padding: 15px;
                background: rgba(255, 255, 255, 0.1);
                border-radius: 10px;
                margin-bottom: 10px;
            }
            
            .user-info-mobile img {
                width: 50px;
                height: 50px;
                border-radius: 50%;
                margin-right: 15px;
                border: 3px solid var(--primary-orange);
            }
            
            .user-info-mobile span {
                color: white;
                font-weight: 600;
            }
        }
        
        @media (min-width: 992px) {
            .user-dropdown-mobile {
                display: none;
            }
        }
        
        @media (max-width: 991px) {
            .user-dropdown-desktop {
                display: none;
            }
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-custom">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="<?php echo getBasePath(); ?>index.php">
                <img src="<?php echo getBasePath(); ?>assets/img/logo.png" alt="Logo" class="navbar-logo me-2">
                Blog Acadêmico
            </a>
            
            <button class="navbar-toggler collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-expanded="false">
                <span class="hamburger">
                    <span></span>
                    <span></span>
                    <span></span>
                    <span></span>
                </span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav mx-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo getBasePath(); ?>index.php">
                            <i class="bi bi-house-door"></i> Início
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo getBasePath(); ?>sobre.php">
                            <i class="bi bi-info-circle"></i> Sobre
                        </a>
                    </li>
                </ul>
                
                <div class="d-flex align-items-center">
                    <?php if (usuarioLogado()): ?>
                        <!-- Ícone de Notificações -->
                        <a href="<?php echo getBasePath(); ?>backend/notificacoes.php" class="notifications-bell" title="Notificações">
                            <i class="bi bi-bell-fill"></i>
                            <?php if ($notificacoesNaoLidas > 0): ?>
                                <span class="notification-badge"><?php echo $notificacoesNaoLidas > 99 ? '99+' : $notificacoesNaoLidas; ?></span>
                            <?php endif; ?>
                        </a>
                        
                        <!-- Desktop User Menu -->
                        <div class="dropdown user-dropdown-desktop">
                            <a class="d-flex align-items-center text-decoration-none dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                                <img src="<?php echo !empty($_SESSION['imagem']) ? getBasePath() . $_SESSION['imagem'] : getBasePath() . 'assets/img/users/default.jpg'; ?>" 
                                     alt="Perfil" class="user-profile-img me-2">
                                <span class="text-white fw-semibold d-none d-lg-inline"><?php echo htmlspecialchars($_SESSION['nome']); ?></span>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li>
                                    <a class="dropdown-item" href="<?php echo getBasePath(); ?>backend/perfil.php">
                                        <i class="bi bi-person-circle"></i> Meu Perfil
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="<?php echo getBasePath(); ?>backend/notificacoes.php">
                                        <i class="bi bi-bell-fill"></i> Notificações
                                        <?php if ($notificacoesNaoLidas > 0): ?>
                                            <span class="badge bg-danger ms-2"><?php echo $notificacoesNaoLidas; ?></span>
                                        <?php endif; ?>
                                    </a>
                                </li>
                                <?php if (ehAdmin()): ?>
                                    <li>
                                        <a class="dropdown-item" href="<?php echo getBasePath(); ?>backend/painelAdmin.php">
                                            <i class="bi bi-speedometer2"></i> Dashboard Admin
                                        </a>
                                    </li>
                                <?php elseif (ehEditor()): ?>
                                    <li>
                                        <a class="dropdown-item" href="<?php echo getBasePath(); ?>backend/painelEditor.php">
                                            <i class="bi bi-pencil-square"></i> Painel Editor
                                        </a>
                                    </li>
                                <?php endif; ?>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <a class="dropdown-item text-danger" href="<?php echo getBasePath(); ?>backend/logout.php">
                                        <i class="bi bi-box-arrow-right"></i> Sair
                                    </a>
                                </li>
                            </ul>
                        </div>
                        
                        <!-- Mobile User Menu -->
                        <div class="user-dropdown-mobile w-100">
                            <div class="user-info-mobile">
                                <img src="<?php echo !empty($_SESSION['imagem']) ? getBasePath() . $_SESSION['imagem'] : getBasePath() . 'assets/img/users/default.jpg'; ?>" alt="Perfil">
                                <span><?php echo htmlspecialchars($_SESSION['nome']); ?></span>
                            </div>
                            <a class="nav-link" href="<?php echo getBasePath(); ?>backend/perfil.php">
                                <i class="bi bi-person-circle"></i> Meu Perfil
                            </a>
                            <a class="nav-link" href="<?php echo getBasePath(); ?>backend/notificacoes.php">
                                <i class="bi bi-bell-fill"></i> Notificações
                                <?php if ($notificacoesNaoLidas > 0): ?>
                                    <span class="badge bg-danger ms-2"><?php echo $notificacoesNaoLidas; ?></span>
                                <?php endif; ?>
                            </a>
                            <?php if (ehAdmin()): ?>
                                <a class="nav-link" href="<?php echo getBasePath(); ?>backend/painelAdmin.php">
                                    <i class="bi bi-speedometer2"></i> Dashboard Admin
                                </a>
                            <?php elseif (ehEditor()): ?>
                                <a class="nav-link" href="<?php echo getBasePath(); ?>backend/painelEditor.php">
                                    <i class="bi bi-pencil-square"></i> Painel Editor
                                </a>
                            <?php endif; ?>
                            <a class="nav-link text-danger" href="<?php echo getBasePath(); ?>backend/logout.php">
                                <i class="bi bi-box-arrow-right"></i> Sair
                            </a>
                        </div>
                    <?php else: ?>
                        <a href="<?php echo getBasePath(); ?>backend/login.php" class="btn btn-login">
                            <i class="bi bi-box-arrow-in-right"></i> Entrar
                        </a>
                        <a href="<?php echo getBasePath(); ?>backend/registrar.php" class="btn btn-register">
                            <i class="bi bi-person-plus"></i> Registrar
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>