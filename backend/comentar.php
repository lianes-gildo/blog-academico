<?php
// comentar.php
// Endpoint AJAX para adicionar comentário.
// Recebe JSON POST { id: <postId>, comentario: "<texto>" }
// Regras:
// - Usuário precisa estar logado.
// - Ao salvar, retorna dados do comentário para inserção imediata na UI.
// - Comentários salvos em data/comentarios.json

session_start();
header('Content-Type: application/json');
date_default_timezone_set('Africa/Maputo');

$input = json_decode(file_get_contents('php://input'), true) ?: [];
$postId = isset($input['id']) ? (int)$input['id'] : 0;
$texto = trim($input['comentario'] ?? '');

if (!$postId || !$texto) {
    echo json_encode(['sucesso'=>false,'mensagem'=>'Dados inválidos.']);
    exit;
}
if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['sucesso'=>false,'mensagem'=>'Você precisa estar logado para comentar.']);
    exit;
}

$usuarioId = $_SESSION['usuario_id'];
$arquivoUsuarios = __DIR__ . '/../data/usuarios.json';
$usuarios = json_decode(file_get_contents($arquivoUsuarios), true) ?: [];
$usuario = null;
foreach ($usuarios as $u) { if ($u['id'] == $usuarioId) { $usuario = $u; break; } }
if (!$usuario) {
    echo json_encode(['sucesso'=>false,'mensagem'=>'Usuário não encontrado.']);
    exit;
}

// montar comentário
$comentarioObj = [
    'artigo_id' => $postId,
    'nome' => $usuario['nome'],
    'comentario' => $texto,
    'data' => date('Y-m-d H:i:s')
];

// salvar no arquivo
$arquivoComentarios = __DIR__ . '/../data/comentarios.json';
$comentarios = json_decode(file_get_contents($arquivoComentarios), true) ?: [];
$comentarios[] = $comentarioObj;
file_put_contents($arquivoComentarios, json_encode($comentarios, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

// responder com sucesso + dados para exibir na UI
echo json_encode([
    'sucesso' => true,
    'nome' => $comentarioObj['nome'],
    'comentario' => htmlspecialchars($comentarioObj['comentario']),
    'data' => $comentarioObj['data']
]);
exit;
