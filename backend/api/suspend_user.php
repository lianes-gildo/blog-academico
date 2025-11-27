// ========================================
// 7. backend/api/suspend_user.php
// Suspende um usuário
// ========================================
<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['papel']) || $_SESSION['papel'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Acesso negado']);
    exit;
}

$userId = isset($_POST['user_id']) ? (int)$_POST['user_id'] : 0;
$duracao = isset($_POST['duracao']) ? $_POST['duracao'] : '';

if ($userId == 0 || empty($duracao)) {
    echo json_encode(['success' => false, 'message' => 'Dados inválidos']);
    exit;
}

$arquivoUsuarios = __DIR__ . '/../../data/usuarios.json';
if (!file_exists($arquivoUsuarios)) {
    echo json_encode(['success' => false, 'message' => 'Arquivo não encontrado']);
    exit;
}

$usuarios = json_decode(file_get_contents($arquivoUsuarios), true);
if (!is_array($usuarios)) {
    echo json_encode(['success' => false, 'message' => 'Erro ao ler usuários']);
    exit;
}

$usuarioEncontrado = false;
$nomeUsuario = '';
$dataExpiracao = date('Y-m-d H:i:s', strtotime("+$duracao"));

foreach ($usuarios as &$user) {
    if ($user['id'] === $userId) {
        $user['suspenso_ate'] = $dataExpiracao;
        $nomeUsuario = $user['nome'];
        $usuarioEncontrado = true;
        break;
    }
}

if (!$usuarioEncontrado) {
    echo json_encode(['success' => false, 'message' => 'Usuário não encontrado']);
    exit;
}

file_put_contents($arquivoUsuarios, json_encode($usuarios, JSON_PRETTY_PRINT));

// Criar arquivo de suspensões ativas
$arquivoSuspensoes = __DIR__ . '/../../data/suspensoes_ativas.json';
if (!file_exists($arquivoSuspensoes)) {
    file_put_contents($arquivoSuspensoes, '[]');
}

$suspensoes = json_decode(file_get_contents($arquivoSuspensoes), true);
if (!is_array($suspensoes)) {
    $suspensoes = [];
}

$suspensoes[] = [
    'usuario_id' => $userId,
    'suspenso_em' => time(),
    'suspenso_ate' => $dataExpiracao,
    'duracao' => $duracao,
    'suspenso_por' => $_SESSION['nome']
];

file_put_contents($arquivoSuspensoes, json_encode($suspensoes, JSON_PRETTY_PRINT));

echo json_encode([
    'success' => true,
    'message' => "Usuário $nomeUsuario suspenso até " . date('d/m/Y H:i', strtotime($dataExpiracao)),
    'usuario_id' => $userId,
    'suspenso_ate' => $dataExpiracao,
    'timestamp' => time()
]);