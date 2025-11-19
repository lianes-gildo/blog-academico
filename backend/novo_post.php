<?php
session_start();
if(!isset($_SESSION['usuario']) || $_SESSION['usuario']['papel'] !== 'admin'){
    header('Location: login.php');
    exit;
}

// Caminho do arquivo JSON
$arquivoPosts = '../data/posts.json';

// Garantir que o arquivo existe
if(!file_exists($arquivoPosts)){
    file_put_contents($arquivoPosts, json_encode([]));
}

// Ler os posts existentes
$posts = json_decode(file_get_contents($arquivoPosts), true);
if(!is_array($posts)){
    $posts = [];
}

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $titulo = trim($_POST['titulo']);
    $introducao = trim($_POST['introducao']);
    $conteudo = trim($_POST['conteudo']);
    $autor = $_SESSION['usuario']['nome'];
    $data = date("d-m-Y H:i");

    $imagem = '';
    if(isset($_FILES['imagem']) && $_FILES['imagem']['error'] == 0){
        $ext = pathinfo($_FILES['imagem']['name'], PATHINFO_EXTENSION);
        $nomeImagem = uniqid().'.'.$ext;
        move_uploaded_file($_FILES['imagem']['tmp_name'], "../assets/img/".$nomeImagem);
        $imagem = "assets/img/".$nomeImagem;
    }

    $novoPost = [
        'id' => count($posts) > 0 ? $posts[count($posts)-1]['id'] + 1 : 1,
        'titulo' => $titulo,
        'autor' => $autor,
        'data' => $data,
        'imagem' => $imagem,
        'introducao' => $introducao,
        'conteudo' => $conteudo,
        'gostos' => 0,
        'visitas' => 0
    ];

    $posts[] = $novoPost;
    file_put_contents($arquivoPosts, json_encode($posts, JSON_PRETTY_PRINT));

    header('Location: painel.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Novo Post - Painel Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<div class="form-container">
    <h2>Criar Novo Post</h2>
    <form method="POST" enctype="multipart/form-data">
        <input type="text" name="titulo" placeholder="Título" required>
        <input type="text" name="introducao" placeholder="Descrição Curta" required>
        <textarea name="conteudo" placeholder="Descrição Longa" required></textarea>
        <input type="file" name="imagem" accept="image/*">
        <button type="submit">Adicionar Post</button>
    </form>
    <p><a href="painel.php">Voltar ao Painel</a></p>
</div>
</body>
</html>
