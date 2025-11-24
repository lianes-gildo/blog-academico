<?php
session_start();

if (!isset($_SESSION['papel']) || ($_SESSION['papel'] !== 'admin' && $_SESSION['papel'] !== 'editor')) {
    header('Location: ../acesso_negado.php');
    exit;
}

$postId = (int)$_GET['id'];
$posts = json_decode(file_get_contents('../data/posts.json'), true);

// Encontrar o post
$postParaApagar = null;
foreach ($posts as $p) {
    if ($p['id'] == $postId) {
        $postParaApagar = $p;
        break;
    }
}

// Verificar se o editor é o autor
if ($_SESSION['papel'] === 'editor' && $postParaApagar && $postParaApagar['autor'] !== $_SESSION['nome']) {
    header('Location: ../acesso_negado.php');
    exit;
}

$novoPosts = [];
foreach ($posts as $p) {
    if ($p['id'] != $postId) {
        $novoPosts[] = $p;
    } else {
        // Apagar imagem se não for a default
        if (file_exists('../' . $p['imagem']) && $p['imagem'] !== 'assets/img/posts/default.jpg') {
            unlink('../' . $p['imagem']);
        }
    }
}

file_put_contents('../data/posts.json', json_encode($novoPosts, JSON_PRETTY_PRINT));

// Limpar estatísticas
$estatisticas = json_decode(file_get_contents('../data/estatisticas.json'), true);
unset($estatisticas['gostos'][$postId]);
unset($estatisticas['visitas'][$postId]);
file_put_contents('../data/estatisticas.json', json_encode($estatisticas, JSON_PRETTY_PRINT));

$redirect = $_SESSION['papel'] === 'admin' ? 'painelAdmin.php' : 'painelEditor.php';
header('Location: ' . $redirect);
exit;
?>