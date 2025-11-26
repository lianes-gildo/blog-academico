<?php
session_start();
require '../includes/header.php';

if (!usuarioLogado()) {
    header('Location: login.php');
    exit;
}

$usuarioId = $_SESSION['usuario_id'];

// Obter notificações
$arquivoNotif = '../data/notificacoes.json';
$todasNotificacoes = [];
$naoLidas = [];

if (file_exists($arquivoNotif)) {
    $notificacoes = json_decode(file_get_contents($arquivoNotif), true);
    if (is_array($notificacoes)) {
        foreach ($notificacoes as $n) {
            if ($n['usuario_destino_id'] == $usuarioId) {
                $todasNotificacoes[] = $n;
                if (!isset($n['lida']) || $n['lida'] === false) {
                    $naoLidas[] = $n;
                }
            }
        }
    }
}

usort($todasNotificacoes, function($a, $b) {
    return $b['data'] - $a['data'];
});
?>

<style>
    .notifications-header {
        background: linear-gradient(135deg, var(--primary-blue), var(--secondary-blue));
        color: white;
        padding: 50px 0 40px;
        margin-bottom: 40px;
    }
    
    .notifications-title {
        font-size: 2.8rem;
        font-weight: 800;
        margin-bottom: 15px;
        animation: fadeInUp 0.6s ease;
    }
    
    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }
    
    .notifications-stats {
        display: flex;
        gap: 30px;
        flex-wrap: wrap;
        animation: fadeInUp 0.8s ease;
    }
    
    .stat-badge {
        background: rgba(255, 255, 255, 0.2);
        padding: 12px 25px;
        border-radius: 50px;
        font-weight: 600;
        font-size: 1.05rem;
        backdrop-filter: blur(10px);
    }
    
    .notifications-container {
        background: white;
        border-radius: 25px;
        padding: 40px;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.08);
        animation: slideInUp 0.6s ease;
    }
    
    .search-box {
        flex: 1;
        min-width: 250px;
        max-width: 500px;
        position: relative;
    }
    
    .search-box input {
        width: 100%;
        padding: 15px 50px 15px 20px;
        border: 2px solid #e0e0e0;
        border-radius: 50px;
        font-size: 1rem;
        transition: all 0.3s ease;
        background: #f8f9fa;
    }
    
    .search-box input:focus {
        border-color: var(--primary-orange);
        outline: none;
        box-shadow: 0 0 0 0.2rem rgba(255, 107, 53, 0.25);
        background: white;
    }
    
    .search-icon {
        position: absolute;
        right: 20px;
        top: 50%;
        transform: translateY(-50%);
        color: #888;
        font-size: 1.3rem;
    }
    
    .btn-mark-all {
        background: linear-gradient(135deg, var(--primary-orange), var(--secondary-orange));
        color: white;
        padding: 15px 30px;
        border-radius: 50px;
        border: none;
        font-weight: 600;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        gap: 10px;
        cursor: pointer;
    }
    
    .btn-mark-all:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 20px rgba(255, 107, 53, 0.4);
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
        position: relative;
    }
    
    .notification-item:hover {
        background: white;
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
        transform: translateX(5px);
    }
    
    .notification-item.unread {
        background: #fff;
        border-left-color: var(--primary-orange);
        box-shadow: 0 3px 10px rgba(255, 107, 53, 0.15);
    }
    
    .notification-icon {
        width: 65px;
        height: 65px;
        border-radius: 15px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2rem;
        flex-shrink: 0;
    }
    
    .icon-mention { background: linear-gradient(135deg, #667eea, #764ba2); }
    .icon-like { background: linear-gradient(135deg, #fa709a, #fee140); }
    .icon-dislike { background: linear-gradient(135deg, #ff6b6b, #ee5a6f); }
    .icon-reply { background: linear-gradient(135deg, #4facfe, #00f2fe); }
    
    .empty-state {
        text-align: center;
        padding: 100px 20px;
    }
    
    .empty-icon {
        font-size: 6rem;
        margin-bottom: 25px;
    }
    
    @media (max-width: 768px) {
        .notifications-container { padding: 25px 20px; }
        .notification-item { flex-direction: column; }
    }
</style>

<div class="notifications-header">
    <div class="container">
        <h1 class="notifications-title">🔔 Central de Notificações</h1>
        <div class="notifications-stats">
            <span class="stat-badge">📬 Total: <span id="total-count"><?php echo count($todasNotificacoes); ?></span></span>
            <span class="stat-badge">✨ Não Lidas: <span id="unread-count"><?php echo count($naoLidas); ?></span></span>
        </div>
    </div>
</div>

<main class="container pb-5">
    <div class="notifications-container">
        <div class="notifications-actions" style="display: flex; justify-content: space-between; margin-bottom: 30px; flex-wrap: wrap; gap: 15px;">
            <div class="search-box">
                <input type="text" id="searchInput" placeholder="🔍 Pesquisar notificações..." autocomplete="off">
                <i class="bi bi-search search-icon"></i>
            </div>
            
            <?php if (count($naoLidas) > 0): ?>
                <button onclick="marcarTodasLidas()" class="btn-mark-all" id="btnMarkAll">
                    <i class="bi bi-check-all"></i> 
                    <span>Marcar Todas como Lidas</span>
                </button>
            <?php endif; ?>
        </div>
        
        <div id="notificationsList">
            <?php if (empty($todasNotificacoes)): ?>
                <div class="empty-state">
                    <div class="empty-icon">📭</div>
                    <h3>Nenhuma notificação ainda</h3>
                    <p class="text-muted">Você será notificado quando alguém interagir com seus comentários!</p>
                </div>
            <?php else: ?>
                <?php foreach ($todasNotificacoes as $notif):
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
                         data-notif-id="<?php echo $notif['id']; ?>"
                         onclick="abrirNotificacao(<?php echo $notif['id']; ?>, <?php echo $notif['post_id']; ?>, <?php echo $notif['comentario_id']; ?>, <?php echo !$notif['lida'] ? 'true' : 'false'; ?>)">
                        <div class="notification-icon <?php echo $classe; ?>"><?php echo $icone; ?></div>
                        <div class="notification-content">
                            <p class="notification-text" style="font-size: 1.05rem; margin-bottom: 10px;"><?php echo $texto; ?></p>
                            <div style="color: var(--primary-orange); font-weight: 600; margin-bottom: 10px;">
                                <i class="bi bi-file-text-fill"></i> <?php echo htmlspecialchars($notif['post_titulo']); ?>
                            </div>
                            <div style="font-size: 0.9rem; color: #888;">
                                <i class="bi bi-clock-fill"></i> <?php echo $tempoDecorrido; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</main>

<script>
// Marcar todas as notificações como lidas (SEM RECARREGAR)
function marcarTodasLidas() {
    fetch('../backend/api/mark_notifications.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'action=mark_all'
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            // Remover classe unread de todas
            document.querySelectorAll('.notification-item.unread').forEach(item => {
                item.classList.remove('unread');
            });
            
            // Atualizar contadores
            document.getElementById('unread-count').textContent = '0';
            
            // Esconder botão
            const btn = document.getElementById('btnMarkAll');
            if (btn) btn.style.display = 'none';
            
            // Atualizar badges do header
            const mobileBadge = document.getElementById('mobile-badge');
            const desktopBadge = document.getElementById('desktop-badge');
            if (mobileBadge) mobileBadge.style.display = 'none';
            if (desktopBadge) desktopBadge.style.display = 'none';
        }
    });
}

// Marcar uma notificação como lida e abrir
function abrirNotificacao(notifId, postId, comentarioId, isUnread) {
    if (isUnread) {
        fetch('../backend/api/mark_notifications.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'action=mark_one&notif_id=' + notifId
        });
    }
    
    window.location.href = '../artigo.php?id=' + postId + '#comment-' + comentarioId;
}

// Pesquisa
const searchInput = document.getElementById('searchInput');
if (searchInput) {
    searchInput.addEventListener('input', function() {
        const term = this.value.toLowerCase();
        document.querySelectorAll('.notification-item').forEach(item => {
            const text = item.textContent.toLowerCase();
            item.style.display = text.includes(term) ? 'flex' : 'none';
        });
    });
}
</script>

<?php require '../includes/footer.php'; ?>