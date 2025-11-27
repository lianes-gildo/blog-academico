// ========================================
// 5. backend/api/resolve_denuncia.php
// Resolve uma denúncia (apaga ou arquiva)
// ========================================
<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['papel']) || $_SESSION['papel'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Acesso negado']);
    exit;
}

$denunciaId = isset($_POST['denuncia_id']) ? (int)$_POST['denuncia_id'] : 0;
$comentarioId = isset($_POST['comentario_id']) ? (int)$_POST['comentario_id'] : 0;
$deleteComment = isset($_POST['delete']) && $_POST['delete'] == '1';

if ($denunciaId == 0) {
    echo json_encode(['success' => false, 'message' => 'ID inválido']);
    exit;
}

$arquivoDenuncias = __DIR__ . '/../../data/denuncias.json';
if (!file_exists($arquivoDenuncias)) {
    echo json_encode(['success' => false, 'message' => 'Arquivo não encontrado']);
    exit;
}

$denuncias = json_decode(file_get_contents($arquivoDenuncias), true);
if (!is_array($denuncias)) {
    echo json_encode(['success' => false, 'message' => 'Erro ao ler denúncias']);
    exit;
}

// Encontrar denúncia
$denunciaIndex = -1;
$denuncia = null;
foreach ($denuncias as $i => $d) {
    if ($d['id'] == $denunciaId) {
        $denunciaIndex = $i;
        $denuncia = $d;
        break;
    }
}

if (!$denuncia) {
    echo json_encode(['success' => false, 'message' => 'Denúncia não encontrada']);
    exit;
}

// Se for para apagar o comentário
if ($deleteComment) {
    $arquivoComentarios = __DIR__ . '/../../data/comentarios.json';
    if (file_exists($arquivoComentarios)) {
        $comentarios = json_decode(file_get_contents($arquivoComentarios), true);
        if (is_array($comentarios)) {
            $comentarios = array_filter($comentarios, function($c) use ($comentarioId) {
                return $c['comentario_id'] != $comentarioId;
            });
            file_put_contents($arquivoComentarios, json_encode(array_values($comentarios), JSON_PRETTY_PRINT));
        }
    }
}

// Marcar como resolvida e mover para o final
$denuncias[$denunciaIndex]['status'] = 'resolvida';
$denuncias[$denunciaIndex]['resolvida_em'] = time();
$denuncias[$denunciaIndex]['resolvida_por'] = $_SESSION['nome'];
$denuncias[$denunciaIndex]['acao_tomada'] = $deleteComment ? 'Comentário removido' : 'Denúncia arquivada';

// Mover para final
$denunciaResolvida = $denuncias[$denunciaIndex];
unset($denuncias[$denunciaIndex]);
$denuncias = array_values($denuncias);
$denuncias[] = $denunciaResolvida;

file_put_contents($arquivoDenuncias, json_encode($denuncias, JSON_PRETTY_PRINT));

// Criar notificação para denunciante
$arquivoNotif = __DIR__ . '/../../data/notificacoes.json';
if (!file_exists($arquivoNotif)) {
    file_put_contents($arquivoNotif, '[]');
}

$notificacoes = json_decode(file_get_contents($arquivoNotif), true);
if (!is_array($notificacoes)) {
    $notificacoes = [];
}

$novoId = 1;
foreach ($notificacoes as $n) {
    if ($n['id'] >= $novoId) {
        $novoId = $n['id'] + 1;
    }
}

// Obter título do post
$posts = json_decode(file_get_contents(__DIR__ . '/../../data/posts.json'), true);
$postTitulo = 'Post desconhecido';
foreach ($posts as $p) {
    if ($p['id'] == $denuncia['artigo_id']) {
        $postTitulo = $p['titulo'];
        break;
    }
}

$notificacoes[] = [
    'id' => $novoId,
    'tipo' => 'denuncia_resolvida',
    'usuario_destino_id' => $denuncia['denunciante_id'],
    'usuario_origem_nome' => $_SESSION['nome'],
    'comentario_id' => $comentarioId,
    'post_id' => $denuncia['artigo_id'],
    'post_titulo' => $postTitulo,
    'data' => time(),
    'lida' => false,
    'acao_tomada' => $deleteComment ? 'Comentário removido' : 'Denúncia arquivada',
    'denuncia_motivo' => $denuncia['motivo_texto'],
    'comentario_autor' => $denuncia['comentario_autor'],
    'data_denuncia' => $denuncia['data']
];

file_put_contents($arquivoNotif, json_encode($notificacoes, JSON_PRETTY_PRINT));

$acao = $deleteComment ? 'Comentário apagado e denúncia resolvida' : 'Denúncia marcada como resolvida';

echo json_encode([
    'success' => true,
    'message' => $acao,
    'timestamp' => time()
]);
