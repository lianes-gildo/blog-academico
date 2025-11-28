<?php
session_start();
require '../includes/header.php';

if (!usuarioLogado()) {
    header('Location: login.php');
    exit;
}

$usuarioId = $_SESSION['usuario_id'];

// Obter notificações
require_once 'criar_notificacao.php';
$todasNotificacoes = obterNotificacoesUsuario($usuarioId);
$naoLidas = obterNotificacoesUsuario($usuarioId, true);

$totalNotificacoes = count($todasNotificacoes);
$totalNaoLidas = count($naoLidas);
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
    .icon-denuncia { background: linear-gradient(135deg, #e74c3c, #c0392b); }
    
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
            <span class="stat-badge">📬 Total: <span id="total-count"><?php echo $totalNotificacoes; ?></span></span>
            <span class="stat-badge">✨ Não Lidas: <span id="unread-count"><?php echo $totalNaoLidas; ?></span></span>
        </div>
    </div>
</div>

<main class="container pb-5">
    <div class="notifications-container">
        <div class="notifications-actions" style="display: flex; justify-content: space-between; margin-bottom: 30px; flex-wrap: wrap; gap: 15px;">
            <?php if ($totalNaoLidas > 0): ?>
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
                        case 'denuncia_resolvida':
                            $icone = '🚨';
                            $texto = "Sua denúncia foi resolvida pelo Administrador <strong>{$notif['usuario_origem_nome']}</strong>.";
                            $classe = 'icon-denuncia';
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
                    
                    $isUnread = !isset($notif['lida']) || $notif['lida'] === false;
                ?>
                    <div class="notification-item <?php echo $isUnread ? 'unread' : ''; ?>" 
                         data-notif-id="<?php echo $notif['id']; ?>"
                         data-read="<?php echo $isUnread ? 'false' : 'true'; ?>"
                         onclick="abrirNotificacao(<?php echo $notif['id']; ?>, <?php echo $notif['post_id']; ?>, <?php echo $notif['comentario_id']; ?>, <?php echo $isUnread ? 'true' : 'false'; ?>)">
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
let lastNotificationCheck = Math.floor(Date.now() / 1000);

// ==========================================
// MARCAR TODAS AS NOTIFICAÇÕES COMO LIDAS
// ==========================================
function marcarTodasLidas() {
    fetch('api/mark_notifications.php', {
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
                item.setAttribute('data-read', 'true');
            });
            
            // Atualizar contadores
            document.getElementById('unread-count').textContent = '0';
            
            // Esconder botão
            const btn = document.getElementById('btnMarkAll');
            if (btn) {
                btn.style.display = 'none';
            }
            
            // Atualizar badges do header (sino)
            atualizarBadgeSino(0);
        }
    });
}

// ==========================================
// MARCAR UMA NOTIFICAÇÃO COMO LIDA E ABRIR
// ==========================================
function abrirNotificacao(notifId, postId, comentarioId, isUnread) {
    if (isUnread) {
        fetch('api/mark_notifications.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'action=mark_one&notif_id=' + notifId
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                // Atualizar UI imediatamente
                const item = document.querySelector(`[data-notif-id="${notifId}"]`);
                if (item) {
                    item.classList.remove('unread');
                    item.setAttribute('data-read', 'true');
                }
                
                // Atualizar contador
                const unreadCount = document.querySelectorAll('.notification-item.unread').length;
                document.getElementById('unread-count').textContent = unreadCount;
                
                // Atualizar sino
                atualizarBadgeSino(unreadCount);
                
                // Esconder botão se não houver mais não lidas
                if (unreadCount === 0) {
                    const btn = document.getElementById('btnMarkAll');
                    if (btn) btn.style.display = 'none';
                }
            }
        });
    }
    
    // Redirecionar para o comentário
    window.location.href = '../artigo.php?id=' + postId + '#comment-' + comentarioId;
}

// ==========================================
// ATUALIZAR BADGE DO SINO (HEADER)
// ==========================================
function atualizarBadgeSino(count) {
    const badges = document.querySelectorAll('.notification-badge');
    badges.forEach(badge => {
        if (count > 0) {
            badge.textContent = count > 99 ? '99+' : count;
            badge.style.display = 'flex';
        } else {
            badge.style.display = 'none';
        }
    });
}

// ==========================================
// REAL-TIME: VERIFICAR NOVAS NOTIFICAÇÕES
// ==========================================
function verificarNovasNotificacoes() {
    fetch('api/check_notifications_count.php')
    .then(r => r.json())
    .then(data => {
        const count = data.count;
        
        // Atualizar contador na página
        document.getElementById('unread-count').textContent = count;
        
        // Atualizar sino
        atualizarBadgeSino(count);
        
        // Mostrar/esconder botão
        const btn = document.getElementById('btnMarkAll');
        if (btn) {
            btn.style.display = count > 0 ? 'flex' : 'none';
        }
    })
    .catch(err => console.error('Erro ao verificar notificações:', err));
}

// Verificar notificações a cada 3 segundos
setInterval(verificarNovasNotificacoes, 3000);

// Inicialização
document.addEventListener('DOMContentLoaded', function() {
    console.log('🚀 Sistema de notificações real-time iniciado');
});
</script>

<?php require '../includes/footer.php'; ?>