<?php
session_start();
require '../includes/header.php';

if (!ehAdmin()) {
    header('Location: ../acesso_negado.php');
    exit;
}

// Limpar denúncias expiradas
$arquivoDenuncias = '../data/denuncias.json';

if (file_exists($arquivoDenuncias)) {
    $denuncias = json_decode(file_get_contents($arquivoDenuncias), true);
    $agora = time();
    
    $denuncias = array_filter($denuncias, function($d) use ($agora) {
        return $d['expira_em'] > $agora;
    });
    
    $denuncias = array_values($denuncias);
    file_put_contents($arquivoDenuncias, json_encode($denuncias, JSON_PRETTY_PRINT));
}

// Carregar denúncias
$denuncias = [];
if (file_exists($arquivoDenuncias)) {
    $denuncias = json_decode(file_get_contents($arquivoDenuncias), true);
    if (!is_array($denuncias)) $denuncias = [];
    
    // Separar pendentes e resolvidas
    $pendentes = array_filter($denuncias, fn($d) => $d['status'] === 'pendente');
    $resolvidas = array_filter($denuncias, fn($d) => $d['status'] === 'resolvida');
    
    usort($pendentes, fn($a, $b) => $b['data'] - $a['data']);
    usort($resolvidas, function($a, $b) {
        $aTime = isset($a['resolvida_em']) ? $a['resolvida_em'] : $a['data'];
        $bTime = isset($b['resolvida_em']) ? $b['resolvida_em'] : $b['data'];
        return $bTime - $aTime;
    });
    
    $denuncias = array_merge($pendentes, $resolvidas);
}

$totalDenuncias = count($denuncias);
$totalPendentes = count($pendentes ?? []);
$totalResolvidas = count($resolvidas ?? []);
?>

<style>
    .denuncias-header {
        background: linear-gradient(135deg, #e74c3c, #c0392b);
        color: white;
        padding: 50px 0 40px;
        margin-bottom: 40px;
    }
    
    .denuncia-card {
        background: white;
        border-radius: 20px;
        padding: 30px;
        margin-bottom: 25px;
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
        border-left: 5px solid #e74c3c;
        transition: all 0.3s ease;
    }
    
    .denuncia-card.resolvida {
        border-left-color: #27ae60;
        opacity: 0.7;
    }
    
    .denuncia-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
    }
    
    .denuncia-header {
        display: flex;
        justify-content: space-between;
        align-items: start;
        margin-bottom: 20px;
        flex-wrap: wrap;
        gap: 15px;
    }
    
    .denuncia-info {
        flex: 1;
    }
    
    .denuncia-id {
        font-size: 0.9rem;
        color: #888;
        font-weight: 600;
    }
    
    .denuncia-motivo {
        font-size: 1.3rem;
        font-weight: 700;
        color: #e74c3c;
        margin: 5px 0;
    }
    
    .comentario-denunciado {
        background: #f8f9fa;
        padding: 20px;
        border-radius: 12px;
        border-left: 4px solid #ff6b6b;
        margin: 20px 0;
    }
    
    .comentario-autor {
        font-weight: 700;
        color: var(--primary-blue);
        margin-bottom: 10px;
    }
    
    .comentario-texto {
        color: #555;
        line-height: 1.6;
    }
    
    .denuncia-meta {
        display: flex;
        gap: 20px;
        font-size: 0.9rem;
        color: #666;
        flex-wrap: wrap;
        margin-top: 15px;
    }
    
    .denuncia-actions {
        display: flex;
        gap: 10px;
        margin-top: 20px;
        flex-wrap: wrap;
    }
    
    .btn-action {
        padding: 10px 20px;
        border-radius: 10px;
        font-weight: 600;
        border: none;
        transition: all 0.3s ease;
        cursor: pointer;
        font-size: 0.95rem;
    }
    
    .btn-view {
        background: var(--primary-blue);
        color: white;
    }
    
    .btn-view:hover {
        background: var(--secondary-blue);
        transform: translateY(-2px);
    }
    
    .btn-delete {
        background: #e74c3c;
        color: white;
    }
    
    .btn-delete:hover {
        background: #c0392b;
        transform: translateY(-2px);
    }
    
    .btn-resolve {
        background: #27ae60;
        color: white;
    }
    
    .btn-resolve:hover {
        background: #229954;
        transform: translateY(-2px);
    }
    
    .badge-status {
        padding: 8px 15px;
        border-radius: 20px;
        font-weight: 600;
        font-size: 0.85rem;
    }
    
    .badge-pendente {
        background: #f39c12;
        color: white;
    }
    
    .badge-resolvida {
        background: #27ae60;
        color: white;
    }
    
    .resolucao-info {
        background: #e8f5e9;
        padding: 15px;
        border-radius: 10px;
        margin-top: 15px;
        border-left: 4px solid #27ae60;
    }
    
    .filter-tabs {
        display: flex;
        gap: 10px;
        margin-bottom: 30px;
        flex-wrap: wrap;
    }
    
    .filter-tab {
        padding: 12px 25px;
        background: white;
        border: 2px solid #ddd;
        border-radius: 50px;
        cursor: pointer;
        font-weight: 600;
        transition: all 0.3s ease;
    }
    
    .filter-tab.active {
        background: #e74c3c;
        border-color: #e74c3c;
        color: white;
    }
    
    .filter-tab:hover {
        transform: translateY(-2px);
    }
