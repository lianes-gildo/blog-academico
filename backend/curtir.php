<?php
// curtir.php
// Endpoint AJAX para curtir / descurtir um post.
// Recebe JSON POST: { "id": <postId> }
// Retorna JSON: { sucesso: bool, gostos: int, atedeu_like: bool, mensagem?: string }
//
// Regras:
// - Usuário precisa estar logado para curtir.
// - Cada usuário só pode curtir uma vez por post (armazenado em data/estatisticas.json -> gostos).
// - Clique alterna entre like/unlike.

session_start();
header('Content-Type: application/json');
date_default_timezone_set('Africa/Maputo');

$input = json_decode(file_get_contents('php://input'), true);
$postId = isset($input['id']) ? (int)$input['id'] : 0;

if (!$postId) {
    echo json_encode(['sucesso'=>false,'mensagem'=>'Post inválido.']);
    exit;
}
if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['sucesso'=>false,'mensagem'=>'Você precisa estar logado para curtir.']);
    exit;
}

$usuarioId = $_SESSION['usuario_id'];
$arquivoPosts = __DIR__ . '/../data/posts.json';
$arquivoEstat = __DIR__ . '/../data/estatisticas.json';

$posts = json_decode(file_get_contents($arquivoPosts), true) ?: [];
$estat = json_decode(file_get_contents($arquivoEstat), true) ?: ['gostos'=>[],'visitas'=>[],'compartilhamentos'=>[]];

// localizar post e índice
$indice = null;
foreach ($posts as $k => $p) {
    if ($p['id'] == $postId) { $indice = $k; break; }
}
if ($indice === null) {
    echo json_encode(['sucesso'=>false,'mensagem'=>'Post não encontrado.']);
    exit;
}

// inicializar array de gostos para esse post
if (!isset($estat['gostos'][$postId])) $estat['gostos'][$postId] = [];

// checar se já deu like
$jaDeu = in_array($usuarioId, $estat['gostos'][$postId]);

if ($jaDeu) {
    // remover like (unlike)
    $estat['gostos'][$postId] = array_values(array_filter($estat['gostos'][$postId], function($id){ global $usuarioId; return $id != $usuarioId; }));
    // decrementar contador no post (proteção para não negativo)
    $posts[$indice]['gostos'] = max(0, (int)$posts[$indice]['gostos'] - 1);
    $atedeu_like = false;
} else {
    // adicionar like
    $estat['gostos'][$postId][] = $usuarioId;
    $posts[$indice]['gostos'] = (int)$posts[$indice]['gostos'] + 1;
    $atedeu_like = true;
}

// salvar arquivos
file_put_contents($arquivoPosts, json_encode($posts, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
file_put_contents($arquivoEstat, json_encode($estat, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

// responder com novo número de gostos
echo json_encode(['sucesso'=>true,'gostos'=>(int)$posts[$indice]['gostos'],'atedeu_like'=>$atedeu_like]);
exit;
