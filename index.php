<?php require 'includes/header.php'; ?>

<style>
    .hero-section {
        background: linear-gradient(135deg, var(--primary-blue) 0%, var(--secondary-blue) 100%);
        color: white;
        padding: 80px 0 60px;
        margin-top: -76px;
        padding-top: 156px;
    }
    
    .hero-title {
        font-size: 3rem;
        font-weight: 800;
        margin-bottom: 20px;
        animation: fadeInUp 0.8s ease;
    }
    
    .hero-subtitle {
        font-size: 1.3rem;
        opacity: 0.9;
        animation: fadeInUp 1s ease;
    }
    
    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(30px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    .search-box {
        background: white;
        border-radius: 50px;
        padding: 8px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        margin-top: 30px;
    }
    
    .search-box input {
        border: none;
        padding: 12px 25px;
        font-size: 1rem;
    }
    
    .search-box input:focus {
        outline: none;
    }
    
    .search-box button {
        background: var(--primary-orange);
        border: none;
        border-radius: 50px;
        padding: 12px 30px;
        color: white;
        font-weight: 600;
        transition: all 0.3s ease;
    }
    
    .search-box button:hover {
        background: var(--secondary-orange);
        transform: scale(1.05);
    }
    
    .section-title {
        font-size: 2.5rem;
        font-weight: 700;
        color: var(--primary-blue);
        margin-bottom: 40px;
        position: relative;
        display: inline-block;
    }
    
    .section-title::after {
        content: '';
        position: absolute;
        bottom: -10px;
        left: 0;
        width: 60%;
        height: 4px;
        background: var(--primary-orange);
        border-radius: 2px;
    }
    
    .post-card {
        background: white;
        border-radius: 20px;
        overflow: hidden;
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
        transition: all 0.3s ease;
        height: 100%;
        display: flex;
        flex-direction: column;
    }
    
    .post-card:hover {
        transform: translateY(-10px);
        box-shadow: 0 15px 40px rgba(0, 0, 0, 0.15);
    }
    
    .post-card-img {
        width: 100%;
        height: 220px;
        object-fit: cover;
        transition: transform 0.5s ease;
    }
    
    .post-card:hover .post-card-img {
        transform: scale(1.1);
    }
    
    .post-card-img-wrapper {
        overflow: hidden;
        position: relative;
    }
    
    .post-card-body {
        padding: 25px;
        flex-grow: 1;
        display: flex;
        flex-direction: column;
    }
    
    .post-title {
        font-size: 1.3rem;
        font-weight: 700;
        color: var(--primary-blue);
        margin-bottom: 15px;
        line-height: 1.4;
    }
    
    .post-excerpt {
        color: #666;
        margin-bottom: 20px;
        line-height: 1.6;
        flex-grow: 1;
    }
    
    .post-meta {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding-top: 15px;
        border-top: 1px solid #eee;
        font-size: 0.9rem;
        color: #888;
    }
    
    .post-author {
        display: flex;
        align-items: center;
        gap: 8px;
    }
    
    .post-stats {
        display: flex;
        gap: 15px;
    }
    
    .stat-item {
        display: flex;
        align-items: center;
        gap: 5px;
        font-weight: 600;
    }
    
    .btn-read-more {
        background: linear-gradient(135deg, var(--primary-orange), var(--secondary-orange));
        color: white;
        padding: 12px 30px;
        border-radius: 50px;
        text-decoration: none;
        font-weight: 600;
        display: inline-block;
        transition: all 0.3s ease;
        border: none;
    }
    
    .btn-read-more:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 20px rgba(255, 107, 53, 0.4);
        color: white;
    }
    
    .empty-state {
        text-align: center;
        padding: 80px 20px;
    }
    
    .empty-state-icon {
        font-size: 5rem;
        color: var(--primary-orange);
        margin-bottom: 20px;
    }
    
    @media (max-width: 768px) {
        .hero-title {
            font-size: 2rem;
        }
        
        .hero-subtitle {
            font-size: 1.1rem;
        }
        
        .section-title {
            font-size: 1.8rem;
        }
        
        .post-card-img {
            height: 180px;
        }
    }
</style>

<!-- Hero Section -->
<section class="hero-section">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8 mx-auto text-center">
                <h1 class="hero-title"> Bem-vindo ao Blog Acadêmico</h1>
                <p class="hero-subtitle">Seu portal de conhecimento para uma jornada universitária de sucesso em Moçambique</p>
                
                <div class="search-box d-none">
                    <div class="row g-0">
                        <div class="col">
                            <input type="text" class="form-control" placeholder="🔍 Pesquisar artigos...">
                        </div>
                        <div class="col-auto">
                            <button class="btn">Pesquisar</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Posts Section -->
<main class="container py-5">
    <div class="text-center mb-5">
        <h2 class="section-title">📚 Últimos Artigos Acadêmicos</h2>
    </div>
    
    <div class="row g-4">
        <?php
        $posts = json_decode(file_get_contents('data/posts.json'), true);
        
        if ($posts && count($posts) > 0) {
            usort($posts, function($a, $b) {
                return strtotime($b['data']) - strtotime($a['data']);
            });

            foreach ($posts as $post) {
                $postId = $post['id'];
                $estatisticas = json_decode(file_get_contents('data/estatisticas.json'), true);
                $visitas = $estatisticas['visitas'][$postId] ?? 0;
                $gostos = $post['gostos'];
                ?>
                <div class="col-lg-4 col-md-6">
                    <article class="post-card">
                        <div class="post-card-img-wrapper">
                            <img src="<?php echo htmlspecialchars($post['imagem']); ?>" 
                                 alt="<?php echo htmlspecialchars($post['titulo']); ?>" 
                                 class="post-card-img">
                        </div>
                        <div class="post-card-body">
                            <h3 class="post-title"><?php echo htmlspecialchars($post['titulo']); ?></h3>
                            <p class="post-excerpt"><?php echo htmlspecialchars(mb_substr($post['descricao_curta'], 0, 120)); ?>...</p>
                            
                            <div class="post-meta">
                                <div class="post-author">
                                    <i class="bi bi-person-circle"></i>
                                    <span><?php echo htmlspecialchars($post['autor']); ?></span>
                                </div>
                                <div class="post-stats">
                                    <span class="stat-item">
                                        <i class="bi bi-heart-fill text-danger"></i>
                                        <?php echo $gostos; ?>
                                    </span>
                                    <span class="stat-item">
                                        <i class="bi bi-eye-fill text-primary"></i>
                                        <?php echo $visitas; ?>
                                    </span>
                                </div>
                            </div>
                            
                            <div class="mt-3">
                                <a href="artigo.php?id=<?php echo $postId; ?>" class="btn-read-more w-100 text-center">
                                    📖 Ler Artigo Completo
                                </a>
                            </div>
                        </div>
                    </article>
                </div>
                <?php
            }
        } else {
            ?>
            <div class="col-12">
                <div class="empty-state">
                    <div class="empty-state-icon">📭</div>
                    <h3>Nenhum artigo publicado ainda</h3>
                    <p class="text-muted">Volte em breve para conferir nossos conteúdos acadêmicos!</p>
                </div>
            </div>
            <?php
        }
        ?>
    </div>
</main>

<?php require 'includes/footer.php'; ?>