</style>

<div class="denuncias-header">
    <div class="container">
        <h1 class="display-5 fw-bold">🚨 Denúncias de Comentários</h1>
        <p class="mb-0 opacity-75">Gerencie as denúncias da comunidade em tempo real</p>
        <div class="mt-3">
            <span class="badge bg-warning me-2">⚠️ Pendentes: <span id="count-pendentes"><?php echo $totalPendentes; ?></span></span>
            <span class="badge bg-success me-2">✅ Resolvidas: <span id="count-resolvidas"><?php echo $totalResolvidas; ?></span></span>
            <span class="badge bg-light text-dark">📊 Total: <span id="count-total"><?php echo $totalDenuncias; ?></span></span>
        </div>
    </div>
</div>

<main class="container pb-5">
    <div class="filter-tabs">
        <div class="filter-tab active" onclick="filtrarDenuncias('todas')" id="tab-todas">
            📋 Todas (<span id="tab-count-todas"><?php echo $totalDenuncias; ?></span>)
        </div>
        <div class="filter-tab" onclick="filtrarDenuncias('pendentes')" id="tab-pendentes">
            ⚠️ Pendentes (<span id="tab-count-pendentes"><?php echo $totalPendentes; ?></span>)
        </div>
        <div class="filter-tab" onclick="filtrarDenuncias('resolvidas')" id="tab-resolvidas">
            ✅ Resolvidas (<span id="tab-count-resolvidas"><?php echo $totalResolvidas; ?></span>)
        </div>
    </div>
    
    <div id="lista-denuncias">
        <?php if (empty($denuncias)): ?>
            <div class="text-center py-5" id="empty-state">
                <div style="font-size: 5rem;">✅</div>
                <h3 class="mt-3">Nenhuma denúncia pendente</h3>
                <p class="text-muted">Todas as denúncias foram resolvidas ou expiraram automaticamente</p>
            </div>
        <?php else: ?>
            <?php foreach ($denuncias as $denuncia): 
                $diasRestantes = floor(($denuncia['expira_em'] - time()) / 86400);
                $isResolvida = $denuncia['status'] === 'resolvida';
            ?>
                <div class="denuncia-card <?php echo $isResolvida ? 'resolvida' : ''; ?>" 
                     data-status="<?php echo $denuncia['status']; ?>"
                     data-denuncia-id="<?php echo $denuncia['id']; ?>">
                    <div class="denuncia-header">
                        <div class="denuncia-info">
                            <div class="denuncia-id">
                                #<?php echo $denuncia['id']; ?> • 
                                <?php if (!$isResolvida): ?>
                                    Expira em <?php echo $diasRestantes; ?> dia<?php echo $diasRestantes != 1 ? 's' : ''; ?>
                                <?php else: ?>
                                    Resolvida em <?php echo date('d/m/Y H:i', $denuncia['resolvida_em']); ?>
                                <?php endif; ?>
                            </div>
                            <div class="denuncia-motivo">🚨 <?php echo htmlspecialchars($denuncia['motivo_texto']); ?></div>
                        </div>
                        <span class="badge-status badge-<?php echo $denuncia['status']; ?>">
                            <?php echo ucfirst($denuncia['status']); ?>
                        </span>
                    </div>
                    
                    <div class="comentario-denunciado">
                        <div class="comentario-autor">
                            <i class="bi bi-person-circle"></i> <?php echo htmlspecialchars($denuncia['comentario_autor']); ?>
                        </div>
                        <div class="comentario-texto">
                            "<?php echo htmlspecialchars($denuncia['comentario_texto']); ?>"
                        </div>
                    </div>
                    
                    <div class="denuncia-meta">
                        <span>
                            <i class="bi bi-flag-fill"></i> 
                            Denunciante: <strong><?php echo htmlspecialchars($denuncia['denunciante_nome']); ?></strong>
                        </span>
                        <span>
                        <i class="bi bi-calendar-fill"></i> 
                            <?php echo date('d/m/Y H:i', $denuncia['data']); ?>
                        </span>
                    </div>
                    
                    <?php if ($isResolvida): ?>
                        <div class="resolucao-info">
                            <strong>✅ Resolvida por:</strong> <?php echo htmlspecialchars($denuncia['resolvida_por'] ?? 'Admin'); ?><br>
                            <strong>📅 Em:</strong> <?php echo date('d/m/Y H:i', $denuncia['resolvida_em']); ?><br>
                            <strong>🔧 Ação:</strong> <?php echo htmlspecialchars($denuncia['acao_tomada'] ?? 'Denúncia arquivada'); ?>
                        </div>
                    <?php else: ?>
                        <div class="denuncia-actions">
                            <a href="../artigo.php?id=<?php echo $denuncia['artigo_id']; ?>#comment-<?php echo $denuncia['comentario_id']; ?>" 
                               class="btn-action btn-view" target="_blank">
                                <i class="bi bi-eye-fill"></i> Ver Comentário
                            </a>
                            <button onclick="resolverDenuncia(<?php echo $denuncia['id']; ?>, <?php echo $denuncia['comentario_id']; ?>, true)" 
                                    class="btn-action btn-delete">
                                <i class="bi bi-trash-fill"></i> Apagar Comentário
                            </button>
                            <button onclick="resolverDenuncia(<?php echo $denuncia['id']; ?>, <?php echo $denuncia['comentario_id']; ?>, false)" 
                                    class="btn-action btn-resolve">
                                <i class="bi bi-check-circle-fill"></i> Marcar Resolvida
                            </button>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</main>

