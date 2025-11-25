<?php
session_start();
require '../includes/header.php';

if (!usuarioLogado()) {
    header('Location: login.php');
    exit;
}

$usuarioId = $_SESSION['usuario_id'];

// Marcar notificações como lidas se solicitado
if (isset($_GET['marcar_lidas'])) {
    $arquivoNotif = '../data/notificacoes.json';
    if (file_exists($arquivoNotif)) {
        $notificacoes = json_decode(file_get_contents($arquivoNotif), true);
        foreach ($notificacoes as &$n) {
            if ($n['usuario_destino_id'] == $usuarioId) {
                $n['lida'] = true;
            }
        }
        file_put_contents($arquivoNotif, json_encode($notificacoes, JSON_PRETTY_PRINT));
    }
    header('Location: notificacoes.php');
    exit;
}

// Marcar notificação específica como lida
if (isset($_GET['marcar_lida'])) {
    $notifId = (int)$_GET['marcar_lida'];
    $arquivoNotif = '../data/notificacoes.json';
    if (file_exists($arquivoNotif)) {
        $notificacoes = json_decode(file_get_contents($arquivoNotif), true);
        foreach ($notificacoes as &$n) {
            if ($n['id'] == $notifId) {
                $n['lida'] = true;
                break;
            }
        }
        file_put_contents($arquivoNotif, json_encode($notificacoes, JSON_PRETTY_PRINT));
    }
    header('Location: notificacoes.php');
    exit;
}

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

// Ordenar por data (mais recente primeiro)
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
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
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
    
    @keyframes slideInUp {
        from {
            opacity: 0;
            transform: translateY(30px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    .notifications-actions {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 30px;
        flex-wrap: wrap;
        gap: 15px;
    }
    
    /* PESQUISA ADAPTATIVA */
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
        pointer-events: none;
    }
    
    .btn-mark-all {
        background: linear-gradient(135deg, var(--primary-orange), var(--secondary-orange));
        color: white;
        padding: 15px 30px;
        border-radius: 50px;
        border: none;
        font-weight: 600;
        transition: all 0.3s ease;
        white-space: nowrap;
        display: flex;
        align-items: center;
        gap: 10px;
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
        overflow: hidden;
    }
    
    .notification-item::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255, 107, 53, 0.1), transparent);
        transition: left 0.5s ease;
    }
    
    .notification-item:hover::before {
        left: 100%;
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
    
    .notification-item.unread::after {
        content: '';
        position: absolute;
        top: 15px;
        right: 15px;
        width: 12px;
        height: 12px;
        background: var(--primary-orange);
        border-radius: 50%;
        animation: pulse 2s infinite;
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
        box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1);
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
        font-size: 1.05rem;
        color: #333;
        margin-bottom: 10px;
        line-height: 1.6;
    }
    
    .notification-text strong {
        color: var(--primary-blue);
        font-weight: 700;
    }
    
    .notification-post {
        color: var(--primary-orange);
        font-weight: 600;
        font-size: 1rem;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 10px;
    }
    
    .notification-meta {
        display: flex;
        gap: 20px;
        font-size: 0.9rem;
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
        padding: 5px 15px;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 700;
        animation: pulse 1.5s ease infinite;
    }
    
    .empty-state {
        text-align: center;
        padding: 100px 20px;
    }
    
    .empty-icon {
        font-size: 6rem;
        margin-bottom: 25px;
        animation: float 3s ease-in-out infinite;
    }
    
    @keyframes float {
        0%, 100% { transform: translateY(0); }
        50% { transform: translateY(-20px); }
    }
    
    .no-results {
        text-align: center;
        padding: 60px 20px;
        display: none;
    }
    
    .no-results-icon {
        font-size: 4rem;
        margin-bottom: 20px;
        opacity: 0.5;
    }
    
    @media (max-width: 768px) {
        .notifications-container {
            padding: 25px 20px;
        }
        
        .notifications-title {
            font-size: 2rem;
        }
        
        .notifications-actions {
            flex-direction: column;
        }
        
        .search-box {
            width: 100%;
            max-width: 100%;
        }
        
        .btn-mark-all {
            width: 100%;
            justify-content: center;
        }
        
        .notification-item {
            flex-direction: column;
            gap: 15px;
        }
        
        .notification-icon {
            width: 55px;
            height: 55px;
            font-size: 1.7rem;
        }
        
        .notification-meta {
            flex-direction: column;
            gap: 8px;
        }
    }
