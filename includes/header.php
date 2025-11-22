<?php
session_start();
date_default_timezone_set('Africa/Maputo');

function usuarioLogado() {
    return isset($_SESSION['usuario_id']);
}

function ehAdmin() {
    return isset($_SESSION['papel']) && $_SESSION['papel'] === 'admin';
}
?>
<!DOCTYPE html>
<html lang="pt-MZ">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blog Acadêmico Moçambique</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body>
    <header class="cabecalho">
        <div class="container">
            <h1 class="logo"><a href="../index.php">Blog Acadêmico</a></h1>

            <nav class="menu">
                <a href="../index.php">Início</a>
                <a href="../sobre.php">Sobre</a>
            </nav>

            <div class="usuario-area">
                <?php if (usuarioLogado()): ?>
                    <div class="dropdown">
                        <button class="dropbtn">
                            <?php if (!empty($_SESSION['imagem'])): ?>
                                <img src="<?php echo $_SESSION['imagem']; ?>" alt="Perfil" class="img-perfil-pequena">
                            <?php endif; ?>
                            <?php echo htmlspecialchars($_SESSION['nome']); ?> ▼
                        </button>
                        <div class="dropdown-conteudo">
                            <a href="../backend/perfil.php">Perfil</a>
                            <?php if (ehAdmin()): ?>
                                <a href="../backend/painelAdmin.php">Dashboard Admin</a>
                            <?php endif; ?>
                            <a href="../backend/logout.php">Sair</a>
                        </div>
                    </div>
                <?php else: ?>
                    <a href="../backend/login.php" class="btn-entrar">Entrar</a>
                    <a href="../backend/registrar.php" class="btn-registrar">Registrar</a>
                <?php endif; ?>
            </div>
        </div>
    </header>