<script>
// ==========================================
// VARIÁVEIS GLOBAIS
// ==========================================
let filtroAtual = 'todas';
let lastCheckDenuncias = Math.floor(Date.now() / 1000);

// ==========================================
// RESOLVER DENÚNCIA (Real-Time com API)
// ==========================================
function resolverDenuncia(denunciaId, comentarioId, apagarComentario) {
    const acao = apagarComentario ? 'apagar o comentário e resolver' : 'marcar como resolvida';
    
    if (!confirm(`✅ Confirmar ação\n\nDeseja ${acao} esta denúncia?\n\n${apagarComentario ? '⚠️ O comentário será removido permanentemente.' : '📋 A denúncia será arquivada.'}`)) {
        return;
    }
    
    // Desabilitar botões temporariamente
    const card = document.querySelector(`[data-denuncia-id="${denunciaId}"]`);
    const buttons = card.querySelectorAll('.btn-action');
    buttons.forEach(btn => {
        btn.disabled = true;
        btn.style.opacity = '0.5';
    });
    
    fetch('api/resolve_denuncia.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `denuncia_id=${denunciaId}&comentario_id=${comentarioId}&delete=${apagarComentario ? '1' : '0'}`
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            // Atualizar card em real-time
            card.classList.add('resolvida');
            card.setAttribute('data-status', 'resolvida');
            
            // Atualizar badge
            const badge = card.querySelector('.badge-status');
            badge.className = 'badge-status badge-resolvida';
            badge.textContent = 'Resolvida';
            
            // Atualizar ID info
            const idInfo = card.querySelector('.denuncia-id');
            const now = new Date();
            idInfo.textContent = `#${denunciaId} • Resolvida em ${now.toLocaleDateString('pt-MZ')} ${now.toLocaleTimeString('pt-MZ', {hour: '2-digit', minute: '2-digit'})}`;
            
            // Substituir botões por info de resolução
            const actions = card.querySelector('.denuncia-actions');
            if (actions) {
                const adminNome = '<?php echo $_SESSION['nome']; ?>';
                const acaoTomada = apagarComentario ? 'Comentário removido' : 'Denúncia arquivada';
                
                actions.outerHTML = `
                    <div class="resolucao-info">
                        <strong>✅ Resolvida por:</strong> ${adminNome}<br>
                        <strong>📅 Em:</strong> ${now.toLocaleDateString('pt-MZ')} ${now.toLocaleTimeString('pt-MZ', {hour: '2-digit', minute: '2-digit'})}<br>
                        <strong>🔧 Ação:</strong> ${acaoTomada}
                    </div>
                `;
            }
            
            // Mover para o final
            if (filtroAtual === 'todas') {
                const parent = card.parentNode;
                parent.appendChild(card);
            }
            
            // Atualizar contadores
            atualizarContadores();
            
            console.log('✅', data.message);
            
        } else {
            alert('❌ ' + data.message);
            
            // Reabilitar botões
            buttons.forEach(btn => {
                btn.disabled = false;
                btn.style.opacity = '1';
            });
        }
    })
    .catch(err => {
        console.error('Erro ao processar denúncia:', err);
        alert('❌ Erro ao processar denúncia. Verifique sua conexão.');
        
        // Reabilitar botões
        buttons.forEach(btn => {
            btn.disabled = false;
            btn.style.opacity = '1';
        });
    });
}

