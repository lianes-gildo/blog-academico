<?php require 'includes/header.php'; ?>

<style>
    .about-hero {
        background: linear-gradient(135deg, var(--primary-orange) 0%, var(--secondary-orange) 100%);
        color: white;
        padding: 80px 0 60px;
        margin-bottom: 60px;
    }
    
    .about-title {
        font-size: 3.5rem;
        font-weight: 800;
        margin-bottom: 20px;
        animation: fadeInUp 0.8s ease;
    }
    
    .about-subtitle {
        font-size: 1.4rem;
        opacity: 0.95;
        animation: fadeInUp 1s ease;
        max-width: 700px;
        margin: 0 auto;
    }
    
    .feature-card {
        background: white;
        border-radius: 25px;
        padding: 40px;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.08);
        transition: all 0.3s ease;
        height: 100%;
    }
    
    .feature-card:hover {
        transform: translateY(-10px);
        box-shadow: 0 20px 50px rgba(0, 0, 0, 0.12);
    }
    
    .feature-icon {
        width: 80px;
        height: 80px;
        background: linear-gradient(135deg, var(--primary-orange), var(--secondary-orange));
        border-radius: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2.5rem;
        margin-bottom: 25px;
    }
    
    .feature-title {
        font-size: 1.5rem;
        font-weight: 700;
        color: var(--primary-blue);
        margin-bottom: 15px;
    }
    
    .feature-text {
        color: #666;
        line-height: 1.8;
        font-size: 1.05rem;
    }
    
    .mission-section {
        background: linear-gradient(135deg, var(--primary-blue), var(--secondary-blue));
        color: white;
        padding: 80px 0;
        margin: 60px 0;
        border-radius: 0;
    }
    
    .mission-title {
        font-size: 2.5rem;
        font-weight: 800;
        margin-bottom: 30px;
    }
    
    .mission-text {
        font-size: 1.2rem;
        line-height: 1.8;
        opacity: 0.95;
        max-width: 800px;
        margin: 0 auto;
    }
    
    .stats-section {
        padding: 60px 0;
    }
    
    .stat-box {
        text-align: center;
        padding: 30px;
    }
    
    .stat-number {
        font-size: 4rem;
        font-weight: 800;
        color: var(--primary-orange);
        line-height: 1;
        margin-bottom: 15px;
    }
    
    .stat-label {
        font-size: 1.2rem;
        color: #666;
        font-weight: 600;
    }
    
    .values-list {
        list-style: none;
        padding: 0;
    }
    
    .values-list li {
        padding: 20px;
        margin-bottom: 15px;
        background: #f8f9fa;
        border-left: 5px solid var(--primary-orange);
        border-radius: 10px;
        font-size: 1.1rem;
        transition: all 0.3s ease;
    }
    
    .values-list li:hover {
        background: white;
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
        transform: translateX(10px);
    }
    
    @media (max-width: 768px) {
        .about-hero {
            padding: 50px 0 40px;
        }
        
        .about-title {
            font-size: 2.2rem;
        }
        
        .about-subtitle {
            font-size: 1.1rem;
        }
        
        .feature-card {
            padding: 30px 20px;
        }
        
        .mission-section {
            padding: 50px 0;
        }
        
        .stat-number {
            font-size: 3rem;
        }
    }
</style>

<div class="about-hero">
    <div class="container text-center">
        <h1 class="about-title">📚 Sobre o Blog Acadêmico</h1>
        <p class="about-subtitle">
            Um espaço pensado para apoiar estudantes, professores e pesquisadores 
            na organização da vida acadêmica em Moçambique
        </p>
    </div>
</div>

<main class="container pb-5">
    <!-- Features Section -->
    <div class="row g-4 mb-5">
        <div class="col-lg-4 col-md-6">
            <div class="feature-card">
                <div class="feature-icon">🎯</div>
                <h3 class="feature-title">Nosso Objetivo</h3>
                <p class="feature-text">
                    Facilitar o acesso a conteúdos acadêmicos de qualidade, com foco em 
                    estudantes de Moçambique e países de língua portuguesa.
                </p>
            </div>
        </div>
        
        <div class="col-lg-4 col-md-6">
            <div class="feature-card">
                <div class="feature-icon">💡</div>
                <h3 class="feature-title">Conteúdo Rico</h3>
                <p class="feature-text">
                    Dicas de estudo, orientações sobre pesquisa científica, ferramentas 
                    digitais e muito mais para sua jornada acadêmica.
                </p>
            </div>
        </div>
        
        <div class="col-lg-4 col-md-6">
            <div class="feature-card">
                <div class="feature-icon">🤝</div>
                <h3 class="feature-title">Comunidade Ativa</h3>
                <p class="feature-text">
                    Interaja com outros estudantes através de comentários, curtidas e 
                    compartilhamento de conhecimento.
                </p>
            </div>
        </div>
    </div>
    
    <!-- How it Works -->
    <div class="row mb-5">
        <div class="col-lg-8 mx-auto">
            <div class="feature-card">
                <h2 class="text-center mb-4" style="color: var(--primary-blue); font-weight: 800;">
                    <i class="bi bi-gear-fill"></i> Como Funciona
                </h2>
                
                <ul class="values-list">
                    <li>
                        <strong>📝 Postagens Acadêmicas:</strong> Publicadas por administradores 
                        e editores, com foco em temas relevantes para a vida universitária.
                    </li>
                    <li>
                        <strong>💬 Interação:</strong> Usuários podem reagir com gostos, deixar 
                        comentários e compartilhar artigos nas redes sociais.
                    </li>
                    <li>
                        <strong>🌍 Acesso Livre:</strong> Quem não estiver logado pode ler e 
                        compartilhar posts, mas não pode comentar nem curtir.
                    </li>
                    <li>
                        <strong>👥 Papéis de Usuário:</strong> Sistema com diferentes níveis de 
                        acesso (Usuário, Editor).
                    </li>
                </ul>
            </div>
        </div>
    </div>
</main>

<!-- Mission Section -->
<div class="mission-section">
    <div class="container text-center">
        <h2 class="mission-title">🚀 Nossa Missão</h2>
        <p class="mission-text">
            Capacitar estudantes moçambicanos e de toda a CPLP com recursos, orientações e 
            ferramentas que facilitem sua jornada acadêmica, promovendo a excelência no ensino 
            superior através do compartilhamento de conhecimento e boas práticas.
        </p>
    </div>
</div>

<div class="container pb-5">
    <!-- Stats Section -->
    <div class="stats-section">
        <div class="row">
            <div class="col-md-4">
                <div class="stat-box">
                    <div class="stat-number">100+</div>
                    <div class="stat-label">Artigos Publicados</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-box">
                    <div class="stat-number">1000+</div>
                    <div class="stat-label">Estudantes Ativos</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-box">
                    <div class="stat-number">24/7</div>
                    <div class="stat-label">Acesso Disponível</div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Focus Section -->
    <div class="row">
        <div class="col-lg-10 mx-auto">
            <div class="feature-card text-center">
                <h2 style="color: var(--primary-blue); font-weight: 800; margin-bottom: 30px;">
                    🇲🇿 Foco em Moçambique
                </h2>
                <p class="feature-text" style="font-size: 1.15rem;">
                    O Blog Acadêmico considera a realidade específica de estudantes em Moçambique, 
                    abordando desafios locais, oportunidades de bolsas, eventos acadêmicos nacionais 
                    e orientações adaptadas ao contexto educacional moçambicano.
                </p>
            </div>
        </div>
    </div>
</div>

<?php require 'includes/footer.php'; ?>