</style>

<div class="notifications-header">
    <div class="container">
        <h1 class="notifications-title">🔔 Central de Notificações</h1>
        <div class="notifications-stats">
            <span class="stat-badge">
                📬 Total: <?php echo count($todasNotificacoes); ?>
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
                <input type="text" 
                       id="searchInput" 
                       placeholder="🔍 Pesquisar notificações..." 
                       autocomplete="off">
                <i class="bi bi-search search-icon"></i>
            </div>
            
            <?php if (count($naoLidas) > 0): ?>
                <a href="?marcar_lidas=1" class="btn-mark-all">
                    <i class="bi bi-check-all"></i> 
                    <span>Marcar Todas como Lidas</span>
                </a>
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
                    
                    $dataFormatada = date('d/m/Y', $notif['data']);
                    $horaFormatada = date('H:i', $notif['data']);
                    
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
                    
                    $searchData = strtolower($notif['usuario_origem_nome'] . ' ' . $notif['post_titulo'] . ' ' . $texto);
                ?>
                    <div class="notification-item <?php echo !$notif['lida'] ? 'unread' : ''; ?>" 
                         data-search="<?php echo htmlspecialchars($searchData); ?>"
                         onclick="window.location.href='../artigo.php?id=<?php echo $notif['post_id']; ?>#comment-<?php echo $notif['comentario_id']; ?>'">
                        <div class="notification-icon <?php echo $classe; ?>">
                            <?php echo $icone; ?>
                        </div>
                        <div class="notification-content">
                            <p class="notification-text"><?php echo $texto; ?></p>
                            <div class="notification-post">
                                <i class="bi bi-file-text-fill"></i> 
                                <?php echo htmlspecialchars($notif['post_titulo']); ?>
                            </div>
                            <div class="notification-meta">
                                <span class="notification-time">
                                    <i class="bi bi-clock-fill"></i> <?php echo $tempoDecorrido; ?>
                                </span>
                                <span>
                                    <i class="bi bi-calendar-fill"></i> <?php echo $dataFormatada; ?> às <?php echo $horaFormatada; ?>
                                </span>
                                <?php if (!$notif['lida']): ?>
                                    <span class="notification-badge">NOVO</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        
        <!-- Mensagem quando não há resultados na pesquisa -->
        <div class="no-results" id="noResults">
            <div class="no-results-icon">🔍</div>
            <h4>Nenhuma notificação encontrada</h4>
            <p class="text-muted">Tente pesquisar com outros termos</p>
        </div>
    </div>
</main>

<script>
// PESQUISA EM TEMPO REAL ADAPTATIVA
const searchInput = document.getElementById('searchInput');
const notificationsList = document.getElementById('notificationsList');
const noResults = document.getElementById('noResults');

if (searchInput) {
    // Focar automaticamente no campo de pesquisa em desktop
    if (window.innerWidth > 768) {
        searchInput.focus();
    }
    
    searchInput.addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase().trim();
        const notifications = notificationsList.querySelectorAll('.notification-item');
        let hasResults = false;
        
        notifications.forEach(notification => {
            const searchData = notification.getAttribute('data-search');
            
            if (searchTerm === '' || searchData.includes(searchTerm)) {
                notification.style.display = 'flex';
                hasResults = true;
            } else {
                notification.style.display = 'none';
            }
        });
        
        // Mostrar mensagem se não houver resultados
        if (!hasResults && searchTerm !== '') {
            noResults.style.display = 'block';
        } else {
            noResults.style.display = 'none';
        }
    });
    
    // Limpar pesquisa ao pressionar ESC
    searchInput.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            this.value = '';
            this.dispatchEvent(new Event('input'));
        }
    });
}
</script>

<?php require '../includes/footer.php'; ?>