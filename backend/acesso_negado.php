<?php 
session_start();
require 'includes/header.php'; 
?>

<style>
    .access-denied-container {
        min-height: 70vh;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 40px 20px;
    }
    
    .access-denied-card {
        background: white;
        border-radius: 30px;
        padding: 60px 40px;
        box-shadow: 0 15px 50px rgba(0, 0, 0, 0.1);
        text-align: center;
        max-width: 600px;
        animation: slideIn 0.5s ease;
    }
    
    @keyframes slideIn {
        from {
            opacity: 0;
            transform: translateY(-30px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    .access-denied-icon {
        font-size: 8rem;
        margin-bottom: 30px;
        animation: bounce 1s ease infinite;
    }
    
    @keyframes bounce {
        0%, 100% {
            transform: translateY(0);
        }
        50% {
            transform: translateY(-20px);
        }
    }
    
    .access-denied-title {
        font-size: 2.5rem;
        font-weight: 800;
        color: var(--primary-blue);
        margin-bottom: 20px;
    }
    
    .access-denied-text {
        font-size: 1.2rem;
        color: #666;
        margin-bottom: 40px;
        line-height: 1.6;
    }
    
    .btn-home {
        background: linear-gradient(135deg, var(--primary-orange), var(--secondary-orange));
        color: white;
        padding: 18px 40px;
        border-radius: 50px;
        font-weight: 700;
        font-size: 1.1rem;
        text-decoration: none;
        display: inline-block;
        transition: all 0.3s ease;
        border: none;
    }
    
    .btn-home:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 30px rgba(255, 107, 53, 0.4);
        color: white;
    }
    
    @media (max-width: 768px) {
        .access-denied-card {
            padding: 40px 30px;
        }
        
        .access-denied-icon {
            font-size: 5rem;
        }
        
        .access-denied-title {
            font-size: 1.8rem;
        }
        
        .access-denied-text {
            font-size: 1rem;
        }
    }
</style>

<div class="access-denied-container">
    <div class="access-denied-card">
        <div class="access-denied-icon">🚫</div>
        <h1 class="access-denied-title">Acesso Negado</h1>
        <p class="access-denied-text">
            Desculpe, você não tem permissão para aceder a esta página.<br>
            Se acredita que isto é um erro, por favor contacte o administrador.
        </p>
        <a href="index.php" class="btn-home">
            <i class="bi bi-house-door-fill"></i> Voltar ao Início
        </a>
    </div>
</div>

<?php require 'includes/footer.php'; ?>