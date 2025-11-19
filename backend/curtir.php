<?php
session_start();
if(!isset($_SESSION['usuario'])) {
    echo json_encode(['erro'=>'Necessita estar logado']);
    exit;
}

$id = $_POST['id'];
$estatisticas = json_decode(file_get_contents('../data/estatisticas.json'), true);
$posts = json_decode(file_get_contents('../data/posts.json'), true);

$usuarioId = $_SESSION['usuario']['id'];

// Inicializar array
if(!isset($estatisticas['gostos'][$id])) $estatisticas['gostos'][$id] = [];

// Evitar duplicados
if(!in_array($usuarioId, $estatisticas['gostos'][$id])){
    $estatisticas['gostos'][$id][] = $usuarioId;

    // Atualizar contador no posts.json
    foreach($posts as &$post){
        if($post['id'] == $id){
            $post['gostos'] = count($estatisticas['gostos'][$id]);
            break;
        }
    }
    file_put_contents('../data/posts.json', json_encode($posts, JSON_PRETTY_PRINT));
    file_put_contents('../data/estatisticas.json', json_encode($estatisticas, JSON_PRETTY_PRINT));
}

echo json_encode(['gostos'=>count($estatisticas['gostos'][$id])]);
