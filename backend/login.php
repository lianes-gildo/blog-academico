<?php
session_start();

// Se já estiver logado, redirecionar
if (isset($_SESSION['usuario_id'])) {
    ?>
    <script>window.location.replace('../index.php');</script>
    <meta http-equiv="refresh" content="0;url=../index.php">
    <?php
    exit;
}

$mensagemErro = '';
$loginSucesso = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $senha = $_POST['senha'] ?? '';

    if (empty($email) || empty($senha)) {
        $mensagemErro = "❌ Por favor, preencha todos os campos!";
    } else {
        $arquivoUsuarios = __DIR__ . '/../data/usuarios.json';
        
        if (!file_exists($arquivoUsuarios)) {
            $mensagemErro = "❌ Erro no sistema. Arquivo de usuários não encontrado.";
        } else {
            $usuarios = json_decode(file_get_contents($arquivoUsuarios), true);
            
            if (!is_array($usuarios)) {
                $mensagemErro = "❌ Erro ao ler dados de usuários.";
            } else {
                $usuarioEncontrado = false;
                
                foreach ($usuarios as $usuario) {
                    if ($usuario['email'] === $email) {
                        $usuarioEncontrado = true;
                        
                        // Verificar suspensão
                        if (isset($usuario['suspenso_ate']) && strtotime($usuario['suspenso_ate']) > time()) {
                            $mensagemErro = "🚫 Sua conta está suspensa até " . date('d/m/Y H:i', strtotime($usuario['suspenso_ate']));
                            break;
                        }
                        
                        // Verificar senha
                        if (password_verify($senha, $usuario['senha'])) {
                            // Login bem-sucedido
                            $_SESSION['usuario_id'] = $usuario['id'];
                            $_SESSION['nome'] = $usuario['nome'];
                            $_SESSION['papel'] = $usuario['papel'];
                            $_SESSION['imagem'] = $usuario['imagem'] ?? 'assets/img/users/default.jpg';
                            
                            $loginSucesso = true;
                            break;
                        } else {
                            $mensagemErro = "❌ Email ou senha incorreto/a!";
                            break;
                        }
                    }
                }
                
                if (!$usuarioEncontrado && empty($mensagemErro)) {
                    $mensagemErro = "❌ Email ou senha incorreto/a!";
                }
            }
        }
    }
}

// Se login foi bem-sucedido, mostrar página de redirecionamento
if ($loginSucesso) {
    ?>
    <!DOCTYPE html>
    <html lang="pt-MZ">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Login Bem-sucedido</title>
        <meta http-equiv="refresh" content="0;url=../index.php">
        <script>
            window.location.replace('../index.php');
        </script>
        <style>
            body {
                font-family: Arial, sans-serif;
                display: flex;
                justify-content: center;
                align-items: center;
                height: 100vh;
                margin: 0;
                background: linear-gradient(135deg, #003B5C, #005B8C);
                color: white;
            }
            .loading {
                text-align: center;
            }
            .spinner {
                border: 4px solid #f3f3f3;
                border-top: 4px solid #FF6B35;
                border-radius: 50%;
                width: 50px;
                height: 50px;
                animation: spin 1s linear infinite;
                margin: 20px auto;
            }
            @keyframes spin {
                0% { transform: rotate(0deg); }
                100% { transform: rotate(360deg); }
            }
        </style>
    </head>
    <body>
        <div class="loading">
            <div class="spinner"></div>
            <h2>✅ Login bem-sucedido!</h2>
            <p>Redirecionando...</p>
            <p><a href="../index.php" style="color: #FF6B35;">Clique aqui se não for redirecionado automaticamente</a></p>
        </div>
    </body>
    </html>
    <?php
    exit;
}

// Mostrar formulário de login
require '../includes/header.php';
?>

<style>
    .auth-container {
        min-height: calc(100vh - 200px);
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 40px 20px;
        background: linear-gradient(135deg, rgba(0, 59, 92, 0.05), rgba(255, 107, 53, 0.05));
    }
    
    .auth-card {
        background: white;
        border-radius: 30px;
        padding: 50px;
        box-shadow: 0 15px 50px rgba(0, 0, 0, 0.1);
        max-width: 500px;
        width: 100%;
        animation: slideIn 0.5s ease;
    }
    
    .auth-header {
        text-align: center;
        margin-bottom: 40px;
    }
    
    .auth-icon {
        width: 100px;
        height: 100px;
        background: linear-gradient(135deg, var(--primary-orange), var(--secondary-orange));
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 3rem;
        margin: 0 auto 25px;
    }
    
    .auth-title {
        font-size: 2.2rem;
        font-weight: 800;
        color: var(--primary-blue);
        margin-bottom: 10px;
    }
    
    .auth-subtitle {
        color: #666;
        font-size: 1.1rem;
    }
    
    .form-floating {
        margin-bottom: 20px;
    }
    
    .form-floating input {
        border-radius: 15px;
        border: 2px solid #ddd;
        padding: 20px 15px;
        height: 60px;
        transition: all 0.3s ease;
    }
    
    .form-floating input:focus {
        border-color: var(--primary-orange);
        box-shadow: 0 0 0 0.2rem rgba(255, 107, 53, 0.25);
    }
    
    .form-floating label {
        padding: 20px 15px;
    }
    
    .btn-auth {
        background: linear-gradient(135deg, var(--primary-orange), var(--secondary-orange));
        color: white;
        padding: 18px;
        border-radius: 15px;
        font-weight: 700;
        font-size: 1.1rem;
        border: none;
        width: 100%;
        transition: all 0.3s ease;
        margin-top: 10px;
    }
    
    .btn-auth:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 30px rgba(255, 107, 53, 0.4);
    }
    
    .auth-footer {
        text-align: center;
        margin-top: 30px;
        padding-top: 30px;
        border-top: 1px solid #eee;
    }
    
    .auth-footer a {
        color: var(--primary-orange);
        font-weight: 600;
        text-decoration: none;
    }
    
    .auth-footer a:hover {
        text-decoration: underline;
    }
    
    @media (max-width: 768px) {
        .auth-card {
            padding: 35px 25px;
        }
        
        .auth-title {
            font-size: 1.8rem;
        }
        
        .auth-icon {
            width: 80px;
            height: 80px;
            font-size: 2.5rem;
        }
    }
</style>

<div class="auth-container">
    <div class="auth-card">
        <div class="auth-header">
            <div class="auth-icon">🔐</div>
            <h1 class="auth-title">Bem-vindo de Volta!</h1>
            <p class="auth-subtitle">Faça login para continuar</p>
        </div>
        
        <?php if (!empty($mensagemErro)): ?>
            <div class="alert alert-danger rounded-4 mb-4">
                <?php echo $mensagemErro; ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="" novalidate>
            <div class="form-floating">
                <input type="email" class="form-control" id="email" name="email" placeholder="name@example.com" required value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                <label for="email"><i class="bi bi-envelope-fill me-2"></i>Email</label>
            </div>
            
            <div class="form-floating">
                <input type="password" class="form-control" id="senha" name="senha" placeholder="Senha" required>
                <label for="senha"><i class="bi bi-lock-fill me-2"></i>Senha</label>
            </div>
            
            <button type="submit" class="btn-auth">
                <i class="bi bi-box-arrow-in-right me-2"></i>Entrar
            </button>
        </form>
        
        <div class="auth-footer">
            <p class="mb-0">Não tem uma conta? <a href="registrar.php">Registre-se aqui</a></p>
        </div>
    </div>
</div>

<?php require '../includes/footer.php'; ?>