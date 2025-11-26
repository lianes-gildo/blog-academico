<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['count' => 0]);
    exit;
}

$arquivoNotif = __DIR__ . '/../../data/notificacoes.json';

if (!file_exists($arquivoNotif)) {
    echo json_encode(['count' => 0]);
    exit;
}

$notificacoes = json_decode(file_get_contents($arquivoNotif), true);
$count = 0;

if (is_array($notificacoes)) {
    foreach ($notificacoes as $n) {
        if ($n['usuario_destino_id'] == $_SESSION['usuario_id'] && 
            (!isset($n['lida']) || $n['lida'] === false)) {
            $count++;
        }
    }
}

echo json_encode(['count' => $count]);
?>