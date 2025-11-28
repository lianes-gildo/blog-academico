<?php
// Resolve uma denúncia (apaga ou arquiva)
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
            // Função para coletar IDs em cascata
            function coletarIdsCascata($comentarioId, $comentarios) {
                $ids = [$comentarioId];
                $encontrou = true;
                
                while ($encontrou) {
                    $encontrou = false;
                    foreach ($comentarios as $c) {
                        if (isset($c['pai_id']) && in_array($c['pai_id'], $ids) && !in_array($c['comentario_id'], $ids)) {
                            $ids[] = $c['comentario_id'];
                            $encontrou = true;
                        }
                    }
                }
                
                return $ids;
            }
            
            $idsParaApagar = coletarIdsCascata($comentarioId, $comentarios);
            
            $comentarios = array_filter($comentarios, function($c) use ($idsParaApagar) {
                return !in_array($c['comentario_id'], $idsParaApagar);
            });
            file_put_contents($arquivoComentarios, json_encode(array_values($comentarios), JSON_PRETTY_PRINT));
        }
    }
}

// Marcar como resolvida
$denuncias[$denunciaIndex]['status'] = 'resolvida';
$denuncias[$denunciaIndex]['resolvida_em'] = time();
$denuncias[$denunciaIndex]['resolvida_por'] = $_SESSION['nome'];
$denuncias[$denunciaIndex]['acao_tomada'] = $deleteComment ? 'Comentário removido' : 'Denúncia arquivada';

file_put_contents($arquivoDenuncias, json_encode($denuncias, JSON_PRETTY_PRINT));

// Criar notificação para denunciante
require_once __DIR__ . '/../criar_notificacao.php';

// Obter título do post
$posts = json_decode(file_get_contents(__DIR__ . '/../../data/posts.json'), true);
$postTitulo = 'Post desconhecido';
foreach ($posts as $p) {
    if ($p['id'] == $denuncia['artigo_id']) {
        $postTitulo = $p['titulo'];
        break;
    }
}

criarNotificacao(
    'denuncia_resolvida',
    $denuncia['denunciante_id'],
    $_SESSION['nome'],
    $comentarioId,
    $denuncia['artigo_id'],
    $postTitulo
);

$acao = $deleteComment ? 'Comentário apagado e denúncia resolvida' : 'Denúncia marcada como resolvida';

echo json_encode([
    'success' => true,
    'message' => $acao,
    'timestamp' => time()
]);