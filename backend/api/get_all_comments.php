<?php
// Retorna todos os comentários de um post
session_start();
header('Content-Type: application/json');

$postId = isset($_GET['post_id']) ? (int)$_GET['post_id'] : 0;

if ($postId == 0) {
    echo json_encode(['success' => false, 'message' => 'ID inválido']);
    exit;
}

$arquivoComentarios = __DIR__ . '/../../data/comentarios.json';
if (!file_exists($arquivoComentarios)) {
    echo json_encode(['success' => true, 'comentarios' => [], 'total' => 0]);
    exit;
}

$todosComentarios = json_decode(file_get_contents($arquivoComentarios), true);
if (!is_array($todosComentarios)) {
    echo json_encode(['success' => true, 'comentarios' => [], 'total' => 0]);
    exit;
}

// Filtrar comentários deste post
$comentariosPost = array_filter($todosComentarios, function($c) use ($postId) {
    return $c['artigo_id'] == $postId;
});

// Garantir arrays de likes/dislikes
foreach ($comentariosPost as &$c) {
    if (!isset($c['likes'])) $c['likes'] = [];
    if (!isset($c['dislikes'])) $c['dislikes'] = [];
}

// Adicionar info se é do usuário logado
$usuarioId = isset($_SESSION['usuario_id']) ? $_SESSION['usuario_id'] : null;
foreach ($comentariosPost as &$c) {
    $c['ehMeu'] = ($usuarioId && $c['usuario_id'] == $usuarioId);
    $c['userLiked'] = ($usuarioId && in_array($usuarioId, $c['likes']));
    $c['userDisliked'] = ($usuarioId && in_array($usuarioId, $c['dislikes']));
}

echo json_encode([
    'success' => true,
    'comentarios' => array_values($comentariosPost),
    'total' => count($comentariosPost),
    'timestamp' => time()
]);