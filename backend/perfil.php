<?php
session_start();
require '../includes/header.php';

if (!usuarioLogado()) {
    header('Location: login.php');
    exit;
}

$usuarioId = $_SESSION['usuario_id'];
$usuarios = json_decode(file_get_contents('../data/usuarios.json'), true);

$usuario = null;
foreach ($usuarios as $u) {
    if ($u['id'] == $usuarioId) {
        $usuario = $u;
        break;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['apagar_conta'])) {
        $usuarios = array_filter($usuarios, fn($u) => $u['id'] != $usuarioId);
        $usuarios = array_values($usuarios);
        file_put_contents('../data/usuarios.json', json_encode($usuarios, JSON_PRETTY_PRINT));
        session_destroy();
        header('Location: ../index.php');
        exit;
    }
    
    $novoNome = trim($_POST['nome']);
    $senhaAntiga = $_POST['senha_antiga'] ?? '';
    $senhaNova = $_POST['senha_nova'] ?? '';

    if (!empty($senhaNova) && !password_verify($senhaAntiga, $usuario['senha'])) {
        $erro = "❌ Senha antiga incorreta!";
    } else {
        if (!empty($senhaNova)) {
            $usuario['senha'] = password_hash($senhaNova, PASSWORD_DEFAULT);
        }
        $usuario['nome'] = $novoNome;

        if (isset($_FILES['foto']) && $_FILES['foto']['error'] === 0) {
            $ext = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
            $nomeFoto = "user_" . $usuarioId . "." . $ext;
            $caminhoDestino = "../assets/img/users/" . $nomeFoto;

            if (move_uploaded_file($_FILES['foto']['tmp_name'], $caminhoDestino)) {
                $usuario['imagem'] = "assets/img/users/" . $nomeFoto;
            }
        }

        foreach ($usuarios as &$u) {
            if ($u['id'] == $usuarioId) {
                $u = $usuario;
                break;
            }
        }
        file_put_contents('../data/usuarios.json', json_encode($usuarios, JSON_PRETTY_PRINT));

        $_SESSION['nome'] = $novoNome;
        $_SESSION['imagem'] = $usuario['imagem'];

        $sucesso = "✅ Perfil atualizado com sucesso!";
    }
}
?>

<style>
    .profile-header {
        background: linear-gradient(135deg, var(--primary-blue), var(--secondary-blue));
        color: white;
        padding: 60px 0 40px;
        margin-bottom: 40px;
    }
    
    .profile-avatar-large {
        width: 150px;
        height: 150px;
        border-radius: 50%;
        border: 5px solid white;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        object-fit: cover;
        margin-bottom: 20px;
    }
    
    .profile-name {
        font-size: 2.5rem;
        font-weight: 800;
        margin-bottom: 10px;
    }
    
    .profile-role {
        display: inline-block;
        padding: 10px 25px;
        background: rgba(255, 255, 255, 0.2);
        border-radius: 50px;
        font-weight: 600;
    }
    
    .profile-card {
        background: white;
        border-radius: 25px;
        padding: 40px;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.08);
        margin-bottom: 30px;
    }
    
    .section-title-profile {
        font-size: 1.8rem;
        font-weight: 700;
        color: var(--primary-blue);
        margin-bottom: 25px;
        display: flex;
        align-items: center;
        gap: 15px;
    }
    
    .danger-zone {
        background: #fff5f5;
        border: 2px solid #e74c3c;
        border-radius: 20px;
        padding: 30px;
    }
    
    .btn-danger-custom {
        background: #e74c3c;
        color: white;
        padding: 15px 30px;
        border-radius: 12px;
        font-weight: 600;
        border: none;
        transition: all 0.3s ease;
    }
    
    .btn-danger-custom:hover {
        background: #c0392b;
        transform: translateY(-3px);
        box-shadow: 0 8px 20px rgba(231, 76, 60, 0.4);
    }
    
    @media (max-width: 768px) {
        .profile-header {
            padding: 40px 0 30px;
        }
        
        .profile-avatar-large {
            width: 120px;
            height: 120px;
        }
        
        .profile-name {
            font-size: 1.8rem;
        }
        
        .profile-card {
            padding: 25px 20px;
        }
    }
