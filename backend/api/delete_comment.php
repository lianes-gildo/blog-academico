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

// Encontrar o comentário
$encontrado = false;
$novosComentarios = [];

foreach ($comentarios as $c) {
    if ($c['comentario_id'] == $comentarioId) {
        // Verificar se é o dono do comentário
        if ($c['usuario_id'] != $usuarioId) {
            echo json_encode(['success' => false, 'message' => 'Você não pode apagar este comentário']);
            exit;
        }
        $encontrado = true;
        // Não adicionar ao array (apagar)
    } else {
        // Também apagar respostas deste comentário
        if (isset($c['pai_id']) && $c['pai_id'] == $comentarioId) {
            // Não adicionar (apagar resposta também)
        } else {
            $novosComentarios[] = $c;
        }
    }
}

if (!$encontrado) {
    echo json_encode(['success' => false, 'message' => 'Comentário não encontrado']);
    exit;
}

file_put_contents($arquivoComentarios, json_encode($novosComentarios, JSON_PRETTY_PRINT));
echo json_encode(['success' => true, 'message' => 'Comentário apagado com sucesso']);
?>