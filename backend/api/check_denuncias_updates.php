<?php
// Verifica se há novas denúncias (para admin)
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['papel']) || $_SESSION['papel'] !== 'admin') {
    echo json_encode(['hasNew' => false]);
    exit;
}

$lastCheck = isset($_GET['last_check']) ? (int)$_GET['last_check'] : 0;

$arquivoDenuncias = __DIR__ . '/../../data/denuncias.json';
if (!file_exists($arquivoDenuncias)) {
    echo json_encode(['hasNew' => false, 'timestamp' => time()]);
    exit;
}

$denuncias = json_decode(file_get_contents($arquivoDenuncias), true);
if (!is_array($denuncias)) {
    echo json_encode(['hasNew' => false, 'timestamp' => time()]);
    exit;
}

// Verificar se há denúncias novas
$hasNew = false;
foreach ($denuncias as $d) {
    if ($d['data'] > $lastCheck && $d['status'] === 'pendente') {
        $hasNew = true;
        break;
    }
}

echo json_encode([
    'hasNew' => $hasNew,
    'timestamp' => time()
]);