<?php
// Denuncia um comentário
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['success' => false, 'message' => 'Não autenticado']);
    exit;
}

$comentarioId = isset($_POST['comentario_id']) ? (int)$_POST['comentario_id'] : 0;
$motivo = isset($_POST['motivo']) ? trim($_POST['motivo']) : '';
$outro = isset($_POST['outro']) ? trim($_POST['outro']) : '';

if ($comentarioId == 0 || empty($motivo)) {
    echo json_encode(['success' => false, 'message' => 'Dados inválidos']);
    exit;
}

// Validar motivo
$motivosValidos = ['spam', 'discurso_odio', 'assedio', 'conteudo_inapropriado', 'informacao_falsa', 'outro'];
if (!in_array($motivo, $motivosValidos)) {
    echo json_encode(['success' => false, 'message' => 'Motivo inválido']);
    exit;
}

// Se for "outro", validar descrição
if ($motivo === 'outro' && (empty($outro) || strlen($outro) > 200)) {
    echo json_encode(['success' => false, 'message' => 'Descrição inválida (máx 200 caracteres)']);
    exit;
}

// Obter informações do comentário
$arquivoComentarios = __DIR__ . '/../../data/comentarios.json';
$comentarios = json_decode(file_get_contents($arquivoComentarios), true);

$comentario = null;
foreach ($comentarios as $c) {
    if ($c['comentario_id'] == $comentarioId) {
        $comentario = $c;
        break;
    }
}

if (!$comentario) {
    echo json_encode(['success' => false, 'message' => 'Comentário não encontrado']);
    exit;
}

// Criar denúncia
$arquivoDenuncias = __DIR__ . '/../../data/denuncias.json';
if (!file_exists($arquivoDenuncias)) {
    file_put_contents($arquivoDenuncias, '[]');
}

$denuncias = json_decode(file_get_contents($arquivoDenuncias), true);
if (!is_array($denuncias)) {
    $denuncias = [];
}

// Gerar ID
$novoId = 1;
foreach ($denuncias as $d) {
    if ($d['id'] >= $novoId) {
        $novoId = $d['id'] + 1;
    }
}

// Mapear motivos
$motivosTexto = [
    'spam' => 'Spam ou Publicidade',
    'discurso_odio' => 'Discurso de Ódio',
    'assedio' => 'Assédio ou Bullying',
    'conteudo_inapropriado' => 'Conteúdo Inapropriado',
    'informacao_falsa' => 'Informação Falsa',
    'outro' => 'Outro: ' . $outro
];

$novaDenuncia = [
    'id' => $novoId,
    'comentario_id' => $comentarioId,
    'artigo_id' => $comentario['artigo_id'],
    'comentario_texto' => $comentario['comentario'],
    'comentario_autor' => $comentario['nome'],
    'comentario_autor_id' => $comentario['usuario_id'],
    'denunciante_id' => $_SESSION['usuario_id'],
    'denunciante_nome' => $_SESSION['nome'],
    'motivo' => $motivo,
    'motivo_texto' => $motivosTexto[$motivo],
    'data' => time(),
    'status' => 'pendente',
    'expira_em' => time() + (7 * 24 * 60 * 60) // 7 dias
];

$denuncias[] = $novaDenuncia;
file_put_contents($arquivoDenuncias, json_encode($denuncias, JSON_PRETTY_PRINT));

echo json_encode([
    'success' => true,
    'message' => 'Denúncia enviada com sucesso',
    'timestamp' => time()
]);