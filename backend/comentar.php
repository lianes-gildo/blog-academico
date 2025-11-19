<?php
session_start();
if(!isset($_SESSION['usuario'])) {
    echo json_encode(['erro'=>'Necessita estar logado']);
    exit;
}

$id = $_POST['id'];
$comentario = trim($_POST['comentario']);

if(empty($comentario)){
    echo json_encode(['erro'=>'Comentário vazio']);
    exit;
}

$comentarios = json_decode(file_get_contents('../data/comentarios.json'), true);

$novo = [
    'artigo_id' => $id,
    'nome' => $_SESSION['usuario']['nome'],
    'comentario' => $comentario,
    'data' => date("d-m-Y H:i")
];

$comentarios[] = $novo;
file_put_contents('../data/comentarios.json', json_encode($comentarios, JSON_PRETTY_PRINT));

echo json_encode(['sucesso'=>true,'comentario'=>$novo]);
