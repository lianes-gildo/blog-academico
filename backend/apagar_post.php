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

if($index !== null){
    // Apagar imagem do servidor
    if(file_exists("../".$posts[$index]['imagem'])){
        unlink("../".$posts[$index]['imagem']);
    }

    // Remover post
    array_splice($posts, $index, 1);
    file_put_contents('../data/posts.json', json_encode($posts, JSON_PRETTY_PRINT));
}

header('Location: painel.php');
exit;
