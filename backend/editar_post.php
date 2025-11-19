<?php
session_start();
if(!isset($_SESSION['usuario']) || $_SESSION['usuario']['papel'] !== 'admin'){
    header('Location: login.php');
    exit;
}

if(!isset($_GET['id'])){
    header('Location: painel.php');
    exit;
}

$id = $_GET['id'];
$posts = json_decode(file_get_contents('../data/posts.json'), true);
$index = null;

foreach($posts as $key => $post){
    if($post['id'] == $id){
        $index = $key;
        break;
    }
}

if($index === null){
    header('Location: painel.php');
    exit;
}

$post = $posts[$index];

// Processar edição
if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $posts[$index]['titulo'] = $_POST['titulo'];
    $posts[$index]['introducao'] = $_POST['introducao'];
    $posts[$index]['conteudo'] = $_POST['conteudo'];

    if(isset($_FILES['imagem']) && $_FILES['imagem']['error']==0){
        $ext = pathinfo($_FILES['imagem']['name'], PATHINFO_EXTENSION);
        $nomeImagem = uniqid().'.'.$ext;
        move_uploaded_file($_FILES['imagem']['tmp_name'], "../assets/img/".$nomeImagem);
        $posts[$index]['imagem'] = "assets/img/".$nomeImagem;
    }

    file_put_contents('../data/posts.json', json_encode($posts, JSON_PRETTY_PRINT));
    header('Location: painel.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Editar Post - Painel Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<div class="form-container">
    <h2>Editar Post</h2>
    <form method="POST" enctype="multipart/form-data">
        <input type="text" name="titulo" placeholder="Título" value="<?= $post['titulo'] ?>" required>
        <input type="text" name="introducao" placeholder="Descrição Curta" value="<?= $post['introducao'] ?>" required>
        <textarea name="conteudo" placeholder="Descrição Longa" required><?= $post['conteudo'] ?></textarea>
        <p>Imagem Atual:</p>
        <img src="../<?= $post['imagem'] ?>" width="150">
        <input type="file" name="imagem" accept="image/*">
        <button type="submit">Atualizar Post</button>
    </form>
    <p><a href="painel.php">Voltar ao Painel</a></p>
</div>
</body>
</html>
