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
    
    .notifications-toolbar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 30px;
        flex-wrap: wrap;
        gap: 15px;
    }
    
    .btn-toolbar {
        padding: 12px 25px;
        border-radius: 50px;
        border: none;
        font-weight: 600;
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
        gap: 10px;
        cursor: pointer;
        font-size: 0.95rem;
    }
    
    .btn-mark-all {
        background: linear-gradient(135deg, var(--primary-orange), var(--secondary-orange));
        color: white;
    }
    
    .btn-mark-all:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 20px rgba(255, 107, 53, 0.4);
    }
    
    .btn-delete-selected {
        background: #e74c3c;
        color: white;
        display: none;
    }
    
    .btn-delete-selected:hover {
        background: #c0392b;
        transform: translateY(-3px);
        box-shadow: 0 8px 20px rgba(231, 76, 60, 0.4);
    }
    
    .btn-delete-selected.show {
        display: inline-flex;
    }
    
    .btn-select-all {
        background: var(--primary-blue);
        color: white;
    }
    
    .btn-select-all:hover {
        background: var(--secondary-blue);
        transform: translateY(-3px);
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
        animation: notifSlideIn 0.4s ease;
    }
    
    @keyframes notifSlideIn {
        from {
            opacity: 0;
            transform: translateX(-30px);
        }
        to {
            opacity: 1;
            transform: translateX(0);
        }
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
    
    .notification-item.selected {
        background: #e3f2fd;
        border-left-color: #2196F3;
    }
    
    .notification-checkbox {
        width: 24px;
        height: 24px;
        cursor: pointer;
        accent-color: var(--primary-orange);
        margin-top: 20px;
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
    
    .notification-content {
        flex-grow: 1;
    }
    
    .notification-actions {
        display: flex;
        gap: 10px;
        position: absolute;
        top: 20px;
        right: 20px;
    }
    
    .btn-notif-action {
        background: white;
        border: 2px solid #ddd;
        color: #666;
        padding: 8px 12px;
        border-radius: 10px;
        font-size: 0.9rem;
        transition: all 0.3s ease;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 5px;
    }
    
    .btn-notif-action:hover {
        background: #f8f9fa;
        transform: scale(1.05);
    }
    
    .btn-delete-notif {
        color: #e74c3c;
        border-color: #e74c3c;
    }
    
    .btn-delete-notif:hover {
        background: #fff5f5;
    }
    
    .empty-state {
        text-align: center;
        padding: 100px 20px;
    }
    
    .empty-icon {
        font-size: 6rem;
        margin-bottom: 25px;
    }
    
    .new-notification-badge {
        position: absolute;
        top: -5px;
        left: -5px;
        background: linear-gradient(135deg, #ff3b30, #ff6b6b);
        color: white;
        padding: 4px 10px;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 800;
        animation: pulse 2s infinite;
        z-index: 10;
    }
    
    @media (max-width: 768px) {
        .notifications-container { padding: 25px 20px; }
        .notification-item { flex-direction: column; }
        .notification-actions { position: static; margin-top: 15px; }
        .notifications-toolbar { flex-direction: column; align-items: stretch; }
        .btn-toolbar { width: 100%; justify-content: center; }
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
        <div class="notifications-toolbar">
            <div>
                <button onclick="toggleSelectAll()" class="btn-toolbar btn-select-all" id="btnSelectAll">
                    <i class="bi bi-check2-square"></i>
                    <span>Selecionar Todas</span>
                </button>
                <button onclick="apagarSelecionadas()" class="btn-toolbar btn-delete-selected" id="btnDeleteSelected">
                    <i class="bi bi-trash-fill"></i>
                    <span id="deleteText">Apagar Selecionadas (0)</span>
                </button>
            </div>
            
            <?php if ($totalNaoLidas > 0): ?>
                <button onclick="marcarTodasLidas()" class="btn-toolbar btn-mark-all" id="btnMarkAll">
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
                         data-post-id="<?php echo $notif['post_id']; ?>"
                         data-comment-id="<?php echo $notif['comentario_id']; ?>">
                        
                        <input type="checkbox" class="notification-checkbox" data-notif-id="<?php echo $notif['id']; ?>" onclick="event.stopPropagation(); updateSelectedCount();">
                        
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
                        
                        <div class="notification-actions">
                            <button onclick="event.stopPropagation(); apagarNotificacao(<?php echo $notif['id']; ?>)" class="btn-notif-action btn-delete-notif" title="Apagar">
                                <i class="bi bi-trash-fill"></i>
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</main>

<script>
// ==========================================
// VARIÁVEIS GLOBAIS
// ==========================================
let lastNotificationCheck = Math.floor(Date.now() / 1000);
let selectedNotifications = new Set();

// ==========================================
// MARCAR TODAS AS NOTIFICAÇÕES COMO LIDAS
// ==========================================
function marcarTodasLidas() {
    fetch('api/mark_notification_read.php', {
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
            
            console.log('✅ Todas as notificações marcadas como lidas');
        }
    })
    .catch(err => console.error('Erro ao marcar como lidas:', err));
}

// ==========================================
// ABRIR NOTIFICAÇÃO E MARCAR COMO LIDA
// ==========================================
document.addEventListener('click', function(e) {
    const notifItem = e.target.closest('.notification-item');
    if (!notifItem) return;
    
    // Ignorar cliques em checkbox ou botões de ação
    if (e.target.closest('.notification-checkbox') || e.target.closest('.btn-notif-action')) {
        return;
    }
    
    const notifId = notifItem.getAttribute('data-notif-id');
    const isUnread = notifItem.getAttribute('data-read') === 'false';
    const postId = notifItem.getAttribute('data-post-id');
    const commentId = notifItem.getAttribute('data-comment-id');
    
    if (isUnread) {
        fetch('api/mark_notification_read.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'action=mark_one&notif_id=' + notifId
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                notifItem.classList.remove('unread');
                notifItem.setAttribute('data-read', 'true');
                
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
    window.location.href = '../artigo.php?id=' + postId + '#comment-' + commentId;
});

// ==========================================
// APAGAR NOTIFICAÇÃO INDIVIDUAL
// ==========================================
function apagarNotificacao(notifId) {
    if (!confirm('🗑️ Deseja apagar esta notificação?')) {
        return;
    }
    
    fetch('api/delete_notification.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'notif_ids=' + JSON.stringify([notifId])
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            // Remover da DOM com animação
            const item = document.querySelector(`[data-notif-id="${notifId}"]`);
            if (item) {
                item.style.transition = 'all 0.3s ease';
                item.style.opacity = '0';
                item.style.transform = 'translateX(-50px)';
                
                setTimeout(() => {
                    item.remove();
                    
                    // Verificar se não há mais notificações
                    const items = document.querySelectorAll('.notification-item');
                    if (items.length === 0) {
                        document.getElementById('notificationsList').innerHTML = `
                            <div class="empty-state">
                                <div class="empty-icon">📭</div>
                                <h3>Nenhuma notificação</h3>
                                <p class="text-muted">Suas notificações aparecerão aqui!</p>
                            </div>
                        `;
                    }
                    
                    // Atualizar contadores
                    atualizarContadores();
                }, 300);
            }
            
            // Remover da seleção se estava selecionada
            selectedNotifications.delete(notifId);
            updateSelectedCount();
            
            console.log('✅', data.message);
        } else {
            alert('❌ ' + data.message);
        }
    })
    .catch(err => {
        console.error('Erro ao apagar notificação:', err);
        alert('❌ Erro ao apagar notificação');
    });
}

// ==========================================
// SELEÇÃO MÚLTIPLA
// ==========================================
function toggleSelectAll() {
    const checkboxes = document.querySelectorAll('.notification-checkbox');
    const allSelected = Array.from(checkboxes).every(cb => cb.checked);
    
    checkboxes.forEach(cb => {
        cb.checked = !allSelected;
        const notifId = parseInt(cb.getAttribute('data-notif-id'));
        
        if (cb.checked) {
            selectedNotifications.add(notifId);
            cb.closest('.notification-item').classList.add('selected');
        } else {
            selectedNotifications.delete(notifId);
            cb.closest('.notification-item').classList.remove('selected');
        }
    });
    
    updateSelectedCount();
    
    const btn = document.getElementById('btnSelectAll');
    const icon = btn.querySelector('i');
    const text = btn.querySelector('span');
    
    if (allSelected) {
        icon.className = 'bi bi-check2-square';
        text.textContent = 'Selecionar Todas';
    } else {
        icon.className = 'bi bi-x-square';
        text.textContent = 'Desmarcar Todas';
    }
}

function updateSelectedCount() {
    const checkboxes = document.querySelectorAll('.notification-checkbox:checked');
    selectedNotifications.clear();
    
    checkboxes.forEach(cb => {
        const notifId = parseInt(cb.getAttribute('data-notif-id'));
        selectedNotifications.add(notifId);
        cb.closest('.notification-item').classList.add('selected');
    });
    
    // Remover classe selected de não selecionadas
    document.querySelectorAll('.notification-item').forEach(item => {
        const checkbox = item.querySelector('.notification-checkbox');
        if (!checkbox.checked) {
            item.classList.remove('selected');
        }
    });
    
    const count = selectedNotifications.size;
    const btnDelete = document.getElementById('btnDeleteSelected');
    const deleteText = document.getElementById('deleteText');
    
    if (count > 0) {
        btnDelete.classList.add('show');
        deleteText.textContent = `Apagar Selecionadas (${count})`;
    } else {
        btnDelete.classList.remove('show');
    }
}

function apagarSelecionadas() {
    const count = selectedNotifications.size;
    
    if (count === 0) {
        alert('⚠️ Selecione pelo menos uma notificação para apagar');
        return;
    }
    
    if (!confirm(`🗑️ Deseja apagar ${count} notificação${count > 1 ? 'ões' : ''}?`)) {
        return;
    }
    
    const idsArray = Array.from(selectedNotifications);
    
    fetch('api/delete_notification.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'notif_ids=' + JSON.stringify(idsArray)
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            // Remover todas as selecionadas com animação
            idsArray.forEach(notifId => {
                const item = document.querySelector(`[data-notif-id="${notifId}"]`);
                if (item) {
                    item.style.transition = 'all 0.3s ease';
                    item.style.opacity = '0';
                    item.style.transform = 'translateX(-50px)';
                    
                    setTimeout(() => {
                        item.remove();
                        
                        // Verificar se não há mais notificações
                        const items = document.querySelectorAll('.notification-item');
                        if (items.length === 0) {
                            document.getElementById('notificationsList').innerHTML = `
                                <div class="empty-state">
                                    <div class="empty-icon">📭</div>
                                    <h3>Nenhuma notificação</h3>
                                    <p class="text-muted">Suas notificações aparecerão aqui!</p>
                                </div>
                            `;
                        }
                    }, 300);
                }
            });
            
            // Limpar seleção
            selectedNotifications.clear();
            updateSelectedCount();
            
            // Atualizar contadores
            setTimeout(() => atualizarContadores(), 500);
            
            console.log('✅', data.message);
        } else {
            alert('❌ ' + data.message);
        }
    })
    .catch(err => {
        console.error('Erro ao apagar notificações:', err);
        alert('❌ Erro ao apagar notificações');
    });
}

// ==========================================
// ATUALIZAR CONTADORES
// ==========================================
function atualizarContadores() {
    const items = document.querySelectorAll('.notification-item');
    const unread = document.querySelectorAll('.notification-item.unread');
    
    document.getElementById('total-count').textContent = items.length;
    document.getElementById('unread-count').textContent = unread.length;
    
    atualizarBadgeSino(unread.length);
    
    // Mostrar/esconder botão marcar todas
    const btnMarkAll = document.getElementById('btnMarkAll');
    if (btnMarkAll) {
        btnMarkAll.style.display = unread.length > 0 ? 'inline-flex' : 'none';
    }
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
    fetch(`api/check_new_notifications.php?last_check=${lastNotificationCheck}`)
    .then(r => r.json())
    .then(data => {
        if (data.hasNew && data.newNotifications && data.newNotifications.length > 0) {
            console.log('🔔 ' + data.count + ' nova(s) notificação(ões) detectada(s)');
            
            // Inserir novas notificações no topo
            data.newNotifications.forEach(notif => {
                inserirNovaNotificacao(notif);
            });
            
            // Atualizar contadores
            atualizarContadores();
        }
        
        lastNotificationCheck = data.timestamp;
    })
    .catch(err => console.error('Erro ao verificar novas notificações:', err));
}

// ==========================================
// INSERIR NOVA NOTIFICAÇÃO NA DOM
// ==========================================
function inserirNovaNotificacao(notif) {
    // Verificar se já existe
    if (document.querySelector(`[data-notif-id="${notif.id}"]`)) {
        return;
    }
    
    // Remover empty state se existir
    const emptyState = document.querySelector('.empty-state');
    if (emptyState) {
        emptyState.remove();
    }
    
    // Mapear tipo para ícone e classe
    const tipos = {
        'mention': { icone: '📣', classe: 'icon-mention', texto: `<strong>${notif.usuario_origem_nome}</strong> mencionou você` },
        'like': { icone: '❤️', classe: 'icon-like', texto: `<strong>${notif.usuario_origem_nome}</strong> curtiu seu comentário` },
        'dislike': { icone: '👎', classe: 'icon-dislike', texto: `<strong>${notif.usuario_origem_nome}</strong> não curtiu` },
        'reply': { icone: '💬', classe: 'icon-reply', texto: `<strong>${notif.usuario_origem_nome}</strong> respondeu` },
        'denuncia_resolvida': { icone: '🚨', classe: 'icon-denuncia', texto: `Denúncia resolvida por <strong>${notif.usuario_origem_nome}</strong>` }
    };
    
    const tipo = tipos[notif.tipo] || tipos['mention'];
    
    // Criar elemento HTML
    const div = document.createElement('div');
    div.className = 'notification-item unread';
    div.setAttribute('data-notif-id', notif.id);
    div.setAttribute('data-read', 'false');
    div.setAttribute('data-post-id', notif.post_id);
    div.setAttribute('data-comment-id', notif.comentario_id);
    
    div.innerHTML = `
        <span class="new-notification-badge">NOVO</span>
        <input type="checkbox" class="notification-checkbox" data-notif-id="${notif.id}" onclick="event.stopPropagation(); updateSelectedCount();">
        <div class="notification-icon ${tipo.classe}">${tipo.icone}</div>
        <div class="notification-content">
            <p class="notification-text" style="font-size: 1.05rem; margin-bottom: 10px;">${tipo.texto}</p>
            <div style="color: var(--primary-orange); font-weight: 600; margin-bottom: 10px;">
                <i class="bi bi-file-text-fill"></i> ${escapeHtml(notif.post_titulo)}
            </div>
            <div style="font-size: 0.9rem; color: #888;">
                <i class="bi bi-clock-fill"></i> Agora mesmo
            </div>
        </div>
        <div class="notification-actions">
            <button onclick="event.stopPropagation(); apagarNotificacao(${notif.id})" class="btn-notif-action btn-delete-notif" title="Apagar">
                <i class="bi bi-trash-fill"></i>
            </button>
        </div>
    `;
    
    // Inserir no topo
    const container = document.getElementById('notificationsList');
    container.insertBefore(div, container.firstChild);
    
    // Remover badge "NOVO" após 3 segundos
    setTimeout(() => {
        const badge = div.querySelector('.new-notification-badge');
        if (badge) {
            badge.style.opacity = '0';
            setTimeout(() => badge.remove(), 300);
        }
    }, 3000);
}

// ==========================================
// FUNÇÃO AUXILIAR
// ==========================================
function escapeHtml(text) {
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return String(text).replace(/[&<>"']/g, m => map[m]);
}

// ==========================================
// INICIALIZAÇÃO
// ==========================================
document.addEventListener('DOMContentLoaded', function() {
    console.log('🚀 Sistema Real-Time de Notificações iniciado');
    console.log('✅ Verificação automática: 3 segundos');
    
    // Verificar novas notificações a cada 3 segundos
    setInterval(verificarNovasNotificacoes, 3000);
});
</script>

<?php require '../includes/footer.php'; ?>