<?php
session_start();
if ($_SESSION['papel'] !== 'admin') exit;

$postId = (int)$_GET['id'];

$posts = json_decode(file_get_contents('../data/posts.json'), true);
$novoPosts = [];
foreach ($posts as $p) {
    if ($p['id'] != $postId) $novoPosts[] = $p;
    else {
        // Apaga imagem
        if (file_exists('../' . $p['imagem'])) unlink('../' . $p['imagem']);
    }
}
file_put_contents('../data/posts.json', json_encode($novoPosts, JSON_PRETTY_PRINT));

// Apaga estatísticas
$estatisticas = json_decode(file_get_contents('../data/estatisticas.json'), true);
unset($estatisticas['gostos'][$postId], $estatisticas['visitas'][$postId]);
file_put_contents('../data/estatisticas.json', json_encode($estatisticas, JSON_PRETTY_PRINT));

header('Location: painelAdmin.php');