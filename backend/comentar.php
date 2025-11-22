<?php
session_start();

if (!isset($_SESSION['usuario_id'])) {
    echo "Faça login para comentar.";
    exit;
}

$postId = (int)$_POST['post_id'];
$comentario = trim($_POST['comentario']);
$nome = $_SESSION['nome'];

if (empty($comentario)) {
    echo "Comentário vazio.";
    exit;
}

$comentarios = json_decode(file_get_contents('../data/comentarios.json'), true) ?? [];

$novoComentario = [
    'artigo_id' => $postId,
    'nome' => $nome,
    'comentario' => $comentario,
    'data' => time()
];

$comentarios[] = $novoComentario;

file_put_contents('../data/comentarios.json', json_encode($comentarios, JSON_PRETTY_PRINT));

echo "Comentário enviado com sucesso!";