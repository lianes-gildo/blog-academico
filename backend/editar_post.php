<?php
session_start();
require '../includes/header.php';

if (!isset($_SESSION['papel']) || ($_SESSION['papel'] !== 'admin' && $_SESSION['papel'] !== 'editor')) {
    header('Location: ../acesso_negado.php');
    exit;
}

$postId = (int)$_GET['id'];
$posts = json_decode(file_get_contents('../data/posts.json'), true);
$post = null;

foreach ($posts as $p) {
    if ($p['id'] == $postId) {
        $post = $p;
        break;
    }
}

if (!$post) {
    header('Location: ' . ($_SESSION['papel'] === 'admin' ? 'painelAdmin.php' : 'painelEditor.php'));
    exit;
}

// Verificar se o editor é o autor
if ($_SESSION['papel'] === 'editor' && $post['autor'] !== $_SESSION['nome']) {
    header('Location: ../acesso_negado.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $post['titulo'] = $_POST['titulo'];
    $post['descricao_curta'] = $_POST['descricao_curta'];
    $post['descricao_longa'] = $_POST['descricao_longa'];

    if (isset($_FILES['imagem']) && $_FILES['imagem']['error'] === 0) {
        if (file_exists('../' . $post['imagem']) && $post['imagem'] !== 'assets/img/posts/default.jpg') {
            unlink('../' . $post['imagem']);
        }
        
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
    
    $redirect = $_SESSION['papel'] === 'admin' ? 'painelAdmin.php' : 'painelEditor.php';
    header('Location: ' . $redirect);
    exit;
}
?>

<style>
    .form-header {
        background: linear-gradient(135deg, var(--primary-blue), var(--secondary-blue));
        color: white;
        padding: 40px 0;
        margin-bottom: 40px;
    }
    
    .current-image {
        max-width: 300px;
        border-radius: 15px;
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
        margin: 15px 0;
    }
    
    .image-label {
        font-weight: 600;
        color: var(--primary-blue);
        margin-top: 20px;
        margin-bottom: 10px;
        display: block;
    }
</style>

<div class="form-header">
    <div class="container">
        <h1 class="display-5 fw-bold">✏️ Editar Post</h1>
        <p class="mb-0 opacity-75">Atualize as informações do artigo</p>
    </div>
</div>

<main class="container pb-5">
    <div class="form-container">
        <form method="POST" enctype="multipart/form-data">
            <div class="mb-4">
                <label for="titulo" class="form-label">
                    <i class="bi bi-cursor-text"></i> Título do Artigo
                </label>
                <input type="text" class="form-control" id="titulo" name="titulo" 
                       value="<?php echo htmlspecialchars($post['titulo']); ?>" required>
            </div>
            
            <div class="mb-4">
                <label for="descricao_curta" class="form-label">
                    <i class="bi bi-text-paragraph"></i> Descrição Curta
                </label>
                <textarea class="form-control" id="descricao_curta" name="descricao_curta" rows="3" required><?php echo htmlspecialchars($post['descricao_curta']); ?></textarea>
            </div>
            
            <div class="mb-4">
                <label for="descricao_longa" class="form-label">
                    <i class="bi bi-file-text"></i> Conteúdo Completo
                </label>
                <textarea class="form-control" id="descricao_longa" name="descricao_longa" rows="12" required><?php echo htmlspecialchars($post['descricao_longa']); ?></textarea>
            </div>
            
            <div class="mb-4">
                <span class="image-label">📷 Imagem Atual:</span>
                <img src="<?php echo '../' . $post['imagem']; ?>" alt="Imagem atual" class="current-image d-block">
                
                <label class="form-label mt-4">
                    <i class="bi bi-image"></i> Nova Imagem (opcional)
                </label>
                <div class="image-upload-area" onclick="document.getElementById('imagem').click()">
                    <div class="upload-icon">📷</div>
                    <h5>Clique para selecionar uma nova imagem</h5>
                    <p class="text-muted mb-0">Deixe em branco para manter a imagem atual</p>
                    <input type="file" id="imagem" name="imagem" accept="image/*" style="display: none;">
                </div>
                <img id="preview" src="" alt="Preview da nova imagem">
            </div>
            
            <div class="row g-3">
                <div class="col-md-6">
                    <button type="submit" class="btn-submit">
                        <i class="bi bi-check-circle-fill"></i> Salvar Alterações
                    </button>
                </div>
                <div class="col-md-6">
                    <a href="<?php echo $_SESSION['papel'] === 'admin' ? 'painelAdmin.php' : 'painelEditor.php'; ?>" 
                       class="btn-cancel">
                        <i class="bi bi-x-circle"></i> Cancelar
                    </a>
                </div>
            </div>
        </form>
    </div>
</main>

<script>
const imageInput = document.getElementById('imagem');
const preview = document.getElementById('preview');
const uploadArea = document.querySelector('.image-upload-area');

imageInput.addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.src = e.target.result;
            preview.style.display = 'block';
        };
        reader.readAsDataURL(file);
    }
});

uploadArea.addEventListener('dragover', (e) => {
    e.preventDefault();
    uploadArea.classList.add('dragover');
});

uploadArea.addEventListener('dragleave', () => {
    uploadArea.classList.remove('dragover');
});

uploadArea.addEventListener('drop', (e) => {
    e.preventDefault();
    uploadArea.classList.remove('dragover');
    
    const files = e.dataTransfer.files;
    if (files.length > 0) {
        imageInput.files = files;
        imageInput.dispatchEvent(new Event('change'));
    }
});
</script>

<?php require '../includes/footer.php'; ?>