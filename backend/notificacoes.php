<?php
session_start();
require '../includes/header.php';
require_once 'criar_notificacao.php';

if (!usuarioLogado()) {
    header('Location: login.php');
    exit;
}

$usuarioId = $_SESSION['usuario_id'];

// Marcar notificações como lidas se solicitado
if (isset($_GET['marcar_lidas'])) {
    marcarTodasComoLidas($usuarioId);
    header('Location: notificacoes.php');
    exit;
}

// Marcar notificação específica como lida
if (isset($_GET['marcar_lida'])) {
    $notifId = (int)$_GET['marcar_lida'];
    marcarNotificacaoComoLida($notifId);
    header('Location: notificacoes.php');
    exit;
}

$notificacoes = obterNotificacoesUsuario($usuarioId);
$naoLidas = obterNotificacoesUsuario($usuarioId, true);
?>

<style>
    .notifications-header {
        background: linear-gradient(135deg, var(--primary-blue), var(--secondary-blue));
        color: white;
        padding: 40px 0;
        margin-bottom: 40px;
    }
    
    .notifications-title {
        font-size: 2.5rem;
        font-weight: 800;
        margin-bottom: 10px;
    }
    
    .notifications-stats {
        display: flex;
        gap: 30px;
        flex-wrap: wrap;
    }
    
    .stat-badge {
        background: rgba(255, 255, 255, 0.2);
        padding: 10px 20px;
        border-radius: 50px;
        font-weight: 600;
    }
    
    .notifications-container {
        background: white;
        border-radius: 25px;
        padding: 40px;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.08);
    }
    
    .notifications-actions {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 30px;
        flex-wrap: wrap;
        gap: 15px;
    }
    
    .search-box {
        flex: 1;
        min-width: 250px;
        position: relative;
    }
    
    .search-box input {
        width: 100%;
        padding: 12px 45px 12px 20px;
        border: 2px solid #ddd;
        border-radius: 50px;
        font-size: 1rem;
        transition: all 0.3s ease;
    }
    
    .search-box input:focus {
        border-color: var(--primary-orange);
        outline: none;
        box-shadow: 0 0 0 0.2rem rgba(255, 107, 53, 0.25);
    }
    
    .search-icon {
        position: absolute;
        right: 20px;
        top: 50%;
        transform: translateY(-50%);
        color: #888;
        font-size: 1.2rem;
    }
    
    .btn-mark-all {
        background: var(--primary-orange);
        color: white;
        padding: 12px 25px;
        border-radius: 50px;
        border: none;
        font-weight: 600;
        transition: all 0.3s ease;
        white-space: nowrap;
    }
    
    .btn-mark-all:hover {
        background: var(--secondary-orange);
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(255, 107, 53, 0.3);
    }
    
    .notification-item {
        display: flex;
        gap: 20px;
        padding: 25px;
        border-radius: 15px;
        margin-bottom: 15px;
        transition: all 0.3s ease;
        border-left: 4px solid transparent;
        background: #f8f9fa;
        cursor: pointer;
    }
    
    .notification-item:hover {
        background: white;
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
        transform: translateX(5px);
    }
    
    .notification-item.unread {
        background: #fff;
        border-left-color: var(--primary-orange);
        box-shadow: 0 3px 10px rgba(255, 107, 53, 0.1);
    }
    
    .notification-icon {
        width: 60px;
        height: 60px;
        border-radius: 15px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.8rem;
        flex-shrink: 0;
    }
    
    .icon-mention {
        background: linear-gradient(135deg, #667eea, #764ba2);
    }
    
    .icon-like {
        background: linear-gradient(135deg, #fa709a, #fee140);
    }
    
    .icon-dislike {
        background: linear-gradient(135deg, #ff6b6b, #ee5a6f);
    }
    
    .icon-reply {
        background: linear-gradient(135deg, #4facfe, #00f2fe);
    }
    
    .notification-content {
        flex: 1;
    }
    
    .notification-text {
        font-size: 1rem;
        color: #333;
        margin-bottom: 8px;
        line-height: 1.6;
    }
    
    .notification-text strong {
        color: var(--primary-blue);
        font-weight: 700;
    }
    
    .notification-post {
        color: var(--primary-orange);
        font-weight: 600;
        font-size: 0.95rem;
        display: inline-block;
        margin-bottom: 8px;
    }
    
    .notification-meta {
        display: flex;
        gap: 20px;
        font-size: 0.85rem;
        color: #888;
        flex-wrap: wrap;
    }
    
    .notification-time {
        display: flex;
        align-items: center;
        gap: 5px;
    }
    
    .notification-badge {
        background: var(--primary-orange);
        color: white;
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 600;
    }
    
    .empty-state {
        text-align: center;
        padding: 80px 20px;
    }
    
    .empty-icon {
        font-size: 5rem;
        margin-bottom: 20px;
    }
    
    @media (max-width: 768px) {
        .notifications-container {
            padding: 25px 20px;
        }
        
        .notifications-actions {
            flex-direction: column;
        }
        
        .search-box {
            width: 100%;
        }
        
        .btn-mark-all {
            width: 100%;
        }
        
        .notification-item {
            flex-direction: column;
            gap: 15px;
        }
        
        .notification-icon {
            width: 50px;
            height: 50px;
            font-size: 1.5rem;
        }
    }
</style>

<div class="notifications-header">
    <div class="container">
        <h1 class="notifications-title">🔔 Notificações</h1>
        <div class="notifications-stats">
            <span class="stat-badge">
                📬 Total: <?php echo count($notificacoes); ?>
            </span>
            <span class="stat-badge">
                ✨ Não Lidas: <?php echo count($naoLidas); ?>
            </span>
        </div>
    </div>
</div>

<main class="container pb-5">
    <div class="notifications-container">
        <div class="notifications-actions">
            <div class="search-box">
                <input type="text" id="searchInput" placeholder="🔍 Pesquisar notificações...">
                <i class="bi bi-search search-icon"></i>
            </div>
            
            <?php if (count($naoLidas) > 0): ?>
                <a href="?marcar_lidas=1" class="btn-mark-all">
                    <i class="bi bi-check-all"></i> Marcar Todas como Lidas
                </a>
            <?php endif; ?>
        </div>
        
        <div id="notificationsList">
            <?php if (empty($notificacoes)): ?>
                <div class="empty-state">
                    <div class="empty-icon">📭</div>
                    <h3>Nenhuma notificação ainda</h3>
                    <p class="text-muted">Você será notificado quando alguém interagir com seus comentários!</p>
                </div>
            <?php else: ?>
                <?php foreach ($notificacoes as $notif):
                    $icone = '📬';
                    $texto = '';
                    $classe = 'icon-mention';
                    
                    switch ($notif['tipo']) {
                        case 'mention':
                            $icone = '📣';
                            $texto = "<strong>{$notif['usuario_origem_nome']}</strong> mencionou você em um comentário";
                            $classe = 'icon-mention';
                            break;
                        case 'like':
                            $icone = '❤️';
                            $texto = "<strong>{$notif['usuario_origem_nome']}</strong> curtiu seu comentário";
                            $classe = 'icon-like';
                            break;
                        case 'dislike':
                            $icone = '👎';
                            $texto = "<strong>{$notif['usuario_origem_nome']}</strong> não curtiu seu comentário";
                            $classe = 'icon-dislike';
                            break;
                        case 'reply':
                            $icone = '💬';
                            $texto = "<strong>{$notif['usuario_origem_nome']}</strong> respondeu ao seu comentário";
                            $classe = 'icon-reply';
                            break;
                    }
                    
                    $dataFormatada = date('d/m/Y', $notif['data']);
                    $horaFormatada = date('H:i', $notif['data']);
                    $tempoDecorrido = '';
                    
                    $diferenca = time() - $notif['data'];
                    if ($diferenca < 60) {
                        $tempoDecorrido = 'Agora mesmo';
                    } elseif ($diferenca < 3600) {
                        $minutos = floor($diferenca / 60);
                        $tempoDecorrido = "Há {$minutos} " . ($minutos == 1 ? 'minuto' : 'minutos');
                    } elseif ($diferenca < 86400) {
                        $horas = floor($diferenca / 3600);
                        $tempoDecorrido = "Há {$horas} " . ($horas == 1 ? 'hora' : 'horas');
                    } else {
                        $dias = floor($diferenca / 86400);
                        $tempoDecorrido = "Há {$dias} " . ($dias == 1 ? 'dia' : 'dias');
                    }
                ?>
                    <div class="notification-item <?php echo !$notif['lida'] ? 'unread' : ''; ?>" 
                         data-search="<?php echo strtolower($notif['usuario_origem_nome'] . ' ' . $notif['post_titulo']); ?>"
                         onclick="window.location.href='../artigo.php?id=<?php echo $notif['post_id']; ?>#comment-<?php echo $notif['comentario_id']; ?>'">
                        <div class="notification-icon <?php echo $classe; ?>">
                            <?php echo $icone; ?>
                        </div>
                        <div class="notification-content">
                            <p class="notification-text"><?php echo $texto; ?></p>
                            <div class="notification-post">
                                <i class="bi bi-file-text"></i> <?php echo htmlspecialchars($notif['post_titulo']); ?>
                            </div>
                            <div class="notification-meta">
                                <span class="notification-time">
                                    <i class="bi bi-clock"></i> <?php echo $tempoDecorrido; ?>
                                </span>
                                <span>
                                    <i class="bi bi-calendar"></i> <?php echo $dataFormatada; ?> às <?php echo $horaFormatada; ?>
                                </span>
                                <?php if (!$notif['lida']): ?>
                                    <span class="notification-badge">Novo</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</main>

<script>
// Pesquisa em tempo real
const searchInput = document.getElementById('searchInput');
const notificationsList = document.getElementById('notificationsList');

if (searchInput) {
    searchInput.addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase();
        const notifications = notificationsList.querySelectorAll('.notification-item');
        
        notifications.forEach(notification => {
            const searchData = notification.getAttribute('data-search');
            if (searchData.includes(searchTerm)) {
                notification.style.display = 'flex';
            } else {
                notification.style.display = 'none';
            }
        });
    });
}
</script>

<?php require '../includes/footer.php'; ?>