<?php
/**
 * Sistema de Criação de Notificações
 * Tipos: mention (menção), like (curtida), dislike (não curtiu), reply (resposta)
 */

function criarNotificacao($tipo, $usuarioDestinoId, $usuarioOrigemNome, $comentarioId, $postId, $postTitulo) {
    $arquivoNotificacoes = __DIR__ . '/../data/notificacoes.json';
    
    // Criar arquivo se não existir
    if (!file_exists($arquivoNotificacoes)) {
        file_put_contents($arquivoNotificacoes, '[]');
    }
    
    $notificacoes = json_decode(file_get_contents($arquivoNotificacoes), true);
    
    if (!is_array($notificacoes)) {
        $notificacoes = [];
    }
    
    // Gerar ID único
    $novoId = 1;
    foreach ($notificacoes as $n) {
        if (isset($n['id']) && $n['id'] >= $novoId) {
            $novoId = $n['id'] + 1;
        }
    }
    
    // Criar notificação
    $notificacao = [
        'id' => $novoId,
        'tipo' => $tipo,
        'usuario_destino_id' => $usuarioDestinoId,
        'usuario_origem_nome' => $usuarioOrigemNome,
        'comentario_id' => $comentarioId,
        'post_id' => $postId,
        'post_titulo' => $postTitulo,
        'data' => time(),
        'lida' => false
    ];
    
    $notificacoes[] = $notificacao;
    
    // Salvar
    file_put_contents($arquivoNotificacoes, json_encode($notificacoes, JSON_PRETTY_PRINT));
    
    return $novoId;
}

function obterNotificacoesUsuario($usuarioId, $apenasNaoLidas = false) {
    $arquivoNotificacoes = __DIR__ . '/../data/notificacoes.json';
    
    if (!file_exists($arquivoNotificacoes)) {
        return [];
    }
    
    $notificacoes = json_decode(file_get_contents($arquivoNotificacoes), true);
    
    if (!is_array($notificacoes)) {
        return [];
    }
    
    $resultado = [];
    foreach ($notificacoes as $n) {
        if ($n['usuario_destino_id'] == $usuarioId) {
            if ($apenasNaoLidas && $n['lida']) {
                continue;
            }
            $resultado[] = $n;
        }
    }
    
    // Ordenar por data (mais recentes primeiro)
    usort($resultado, function($a, $b) {
        return $b['data'] - $a['data'];
    });
    
    return $resultado;
}

function contarNotificacoesNaoLidas($usuarioId) {
    $notificacoes = obterNotificacoesUsuario($usuarioId, true);
    return count($notificacoes);
}

function marcarNotificacaoComoLida($notificacaoId) {
    $arquivoNotificacoes = __DIR__ . '/../data/notificacoes.json';
    
    if (!file_exists($arquivoNotificacoes)) {
        return false;
    }
    
    $notificacoes = json_decode(file_get_contents($arquivoNotificacoes), true);
    
    if (!is_array($notificacoes)) {
        return false;
    }
    
    foreach ($notificacoes as &$n) {
        if ($n['id'] == $notificacaoId) {
            $n['lida'] = true;
            file_put_contents($arquivoNotificacoes, json_encode($notificacoes, JSON_PRETTY_PRINT));
            return true;
        }
    }
    
    return false;
}

function marcarTodasComoLidas($usuarioId) {
    $arquivoNotificacoes = __DIR__ . '/../data/notificacoes.json';
    
    if (!file_exists($arquivoNotificacoes)) {
        return false;
    }
    
    $notificacoes = json_decode(file_get_contents($arquivoNotificacoes), true);
    
    if (!is_array($notificacoes)) {
        return false;
    }
    
    foreach ($notificacoes as &$n) {
        if ($n['usuario_destino_id'] == $usuarioId) {
            $n['lida'] = true;
        }
    }
    
    file_put_contents($arquivoNotificacoes, json_encode($notificacoes, JSON_PRETTY_PRINT));
    return true;
}
?>