<?php
session_start();
require '../includes/header.php';

if (!isset($_SESSION['papel']) || $_SESSION['papel'] !== 'admin') {
    header('Location: ../index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titulo = $_POST['titulo'];
    $descricao_curta = $_POST['descricao_curta'];
    $descricao_longa = $_POST['descricao_longa'];

    // Upload imagem
    if (isset($_FILES['imagem']) && $_FILES['imagem']['error'] === 0) {
        $ext = pathinfo($_FILES['imagem']['name'], PATHINFO_EXTENSION);
        $nomeImagem = uniqid() . '.' . $ext;
        move_uploaded_file($_FILES['imagem']['tmp_name'], '../assets/img/posts/' . $nomeImagem);
        $caminhoImagem = 'assets/img/posts/' . $nomeImagem;
    } else {
        $caminhoImagem = 'assets/img/posts/default.jpg';
    }

    $posts = json_decode(file_get_contents('../data/posts.json'), true);
    $novoId = empty($posts) ? 1 : max(array_column($posts, 'id')) + 1;

    $novoPost = [
        'id' => $novoId,
        'titulo' => $titulo,
        'autor' => $_SESSION['nome'],
        'data' => date('Y-m-d H:i:s'),
        'imagem' => $caminhoImagem,
        'descricao_curta' => $descricao_curta,
        'descricao_longa' => $descricao_longa,
        'gostos' => 0
    ];

    $posts[] = $novoPost;
    file_put_contents('../data/posts.json', json_encode($posts, JSON_PRETTY_PRINT));

    $estatisticas = json_decode(file_get_contents('../data/estatisticas.json'), true);
    $estatisticas['visitas'][$novoId] = 0;
    $estatisticas['gostos'][$novoId] = [];
    file_put_contents('../data/estatisticas.json', json_encode($estatisticas, JSON_PRETTY_PRINT));

    header('Location: painelAdmin.php');
    exit;
}
?>

<main class="container formulario-central">
    <h2>Adicionar Post</h2>
    <form method="POST" enctype="multipart/form-data">
        <label for="titulo">Título</label>
        <input type="text" id="titulo" name="titulo" placeholder="Título" required>

        <label for="descricao_curta">Descrição curta</label>
        <textarea id="descricao_curta" name="descricao_curta" placeholder="Descrição curta" required></textarea>

        <label for="descricao_longa">Descrição longa</label>
        <textarea id="descricao_longa" name="descricao_longa" placeholder="Descrição longa" required></textarea>

        <label for="imagem">Imagem do post</label>
        <input type="file" id="imagem" name="imagem" accept="image/*" required>
        <img id="preview" src="" alt="Preview da Imagem">

        <button type="submit">Publicar</button>
    </form>

    <script>
    document.getElementById('imagem').addEventListener('change', function(e) {
        const preview = document.getElementById('preview');
        preview.src = URL.createObjectURL(e.target.files[0]);
        preview.style.display = 'block';
    });
    </script>
</main>

<?php require '../includes/footer.php'; ?>