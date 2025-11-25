<?php
session_start();
require '../includes/header.php';

if (!isset($_SESSION['papel']) || $_SESSION['papel'] !== 'admin') {
    header('Location: ../acesso_negado.php');
    exit;
}

// Processar ações
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuarios = json_decode(file_get_contents('../data/usuarios.json'), true);
    
    if (isset($_POST['alterar_papel'])) {
        $userId = (int)$_POST['user_id'];
        $novoPapel = $_POST['papel'];
        
        foreach ($usuarios as &$user) {
            if ($user['id'] === $userId) {
                $user['papel'] = $novoPapel;
                break;
            }
        }
        
        file_put_contents('../data/usuarios.json', json_encode($usuarios, JSON_PRETTY_PRINT));
        $mensagem = "✅ Papel do usuário atualizado com sucesso!";
    }
    
    if (isset($_POST['suspender'])) {
        $userId = (int)$_POST['user_id'];
        $duracao = $_POST['duracao'];
        
        $dataExpiracao = date('Y-m-d H:i:s', strtotime("+$duracao"));
        
        foreach ($usuarios as &$user) {
            if ($user['id'] === $userId) {
                $user['suspenso_ate'] = $dataExpiracao;
                break;
            }
        }
        
        file_put_contents('../data/usuarios.json', json_encode($usuarios, JSON_PRETTY_PRINT));
        $mensagem = "⚠️ Usuário suspenso até " . date('d/m/Y H:i', strtotime($dataExpiracao));
    }
    
    if (isset($_POST['reativar'])) {
        $userId = (int)$_POST['user_id'];
        
        foreach ($usuarios as &$user) {
            if ($user['id'] === $userId) {
                unset($user['suspenso_ate']);
                break;
            }
        }
        
        file_put_contents('../data/usuarios.json', json_encode($usuarios, JSON_PRETTY_PRINT));
        $mensagem = "✅ Usuário reativado com sucesso!";
    }
}

$usuarios = json_decode(file_get_contents('../data/usuarios.json'), true) ?? [];
?>

