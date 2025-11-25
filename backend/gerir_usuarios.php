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
        $tipoMensagem = "success";
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
        $tipoMensagem = "warning";
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
        $tipoMensagem = "success";
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
        animation: slideInDown 0.5s ease;
    }
    
    @keyframes slideInDown {
        from {
            opacity: 0;
            transform: translateY(-20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    .user-card {
        background: white;
        border-radius: 20px;
        padding: 30px;
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
        margin-bottom: 30px;
        transition: all 0.3s ease;
    }
    
    .user-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
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
    
    .btn-role:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
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
        <div class="alert alert-<?php echo $tipoMensagem; ?> alert-custom">
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
                            <form method="POST" class="d-inline" id="form-papel-<?php echo $user['id']; ?>">
                                <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                <input type="hidden" name="alterar_papel" value="1">
                                <input type="hidden" name="papel" id="papel-input-<?php echo $user['id']; ?>">
                                
                                <div class="btn-group" role="group">
                                    <button type="button" 
                                            onclick="confirmarAlteracaoPapel(<?php echo $user['id']; ?>, 'usuario', '<?php echo addslashes($user['nome']); ?>')"
                                            class="btn btn-role btn-sm" 
                                            style="background: #4facfe; color: white;"
                                            <?php echo $user['papel'] === 'usuario' ? 'disabled' : ''; ?>>
                                        👤 Usuário
                                    </button>
                                    <button type="button" 
                                            onclick="confirmarAlteracaoPapel(<?php echo $user['id']; ?>, 'editor', '<?php echo addslashes($user['nome']); ?>')"
                                            class="btn btn-role btn-sm" 
                                            style="background: #f093fb; color: white;"
                                            <?php echo $user['papel'] === 'editor' ? 'disabled' : ''; ?>>
                                        ✏️ Editor
                                    </button>
                                    <button type="button" 
                                            onclick="confirmarAlteracaoPapel(<?php echo $user['id']; ?>, 'admin', '<?php echo addslashes($user['nome']); ?>')"
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
                                <form method="POST" class="mt-3" id="form-suspender-<?php echo $user['id']; ?>">
                                    <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                    <input type="hidden" name="suspender" value="1">
                                    <label class="form-label fw-bold">Suspender por:</label>
                                    <div class="row g-2">
                                        <div class="col-md-8">
                                            <select name="duracao" class="form-select" required id="duracao-<?php echo $user['id']; ?>">
                                                <option value="1 day">1 dia</option>
                                                <option value="1 week">1 semana</option>
                                                <option value="1 month">1 mês</option>
                                                <option value="3 months">3 meses</option>
                                                <option value="6 months">6 meses</option>
                                                <option value="1 year">1 ano</option>
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <button type="button" 
                                                    onclick="confirmarSuspensao(<?php echo $user['id']; ?>, '<?php echo addslashes($user['nome']); ?>')" 
                                                    class="btn btn-danger w-100">
                                                🚫 Suspender
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            <?php else: ?>
                                <form method="POST" class="mt-3" id="form-reativar-<?php echo $user['id']; ?>">
                                    <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                    <input type="hidden" name="reativar" value="1">
                                    <button type="button" 
                                            onclick="confirmarReativacao(<?php echo $user['id']; ?>, '<?php echo addslashes($user['nome']); ?>')" 
                                            class="btn btn-success w-100">
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
function confirmarAlteracaoPapel(userId, novoPapel, nomeUsuario) {
    const papelFormatado = novoPapel === 'usuario' ? 'Usuário' : (novoPapel === 'editor' ? 'Editor' : 'Administrador');
    const icone = novoPapel === 'usuario' ? '👤' : (novoPapel === 'editor' ? '✏️' : '👑');
    
    const mensagem = `${icone} ALTERAR PAPEL DE USUÁRIO\n\n` +
                     `Tem certeza que deseja alterar o papel de:\n\n` +
                     `📌 Usuário: ${nomeUsuario}\n` +
                     `🔄 Novo papel: ${papelFormatado}\n\n` +
                     `Esta ação mudará as permissões do usuário no sistema.`;
    
    if (confirm(mensagem)) {
        document.getElementById('papel-input-' + userId).value = novoPapel;
        document.getElementById('form-papel-' + userId).submit();
    }
}

function confirmarSuspensao(userId, nomeUsuario) {
    const select = document.getElementById('duracao-' + userId);
    const duracao = select.options[select.selectedIndex].text;
    
    const mensagem = `🚫 SUSPENDER USUÁRIO\n\n` +
                     `Tem certeza que deseja suspender:\n\n` +
                     `📌 Usuário: ${nomeUsuario}\n` +
                     `⏰ Duração: ${duracao}\n\n` +
                     `O usuário não poderá acessar o sistema durante este período.`;
    
    if (confirm(mensagem)) {
        document.getElementById('form-suspender-' + userId).submit();
    }
}

function confirmarReativacao(userId, nomeUsuario) {
    const mensagem = `✅ REATIVAR USUÁRIO\n\n` +
                     `Tem certeza que deseja reativar:\n\n` +
                     `📌 Usuário: ${nomeUsuario}\n\n` +
                     `O usuário voltará a ter acesso completo ao sistema.`;
    
    if (confirm(mensagem)) {
        document.getElementById('form-reativar-' + userId).submit();
    }
}
</script>

<?php require '../includes/footer.php'; ?>