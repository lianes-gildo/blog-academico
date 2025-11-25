<?php
/**
 * Sistema de Processamento de Notificações
 * Processa menções, likes e dislikes em comentários
 */

// Incluir as funções de notificação
require_once __DIR__ . '/criar_notificacao.php';

function extrairMencoes($texto) {
    preg_match_all('/@(\w+)/', $texto, $matches);
    return array_unique($matches[1]);
}

function buscarUsuarioPorNome($nome) {
    $usuarios = json_decode(file_get_contents(__DIR__ . '/../data/usuarios.json'), true);
    foreach ($usuarios as $user) {
        if (strtolower($user['nome']) === strtolower($nome)) {
            return $user;
        }
    }
    return null;
}

function obterTituloPost($postId) {
    $posts = json_decode(file_get_contents(__DIR__ . '/../data/posts.json'), true);
    foreach ($posts as $post) {
        if ($post['id'] == $postId) {
            return $post['titulo'];
        }
    }
    return 'Post não encontrado';
}

/**
 * Processar notificações ao comentar/responder
 */
function processarNotificacoesComentario($comentario, $postId, $usuarioOrigemNome) {
    $mencoes = extrairMencoes($comentario);
    $postTitulo = obterTituloPost($postId);
    
    foreach ($mencoes as $nomeUsuario) {
        $usuarioDestino = buscarUsuarioPorNome($nomeUsuario);
        if ($usuarioDestino && $usuarioDestino['nome'] !== $usuarioOrigemNome) {
            criarNotificacao(
                'mention',
                $usuarioDestino['id'],
                $usuarioOrigemNome,
                0, // será atualizado depois
                $postId,
                $postTitulo
            );
        }
    }
}

/**
 * Processar notificações de like/dislike
 */
function processarNotificacaoInteracao($tipo, $comentarioId, $usuarioOrigemId, $usuarioOrigemNome) {
    $comentarios = json_decode(file_get_contents(__DIR__ . '/../data/comentarios.json'), true);
    
    // Encontrar o comentário
    $comentarioAlvo = null;
    foreach ($comentarios as $c) {
        if (isset($c['comentario_id']) && $c['comentario_id'] == $comentarioId) {
            $comentarioAlvo = $c;
            break;
        }
    }
    
    if (!$comentarioAlvo) {
        return;
    }
    
    // Buscar o dono do comentário
    $donoComentario = buscarUsuarioPorNome($comentarioAlvo['nome']);
    if (!$donoComentario || $donoComentario['id'] == $usuarioOrigemId) {
        return; // Não notificar se for o próprio usuário
    }
    
    $postTitulo = obterTituloPost($comentarioAlvo['artigo_id']);
    
    criarNotificacao(
        $tipo, // 'like' ou 'dislike'
        $donoComentario['id'],
        $usuarioOrigemNome,
        $comentarioId,
        $comentarioAlvo['artigo_id'],
        $postTitulo
    );
}

/**
 * Processar notificações de resposta
 */
function processarNotificacaoResposta($comentarioPaiId, $usuarioOrigemId, $usuarioOrigemNome) {
    $comentarios = json_decode(file_get_contents(__DIR__ . '/../data/comentarios.json'), true);
    
    // Encontrar o comentário pai
    $comentarioPai = null;
    foreach ($comentarios as $c) {
        if (isset($c['comentario_id']) && $c['comentario_id'] == $comentarioPaiId) {
            $comentarioPai = $c;
            break;
        }
    }
    
    if (!$comentarioPai) {
        return;
    }
    
    // Buscar o dono do comentário pai
    $donoComentario = buscarUsuarioPorNome($comentarioPai['nome']);
    if (!$donoComentario || $donoComentario['id'] == $usuarioOrigemId) {
        return; // Não notificar se for o próprio usuário
    }
    
    $postTitulo = obterTituloPost($comentarioPai['artigo_id']);
    
    criarNotificacao(
        'reply',
        $donoComentario['id'],
        $usuarioOrigemNome,
        $comentarioPaiId,
        $comentarioPai['artigo_id'],
        $postTitulo
    );
}
?>