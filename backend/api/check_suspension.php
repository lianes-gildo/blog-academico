<?php
// Verifica suspensão E FORÇA LOGOUT
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['suspended' => false]);
    exit;
}

$usuarioId = $_SESSION['usuario_id'];

$arquivoUsuarios = __DIR__ . '/../../data/usuarios.json';
if (!file_exists($arquivoUsuarios)) {
    echo json_encode(['suspended' => false]);
    exit;
}

$usuarios = json_decode(file_get_contents($arquivoUsuarios), true);
if (!is_array($usuarios)) {
    echo json_encode(['suspended' => false]);
    exit;
}

$suspenso = false;
$suspensaoAte = null;
$adminNome = 'Administrador';

foreach ($usuarios as $user) {
    if ($user['id'] == $usuarioId) {
        if (isset($user['suspenso_ate']) && strtotime($user['suspenso_ate']) > time()) {
            $suspenso = true;
            $suspensaoAte = $user['suspenso_ate'];
        }
        break;
    }
}

if ($suspenso) {
    // Buscar quem suspendeu
    $arquivoSuspensoes = __DIR__ . '/../../data/suspensoes_ativas.json';
    if (file_exists($arquivoSuspensoes)) {
        $suspensoes = json_decode(file_get_contents($arquivoSuspensoes), true);
        if (is_array($suspensoes)) {
            foreach ($suspensoes as $s) {
                if ($s['usuario_id'] == $usuarioId) {
                    $adminNome = $s['suspenso_por'];
                    break;
                }
            }
        }
    }
    
    // Destruir sessão IMEDIATAMENTE
    $_SESSION = [];
    session_destroy();
    
    echo json_encode([
        'suspended' => true,
        'until' => $suspensaoAte,
        'admin_nome' => $adminNome,
        'message' => "A tua conta foi suspensa pelo Admin $adminNome até " . date('d/m/Y H:i', strtotime($suspensaoAte)) . ". Para mais informações contacte lndigitalcraft@gmail.com",
        'force_logout' => true
    ]);
} else {
    echo json_encode(['suspended' => false]);
}