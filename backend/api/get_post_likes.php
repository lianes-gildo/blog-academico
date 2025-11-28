<?php
// Retorna total de likes de um post
header('Content-Type: application/json');

$postId = isset($_GET['post_id']) ? (int)$_GET['post_id'] : 0;

if ($postId == 0) {
    echo json_encode(['success' => false, 'message' => 'ID inválido']);
    exit;
}

$posts = json_decode(file_get_contents(__DIR__ . '/../../data/posts.json'), true);

$total = 0;
foreach ($posts as $p) {
    if ($p['id'] == $postId) {
        $total = $p['gostos'];
        break;
    }
}

echo json_encode([
    'success' => true,
    'total' => $total,
    'timestamp' => time()
]);