// ==========================================
// FILTRAR DENÚNCIAS
// ==========================================
function filtrarDenuncias(filtro) {
    filtroAtual = filtro;
    
    // Atualizar tabs
    document.querySelectorAll('.filter-tab').forEach(tab => tab.classList.remove('active'));
    document.getElementById('tab-' + filtro).classList.add('active');
    
    // Filtrar cards
    const cards = document.querySelectorAll('.denuncia-card');
    let visibleCount = 0;
    
    cards.forEach(card => {
        const status = card.getAttribute('data-status');
        let shouldShow = false;
        
        if (filtro === 'todas') {
            shouldShow = true;
        } else if (filtro === 'pendentes' && status === 'pendente') {
            shouldShow = true;
        } else if (filtro === 'resolvidas' && status === 'resolvida') {
            shouldShow = true;
        }
        
        card.style.display = shouldShow ? 'block' : 'none';
        if (shouldShow) visibleCount++;
    });
    
    // Mostrar empty state se necessário
    if (visibleCount === 0) {
        const emptyState = document.getElementById('empty-state');
        if (!emptyState) {
            const container = document.getElementById('lista-denuncias');
            container.innerHTML = `
                <div class="text-center py-5" id="empty-state">
                    <div style="font-size: 5rem;">📭</div>
                    <h3 class="mt-3">Nenhuma denúncia ${filtro === 'pendentes' ? 'pendente' : filtro === 'resolvidas' ? 'resolvida' : ''}</h3>
                    <p class="text-muted">Não há denúncias para mostrar neste filtro</p>
                </div>
            `;
        }
    } else {
        const emptyState = document.getElementById('empty-state');
        if (emptyState) emptyState.remove();
    }
}

// ==========================================
// ATUALIZAR CONTADORES
// ==========================================
function atualizarContadores() {
    const cards = document.querySelectorAll('.denuncia-card');
    let total = cards.length;
    let pendentes = 0;
    let resolvidas = 0;
    
    cards.forEach(card => {
        if (card.getAttribute('data-status') === 'pendente') {
            pendentes++;
        } else {
            resolvidas++;
        }
    });
    
    document.getElementById('count-total').textContent = total;
    document.getElementById('count-pendentes').textContent = pendentes;
    document.getElementById('count-resolvidas').textContent = resolvidas;
    
    document.getElementById('tab-count-todas').textContent = total;
    document.getElementById('tab-count-pendentes').textContent = pendentes;
    document.getElementById('tab-count-resolvidas').textContent = resolvidas;
}

// ==========================================
// REAL-TIME: VERIFICAR NOVAS DENÚNCIAS
// ==========================================
function verificarNovasDenuncias() {
    fetch(`api/check_denuncias_updates.php?last_check=${lastCheckDenuncias}`)
    .then(r => r.json())
    .then(data => {
        if (data.hasNew) {
            console.log('🔄 Novas denúncias detectadas, recarregando...');
            location.reload();
        }
        lastCheckDenuncias = data.timestamp;
    })
    .catch(err => console.error('Erro ao verificar denúncias:', err));
}

// ==========================================
// INICIALIZAÇÃO
// ==========================================
document.addEventListener('DOMContentLoaded', function() {
    console.log('🚀 Painel de denúncias real-time iniciado');
    
    // Verificar novas denúncias a cada 5 segundos
    setInterval(verificarNovasDenuncias, 5000);
    
    console.log('✅ Denúncias: Real-time ativo (5s)');
});
</script>

<?php require '../includes/footer.php'; ?>