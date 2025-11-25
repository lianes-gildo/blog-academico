<?php
/**
 * backend/responder_comentario.php - Atualizado com sistema de notificações
 */
session_start();
header('Content-Type: application/json');
require_once 'notificacoes_processor.php';

if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['sucesso' => false, 'mensagem' => 'Faça login para responder']);
    exit;
}

$comentarioIdPai = (int)$_POST['comentario_id'];
$resposta = trim($_POST['resposta']);
$nome = $_SESSION['nome'];
$usuarioId = $_SESSION['usuario_id'];

if (empty($resposta)) {
    echo json_encode(['sucesso' => false, 'mensagem' => 'Resposta vazia']);
    exit;
}

$arquivoComentarios = __DIR__ . '/../data/comentarios.json';

// Verificar se arquivo existe
if (!file_exists($arquivoComentarios)) {
    file_put_contents($arquivoComentarios, '[]');
}

$comentarios = json_decode(file_get_contents($arquivoComentarios), true);

if (!is_array($comentarios)) {
    $comentarios = [];
}

// Gerar ID único
$novoId = 1;
foreach ($comentarios as $c) {
    if (isset($c['comentario_id']) && $c['comentario_id'] >= $novoId) {
        $novoId = $c['comentario_id'] + 1;
    }
}

// Encontrar artigo_id do comentário pai
$artigoId = 0;
foreach ($comentarios as $c) {
    if (isset($c['comentario_id']) && $c['comentario_id'] == $comentarioIdPai) {
        $artigoId = $c['artigo_id'];
        break;
    }
}

if ($artigoId === 0) {
    echo json_encode(['sucesso' => false, 'mensagem' => 'Comentário pai não encontrado']);
    exit;
}

// Criar resposta
$novaResposta = [
    'comentario_id' => $novoId,
    'artigo_id' => $artigoId,
    'nome' => $nome,
    'usuario_id' => $usuarioId,
    'comentario' => $resposta,
    'data' => time(),
    'pai_id' => $comentarioIdPai,
    'likes' => [],
    'dislikes' => []
];

$comentarios[] = $novaResposta;

// Salvar
if (file_put_contents($arquivoComentarios, json_encode($comentarios, JSON_PRETTY_PRINT))) {
    // Processar notificações
    // 1. Notificar o dono do comentário pai
    processarNotificacaoResposta($comentarioIdPai, $usuarioId, $nome);
    
    // 2. Processar menções na resposta
    processarNotificacoesComentario($resposta, $artigoId, $nome);
    
    // Atualizar o ID do comentário nas notificações criadas
    $notificacoes = json_decode(file_get_contents('../data/notificacoes.json'), true);
    foreach ($notificacoes as &$notif) {
        if ($notif['comentario_id'] == 0 && $notif['post_id'] == $artigoId && $notif['usuario_origem_nome'] == $nome) {
            $notif['comentario_id'] = $novoId;
        }
    }
    file_put_contents('../data/notificacoes.json', json_encode($notificacoes, JSON_PRETTY_PRINT));
    
    echo json_encode([
        'sucesso' => true,
        'comentario_id' => $novoId,
        'nome' => $nome,
        'comentario' => $resposta,
        'data' => date('d/m/Y H:i', time())
    ]);
} else {
    echo json_encode(['sucesso' => false, 'mensagem' => 'Erro ao salvar resposta']);
}
?>