// ========================================
// 1. backend/api/check_comments_updates.php
// Verifica se há novos comentários
// ========================================
<?php
session_start();
header('Content-Type: application/json');

$postId = isset($_GET['post_id']) ? (int)$_GET['post_id'] : 0;
$lastCheck = isset($_GET['last_check']) ? (int)$_GET['last_check'] : 0;

if ($postId == 0) {
    echo json_encode(['hasUpdates' => false, 'timestamp' => time()]);
    exit;
}

$arquivoComentarios = __DIR__ . '/../../data/comentarios.json';
if (!file_exists($arquivoComentarios)) {
    echo json_encode(['hasUpdates' => false, 'timestamp' => time()]);
    exit;
}

$comentarios = json_decode(file_get_contents($arquivoComentarios), true);
if (!is_array($comentarios)) {
    echo json_encode(['hasUpdates' => false, 'timestamp' => time()]);
    exit;
}

// Verificar se há comentários novos ou modificados
$hasUpdates = false;
foreach ($comentarios as $c) {
    if ($c['artigo_id'] == $postId && $c['data'] > $lastCheck) {
        $hasUpdates = true;
        break;
    }
}

echo json_encode([
    'hasUpdates' => $hasUpdates,
    'timestamp' => time()
]);