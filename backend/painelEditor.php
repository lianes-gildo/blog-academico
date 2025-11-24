<?php
session_start();
require '../includes/header.php';

if (!isset($_SESSION['papel']) || ($_SESSION['papel'] !== 'editor' && $_SESSION['papel'] !== 'admin')) {
    header('Location: ../acesso_negado.php');
    exit;
}

$posts = json_decode(file_get_contents('../data/posts.json'), true) ?? [];
$estatisticas = json_decode(file_get_contents('../data/estatisticas.json'), true);

// Filtrar posts do editor atual
$meusPosts = array_filter($posts, function($post) {
    return $post['autor'] === $_SESSION['nome'];
});

$totalMeusPosts = count($meusPosts);
$totalVisitas = 0;
$totalLikes = 0;

foreach ($meusPosts as $post) {
    $totalVisitas += $estatisticas['visitas'][$post['id']] ?? 0;
    $totalLikes += $post['gostos'];
}
?>

<style>
    .editor-header {
        background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        color: white;
        padding: 40px 0;
        margin-bottom: 40px;
    }
    
    .editor-title {
        font-size: 2.5rem;
        font-weight: 800;
        margin-bottom: 10px;
    }
    
    .stats-card-editor {
        background: white;
        border-radius: 20px;
        padding: 30px;
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
        transition: all 0.3s ease;
        height: 100%;
    }
    
    .stats-card-editor:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
    }
    
    .stats-icon-editor {
        width: 70px;
        height: 70px;
        border-radius: 15px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2rem;
        margin-bottom: 20px;
    }
    
    .stats-icon-posts-editor {
        background: linear-gradient(135deg, #f093fb, #f5576c);
    }
    
    .stats-icon-views-editor {
        background: linear-gradient(135deg, #4facfe, #00f2fe);
    }
    
    .stats-icon-likes-editor {
        background: linear-gradient(135deg, #fa709a, #fee140);
    }
    
    @media (max-width: 768px) {
        .editor-title {
            font-size: 1.8rem;
        }
    }
</style>

<div class="editor-header">
    <div class="container">
        <h1 class="editor-title">✏️ Painel do Editor</h1>
        <p class="mb-0 opacity-75">Bem-vindo, <?php echo htmlspecialchars($_SESSION['nome']); ?>!</p>
    </div>
</div>

<main class="container pb-5">
    <!-- Statistics Cards -->
    <div class="row g-4 mb-5">
        <div class="col-lg-4 col-md-6">
            <div class="stats-card-editor">
                <div class="stats-icon-editor stats-icon-posts-editor">
                    📝
                </div>
                <div class="stats-number"><?php echo $totalMeusPosts; ?></div>
                <div class="stats-label">Meus Posts</div>
            </div>
        </div>
        
        <div class="col-lg-4 col-md-6">
            <div class="stats-card-editor">
                <div class="stats-icon-editor stats-icon-views-editor">
                    👁️
                </div>
                <div class="stats-number"><?php echo $totalVisitas; ?></div>
                <div class="stats-label">Visualizações</div>
            </div>
        </div>
        
        <div class="col-lg-4 col-md-6">
            <div class="stats-card-editor">
                <div class="stats-icon-editor stats-icon-likes-editor">
                    ❤️
                </div>
                <div class="stats-number"><?php echo $totalLikes; ?></div>
                <div class="stats-label">Total de Likes</div>
            </div>
        </div>
    </div>
    
    <!-- Quick Actions -->
    <div class="action-card">
        <h2 class="section-title">
            <i class="bi bi-lightning-charge-fill"></i>
            Ações Rápidas
        </h2>
        <div class="row g-3">
            <div class="col-md-12">
                <a href="adicionar_post.php" class="btn btn-primary-custom w-100">
                    <i class="bi bi-plus-circle-fill"></i>
                    Criar Novo Post
                </a>
            </div>
        </div>
    </div>
    
    <!-- My Posts -->
    <div class="action-card">
        <h2 class="section-title">
            <i class="bi bi-file-earmark-text-fill"></i>
            Meus Posts
        </h2>
        
        <?php if (empty($meusPosts)): ?>
            <div class="text-center py-5">
                <div style="font-size: 4rem;">📭</div>
                <h4 class="mt-3">Você ainda não publicou nenhum post</h4>
                <p class="text-muted">Comece criando seu primeiro artigo!</p>
                <a href="adicionar_post.php" class="btn btn-primary-custom mt-3">
                    <i class="bi bi-plus-circle-fill"></i>
                    Criar Primeiro Post
                </a>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-custom">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Imagem</th>
                            <th>Título</th>
                            <th>Data</th>
                            <th class="text-center">❤️</th>
                            <th class="text-center">👁️</th>
                            <th class="text-center">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($meusPosts as $post):
                            $visitas = $estatisticas['visitas'][$post['id']] ?? 0;
                        ?>
                        <tr>
                            <td><strong>#<?php echo $post['id']; ?></strong></td>
                            <td>
                                <img src="<?php echo $post['imagem']; ?>" alt="Post" class="post-thumbnail">
                            </td>
                            <td>
                                <strong><?php echo htmlspecialchars($post['titulo']); ?></strong><br>
                                <small class="text-muted"><?php echo htmlspecialchars(mb_substr($post['descricao_curta'], 0, 60)); ?>...</small>
                            </td>
                            <td><?php echo date('d/m/Y', strtotime($post['data'])); ?></td>
                            <td class="text-center"><strong><?php echo $post['gostos']; ?></strong></td>
                            <td class="text-center"><strong><?php echo $visitas; ?></strong></td>
                            <td class="text-center">
                                <a href="editar_post.php?id=<?php echo $post['id']; ?>" class="btn btn-action btn-edit me-2 mb-2 mb-md-0">
                                    <i class="bi bi-pencil-fill"></i> Editar
                                </a>
                                <a href="apagar_post.php?id=<?php echo $post['id']; ?>" 
                                   class="btn btn-action btn-delete"
                                   onclick="return confirm('⚠️ Tem certeza que deseja apagar este post? Esta ação não pode ser desfeita.')">
                                    <i class="bi bi-trash-fill"></i> Apagar
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</main>

<?php require '../includes/footer.php'; ?>