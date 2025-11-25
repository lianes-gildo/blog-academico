<?php
/**
 * backend/comentarios_interacao.php - Atualizado com sistema de notificações
 */
session_start();
header('Content-Type: application/json');
require_once 'notificacoes_processor.php';

if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['sucesso' => false, 'mensagem' => 'Não autenticado']);
    exit;
}

$acao = $_POST['acao'] ?? '';
$comentarioId = (int)($_POST['comentario_id'] ?? 0);
$usuarioId = $_SESSION['usuario_id'];
$usuarioNome = $_SESSION['nome'];

$comentarios = json_decode(file_get_contents('../data/comentarios.json'), true) ?? [];

// Encontrar índice do comentário
$indiceComentario = null;
foreach ($comentarios as $i => $c) {
    if (isset($c['comentario_id']) && $c['comentario_id'] == $comentarioId) {
        $indiceComentario = $i;
        break;
    }
}

if ($indiceComentario === null) {
    echo json_encode(['sucesso' => false, 'mensagem' => 'Comentário não encontrado']);
    exit;
}

switch ($acao) {
    case 'like':
        if (!isset($comentarios[$indiceComentario]['likes'])) {
            $comentarios[$indiceComentario]['likes'] = [];
        }
        if (!isset($comentarios[$indiceComentario]['dislikes'])) {
            $comentarios[$indiceComentario]['dislikes'] = [];
        }
        
        $likes = &$comentarios[$indiceComentario]['likes'];
        $dislikes = &$comentarios[$indiceComentario]['dislikes'];
        
        // Remover de dislikes se existir
        $dislikes = array_diff($dislikes, [$usuarioId]);
        
        // Toggle like
        $addedLike = false;
        if (in_array($usuarioId, $likes)) {
            $likes = array_diff($likes, [$usuarioId]);
        } else {
            $likes[] = $usuarioId;
            $addedLike = true;
        }
        
        $comentarios[$indiceComentario]['likes'] = array_values($likes);
        $comentarios[$indiceComentario]['dislikes'] = array_values($dislikes);
        
        file_put_contents('../data/comentarios.json', json_encode($comentarios, JSON_PRETTY_PRINT));
        
        // Criar notificação apenas se deu like (não se removeu)
        if ($addedLike) {
            processarNotificacaoInteracao('like', $comentarioId, $usuarioId, $usuarioNome);
        }
        
        echo json_encode([
            'sucesso' => true,
            'likes' => count($comentarios[$indiceComentario]['likes']),
            'dislikes' => count($comentarios[$indiceComentario]['dislikes'])
        ]);
        break;
        
    case 'dislike':
        if (!isset($comentarios[$indiceComentario]['likes'])) {
            $comentarios[$indiceComentario]['likes'] = [];
        }
        if (!isset($comentarios[$indiceComentario]['dislikes'])) {
            $comentarios[$indiceComentario]['dislikes'] = [];
        }
        
        $likes = &$comentarios[$indiceComentario]['likes'];
        $dislikes = &$comentarios[$indiceComentario]['dislikes'];
        
        // Remover de likes se existir
        $likes = array_diff($likes, [$usuarioId]);
        
        // Toggle dislike
        $addedDislike = false;
        if (in_array($usuarioId, $dislikes)) {
            $dislikes = array_diff($dislikes, [$usuarioId]);
        } else {
            $dislikes[] = $usuarioId;
            $addedDislike = true;
        }
        
        $comentarios[$indiceComentario]['likes'] = array_values($likes);
        $comentarios[$indiceComentario]['dislikes'] = array_values($dislikes);
        
        file_put_contents('../data/comentarios.json', json_encode($comentarios, JSON_PRETTY_PRINT));
        
        // Criar notificação apenas se deu dislike (não se removeu)
        if ($addedDislike) {
            processarNotificacaoInteracao('dislike', $comentarioId, $usuarioId, $usuarioNome);
        }
        
        echo json_encode([
            'sucesso' => true,
            'likes' => count($comentarios[$indiceComentario]['likes']),
            'dislikes' => count($comentarios[$indiceComentario]['dislikes'])
        ]);
        break;
        
    default:
        echo json_encode(['sucesso' => false, 'mensagem' => 'Ação inválida']);
}
?>