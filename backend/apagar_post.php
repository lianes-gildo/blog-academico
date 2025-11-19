<?php
session_start();
date_default_timezone_set('Africa/Maputo');

// -----------------------
// Verifica admin
// -----------------------
if (!isset($_SESSION['usuario']['id'])) {
    header('Location:/backend/login.php');
    exit;
}

$usuarios = json_decode(file_get_contents(__DIR__ . '/../data/usuarios.json'), true) ?: [];
$usuario = null;
foreach ($usuarios as $u) {
    if (($u['id'] ?? 0) == $_SESSION['usuario']['id']) {
        $usuario = $u;
        break;
    }
}

if (!$usuario || ($usuario['papel'] ?? '') !== 'admin') {
    echo "Acesso negado.";
    exit;
}

// -----------------------
// ID do post
// -----------------------
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$id) {
    header('Location:/backend/painelAdmin.php');
    exit;
}

// -----------------------
// Carrega posts
// -----------------------
$arquivoPosts = __DIR__ . '/../data/posts.json';
$posts = json_decode(file_get_contents($arquivoPosts), true) ?: [];

$indice = null;
foreach ($posts as $k => $p) {
    if ((int)($p['id'] ?? 0) === $id) {
        $indice = $k;
        break;
    }
}

if ($indice === null) {
    header('Location:/backend/painelAdmin.php');
    exit;
}

// -----------------------
// Remove imagem
// -----------------------
$img = $posts[$indice]['imagem'] ?? '';
if ($img && strpos($img, 'default') === false) {
    $caminhoImagem = __DIR__ . '/../' . ltrim($img, '/');
    if (is_file($caminhoImagem)) {
        @unlink($caminhoImagem);
    }
}

// -----------------------
// Remove post
// -----------------------
array_splice($posts, $indice, 1);
file_put_contents($arquivoPosts, json_encode($posts, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

// -----------------------
// Remove estatísticas
// -----------------------
$arquivoEstat = __DIR__ . '/../data/estatisticas.json';
$estat = json_decode(file_get_contents($arquivoEstat), true) ?: ['gostos'=>[], 'visitas'=>[], 'compartilhamentos'=>[]];

$idStr = (string)$id;
unset($estat['gostos'][$idStr], $estat['visitas'][$idStr], $estat['compartilhamentos'][$idStr]);
file_put_contents($arquivoEstat, json_encode($estat, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

// -----------------------
// Remove comentários
// -----------------------
$arquivoComentarios = __DIR__ . '/../data/comentarios.json';
$comentarios = json_decode(file_get_contents($arquivoComentarios), true) ?: [];
$comentarios = array_values(array_filter($comentarios, function($c) use ($id) {
    return (int)($c['artigo_id'] ?? 0) !== $id;
}));
file_put_contents($arquivoComentarios, json_encode($comentarios, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

// -----------------------
// Redireciona
// -----------------------
header('Location: /backend/painelAdmin.php');
exit;