</style>

<div class="profile-header">
    <div class="container text-center">
        <img src="<?php echo !empty($usuario['imagem']) ? '../' . $usuario['imagem'] : '../assets/img/users/default.jpg'; ?>" 
             alt="Avatar" class="profile-avatar-large">
        <h1 class="profile-name"><?php echo htmlspecialchars($usuario['nome']); ?></h1>
        <span class="profile-role">
            <?php 
            $icons = ['admin' => '👑', 'editor' => '✏️', 'usuario' => '👤'];
            echo $icons[$usuario['papel']] . ' ' . ucfirst($usuario['papel']); 
            ?>
        </span>
    </div>
</div>

<main class="container pb-5">
    <?php if (isset($sucesso)): ?>
        <div class="alert alert-success rounded-4">
            <?php echo $sucesso; ?>
        </div>
    <?php endif; ?>
    
    <?php if (isset($erro)): ?>
        <div class="alert alert-danger rounded-4">
            <?php echo $erro; ?>
        </div>
    <?php endif; ?>
    
    <div class="row">
        <div class="col-lg-8 mx-auto">
            <div class="profile-card">
                <h2 class="section-title-profile">
                    <i class="bi bi-person-circle"></i>
                    Editar Perfil
                </h2>
                
                <form method="POST" enctype="multipart/form-data">
                    <div class="mb-4">
                        <label for="nome" class="form-label fw-bold">
                            <i class="bi bi-pencil me-2"></i>Nome Completo
                        </label>
                        <input type="text" class="form-control" id="nome" name="nome" 
                               value="<?php echo htmlspecialchars($usuario['nome']); ?>" required>
                    </div>
                    
                    <div class="mb-4">
                        <label class="form-label fw-bold">
                            <i class="bi bi-envelope me-2"></i>Email
                        </label>
                        <input type="email" class="form-control" 
                               value="<?php echo htmlspecialchars($usuario['email']); ?>" disabled>
                        <small class="text-muted">O email não pode ser alterado</small>
                    </div>
                    
                    <hr class="my-4">
                    
                    <h5 class="mb-3 fw-bold">🔒 Alterar Senha</h5>
                    
                    <div class="mb-3">
                        <label for="senha_antiga" class="form-label">Senha Antiga</label>
                        <input type="password" class="form-control" id="senha_antiga" name="senha_antiga"
                               placeholder="Deixe vazio para manter a senha atual">
                    </div>
                    
                    <div class="mb-4">
                        <label for="senha_nova" class="form-label">Nova Senha</label>
                        <input type="password" class="form-control" id="senha_nova" name="senha_nova"
                               placeholder="Digite a nova senha" minlength="6">
                    </div>
                    
                    <hr class="my-4">
                    
                    <h5 class="mb-3 fw-bold">📷 Foto de Perfil</h5>
                    
                    <div class="mb-4">
                        <input type="file" class="form-control" id="foto" name="foto" accept="image/*">
                        <small class="text-muted">Formatos aceitos: JPG, PNG, GIF</small>
                    </div>
                    
                    <button type="submit" class="btn btn-primary-custom w-100">
                        <i class="bi bi-check-circle-fill"></i> Salvar Alterações
                    </button>
                </form>
            </div>
            
            <div class="danger-zone">
                <h3 class="text-danger mb-3">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                    Zona de Perigo
                </h3>
                <p class="mb-4">Ao apagar sua conta, todos os seus dados serão permanentemente removidos. Esta ação não pode ser desfeita.</p>
                
                <form method="POST" onsubmit="return confirm('⚠️ Tem certeza que deseja apagar sua conta? Esta ação é irreversível!')">
                    <input type="hidden" name="apagar_conta" value="1">
                    <button type="submit" class="btn-danger-custom">
                        <i class="bi bi-trash-fill"></i> Apagar Minha Conta
                    </button>
                </form>
            </div>
        </div>
    </div>
</main>

<?php require '../includes/footer.php'; ?>