<?php
/**
 * API para verificar novas notificações (Real-Time)
 * Retorna notificações mais recentes que o último timestamp
 */
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['hasNew' => false, 'timestamp' => time()]);
    exit;
}

$usuarioId = $_SESSION['usuario_id'];
$lastCheck = isset($_GET['last_check']) ? (int)$_GET['last_check'] : 0;

$arquivoNotif = __DIR__ . '/../../data/notificacoes.json';
if (!file_exists($arquivoNotif)) {
    echo json_encode(['hasNew' => false, 'timestamp' => time()]);
    exit;
}

$notificacoes = json_decode(file_get_contents($arquivoNotif), true);
if (!is_array($notificacoes)) {
    echo json_encode(['hasNew' => false, 'timestamp' => time()]);
    exit;
}

// Verificar se há notificações novas desde a última verificação
$hasNew = false;
$novasNotificacoes = [];

foreach ($notificacoes as $n) {
    if ($n['usuario_destino_id'] == $usuarioId && $n['data'] > $lastCheck) {
        $hasNew = true;
        $novasNotificacoes[] = $n;
    }
}

echo json_encode([
    'hasNew' => $hasNew,
    'newNotifications' => $novasNotificacoes,
    'count' => count($novasNotificacoes),
    'timestamp' => time()
]);
?>