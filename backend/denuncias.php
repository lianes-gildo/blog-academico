<?php
session_start();
require '../includes/header.php';

if (!ehAdmin()) {
    header('Location: ../acesso_negado.php');
    exit;
}

// Limpar denúncias expiradas automaticamente
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
    
    // Ordenar por data (mais recente primeiro)
    usort($denuncias, function($a, $b) {
        return $b['data'] - $a['data'];
    });
}

$totalDenuncias = count($denuncias);
$pendentes = count(array_filter($denuncias, fn($d) => $d['status'] === 'pendente'));
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
    }
    
    .btn-view {
        background: var(--primary-blue);
        color: white;
    }
    
    .btn-delete {
        background: #e74c3c;
        color: white;
    }
    
    .btn-resolve {
        background: #27ae60;
        color: white;
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
</style>

<div class="denuncias-header">
    <div class="container">
        <h1 class="display-5 fw-bold">🚨 Denúncias de Comentários</h1>
        <p class="mb-0 opacity-75">Gerencie as denúncias da comunidade</p>
        <div class="mt-3">
            <span class="badge bg-warning me-2">⚠️ Pendentes: <?php echo $pendentes; ?></span>
            <span class="badge bg-light text-dark">📊 Total: <?php echo $totalDenuncias; ?></span>
        </div>
    </div>
</div>

<main class="container pb-5">
    <?php if (empty($denuncias)): ?>
        <div class="text-center py-5">
            <div style="font-size: 5rem;">✅</div>
            <h3 class="mt-3">Nenhuma denúncia pendente</h3>
            <p class="text-muted">Todas as denúncias foram resolvidas ou expiraram automaticamente</p>
        </div>
    <?php else: ?>
        <?php foreach ($denuncias as $denuncia): 
            $diasRestantes = floor(($denuncia['expira_em'] - time()) / 86400);
        ?>
            <div class="denuncia-card">
                <div class="denuncia-header">
                    <div class="denuncia-info">
                        <div class="denuncia-id">#<?php echo $denuncia['id']; ?> • Expira em <?php echo $diasRestantes; ?> dias</div>
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
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</main>

<script>
function resolverDenuncia(denunciaId, comentarioId, apagarComentario) {
    const acao = apagarComentario ? 'apagar e resolver' : 'marcar como resolvida';
    
    if (!confirm(`Tem certeza que deseja ${acao} esta denúncia?`)) {
        return;
    }
    
    fetch('api/resolve_denuncia.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `denuncia_id=${denunciaId}&comentario_id=${comentarioId}&delete=${apagarComentario ? '1' : '0'}`
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            alert('✅ ' + data.message);
            location.reload();
        } else {
            alert('❌ ' + data.message);
        }
    });
}
</script>

<?php require '../includes/footer.php'; ?>