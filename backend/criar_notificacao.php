<?php
/**
 * Sistema de Gerenciamento de Notificações
 * Funções para criar, ler e marcar notificações
 */

/**
 * Criar uma nova notificação
 */
function criarNotificacao($tipo, $usuarioDestinoId, $usuarioOrigemNome, $comentarioId, $postId, $postTitulo) {
    $arquivoNotif = __DIR__ . '/../data/notificacoes.json';
    
    // Criar arquivo se não existir
    if (!file_exists($arquivoNotif)) {
        file_put_contents($arquivoNotif, '[]');
    }
    
    $notificacoes = json_decode(file_get_contents($arquivoNotif), true);
    
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
    
    // Verificar se já existe notificação similar recente (últimas 24h)
    $agora = time();
    $umDiaAtras = $agora - 86400;
    
    foreach ($notificacoes as $n) {
        if ($n['tipo'] === $tipo && 
            $n['usuario_destino_id'] === $usuarioDestinoId &&
            $n['comentario_id'] === $comentarioId &&
            $n['data'] > $umDiaAtras) {
            // Notificação similar já existe, não criar duplicata
            return false;
        }
    }
    
    // Criar nova notificação
    $novaNotificacao = [
        'id' => $novoId,
        'tipo' => $tipo, // 'mention', 'like', 'dislike', 'reply'
        'usuario_destino_id' => $usuarioDestinoId,
        'usuario_origem_nome' => $usuarioOrigemNome,
        'comentario_id' => $comentarioId,
        'post_id' => $postId,
        'post_titulo' => $postTitulo,
        'data' => $agora,
        'lida' => false
    ];
    
    $notificacoes[] = $novaNotificacao;
    
    // Salvar
    file_put_contents($arquivoNotif, json_encode($notificacoes, JSON_PRETTY_PRINT));
    
    return true;
}

/**
 * Obter notificações de um usuário
 */
function obterNotificacoesUsuario($usuarioId, $apenasNaoLidas = false) {
    $arquivoNotif = __DIR__ . '/../data/notificacoes.json';
    
    if (!file_exists($arquivoNotif)) {
        return [];
    }
    
    $notificacoes = json_decode(file_get_contents($arquivoNotif), true);
    
    if (!is_array($notificacoes)) {
        return [];
    }
    
    // Filtrar por usuário
    $notificacoesUsuario = array_filter($notificacoes, function($n) use ($usuarioId, $apenasNaoLidas) {
        $pertenceUsuario = isset($n['usuario_destino_id']) && $n['usuario_destino_id'] == $usuarioId;
        
        if ($apenasNaoLidas) {
            return $pertenceUsuario && (!isset($n['lida']) || $n['lida'] === false);
        }
        
        return $pertenceUsuario;
    });
    
    // Ordenar por data (mais recente primeiro)
    usort($notificacoesUsuario, function($a, $b) {
        return $b['data'] - $a['data'];
    });
    
    return array_values($notificacoesUsuario);
}

/**
 * Contar notificações não lidas
 */
function contarNotificacoesNaoLidas($usuarioId) {
    $naoLidas = obterNotificacoesUsuario($usuarioId, true);
    return count($naoLidas);
}

/**
 * Marcar uma notificação como lida
 */
function marcarNotificacaoComoLida($notifId) {
    $arquivoNotif = __DIR__ . '/../data/notificacoes.json';
    
    if (!file_exists($arquivoNotif)) {
        return false;
    }
    
    $notificacoes = json_decode(file_get_contents($arquivoNotif), true);
    
    if (!is_array($notificacoes)) {
        return false;
    }
    
    // Encontrar e marcar como lida
    $encontrada = false;
    foreach ($notificacoes as &$n) {
        if (isset($n['id']) && $n['id'] == $notifId) {
            $n['lida'] = true;
            $encontrada = true;
            break;
        }
    }
    
    if ($encontrada) {
        file_put_contents($arquivoNotif, json_encode($notificacoes, JSON_PRETTY_PRINT));
        return true;
    }
    
    return false;
}

/**
 * Marcar todas as notificações de um usuário como lidas
 */
function marcarTodasComoLidas($usuarioId) {
    $arquivoNotif = __DIR__ . '/../data/notificacoes.json';
    
    if (!file_exists($arquivoNotif)) {
        return false;
    }
    
    $notificacoes = json_decode(file_get_contents($arquivoNotif), true);
    
    if (!is_array($notificacoes)) {
        return false;
    }
    
    // Marcar todas as notificações do usuário como lidas
    $modificado = false;
    foreach ($notificacoes as &$n) {
        if (isset($n['usuario_destino_id']) && $n['usuario_destino_id'] == $usuarioId) {
            if (!isset($n['lida']) || $n['lida'] === false) {
                $n['lida'] = true;
                $modificado = true;
            }
        }
    }
    
    if ($modificado) {
        file_put_contents($arquivoNotif, json_encode($notificacoes, JSON_PRETTY_PRINT));
        return true;
    }
    
    return false;
}

/**
 * Apagar notificação
 */
function apagarNotificacao($notifId) {
    $arquivoNotif = __DIR__ . '/../data/notificacoes.json';
    
    if (!file_exists($arquivoNotif)) {
        return false;
    }
    
    $notificacoes = json_decode(file_get_contents($arquivoNotif), true);
    
    if (!is_array($notificacoes)) {
        return false;
    }
    
    // Filtrar removendo a notificação
    $novasNotificacoes = array_filter($notificacoes, function($n) use ($notifId) {
        return !isset($n['id']) || $n['id'] != $notifId;
    });
    
    // Reindexar array
    $novasNotificacoes = array_values($novasNotificacoes);
    
    file_put_contents($arquivoNotif, json_encode($novasNotificacoes, JSON_PRETTY_PRINT));
    
    return true;
}

/**
 * Apagar notificações antigas (mais de 30 dias)
 */
function limparNotificacoesAntigas() {
    $arquivoNotif = __DIR__ . '/../data/notificacoes.json';
    
    if (!file_exists($arquivoNotif)) {
        return false;
    }
    
    $notificacoes = json_decode(file_get_contents($arquivoNotif), true);
    
    if (!is_array($notificacoes)) {
        return false;
    }
    
    $trintaDiasAtras = time() - (30 * 86400);
    
    // Manter apenas notificações dos últimos 30 dias
    $notificacoesRecentes = array_filter($notificacoes, function($n) use ($trintaDiasAtras) {
        return isset($n['data']) && $n['data'] > $trintaDiasAtras;
    });
    
    // Reindexar
    $notificacoesRecentes = array_values($notificacoesRecentes);
    
    file_put_contents($arquivoNotif, json_encode($notificacoesRecentes, JSON_PRETTY_PRINT));
    
    return true;
}
?>