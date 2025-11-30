<?php
/**
 * API para apagar notificações
 * Permite apagar uma ou múltiplas notificações
 */
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['success' => false, 'message' => 'Não autenticado']);
    exit;
}

$usuarioId = $_SESSION['usuario_id'];
$notifIds = isset($_POST['notif_ids']) ? json_decode($_POST['notif_ids'], true) : [];

if (empty($notifIds) || !is_array($notifIds)) {
    echo json_encode(['success' => false, 'message' => 'IDs inválidos']);
    exit;
}

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

// Filtrar apenas notificações que pertencem ao usuário e estão na lista de IDs
$notificacoesRestantes = array_filter($notificacoes, function($n) use ($notifIds, $usuarioId) {
    // Se a notificação está na lista de IDs para apagar E pertence ao usuário, remover
    if (in_array($n['id'], $notifIds) && $n['usuario_destino_id'] == $usuarioId) {
        return false; // Remove
    }
    return true; // Mantém
});

// Reindexar array
$notificacoesRestantes = array_values($notificacoesRestantes);

// Salvar
if (file_put_contents($arquivoNotif, json_encode($notificacoesRestantes, JSON_PRETTY_PRINT))) {
    echo json_encode([
        'success' => true,
        'message' => count($notifIds) === 1 ? 'Notificação apagada' : count($notifIds) . ' notificações apagadas',
        'deleted_count' => count($notifIds),
        'timestamp' => time()
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Erro ao salvar']);
}
?>