<?php
session_start();
header('Content-Type: application/json');

$postId = isset($_GET['post_id']) ? (int)$_GET['post_id'] : 0;

if ($postId == 0) {
    echo json_encode(['comentarios' => [], 'total' => 0]);
    exit;
}

$arquivoComentarios = __DIR__ . '/../../data/comentarios.json';

if (!file_exists($arquivoComentarios)) {
    echo json_encode(['comentarios' => [], 'total' => 0]);
    exit;
}

$comentarios = json_decode(file_get_contents($arquivoComentarios), true);

if (!is_array($comentarios)) {
    echo json_encode(['comentarios' => [], 'total' => 0]);
    exit;
}

// Filtrar comentários deste post
$comentariosPost = array_filter($comentarios, function($c) use ($postId) {
    return $c['artigo_id'] == $postId;
});

// Garantir que arrays de likes/dislikes existam
foreach ($comentariosPost as &$c) {
    if (!isset($c['likes'])) $c['likes'] = [];
    if (!isset($c['dislikes'])) $c['dislikes'] = [];
}

// Reindexar
$comentariosPost = array_values($comentariosPost);

echo json_encode([
    'comentarios' => $comentariosPost,
    'total' => count($comentariosPost)
]);
?>