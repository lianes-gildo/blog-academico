<?php
/**
 * backend/comentar.php - Atualizado com sistema de notificações
 */
session_start();
require_once 'notificacoes_processor.php';

if (!isset($_SESSION['usuario_id'])) {
    echo "Faça login para comentar.";
    exit;
}

$postId = (int)$_POST['post_id'];
$comentario = trim($_POST['comentario']);
$nome = $_SESSION['nome'];
$usuarioId = $_SESSION['usuario_id'];

if (empty($comentario)) {
    echo "Comentário vazio.";
    exit;
}

$comentarios = json_decode(file_get_contents('../data/comentarios.json'), true) ?? [];

// Gerar ID único
$novoId = 1;
foreach ($comentarios as $c) {
    if (isset($c['comentario_id']) && $c['comentario_id'] >= $novoId) {
        $novoId = $c['comentario_id'] + 1;
    }
}

$novoComentario = [
    'comentario_id' => $novoId,
    'artigo_id' => $postId,
    'nome' => $nome,
    'usuario_id' => $usuarioId,
    'comentario' => $comentario,
    'data' => time(),
    'likes' => [],
    'dislikes' => []
];

$comentarios[] = $novoComentario;

file_put_contents('../data/comentarios.json', json_encode($comentarios, JSON_PRETTY_PRINT));

// Processar notificações de menções
processarNotificacoesComentario($comentario, $postId, $nome);

// Atualizar o ID do comentário nas notificações criadas
$notificacoes = json_decode(file_get_contents('../data/notificacoes.json'), true);
foreach ($notificacoes as &$notif) {
    if ($notif['comentario_id'] == 0 && $notif['post_id'] == $postId && $notif['usuario_origem_nome'] == $nome) {
        $notif['comentario_id'] = $novoId;
    }
}
file_put_contents('../data/notificacoes.json', json_encode($notificacoes, JSON_PRETTY_PRINT));

echo "Comentário enviado com sucesso!";
?>