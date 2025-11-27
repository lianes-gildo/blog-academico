// ========================================
// ARQUIVO DE CONFIGURAÇÃO: config.php
// ========================================
<?php
// Configurações globais do sistema

// Intervalo de verificação real-time (em milissegundos)
define('REALTIME_CHECK_INTERVAL', 2000); // 2 segundos

// Intervalo de verificação de suspensão (em milissegundos)
define('SUSPENSION_CHECK_INTERVAL', 5000); // 5 segundos

// Intervalo de verificação de notificações (em milissegundos)
define('NOTIFICATION_CHECK_INTERVAL', 3000); // 3 segundos

// Intervalo de verificação de denúncias (em milissegundos)
define('DENUNCIAS_CHECK_INTERVAL', 5000); // 5 segundos

// Tempo de expiração de denúncias (em dias)
define('DENUNCIA_EXPIRATION_DAYS', 7);

// Email de contato
define('CONTACT_EMAIL', 'lndigitalcraft@gmail.com');

// Fuso horário
date_default_timezone_set('Africa/Maputo');

// Função auxiliar para debug (remover em produção)
function debug_log($message, $data = null) {
    $logFile = __DIR__ . '/../data/debug.log';
    $timestamp = date('Y-m-d H:i:s');
    $logMessage = "[$timestamp] $message";
    if ($data !== null) {
        $logMessage .= ' | Data: ' . json_encode($data);
    }
    file_put_contents($logFile, $logMessage . PHP_EOL, FILE_APPEND);
}

// ========================================
// LISTA COMPLETA DE APIs NECESSÁRIAS:
// ========================================
/*
backend/api/
├── check_comments_updates.php      ✓ Verifica novos comentários
├── get_all_comments.php            ✓ Retorna todos comentários
├── delete_comment_cascade.php      ✓ Apaga comentário em cascata
├── report_comment.php              ✓ Denunciar comentário
├── resolve_denuncia.php            ✓ Resolver denúncia
├── check_denuncias_updates.php     ✓ Verifica novas denúncias
├── suspend_user.php                ✓ Suspender usuário
├── check_suspension.php            ✓ Verifica suspensão
├── check_notifications_count.php   ✓ Conta notificações
└── mark_notification_read.php      ✓ Marca notificação lida

APIs ANTIGAS PARA REMOVER:
- check_comments.php (substituída por check_comments_updates.php)
- get_comments.php (substituída por get_all_comments.php)
- delete_comment.php (substituída por delete_comment_cascade.php)
- resolve_denuncia_realtime.php (substituída por resolve_denuncia.php)
- suspend_user_realtime.php (substituída por suspend_user.php)
- check_notifications.php (substituída por check_notifications_count.php)
- mark_notifications.php (substituída por mark_notification_read.php)

MANTER ESTAS APIs (já existem e funcionam):
- backend/curtir.php (curtir post)
- backend/comentar.php (adicionar comentário)
- backend/comentarios_interacao.php (like/dislike comentário)
- backend/responder_comentario.php (responder comentário)
*/
?>