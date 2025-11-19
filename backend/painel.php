<?php
session_start();
if(!isset($_SESSION['usuario']) || $_SESSION['usuario']['papel'] !== 'admin'){
    header('Location: login.php');
    exit;
}

$posts = json_decode(file_get_contents('../data/posts.json'), true);
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Painel Admin - Blog Académico</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<header>
    <h1>Painel Admin</h1>
    <nav>
        <a href="logout.php">Sair</a>
    </nav>
</header>

<main>
    <a href="novo_post.php" class="botao">Adicionar Post</a>

    <table border="1" cellspacing="0" cellpadding="10">
        <thead>
            <tr>
                <th>#ID</th>
                <th>Imagem</th>
                <th>Descrição Curta</th>
                <th>Descrição Longa</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($posts as $post): ?>
            <tr>
                <td><?= $post['id'] ?></td>
                <td><img src="../<?= $post['imagem'] ?>" alt="<?= $post['titulo'] ?>" width="100"></td>
                <td><?= $post['introducao'] ?></td>
                <td><?= $post['conteudo'] ?></td>
                <td>
                    <a href="editar_post.php?id=<?= $post['id'] ?>">Editar</a> | 
                    <a href="apagar_post.php?id=<?= $post['id'] ?>" onclick="return confirm('Deseja apagar este post?')">Apagar</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</main>

<style>
body { font-family: Arial, sans-serif; background:#f5f6fa; padding:20px; }
header { margin-bottom:20px; }
header h1 { display:inline-block; }
nav { display:inline-block; margin-left:20px; }
.botao { display:inline-block; margin-bottom:15px; padding:10px 20px; background:#4c8bf5; color:#fff; border-radius:6px; text-decoration:none; }
.botao:hover { background:#2b6edc; }
table { width:100%; border-collapse: collapse; background:#fff; border-radius:10px; overflow:hidden; }
th, td { padding:10px; text-align:left; }
th { background:#2b6edc; color:#fff; }
td img { border-radius:6px; }
a { text-decoration:none; color:#4c8bf5; }
a:hover { text-decoration:underline; }
</style>
</body>
</html>
