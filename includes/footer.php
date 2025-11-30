<footer class="footer-modern mt-5">
        <div class="footer-main">
            <div class="container">
                <div class="row g-4">
                    <!-- Coluna 1: Sobre -->
                    <div class="col-lg-4 col-md-6">
                        <div class="footer-section">
                            <h3 class="footer-brand mb-4">
                                <span class="brand-icon"><img src="<?php echo getBasePath(); ?>assets/img/logo.png" alt="Logo" class="navbar-logo me-2"></span>
                                Blog Acadêmico
                            </h3>
                            <p class="footer-description">
                                Plataforma dedicada a apoiar estudantes, professores e pesquisadores 
                                na jornada acadêmica em Moçambique e países lusófonos.
                            </p>
                            <div class="footer-stats mt-4">
                                <div class="stat-item-footer">
                                    <strong>1000+</strong>
                                    <span>Estudantes</span>
                                </div>
                                <div class="stat-item-footer">
                                    <strong>100+</strong>
                                    <span>Artigos</span>
                                </div>
                                <div class="stat-item-footer">
                                    <strong>24/7</strong>
                                    <span>Disponível</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Coluna 2: Links Rápidos -->
                    <div class="col-lg-2 col-md-6">
                        <div class="footer-section">
                            <h5 class="footer-title">⚡ Links Rápidos</h5>
                            <ul class="footer-links">
                                <li><a href="<?php echo usuarioLogado() ? '../index.php' : 'index.php'; ?>">
                                    <i class="bi bi-house-door"></i> Início
                                </a></li>
                                <li><a href="<?php echo usuarioLogado() ? '../sobre.php' : 'sobre.php'; ?>">
                                    <i class="bi bi-info-circle"></i> Sobre
                                </a></li>
                                <?php if (!usuarioLogado()): ?>
                                <li><a href="<?php echo usuarioLogado() ? '../backend/login.php' : 'backend/login.php'; ?>">
                                    <i class="bi bi-box-arrow-in-right"></i> Login
                                </a></li>
                                <li><a href="<?php echo usuarioLogado() ? '../backend/registrar.php' : 'backend/registrar.php'; ?>">
                                    <i class="bi bi-person-plus"></i> Registrar
                                </a></li>
                                <?php else: ?>
                                <li><a href="../backend/perfil.php">
                                    <i class="bi bi-person-circle"></i> Meu Perfil
                                </a></li>
                                <?php endif; ?>
                            </ul>
                        </div>
                    </div>
                    
                    <!-- Coluna 3: Recursos -->
                    <div class="col-lg-3 col-md-6">
                        <div class="footer-section">
                            <h5 class="footer-title">📖 Recursos</h5>
                            <ul class="footer-links">
                                <li><a href="#">
                                    <i class="bi bi-book"></i> Guias de Estudo
                                </a></li>
                                <li><a href="#">
                                    <i class="bi bi-file-text"></i> Artigos Acadêmicos
                                </a></li>
                                <li><a href="#">
                                    <i class="bi bi-tools"></i> Ferramentas
                                </a></li>
                                <li><a href="#">
                                    <i class="bi bi-question-circle"></i> FAQ
                                </a></li>
                            </ul>
                        </div>
                    </div>
                    
                    <!-- Coluna 4: Contato e Redes Sociais -->
                    <div class="col-lg-3 col-md-6">
                        <div class="footer-section">
                            <h5 class="footer-title">🌐 Conecte-se</h5>
                            <p class="footer-contact mb-3">
                                <i class="bi bi-envelope-fill"></i>
                                <a href="mailto:lndigitalcraft@gmail.com">lndigitalcraft@gmail.com</a>
                            </p>
                            <p class="footer-contact mb-4">
                                <i class="bi bi-geo-alt-fill"></i>
                                Maputo, Moçambique
                            </p>
                            
                            <div class="social-links-footer">
                                <a href="#" class="social-icon-footer facebook" title="Facebook">
                                    <i class="bi bi-facebook"></i>
                                </a>
                                <a href="#" class="social-icon-footer twitter" title="Twitter">
                                    <i class="bi bi-twitter"></i>
                                </a>
                                <a href="#" class="social-icon-footer instagram" title="Instagram">
                                    <i class="bi bi-instagram"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Footer Bottom -->
        <div class="footer-bottom">
            <div class="container">
                <div class="row align-items-center">
                    <div class="col-md-6 text-center text-md-start mb-3 mb-md-0">
                        <p class="mb-0">
                            © <?php echo date('Y'); ?> Blog Acadêmico. Todos os direitos reservados.
                        </p>
                    </div>
                    <div class="col-md-6 text-center text-md-end">
                        <p class="mb-0">
                            Desenvolvido por 
                            <strong>Lianes Gildo Nhacula</strong>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </footer>
    
    <style>
        .footer-modern {
            background: linear-gradient(135deg, var(--primary-blue) 0%, var(--secondary-blue) 100%);
            color: white;
            margin-top: auto;
            position: relative;
            overflow: hidden;
        }
        
        .footer-modern::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--primary-orange), var(--secondary-orange));
        }
        
        .footer-main {
            padding: 60px 0 40px;
        }
        
        .footer-section {
            height: 100%;
        }
        
        .footer-brand {
            font-size: 1.8rem;
            font-weight: 800;
            color: white;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .brand-icon {
            font-size: 2rem;
            display: inline-block;
            animation: float 3s ease-in-out infinite;
        }
        
        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }
        
        .footer-description {
            color: rgba(255, 255, 255, 0.85);
            line-height: 1.8;
            font-size: 0.95rem;
        }
        
        .footer-stats {
            display: flex;
            gap: 25px;
            flex-wrap: wrap;
        }
        
        .stat-item-footer {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }
        
        .stat-item-footer strong {
            font-size: 1.5rem;
            font-weight: 800;
            color: var(--primary-orange);
            line-height: 1;
        }
        
        .stat-item-footer span {
            font-size: 0.85rem;
            color: rgba(255, 255, 255, 0.7);
        }
        
        .footer-title {
            font-size: 1.2rem;
            font-weight: 700;
            margin-bottom: 25px;
            color: white;
            position: relative;
            padding-bottom: 12px;
        }
        
        .footer-title::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 50px;
            height: 3px;
            background: var(--primary-orange);
            border-radius: 2px;
        }
        
        .footer-links {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .footer-links li {
            margin-bottom: 12px;
        }
        
        .footer-links a {
            color: rgba(255, 255, 255, 0.85);
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 10px;
            transition: all 0.3s ease;
            font-size: 0.95rem;
        }
        
        .footer-links a:hover {
            color: var(--primary-orange);
            transform: translateX(5px);
        }
        
        .footer-links a i {
            font-size: 1rem;
            width: 20px;
        }
        
        .footer-contact {
            color: rgba(255, 255, 255, 0.85);
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 0.95rem;
        }
        
        .footer-contact i {
            color: var(--primary-orange);
            font-size: 1.1rem;
        }
        
        .footer-contact a {
            color: rgba(255, 255, 255, 0.85);
            text-decoration: none;
            transition: color 0.3s ease;
        }
        
        .footer-contact a:hover {
            color: var(--primary-orange);
        }
        
        .social-links-footer {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
        }
        
        .social-icon-footer {
            width: 45px;
            height: 45px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            text-decoration: none;
            font-size: 1.2rem;
            transition: all 0.3s ease;
            background: rgba(255, 255, 255, 0.1);
            border: 2px solid transparent;
        }
        
        .social-icon-footer:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.3);
        }
        
        .social-icon-footer.facebook:hover {
            background: #1877f2;
            border-color: #1877f2;
        }
        
        .social-icon-footer.twitter:hover {
            background: #1da1f2;
            border-color: #1da1f2;
        }
        
        .social-icon-footer.instagram:hover {
            background: linear-gradient(45deg, #f09433 0%, #e6683c 25%, #dc2743 50%, #cc2366 75%, #bc1888 100%);
            border-color: #e6683c;
        }
        
        .social-icon-footer.linkedin:hover {
            background: #0077b5;
            border-color: #0077b5;
        }
        
        .social-icon-footer.youtube:hover {
            background: #ff0000;
            border-color: #ff0000;
        }
        
        .footer-bottom {
            background: rgba(0, 0, 0, 0.2);
            padding: 25px 0;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .footer-bottom p {
            color: rgba(255, 255, 255, 0.8);
            font-size: 0.9rem;
        }
        
        .footer-bottom strong {
            color: var(--primary-orange);
            font-weight: 600;
        }
        
        .heart {
            color: var(--primary-orange);
            display: inline-block;
            animation: heartbeat 1.5s ease-in-out infinite;
        }
        
        @keyframes heartbeat {
            0%, 100% { transform: scale(1); }
            10%, 30% { transform: scale(1.1); }
            20%, 40% { transform: scale(1); }
        }
        
        /* Responsive */
        @media (max-width: 991px) {
            .footer-main {
                padding: 50px 0 30px;
            }
            
            .footer-section {
                margin-bottom: 30px;
            }
            
            .footer-stats {
                justify-content: flex-start;
            }
        }
        
        @media (max-width: 767px) {
            .footer-brand {
                font-size: 1.5rem;
                justify-content: center;
            }
            
            .footer-description {
                text-align: center;
            }
            
            .footer-stats {
                justify-content: center;
            }
            
            .footer-title {
                text-align: center;
            }
            
            .footer-title::after {
                left: 50%;
                transform: translateX(-50%);
            }
            
            .footer-links {
                text-align: center;
            }
            
            .footer-links a {
                justify-content: center;
            }
            
            .footer-contact {
                justify-content: center;
            }
            
            .social-links-footer {
                justify-content: center;
            }
            
            .footer-bottom p {
                font-size: 0.85rem;
            }
        }
    </style>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JS -->
    <script src="../assets/js/script.js"></script>
</body>
</html>