<style>
    .user-header {
        background: linear-gradient(135deg, var(--primary-blue), var(--secondary-blue));
        color: white;
        padding: 40px 0;
        margin-bottom: 40px;
    }
    
    .alert-custom {
        border-radius: 15px;
        padding: 20px;
        border: none;
        box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1);
    }
    
    .user-card {
        background: white;
        border-radius: 20px;
        padding: 30px;
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
        margin-bottom: 30px;
    }
    
    .user-avatar {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        object-fit: cover;
        border: 3px solid var(--primary-orange);
    }
    
    .badge-role {
        padding: 8px 15px;
        border-radius: 20px;
        font-weight: 600;
        font-size: 0.85rem;
    }
    
    .badge-admin {
        background: linear-gradient(135deg, #667eea, #764ba2);
        color: white;
    }
    
    .badge-editor {
        background: linear-gradient(135deg, #f093fb, #f5576c);
        color: white;
    }
    
    .badge-usuario {
        background: linear-gradient(135deg, #4facfe, #00f2fe);
        color: white;
    }
    
    .badge-suspended {
        background: #e74c3c;
        color: white;
        padding: 8px 15px;
        border-radius: 20px;
        font-size: 0.85rem;
    }
    
    .btn-role {
        padding: 10px 20px;
        border-radius: 10px;
        font-weight: 600;
        border: none;
        transition: all 0.3s ease;
        margin: 5px;
    }
    
    @media (max-width: 768px) {
        .user-card {
            padding: 20px;
        }
        
        .btn-role {
            width: 100%;
            margin: 5px 0;
        }
    }
</style>

<div class="user-header">
    <div class="container">
        <h1 class="display-5 fw-bold">👥 Gestão de Usuários</h1>
        <p class="mb-0 opacity-75">Gerencie papéis e suspensões de usuários</p>
    </div>
</div>

<main class="container pb-5">
    <?php if (isset($mensagem)): ?>
        <div class="alert alert-success alert-custom">
            <?php echo $mensagem; ?>
        </div>
    <?php endif; ?>
    
    <div class="row g-4">
        <?php foreach ($usuarios as $user): 
            $suspenso = isset($user['suspenso_ate']) && strtotime($user['suspenso_ate']) > time();
        ?>
            <div class="col-lg-6">
                <div class="user-card">
                    <div class="d-flex align-items-start gap-3 mb-3">
                        <img src="<?php echo !empty($user['imagem']) ? '../' . $user['imagem'] : '../assets/img/users/default.jpg'; ?>" 
                             alt="Avatar" class="user-avatar">
                        <div class="flex-grow-1">
                            <h5 class="mb-1 fw-bold"><?php echo htmlspecialchars($user['nome']); ?></h5>
                            <p class="mb-2 text-muted">
                                <i class="bi bi-envelope"></i> <?php echo htmlspecialchars($user['email']); ?>
                            </p>
                            <div class="d-flex gap-2 flex-wrap">
                                <span class="badge-role badge-<?php echo $user['papel']; ?>">
                                    <?php 
                                    $icons = ['admin' => '👑', 'editor' => '✏️', 'usuario' => '👤'];
                                    echo $icons[$user['papel']] . ' ' . ucfirst($user['papel']); 
                                    ?>
                                </span>
                                <?php if ($suspenso): ?>
                                    <span class="badge-suspended">
                                        🚫 Suspenso até <?php echo date('d/m/Y', strtotime($user['suspenso_ate'])); ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    
                    <?php if ($user['id'] !== $_SESSION['usuario_id']): ?>
                        <hr>
                        <div class="mt-3">
                            <h6 class="mb-3 fw-bold">Ações:</h6>
                            
                            <!-- Alterar Papel -->
                            <form method="POST" class="d-inline" onsubmit="return confirmarAlteracaoPapel('<?php echo htmlspecialchars($user['nome']); ?>', event.submitter.value)">
                                <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                <input type="hidden" name="alterar_papel" value="1">
                                <div class="btn-group" role="group">
                                    <button type="submit" name="papel" value="usuario" 
                                            class="btn btn-role btn-sm" 
                                            style="background: #4facfe; color: white;"
                                            <?php echo $user['papel'] === 'usuario' ? 'disabled' : ''; ?>>
                                        👤 Usuário
                                    </button>
                                    <button type="submit" name="papel" value="editor" 
                                            class="btn btn-role btn-sm" 
                                            style="background: #f093fb; color: white;"
                                            <?php echo $user['papel'] === 'editor' ? 'disabled' : ''; ?>>
                                        ✏️ Editor
                                    </button>
                                    <button type="submit" name="papel" value="admin" 
                                            class="btn btn-role btn-sm" 
                                            style="background: #667eea; color: white;"
                                            <?php echo $user['papel'] === 'admin' ? 'disabled' : ''; ?>>
                                        👑 Admin
                                    </button>
                                </div>
                            </form>
                            
                            <hr class="my-3">
                            
                            <!-- Suspender/Reativar -->
                            <?php if (!$suspenso): ?>
                                <form method="POST" class="mt-3" onsubmit="return confirmarSuspensao('<?php echo htmlspecialchars($user['nome']); ?>', this.duracao.options[this.duracao.selectedIndex].text)">
                                    <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                    <input type="hidden" name="suspender" value="1">
                                    <label class="form-label fw-bold">Suspender por:</label>
                                    <div class="row g-2">
                                        <div class="col-md-8">
                                            <select name="duracao" class="form-select" required>
                                                <option value="1 day">1 dia</option>
                                                <option value="1 week">1 semana</option>
                                                <option value="1 month">1 mês</option>
                                                <option value="3 months">3 meses</option>
                                                <option value="6 months">6 meses</option>
                                                <option value="9 months">9 meses</option>
                                                <option value="1 year">1 ano</option>
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <button type="submit" class="btn btn-danger w-100">
                                                🚫 Suspender
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            <?php else: ?>
                                <form method="POST" class="mt-3" onsubmit="return confirmarReativacao('<?php echo htmlspecialchars($user['nome']); ?>')">
                                    <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                    <input type="hidden" name="reativar" value="1">
                                    <button type="submit" class="btn btn-success w-100">
                                        ✅ Reativar Usuário
                                    </button>
                                </form>
                            <?php endif; ?>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info mt-3">
                            ℹ️ Este é o seu próprio perfil
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</main>

<script>
function confirmarAlteracaoPapel(nomeUsuario, novoPapel) {
    const papelFormatado = novoPapel === 'usuario' ? 'Usuário' : (novoPapel === 'editor' ? 'Editor' : 'Administrador');
    const mensagem = `⚠️ Tem certeza que deseja alterar o papel de "${nomeUsuario}" para "${papelFormatado}"?\n\nEsta ação mudará as permissões do usuário no sistema.`;
    return confirm(mensagem);
}

function confirmarSuspensao(nomeUsuario, duracao) {
    const mensagem = `⚠️ Tem certeza que deseja suspender "${nomeUsuario}" por ${duracao}?\n\nO usuário não poderá acessar o sistema durante este período.`;
    return confirm(mensagem);
}

function confirmarReativacao(nomeUsuario) {
    const mensagem = `✅ Tem certeza que deseja reativar a conta de "${nomeUsuario}"?\n\nO usuário voltará a ter acesso completo ao sistema.`;
    return confirm(mensagem);
}
</script>

<?php require '../includes/footer.php'; ?>