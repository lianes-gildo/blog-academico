<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['sucesso' => false]);
    exit;
}

$postId = (int)$_POST['post_id'];
$usuarioId = $_SESSION['usuario_id'];

$estatisticas = json_decode(file_get_contents('../data/estatisticas.json'), true);
$posts = json_decode(file_get_contents('../data/posts.json'), true);

// Encontra o post para atualizar gostos no posts.json
$indicePost = null;
foreach ($posts as $i => $p) {
    if ($p['id'] == $postId) {
        $indicePost = $i;
        break;
    }
}

if ($indicePost === null) {
    echo json_encode(['sucesso' => false]);
    exit;
}

// Lista de quem curtiu
$gostasLista = $estatisticas['gostos'][$postId] ?? [];

$jaCurtiu = in_array($usuarioId, $gostasLista);

if ($jaCurtiu) {
    // Unlike
    $gostasLista = array_diff($gostasLista, [$usuarioId]);
    $posts[$indicePost]['gostos']--;
} else {
    // Like
    $gostasLista[] = $usuarioId;
    $posts[$indicePost]['gostos']++;
}

$estatisticas['gostos'][$postId] = array_values($gostasLista); // reindex
file_put_contents('../data/estatisticas.json', json_encode($estatisticas, JSON_PRETTY_PRINT));
file_put_contents('../data/posts.json', json_encode($posts, JSON_PRETTY_PRINT));

echo json_encode([
    'sucesso' => true,
    'total' => $posts[$indicePost]['gostos']
]);