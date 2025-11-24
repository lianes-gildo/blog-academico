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
?>
<!DOCTYPE html>
<html lang="pt-MZ">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>📚 Blog Acadêmico Moçambique</title>
    
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
        
        /* Hamburger Menu */
        .navbar-toggler {
            border: none;
            padding: 5px;
        }
        
        .navbar-toggler:focus {
            box-shadow: none;
        }
        
        .navbar-toggler-icon {
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 30 30'%3e%3cpath stroke='rgba%28255, 255, 255, 1%29' stroke-linecap='round' stroke-miterlimit='10' stroke-width='2' d='M4 7h22M4 15h22M4 23h22'/%3e%3c/svg%3e");
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
            }
            
            .nav-link {
                padding: 10px 0;
            }
            
            .btn-login, .btn-register {
                width: 100%;
                margin: 10px 0;
            }
            
            .user-dropdown-mobile {
                margin-top: 15px;
                padding-top: 15px;
                border-top: 1px solid rgba(255, 255, 255, 0.2);
            }
            
            .user-info-mobile {
                display: flex;
                align-items: center;
                padding: 10px;
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
                📚 Blog Acadêmico
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
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
                
                <!-- Barra de Pesquisa -->
                <form class="search-form mx-3 d-none d-lg-block" action="<?php echo getBasePath(); ?>buscar.php" method="GET">
                    <div class="search-wrapper">
                        <i class="bi bi-search search-icon"></i>
                        <input type="text" name="q" class="search-input" placeholder="🔍 Pesquisar artigos..." required>
                    </div>
                </form>
                
                <div class="d-flex align-items-center">
                    <?php if (usuarioLogado()): ?>
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