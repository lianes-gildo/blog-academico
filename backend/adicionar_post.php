<?php
session_start();
require '../includes/header.php';

if (!isset($_SESSION['papel']) || ($_SESSION['papel'] !== 'admin' && $_SESSION['papel'] !== 'editor')) {
    header('Location: ../acesso_negado.php');
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

    $redirect = $_SESSION['papel'] === 'admin' ? 'painelAdmin.php' : 'painelEditor.php';
    header('Location: ' . $redirect);
    exit;
}
?>

<style>
    .form-header {
        background: linear-gradient(135deg, var(--primary-orange), var(--secondary-orange));
        color: white;
        padding: 40px 0;
        margin-bottom: 40px;
    }
    
    .form-container {
        background: white;
        border-radius: 25px;
        padding: 50px;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.08);
        margin-bottom: 40px;
    }
    
    .form-label {
        font-weight: 600;
        color: var(--primary-blue);
        margin-bottom: 10px;
        font-size: 1.1rem;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .form-control, .form-select {
        border-radius: 12px;
        border: 2px solid #ddd;
        padding: 15px;
        font-size: 1rem;
        transition: all 0.3s ease;
    }
    
    .form-control:focus, .form-select:focus {
        border-color: var(--primary-orange);
        box-shadow: 0 0 0 0.2rem rgba(255, 107, 53, 0.25);
    }
    
    .image-upload-area {
        border: 3px dashed #ddd;
        border-radius: 20px;
        padding: 40px;
        text-align: center;
        transition: all 0.3s ease;
        cursor: pointer;
        background: #f8f9fa;
    }
    
    .image-upload-area:hover {
        border-color: var(--primary-orange);
        background: #fff;
    }
    
    .image-upload-area.dragover {
        border-color: var(--primary-orange);
        background: rgba(255, 107, 53, 0.1);
    }
    
    .upload-icon {
        font-size: 4rem;
        color: var(--primary-orange);
        margin-bottom: 20px;
    }
    
    #preview {
        max-width: 100%;
        max-height: 400px;
        border-radius: 15px;
        margin-top: 20px;
        display: none;
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
    }
    
    .btn-submit {
        background: linear-gradient(135deg, var(--primary-orange), var(--secondary-orange));
        color: white;
        padding: 18px 50px;
        border-radius: 50px;
        font-weight: 700;
        font-size: 1.2rem;
        border: none;
        width: 100%;
        transition: all 0.3s ease;
    }
    
    .btn-submit:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 30px rgba(255, 107, 53, 0.4);
    }
    
    .btn-cancel {
        background: #6c757d;
        color: white;
        padding: 18px 50px;
        border-radius: 50px;
        font-weight: 700;
        font-size: 1.2rem;
        border: none;
        width: 100%;
        text-decoration: none;
        display: inline-block;
        text-align: center;
        transition: all 0.3s ease;
    }
    
    .btn-cancel:hover {
        background: #5a6268;
        color: white;
        transform: translateY(-3px);
    }
    
    @media (max-width: 768px) {
        .form-container {
            padding: 30px 20px;
        }
        
        .upload-icon {
            font-size: 3rem;
        }
    }
</style>

<div class="form-header">
    <div class="container">
        <h1 class="display-5 fw-bold">📝 Criar Novo Post</h1>
        <p class="mb-0 opacity-75">Compartilhe conhecimento com a comunidade acadêmica</p>
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
                       placeholder="Ex: Como Organizar seu Tempo de Estudo" required>
            </div>
            
            <div class="mb-4">
                <label for="descricao_curta" class="form-label">
                    <i class="bi bi-text-paragraph"></i> Descrição Curta
                </label>
                <textarea class="form-control" id="descricao_curta" name="descricao_curta" rows="3"
                          placeholder="Um resumo breve do artigo (aparecerá na listagem)" required></textarea>
                <small class="text-muted">Máximo 200 caracteres recomendado</small>
            </div>
            
            <div class="mb-4">
                <label for="descricao_longa" class="form-label">
                    <i class="bi bi-file-text"></i> Conteúdo Completo
                </label>
                <textarea class="form-control" id="descricao_longa" name="descricao_longa" rows="12"
                          placeholder="Escreva o conteúdo completo do artigo aqui..." required></textarea>
            </div>
            
            <div class="mb-4">
                <label class="form-label">
                    <i class="bi bi-image"></i> Imagem de Destaque
                </label>
                <div class="image-upload-area" onclick="document.getElementById('imagem').click()">
                    <div class="upload-icon">📷</div>
                    <h5>Clique para selecionar uma imagem</h5>
                    <p class="text-muted mb-0">ou arraste e solte aqui</p>
                    <input type="file" id="imagem" name="imagem" accept="image/*" style="display: none;" required>
                </div>
                <img id="preview" src="" alt="Preview da Imagem">
            </div>
            
            <div class="row g-3">
                <div class="col-md-6">
                    <button type="submit" class="btn-submit">
                        <i class="bi bi-check-circle-fill"></i> Publicar Artigo
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

// Drag and drop
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