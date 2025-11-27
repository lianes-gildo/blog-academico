// ========================================
// 10. backend/api/mark_notification_read.php
// Marca notificação como lida
// ========================================
<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['success' => false, 'message' => 'Não autenticado']);
    exit;
}

$action = $_POST['action'] ?? '';
$notifId = isset($_POST['notif_id']) ? (int)$_POST['notif_id'] : 0;
$usuarioId = $_SESSION['usuario_id'];

$arquivoNotif = __DIR__ . '/../../data/notificacoes.json';
if (!file_exists($arquivoNotif)) {
    echo json_encode(['success' => false, 'message' => 'Arquivo não encontrado']);
    exit;
}

$notificacoes = json_decode(file_get_contents($arquivoNotif), true);
if (!is_array($notificacoes)) {
    echo json_encode(['success' => false, 'message' => 'Erro ao ler notificações']);
    exit;
}

if ($action === 'mark_all') {
    foreach ($notificacoes as &$n) {
        if ($n['usuario_destino_id'] == $usuarioId) {
            $n['lida'] = true;
        }
    }
    file_put_contents($arquivoNotif, json_encode($notificacoes, JSON_PRETTY_PRINT));
    echo json_encode(['success' => true, 'message' => 'Todas marcadas como lidas']);
    
} elseif ($action === 'mark_one' && $notifId > 0) {
    foreach ($notificacoes as &$n) {
        if ($n['id'] == $notifId && $n['usuario_destino_id'] == $usuarioId) {
            $n['lida'] = true;
            break;
        }
    }
    file_put_contents($arquivoNotif, json_encode($notificacoes, JSON_PRETTY_PRINT));
    echo json_encode(['success' => true, 'message' => 'Notificação marcada como lida']);
    
} else {
    echo json_encode(['success' => false, 'message' => 'Ação inválida']);
}