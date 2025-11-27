// ========================================
// 3. backend/api/delete_comment_cascade.php
// Apaga comentário e todas respostas (cascata)
// ========================================
<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['success' => false, 'message' => 'Não autenticado']);
    exit;
}

$comentarioId = isset($_POST['comentario_id']) ? (int)$_POST['comentario_id'] : 0;
$usuarioId = $_SESSION['usuario_id'];

if ($comentarioId == 0) {
    echo json_encode(['success' => false, 'message' => 'ID inválido']);
    exit;
}

$arquivoComentarios = __DIR__ . '/../../data/comentarios.json';
if (!file_exists($arquivoComentarios)) {
    echo json_encode(['success' => false, 'message' => 'Arquivo não encontrado']);
    exit;
}

$comentarios = json_decode(file_get_contents($arquivoComentarios), true);
if (!is_array($comentarios)) {
    echo json_encode(['success' => false, 'message' => 'Erro ao ler comentários']);
    exit;
}

// Verificar propriedade do comentário
$comentarioEncontrado = null;
foreach ($comentarios as $c) {
    if ($c['comentario_id'] == $comentarioId) {
        if ($c['usuario_id'] != $usuarioId) {
            echo json_encode(['success' => false, 'message' => 'Você não pode apagar este comentário']);
            exit;
        }
        $comentarioEncontrado = $c;
        break;
    }
}

if (!$comentarioEncontrado) {
    echo json_encode(['success' => false, 'message' => 'Comentário não encontrado']);
    exit;
}

// Função para coletar IDs em cascata
function coletarIdsCascata($comentarioId, $comentarios) {
    $ids = [$comentarioId];
    $encontrou = true;
    
    while ($encontrou) {
        $encontrou = false;
        foreach ($comentarios as $c) {
            if (isset($c['pai_id']) && in_array($c['pai_id'], $ids) && !in_array($c['comentario_id'], $ids)) {
                $ids[] = $c['comentario_id'];
                $encontrou = true;
            }
        }
    }
    
    return $ids;
}

$idsParaApagar = coletarIdsCascata($comentarioId, $comentarios);

// Remover comentários
$novosComentarios = array_filter($comentarios, function($c) use ($idsParaApagar) {
    return !in_array($c['comentario_id'], $idsParaApagar);
});

file_put_contents($arquivoComentarios, json_encode(array_values($novosComentarios), JSON_PRETTY_PRINT));

// Apagar denúncias relacionadas
$arquivoDenuncias = __DIR__ . '/../../data/denuncias.json';
if (file_exists($arquivoDenuncias)) {
    $denuncias = json_decode(file_get_contents($arquivoDenuncias), true);
    if (is_array($denuncias)) {
        $denuncias = array_filter($denuncias, function($d) use ($idsParaApagar) {
            return !in_array($d['comentario_id'], $idsParaApagar);
        });
        file_put_contents($arquivoDenuncias, json_encode(array_values($denuncias), JSON_PRETTY_PRINT));
    }
}

echo json_encode([
    'success' => true,
    'message' => count($idsParaApagar) . ' comentário(s) apagado(s) com sucesso',
    'total_apagados' => count($idsParaApagar),
    'timestamp' => time()
]);
