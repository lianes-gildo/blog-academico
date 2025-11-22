<?php
session_start();
require '../includes/header.php';

if ($_SESSION['papel'] !== 'admin') {
    header('Location: ../index.php');
    exit;
}

$postId = (int)$_GET['id'];
$posts = json_decode(file_get_contents('../data/posts.json'), true);
$post = null;
foreach ($posts as $p) if ($p['id'] == $postId) { $post = $p; break; }

if (!$post) exit('Post não encontrado');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $post['titulo'] = $_POST['titulo'];
    $post['descricao_curta'] = $_POST['descricao_curta'];
    $post['descricao_longa'] = $_POST['descricao_longa'];

    if (isset($_FILES['imagem']) && $_FILES['imagem']['error'] === 0) {
        if (file_exists('../' . $post['imagem'])) unlink('../' . $post['imagem']);
        
        $ext = pathinfo($_FILES['imagem']['name'], PATHINFO_EXTENSION);
        $nomeImagem = uniqid() . '.' . $ext;
        move_uploaded_file($_FILES['imagem']['tmp_name'], '../assets/img/posts/' . $nomeImagem);
        $post['imagem'] = 'assets/img/posts/' . $nomeImagem;
    }

    foreach ($posts as &$p) {
        if ($p['id'] == $postId) {
            $p = $post;
            break;
        }
    }
    file_put_contents('../data/posts.json', json_encode($posts, JSON_PRETTY_PRINT));
    header('Location: painelAdmin.php');
    exit;
}
?>

<main class="container formulario-central">
    <h2>Editar Post</h2>
    <form method="POST" enctype="multipart/form-data">
        <label for="titulo">Título</label>
        <input type="text" id="titulo" name="titulo" value="<?php echo htmlspecialchars($post['titulo']); ?>" required>

        <label for="descricao_curta">Descrição curta</label>
        <textarea id="descricao_curta" name="descricao_curta" required><?php echo htmlspecialchars($post['descricao_curta']); ?></textarea>

        <label for="descricao_longa">Descrição longa</label>
        <textarea id="descricao_longa" name="descricao_longa" required><?php echo htmlspecialchars($post['descricao_longa']); ?></textarea>

        <label>Imagem atual:</label>
        <img src="<?php echo $post['imagem']; ?>" alt="Imagem atual" width="200">

        <label for="imagem">Nova imagem (opcional):</label>
        <input type="file" id="imagem" name="imagem" accept="image/*">
        <img id="preview" src="" alt="Preview da nova imagem">

        <button type="submit">Salvar</button>
    </form>

    <script>
    document.querySelector('input[type=file]').addEventListener('change', function(e) {
        const preview = document.getElementById('preview');
        if (e.target.files[0]) {
            preview.src = URL.createObjectURL(e.target.files[0]);
            preview.style.display = 'block';
        }
    });
    </script>
</main>

<?php require '../includes/footer.php'; ?>