<?php
session_start();
require '../includes/header.php';

if (!isset($_SESSION['papel']) || $_SESSION['papel'] !== 'admin') {
    header('Location: ../acesso_negado.php');
    exit;
}

$posts = json_decode(file_get_contents('../data/posts.json'), true) ?? [];
$usuarios = json_decode(file_get_contents('../data/usuarios.json'), true) ?? [];
$estatisticas = json_decode(file_get_contents('../data/estatisticas.json'), true);

$totalPosts = count($posts);
$totalUsuarios = count($usuarios);
$totalVisitas = array_sum($estatisticas['visitas'] ?? []);
$totalLikes = array_sum(array_map('count', $estatisticas['gostos'] ?? []));
?>

<style>
    .admin-header {
        background: linear-gradient(135deg, var(--primary-blue), var(--secondary-blue));
        color: white;
        padding: 40px 0;
        margin-bottom: 40px;
    }
    
    .admin-title {
        font-size: 2.5rem;
        font-weight: 800;
        margin-bottom: 10px;
    }
    
    .stats-card {
        background: white;
        border-radius: 20px;
        padding: 30px;
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
        transition: all 0.3s ease;
        height: 100%;
    }
    
    .stats-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
    }
    
    .stats-icon {
        width: 70px;
        height: 70px;
        border-radius: 15px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2rem;
        margin-bottom: 20px;
    }
    
    .stats-icon-posts {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }
    
    .stats-icon-users {
        background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    }
    
    .stats-icon-views {
        background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
    }
    
    .stats-icon-likes {
        background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
    }
    
    .stats-number {
        font-size: 3rem;
        font-weight: 800;
        color: var(--primary-blue);
        line-height: 1;
    }
    
    .stats-label {
        color: #666;
        font-size: 1.1rem;
        font-weight: 600;
        margin-top: 10px;
    }
    
    .action-card {
        background: white;
        border-radius: 20px;
        padding: 30px;
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
        margin-bottom: 30px;
    }
    
    .section-title {
        font-size: 1.8rem;
        font-weight: 700;
        color: var(--primary-blue);
        margin-bottom: 25px;
        display: flex;
        align-items: center;
        gap: 15px;
    }
    
    .btn-primary-custom {
        background: linear-gradient(135deg, var(--primary-orange), var(--secondary-orange));
        border: none;
        padding: 15px 30px;
        border-radius: 12px;
        font-weight: 600;
        color: white;
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
        gap: 10px;
    }
    
    .btn-primary-custom:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 20px rgba(255, 107, 53, 0.4);
    }
    
    .btn-secondary-custom {
        background: var(--primary-blue);
        border: none;
        padding: 15px 30px;
        border-radius: 12px;
        font-weight: 600;
        color: white;
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
        gap: 10px;
    }
    
    .btn-secondary-custom:hover {
        background: var(--secondary-blue);
        transform: translateY(-3px);
        box-shadow: 0 8px 20px rgba(0, 59, 92, 0.4);
    }
    
    .table-custom {
        background: white;
        border-radius: 15px;
        overflow: hidden;
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.05);
    }
    
    .table-custom thead {
        background: linear-gradient(135deg, var(--primary-blue), var(--secondary-blue));
        color: white;
    }
    
    .table-custom thead th {
        padding: 20px 15px;
        font-weight: 600;
        border: none;
    }
    
    .table-custom tbody td {
        padding: 20px 15px;
        vertical-align: middle;
        border-bottom: 1px solid #f0f0f0;
    }
    
    .table-custom tbody tr:hover {
        background: #f8f9fa;
    }
    
    .post-thumbnail {
        width: 80px;
        height: 80px;
        object-fit: cover;
        border-radius: 12px;
        box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1);
    }
    
    .btn-action {
        padding: 8px 16px;
        border-radius: 8px;
        font-size: 0.9rem;
        font-weight: 600;
        transition: all 0.3s ease;
        border: none;
        display: inline-flex;
        align-items: center;
        gap: 5px;
    }
    
    .btn-edit {
        background: #3498db;
        color: white;
    }
    
    .btn-edit:hover {
        background: #2980b9;
        transform: translateY(-2px);
    }
    
    .btn-delete {
        background: #e74c3c;
        color: white;
    }
    
    .btn-delete:hover {
        background: #c0392b;
        transform: translateY(-2px);
    }
    
    @media (max-width: 768px) {
        .admin-title {
            font-size: 1.8rem;
        }
        
        .stats-number {
            font-size: 2.5rem;
        }
        
        .table-responsive {
            border-radius: 15px;
        }
        
        .btn-primary-custom,
        .btn-secondary-custom {
            width: 100%;
            justify-content: center;
            margin-bottom: 10px;
        }
    }
</style>

<div class="admin-header">
    <div class="container">
        <h1 class="admin-title">🎛️ Painel Administrativo</h1>
        <p class="mb-0 opacity-75">Bem-vindo, <?php echo htmlspecialchars($_SESSION['nome']); ?>!</p>
    </div>
</div>

<main class="container pb-5">
    <!-- Statistics Cards -->
    <div class="row g-4 mb-5">
        <div class="col-lg-3 col-md-6">
            <div class="stats-card">
                <div class="stats-icon stats-icon-posts">
                    📝
                </div>
                <div class="stats-number"><?php echo $totalPosts; ?></div>
                <div class="stats-label">Posts Publicados</div>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6">
            <div class="stats-card">
                <div class="stats-icon stats-icon-users">
                    👥
                </div>
                <div class="stats-number"><?php echo $totalUsuarios; ?></div>
                <div class="stats-label">Usuários Registrados</div>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6">
            <div class="stats-card">
                <div class="stats-icon stats-icon-views">
                    👁️
                </div>
                <div class="stats-number"><?php echo $totalVisitas; ?></div>
                <div class="stats-label">Total de Visualizações</div>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6">
            <div class="stats-card">
                <div class="stats-icon stats-icon-likes">
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
            <div class="col-md-6">
                <a href="adicionar_post.php" class="btn btn-primary-custom w-100">
                    <i class="bi bi-plus-circle-fill"></i>
                    Novo Post
                </a>
            </div>
            <div class="col-md-6">
                <a href="gerir_usuarios.php" class="btn btn-secondary-custom w-100">
                    <i class="bi bi-people-fill"></i>
                    Gerir Usuários
                </a>
            </div>
        </div>
    </div>
    
    <!-- Posts Management -->
    <div class="action-card">
        <h2 class="section-title">
            <i class="bi bi-file-earmark-text-fill"></i>
            Gerir Posts
        </h2>
        
        <?php if (empty($posts)): ?>
            <div class="text-center py-5">
                <div style="font-size: 4rem;">📭</div>
                <h4 class="mt-3">Nenhum post publicado ainda</h4>
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
                            <th>Autor</th>
                            <th>Data</th>
                            <th class="text-center">❤️</th>
                            <th class="text-center">👁️</th>
                            <th class="text-center">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($posts as $post):
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
                            <td><?php echo htmlspecialchars($post['autor']); ?></td>
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