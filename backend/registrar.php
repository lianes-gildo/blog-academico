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
$registroSucesso = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $senha = $_POST['senha'] ?? '';
    $confirma = $_POST['confirma'] ?? '';

    // Validações
    if (empty($nome) || empty($email) || empty($senha) || empty($confirma)) {
        $mensagemErro = "❌ Por favor, preencha todos os campos!";
    } elseif ($senha !== $confirma) {
        $mensagemErro = "❌ As senhas não coincidem!";
    } elseif (strlen($senha) < 6) {
        $mensagemErro = "❌ A senha deve ter pelo menos 6 caracteres!";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $mensagemErro = "❌ Email inválido!";
    } else {
        $arquivoUsuarios = __DIR__ . '/../data/usuarios.json';
        
        if (!file_exists($arquivoUsuarios)) {
            // Criar arquivo se não existir
            file_put_contents($arquivoUsuarios, '[]');
        }
        
        $usuarios = json_decode(file_get_contents($arquivoUsuarios), true);
        
        if (!is_array($usuarios)) {
            $usuarios = [];
        }
        
        // Verificar se email já existe
        $emailExiste = false;
        foreach ($usuarios as $u) {
            if ($u['email'] === $email) {
                $emailExiste = true;
                break;
            }
        }
        
        if ($emailExiste) {
            $mensagemErro = "❌ Este email já está registrado!";
        } else {
            // Criar novo usuário
            $novoId = empty($usuarios) ? 1 : max(array_column($usuarios, 'id')) + 1;

            $novoUsuario = [
                'id' => $novoId,
                'nome' => $nome,
                'email' => $email,
                'senha' => password_hash($senha, PASSWORD_DEFAULT),
                'papel' => 'usuario',
                'imagem' => 'assets/img/users/default.jpg'
            ];

            $usuarios[] = $novoUsuario;
            
            // Salvar no arquivo
            if (file_put_contents($arquivoUsuarios, json_encode($usuarios, JSON_PRETTY_PRINT))) {
                // Criar sessão
                $_SESSION['usuario_id'] = $novoId;
                $_SESSION['nome'] = $nome;
                $_SESSION['papel'] = 'usuario';
                $_SESSION['imagem'] = $novoUsuario['imagem'];
                
                $registroSucesso = true;
            } else {
                $mensagemErro = "❌ Erro ao salvar usuário. Verifique as permissões da pasta data/";
            }
        }
    }
}

// Se registro foi bem-sucedido, mostrar página de redirecionamento
if ($registroSucesso) {
    ?>
    <!DOCTYPE html>
    <html lang="pt-MZ">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Registro Bem-sucedido</title>
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
            <h2>✅ Conta criada com sucesso!</h2>
            <p>Redirecionando...</p>
            <p><a href="../index.php" style="color: #FF6B35;">Clique aqui se não for redirecionado automaticamente</a></p>
        </div>
    </body>
    </html>
    <?php
    exit;
}

// Mostrar formulário de registro
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
        max-width: 550px;
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
        background: linear-gradient(135deg, var(--primary-blue), var(--secondary-blue));
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
        border-color: var(--primary-blue);
        box-shadow: 0 0 0 0.2rem rgba(0, 59, 92, 0.25);
    }
    
    .form-floating label {
        padding: 20px 15px;
    }
    
    .btn-auth {
        background: linear-gradient(135deg, var(--primary-blue), var(--secondary-blue));
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
        box-shadow: 0 10px 30px rgba(0, 59, 92, 0.4);
    }
    
    .auth-footer {
        text-align: center;
        margin-top: 30px;
        padding-top: 30px;
        border-top: 1px solid #eee;
    }
    
    .auth-footer a {
        color: var(--primary-blue);
        font-weight: 600;
        text-decoration: none;
    }
    
    .auth-footer a:hover {
        text-decoration: underline;
    }
    
    .password-strength {
        height: 4px;
        background: #ddd;
        border-radius: 2px;
        margin-top: 10px;
        overflow: hidden;
    }
    
    .password-strength-bar {
        height: 100%;
        width: 0;
        transition: all 0.3s ease;
    }
    
    .strength-weak { background: #e74c3c; width: 33%; }
    .strength-medium { background: #f39c12; width: 66%; }
    .strength-strong { background: #27ae60; width: 100%; }
    
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
            <div class="auth-icon">✨</div>
            <h1 class="auth-title">Criar Conta</h1>
            <p class="auth-subtitle">Junte-se à comunidade acadêmica</p>
        </div>
        
        <?php if (!empty($mensagemErro)): ?>
            <div class="alert alert-danger rounded-4 mb-4">
                <?php echo $mensagemErro; ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="" novalidate>
            <div class="form-floating">
                <input type="text" class="form-control" id="nome" name="nome" placeholder="Nome completo" required value="<?php echo isset($_POST['nome']) ? htmlspecialchars($_POST['nome']) : ''; ?>">
                <label for="nome"><i class="bi bi-person-fill me-2"></i>Nome Completo</label>
            </div>
            
            <div class="form-floating">
                <input type="email" class="form-control" id="email" name="email" placeholder="name@example.com" required value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                <label for="email"><i class="bi bi-envelope-fill me-2"></i>Email</label>
            </div>
            
            <div class="form-floating">
                <input type="password" class="form-control" id="senha" name="senha" placeholder="Senha" required minlength="6">
                <label for="senha"><i class="bi bi-lock-fill me-2"></i>Senha</label>
                <div class="password-strength">
                    <div class="password-strength-bar" id="strength-bar"></div>
                </div>
            </div>
            
            <div class="form-floating">
                <input type="password" class="form-control" id="confirma" name="confirma" placeholder="Confirmar senha" required minlength="6">
                <label for="confirma"><i class="bi bi-lock-fill me-2"></i>Confirmar Senha</label>
            </div>
            
            <button type="submit" class="btn-auth">
                <i class="bi bi-person-plus-fill me-2"></i>Criar Conta
            </button>
        </form>
        
        <div class="auth-footer">
            <p class="mb-0">Já tem uma conta? <a href="login.php">Faça login aqui</a></p>
        </div>
    </div>
</div>

<script>
const senhaInput = document.getElementById('senha');
const strengthBar = document.getElementById('strength-bar');

senhaInput.addEventListener('input', function() {
    const senha = this.value;
    const length = senha.length;
    
    strengthBar.className = 'password-strength-bar';
    
    if (length === 0) {
        strengthBar.style.width = '0';
    } else if (length < 6) {
        strengthBar.classList.add('strength-weak');
    } else if (length < 10) {
        strengthBar.classList.add('strength-medium');
    } else {
        strengthBar.classList.add('strength-strong');
    }
});
</script>

<?php require '../includes/footer.php'; ?>