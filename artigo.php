<?php
require 'includes/header.php';

if (!isset($_GET['id'])) {
    header('Location: index.php');
    exit;
}

$postId = (int)$_GET['id'];
$posts = json_decode(file_get_contents('data/posts.json'), true);
$post = null;

foreach ($posts as $p) {
    if ($p['id'] == $postId) {
        $post = $p;
        break;
    }
}

if (!$post) {
    header('Location: index.php');
    exit;
}

// Incrementar visualizações
$estatisticas = json_decode(file_get_contents('data/estatisticas.json'), true);
if (!isset($estatisticas['visitas'][$postId])) {
    $estatisticas['visitas'][$postId] = 0;
}
$estatisticas['visitas'][$postId]++;
file_put_contents('data/estatisticas.json', json_encode($estatisticas, JSON_PRETTY_PRINT));

// Verificar se usuário curtiu
$gostou = false;
if (usuarioLogado()) {
    $gostosLista = $estatisticas['gostos'][$postId] ?? [];
    $gostou = in_array($_SESSION['usuario_id'], $gostosLista);
}
?>

<style>
    .article-hero {
        background: linear-gradient(135deg, var(--primary-blue), var(--secondary-blue));
        color: white;
        padding: 60px 0 40px;
        margin-bottom: 50px;
    }
    
    .article-title {
        font-size: 2.8rem;
        font-weight: 800;
        line-height: 1.3;
        margin-bottom: 25px;
    }
    
    .article-meta {
        display: flex;
        align-items: center;
        gap: 30px;
        flex-wrap: wrap;
        opacity: 0.9;
    }
    
    .meta-item {
        display: flex;
        align-items: center;
        gap: 10px;
        font-size: 1.1rem;
    }
    
    .article-content {
        background: white;
        border-radius: 25px;
        padding: 50px;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.08);
        margin-bottom: 40px;
    }
    
    .article-image {
        width: 100%;
        height: 500px;
        object-fit: cover;
        border-radius: 20px;
        margin-bottom: 40px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
    }
    
    .article-text {
        font-size: 1.15rem;
        line-height: 1.8;
        color: #333;
        margin-bottom: 40px;
    }
    
    .article-actions {
        display: flex;
        gap: 15px;
        padding: 25px;
        background: #f8f9fa;
        border-radius: 15px;
        flex-wrap: wrap;
    }
    
    .btn-like {
        background: white;
        border: 2px solid #e74c3c;
        color: #e74c3c;
        padding: 12px 30px;
        border-radius: 50px;
        font-weight: 600;
        font-size: 1.1rem;
        transition: all 0.3s ease;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 10px;
    }
    
    .btn-like:hover {
        transform: translateY(-3px);
        box-shadow: 0 5px 15px rgba(231, 76, 60, 0.3);
    }
    
    .btn-like.liked {
        background: #e74c3c;
        color: white;
    }
    
    .btn-share {
        background: var(--primary-blue);
        color: white;
        padding: 12px 30px;
        border-radius: 50px;
        font-weight: 600;
        font-size: 1.1rem;
        border: none;
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
        gap: 10px;
    }
    
    .btn-share:hover {
        background: var(--secondary-blue);
        transform: translateY(-3px);
        box-shadow: 0 5px 15px rgba(0, 59, 92, 0.3);
    }
    
    .comments-section {
        background: white;
        border-radius: 25px;
        padding: 40px;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.08);
    }
    
    .comments-title {
        font-size: 2rem;
        font-weight: 700;
        color: var(--primary-blue);
        margin-bottom: 30px;
        display: flex;
        align-items: center;
        gap: 15px;
    }
    
    .comment-box {
        background: #f8f9fa;
        border-radius: 15px;
        padding: 20px;
        margin-bottom: 15px;
        border-left: 4px solid var(--primary-orange);
        position: relative;
        animation: slideIn 0.3s ease;
    }
    
    @keyframes slideIn {
        from {
            opacity: 0;
            transform: translateX(-20px);
        }
        to {
            opacity: 1;
            transform: translateX(0);
        }
    }
    
    .comment-header {
        display: flex;
        justify-content: space-between;
        align-items: start;
        margin-bottom: 10px;
    }
    
    .comment-author {
        font-weight: 700;
        color: var(--primary-blue);
    }
    
    .comment-menu {
        position: relative;
    }
    
    .comment-menu-btn {
        background: none;
        border: none;
        color: #888;
        font-size: 1.3rem;
        cursor: pointer;
        padding: 5px 10px;
        border-radius: 5px;
        transition: all 0.3s ease;
    }
    
    .comment-menu-btn:hover {
        background: #e0e0e0;
        color: #333;
    }
    
    .comment-menu-dropdown {
        display: none;
        position: absolute;
        right: 0;
        top: 100%;
        background: white;
        border-radius: 10px;
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.15);
        min-width: 180px;
        z-index: 100;
        margin-top: 5px;
    }
    
    .comment-menu-dropdown.show {
        display: block;
        animation: fadeInDown 0.2s ease;
    }
    
    @keyframes fadeInDown {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    .comment-menu-item {
        padding: 12px 20px;
        cursor: pointer;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        gap: 10px;
        border: none;
        background: none;
        width: 100%;
        text-align: left;
        font-size: 0.95rem;
    }
    
    .comment-menu-item:first-child {
        border-radius: 10px 10px 0 0;
    }
    
    .comment-menu-item:last-child {
        border-radius: 0 0 10px 10px;
    }
    
    .comment-menu-item:hover {
        background: #f8f9fa;
    }
    
    .comment-menu-item.danger {
        color: #e74c3c;
    }
    
    .comment-menu-item.danger:hover {
        background: #fff5f5;
    }
    
    .comment-text {
        color: #555;
        margin-bottom: 10px;
        line-height: 1.6;
        word-wrap: break-word;
    }
    
    .comment-text .mention {
        color: var(--primary-orange);
        font-weight: 700;
    }
    
    .comment-date {
        font-size: 0.85rem;
        color: #888;
    }
    
    .comment-form textarea {
        border-radius: 15px;
        border: 2px solid #ddd;
        padding: 20px;
        font-size: 1rem;
        resize: vertical;
        transition: border-color 0.3s ease;
    }
    
    .comment-form textarea:focus {
        border-color: var(--primary-orange);
        box-shadow: 0 0 0 0.2rem rgba(255, 107, 53, 0.25);
    }
    
    .comment-actions {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
        margin-top: 10px;
    }
    
    .btn-comment-action {
        background: white;
        border: 2px solid #ddd;
        color: #666;
        padding: 6px 15px;
        border-radius: 20px;
        font-size: 0.9rem;
        font-weight: 600;
        transition: all 0.3s ease;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 5px;
    }
    
    .btn-comment-action:hover {
        background: #f8f9fa;
        transform: translateY(-2px);
        box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1);
    }
    
    .btn-comment-action.active {
        background: var(--primary-orange);
        border-color: var(--primary-orange);
        color: white;
    }
    
    .btn-comment-action:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }
    
    .reply-form {
        margin-top: 15px;
        padding: 15px;
        background: white;
        border-radius: 10px;
        border: 2px solid var(--primary-orange);
    }
    
    .reply-to-info {
        color: #666;
        font-size: 0.9rem;
        padding: 8px 12px;
        background: #f8f9fa;
        border-radius: 8px;
        border-left: 3px solid var(--primary-orange);
        margin-bottom: 10px;
    }
    
    .reply-to-info strong {
        color: var(--primary-orange);
    }
    
    /* Modal de Denúncia */
    .modal-denuncia {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.6);
        z-index: 2000;
        align-items: center;
        justify-content: center;
    }
    
    .modal-denuncia.show {
        display: flex;
    }
    
    .modal-denuncia-content {
        background: white;
        border-radius: 20px;
        padding: 40px;
        max-width: 500px;
        width: 90%;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        animation: modalSlideIn 0.3s ease;
    }
    
    @keyframes modalSlideIn {
        from {
            opacity: 0;
            transform: translateY(-50px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    .modal-denuncia-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 25px;
    }
    
    .modal-denuncia-title {
        font-size: 1.8rem;
        font-weight: 700;
        color: var(--primary-blue);
    }
    
    .modal-close-btn {
        background: none;
        border: none;
        font-size: 2rem;
        color: #888;
        cursor: pointer;
        transition: color 0.3s ease;
    }
    
    .modal-close-btn:hover {
        color: #e74c3c;
    }
    
    .denuncia-option {
        padding: 15px;
        background: #f8f9fa;
        border-radius: 10px;
        margin-bottom: 10px;
        cursor: pointer;
        transition: all 0.3s ease;
        border: 2px solid transparent;
    }
    
    .denuncia-option:hover {
        background: #fff;
        border-color: var(--primary-orange);
    }
    
    .denuncia-option input[type="radio"] {
        margin-right: 10px;
    }
    
    .denuncia-outro {
        display: none;
        margin-top: 15px;
    }
    
    .denuncia-outro.show {
        display: block;
    }
    
    .btn-submit-denuncia {
        background: #e74c3c;
        color: white;
        padding: 15px 30px;
        border-radius: 50px;
        border: none;
        font-weight: 600;
        width: 100%;
        transition: all 0.3s ease;
        margin-top: 20px;
    }
    
    .btn-submit-denuncia:hover {
        background: #c0392b;
        transform: translateY(-3px);
    }
    
    @media (max-width: 768px) {
        .article-hero {
            padding: 40px 0 30px;
        }
        
        .article-title {
            font-size: 1.8rem;
        }
        
        .article-content {
            padding: 30px 20px;
        }
        
        .article-image {
            height: 250px;
        }
        
        .article-text {
            font-size: 1rem;
        }
        
        .comments-section {
            padding: 25px 20px;
        }
        
        .comment-box {
            padding: 15px;
        }
    }
</style>

<div class="article-hero">
    <div class="container">
        <h1 class="article-title"><?php echo htmlspecialchars($post['titulo']); ?></h1>
        <div class="article-meta">
            <div class="meta-item">
                <i class="bi bi-person-circle"></i>
                <strong><?php echo htmlspecialchars($post['autor']); ?></strong>
            </div>
            <div class="meta-item">
                <i class="bi bi-calendar-event"></i>
                <?php echo date('d/m/Y H:i', strtotime($post['data'])); ?>
            </div>
            <div class="meta-item">
                <i class="bi bi-eye-fill"></i>
                <?php echo $estatisticas['visitas'][$postId]; ?> visualizações
            </div>
        </div>
    </div>
</div>

<main class="container pb-5">
    <div class="article-content">
        <img src="<?php echo $post['imagem']; ?>" alt="<?php echo htmlspecialchars($post['titulo']); ?>" class="article-image">
        
        <div class="article-text">
            <?php echo nl2br(htmlspecialchars($post['descricao_longa'])); ?>
        </div>
        
        <div class="article-actions">
            <button onclick="curtir(<?php echo $postId; ?>)" class="btn-like <?php echo $gostou ? 'liked' : ''; ?>" id="btn-like-<?php echo $postId; ?>">
                <i class="bi bi-heart-fill"></i>
                <span id="contador-gostos-<?php echo $postId; ?>"><?php echo $post['gostos']; ?></span> Likes
            </button>
            <button onclick="compartilhar()" class="btn-share">
                <i class="bi bi-share-fill"></i> Compartilhar
            </button>
        </div>
    </div>

    <!-- CONTINUAÇÃO DO artigo.php -->
    
    <div class="comments-section">
        <h3 class="comments-title">
            <i class="bi bi-chat-dots-fill"></i>
            Comentários
        </h3>
        
        <div id="lista-comentarios">
            <?php
            $arquivoComentarios = 'data/comentarios.json';
            
            if (!file_exists($arquivoComentarios)) {
                file_put_contents($arquivoComentarios, '[]');
            }
            
            $todosComentarios = json_decode(file_get_contents($arquivoComentarios), true);
            
            if (!is_array($todosComentarios)) {
                $todosComentarios = [];
            }
            
            // Função para formatar @menções
            function formatarMencoes($texto) {
                $texto = htmlspecialchars($texto);
                $texto = preg_replace('/@(\w+)/', '<strong class="mention">@$1</strong>', $texto);
                return $texto;
            }
            
            // Função para renderizar comentário
            function renderComentario($c, $nivel, $todosCom, $usuarioLogado) {
                $comentarioId = $c['comentario_id'];
                $likes = count($c['likes'] ?? []);
                $dislikes = count($c['dislikes'] ?? []);
                $userLiked = $usuarioLogado && isset($_SESSION['usuario_id']) && in_array($_SESSION['usuario_id'], $c['likes'] ?? []);
                $userDisliked = $usuarioLogado && isset($_SESSION['usuario_id']) && in_array($_SESSION['usuario_id'], $c['dislikes'] ?? []);
                $nomeAutor = htmlspecialchars($c['nome']);
                $ehMeuComentario = $usuarioLogado && isset($_SESSION['usuario_id']) && $c['usuario_id'] == $_SESSION['usuario_id'];
                
                $marginLeft = $nivel > 0 ? ' style="margin-left: ' . ($nivel * 40) . 'px; border-left: 3px solid #FF6B35;"' : '';
                
                echo '<div class="comment-box"' . $marginLeft . ' id="comment-' . $comentarioId . '" data-comment-id="' . $comentarioId . '">';
                
                // Header do comentário
                echo '<div class="comment-header">';
                echo '<div class="comment-author">' . $nomeAutor . '</div>';
                
                // Menu de opções (3 pontinhos)
                if ($usuarioLogado) {
                    echo '<div class="comment-menu">';
                    echo '<button class="comment-menu-btn" onclick="toggleCommentMenu(' . $comentarioId . ')">⋮</button>';
                    echo '<div class="comment-menu-dropdown" id="menu-' . $comentarioId . '">';
                    
                    if ($ehMeuComentario) {
                        // Opção de apagar (só aparece se for meu comentário)
                        echo '<button class="comment-menu-item danger" onclick="confirmarApagarComentario(' . $comentarioId . ')">';
                        echo '<i class="bi bi-trash-fill"></i> Apagar Comentário';
                        echo '</button>';
                    } else {
                        // Opção de denunciar (só aparece se NÃO for meu comentário)
                        echo '<button class="comment-menu-item danger" onclick="abrirModalDenuncia(' . $comentarioId . ')">';
                        echo '<i class="bi bi-flag-fill"></i> Denunciar Comentário';
                        echo '</button>';
                    }
                    
                    echo '</div>';
                    echo '</div>';
                }
                
                echo '</div>';
                
                echo '<div class="comment-text">' . formatarMencoes($c['comentario']) . '</div>';
                echo '<div class="comment-date">' . date('d/m/Y H:i', $c['data']) . '</div>';
                
                // Ações de comentário
                echo '<div class="comment-actions">';
                
                if ($usuarioLogado) {
                    echo '<button onclick="likeComment(' . $comentarioId . ')" class="btn-comment-action ' . ($userLiked ? 'active' : '') . '" id="like-btn-' . $comentarioId . '">';
                    echo '<i class="bi bi-hand-thumbs-up-fill"></i> <span id="like-count-' . $comentarioId . '">' . $likes . '</span>';
                    echo '</button>';
                    
                    echo '<button onclick="dislikeComment(' . $comentarioId . ')" class="btn-comment-action ' . ($userDisliked ? 'active' : '') . '" id="dislike-btn-' . $comentarioId . '">';
                    echo '<i class="bi bi-hand-thumbs-down-fill"></i> <span id="dislike-count-' . $comentarioId . '">' . $dislikes . '</span>';
                    echo '</button>';
                    
                    echo '<button onclick="toggleReply(' . $comentarioId . ', \'' . addslashes($c['nome']) . '\')" class="btn-comment-action">';
                    echo '<i class="bi bi-reply-fill"></i> Responder';
                    echo '</button>';
                } else {
                    echo '<button onclick="redirecionarLogin()" class="btn-comment-action">';
                    echo '<i class="bi bi-hand-thumbs-up-fill"></i> <span>' . $likes . '</span>';
                    echo '</button>';
                    
                    echo '<button onclick="redirecionarLogin()" class="btn-comment-action">';
                    echo '<i class="bi bi-hand-thumbs-down-fill"></i> <span>' . $dislikes . '</span>';
                    echo '</button>';
                }
                
                echo '</div>';
                
                // Formulário de resposta
                if ($usuarioLogado) {
                    echo '<div class="reply-form" id="reply-form-' . $comentarioId . '" style="display: none;">';
                    echo '<div class="reply-to-info">Respondendo a <strong>@' . $nomeAutor . '</strong></div>';
                    echo '<textarea class="form-control" id="reply-text-' . $comentarioId . '" rows="2" placeholder="@' . $nomeAutor . ' Escreva sua resposta..."></textarea>';
                    echo '<button onclick="enviarResposta(' . $comentarioId . ', \'' . addslashes($c['nome']) . '\')" class="btn btn-sm btn-primary-custom mt-2">Enviar Resposta</button>';
                    echo '</div>';
                }
                
                echo '</div>';
                
                // Renderizar respostas recursivamente
                foreach ($todosCom as $resposta) {
                    if (isset($resposta['pai_id']) && $resposta['pai_id'] == $comentarioId) {
                        renderComentario($resposta, $nivel + 1, $todosCom, $usuarioLogado);
                    }
                }
            }
            
            // Filtrar comentários principais
            $comentariosArtigo = [];
            foreach ($todosComentarios as $c) {
                if ($c['artigo_id'] == $postId && !isset($c['pai_id'])) {
                    $comentariosArtigo[] = $c;
                }
            }
            
            if (empty($comentariosArtigo)) {
                echo '<div class="text-center py-5" id="empty-state">';
                echo '<div style="font-size: 3rem;">💬</div>';
                echo '<p class="text-muted mt-3">Seja o primeiro a comentar!</p>';
                echo '</div>';
            } else {
                foreach ($comentariosArtigo as $c) {
                    renderComentario($c, 0, $todosComentarios, usuarioLogado());
                }
            }
            ?>
        </div>

        <?php if (usuarioLogado()): ?>
            <div class="comment-form mt-4">
                <textarea id="campo-comentario" class="form-control" rows="4" placeholder="✍️ Escreva seu comentário..."></textarea>
                <button onclick="enviarComentario(<?php echo $postId; ?>)" class="btn btn-primary-custom mt-3">
                    <i class="bi bi-send-fill"></i> Enviar Comentário
                </button>
                <p id="msg-comentario" class="mt-3"></p>
            </div>
        <?php else: ?>
            <div class="alert alert-info mt-4">
                <i class="bi bi-info-circle-fill"></i>
                <a href="backend/login.php" class="alert-link">Faça login</a> para comentar neste artigo.
            </div>
        <?php endif; ?>
    </div>
</main>

<!-- Modal de Denúncia -->
<div class="modal-denuncia" id="modal-denuncia">
    <div class="modal-denuncia-content">
        <div class="modal-denuncia-header">
            <h3 class="modal-denuncia-title">🚨 Denunciar Comentário</h3>
            <button class="modal-close-btn" onclick="fecharModalDenuncia()">&times;</button>
        </div>
        
        <div class="modal-denuncia-body">
            <p style="color: #666; margin-bottom: 20px;">Selecione o motivo da denúncia:</p>
            
            <div class="denuncia-option" onclick="selecionarMotivo('spam')">
                <input type="radio" name="motivo" value="spam" id="motivo-spam">
                <label for="motivo-spam">📧 Spam ou Publicidade</label>
            </div>
            
            <div class="denuncia-option" onclick="selecionarMotivo('discurso_odio')">
                <input type="radio" name="motivo" value="discurso_odio" id="motivo-odio">
                <label for="motivo-odio">😡 Discurso de Ódio</label>
            </div>
            
            <div class="denuncia-option" onclick="selecionarMotivo('assedio')">
                <input type="radio" name="motivo" value="assedio" id="motivo-assedio">
                <label for="motivo-assedio">😢 Assédio ou Bullying</label>
            </div>
            
            <div class="denuncia-option" onclick="selecionarMotivo('conteudo_inapropriado')">
                <input type="radio" name="motivo" value="conteudo_inapropriado" id="motivo-inapropriado">
                <label for="motivo-inapropriado">🔞 Conteúdo Inapropriado</label>
            </div>
            
            <div class="denuncia-option" onclick="selecionarMotivo('informacao_falsa')">
                <input type="radio" name="motivo" value="informacao_falsa" id="motivo-falsa">
                <label for="motivo-falsa">❌ Informação Falsa</label>
            </div>
            
            <div class="denuncia-option" onclick="selecionarMotivo('outro')">
                <input type="radio" name="motivo" value="outro" id="motivo-outro">
                <label for="motivo-outro">📝 Outro</label>
            </div>
            
            <div class="denuncia-outro" id="denuncia-outro-campo">
                <textarea class="form-control" id="outro-motivo" rows="3" placeholder="Descreva o motivo (máx. 200 caracteres)" maxlength="200"></textarea>
            </div>
            
            <button class="btn-submit-denuncia" onclick="enviarDenuncia()">
                Enviar Denúncia
            </button>
        </div>
    </div>
</div>

<script>
// Variáveis globais
let comentarioParaDenunciar = null;
let motivoSelecionado = null;
let lastUpdateTimestamp = <?php echo time(); ?>;

// Fechar menu ao clicar fora
document.addEventListener('click', function(e) {
    if (!e.target.closest('.comment-menu')) {
        document.querySelectorAll('.comment-menu-dropdown').forEach(menu => {
            menu.classList.remove('show');
        });
    }
});

// Toggle menu de comentário
function toggleCommentMenu(comentarioId) {
    event.stopPropagation();
    const menu = document.getElementById('menu-' + comentarioId);
    
    // Fechar todos os outros menus
    document.querySelectorAll('.comment-menu-dropdown').forEach(m => {
        if (m.id !== 'menu-' + comentarioId) {
            m.classList.remove('show');
        }
    });
    
    menu.classList.toggle('show');
}

// Curtir post
function curtir(id) {
    <?php if (!usuarioLogado()): ?>
        alert("❤️ Faça login para curtir!");
        window.location.href = 'backend/login.php';
        return;
    <?php endif; ?>

    fetch('backend/curtir.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'post_id=' + id
    })
    .then(r => r.json())
    .then(data => {
        if (data.sucesso) {
            const btn = document.getElementById('btn-like-' + id);
            btn.classList.toggle('liked');
            document.getElementById('contador-gostos-'+id).textContent = data.total;
        }
    });
}

// Enviar comentário
function enviarComentario(id) {
    const texto = document.getElementById('campo-comentario').value.trim();
    if (!texto) {
        alert("✍️ Escreva um comentário antes de enviar!");
        return;
    }

    fetch('backend/comentar.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'post_id=' + id + '&comentario=' + encodeURIComponent(texto)
    })
    .then(r => r.text())
    .then(msg => {
        document.getElementById('msg-comentario').innerHTML = '<div class="alert alert-success">✅ ' + msg + '</div>';
        document.getElementById('campo-comentario').value = '';
        
        // Atualizar comentários em real-time
        setTimeout(() => {
            carregarComentarios();
        }, 500);
    });
}

// Like em comentário
function likeComment(comentarioId) {
    fetch('backend/comentarios_interacao.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'acao=like&comentario_id=' + comentarioId
    })
    .then(r => r.json())
    .then(data => {
        if (data.sucesso) {
            document.getElementById('like-count-' + comentarioId).textContent = data.likes;
            document.getElementById('dislike-count-' + comentarioId).textContent = data.dislikes;
            document.getElementById('like-btn-' + comentarioId).classList.toggle('active');
            document.getElementById('dislike-btn-' + comentarioId).classList.remove('active');
        }
    });
}

// Dislike em comentário
function dislikeComment(comentarioId) {
    fetch('backend/comentarios_interacao.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'acao=dislike&comentario_id=' + comentarioId
    })
    .then(r => r.json())
    .then(data => {
        if (data.sucesso) {
            document.getElementById('like-count-' + comentarioId).textContent = data.likes;
            document.getElementById('dislike-count-' + comentarioId).textContent = data.dislikes;
            document.getElementById('dislike-btn-' + comentarioId).classList.toggle('active');
            document.getElementById('like-btn-' + comentarioId).classList.remove('active');
        }
    });
}

// Toggle resposta
function toggleReply(comentarioId, nomeAutor) {
    const form = document.getElementById('reply-form-' + comentarioId);
    const textarea = document.getElementById('reply-text-' + comentarioId);
    
    if (form.style.display === 'none') {
        form.style.display = 'block';
        textarea.focus();
    } else {
        form.style.display = 'none';
    }
}

// Enviar resposta
function enviarResposta(comentarioId, nomeAutor) {
    const textarea = document.getElementById('reply-text-' + comentarioId);
    let texto = textarea.value.trim();
    
    if (!texto) {
        alert("✍️ Escreva uma resposta!");
        return;
    }
    
    if (!texto.startsWith('@')) {
        texto = '@' + nomeAutor + ' ' + texto;
    }

    fetch('backend/responder_comentario.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'comentario_id=' + comentarioId + '&resposta=' + encodeURIComponent(texto)
    })
    .then(r => r.json())
    .then(data => {
        if (data.sucesso) {
            textarea.value = '';
            document.getElementById('reply-form-' + comentarioId).style.display = 'none';
            
            // Recarregar comentários
            carregarComentarios();
        } else {
            alert('❌ Erro: ' + data.mensagem);
        }
    });
}

// Continua na Parte 3...
// CONTINUAÇÃO DO JAVASCRIPT DO artigo.php

// Confirmar apagar comentário
function confirmarApagarComentario(comentarioId) {
    if (confirm('⚠️ Tem certeza que deseja apagar este comentário?\n\nTodas as respostas também serão apagadas.\n\nEsta ação não pode ser desfeita.')) {
        apagarComentario(comentarioId);
    }
}

// Apagar comentário (Real-Time)
function apagarComentario(comentarioId) {
    fetch('backend/api/delete_comment_realtime.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'comentario_id=' + comentarioId
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            alert('✅ ' + data.message);
            
            // Remover comentário da DOM imediatamente
            const comentarioElement = document.getElementById('comment-' + comentarioId);
            if (comentarioElement) {
                comentarioElement.style.transition = 'all 0.3s ease';
                comentarioElement.style.opacity = '0';
                comentarioElement.style.transform = 'translateX(-50px)';
                
                setTimeout(() => {
                    comentarioElement.remove();
                    
                    // Verificar se não há mais comentários
                    const comentarios = document.querySelectorAll('.comment-box');
                    if (comentarios.length === 0) {
                        document.getElementById('lista-comentarios').innerHTML = `
                            <div class="text-center py-5" id="empty-state">
                                <div style="font-size: 3rem;">💬</div>
                                <p class="text-muted mt-3">Seja o primeiro a comentar!</p>
                            </div>
                        `;
                    }
                }, 300);
            }
            
            // Atualizar timestamp
            lastUpdateTimestamp = data.timestamp;
        } else {
            alert('❌ ' + data.message);
        }
    })
    .catch(err => {
        console.error(err);
        alert('❌ Erro ao apagar comentário');
    });
}

// Abrir modal de denúncia
function abrirModalDenuncia(comentarioId) {
    comentarioParaDenunciar = comentarioId;
    motivoSelecionado = null;
    
    // Resetar formulário
    document.querySelectorAll('input[name="motivo"]').forEach(input => {
        input.checked = false;
    });
    document.getElementById('denuncia-outro-campo').classList.remove('show');
    document.getElementById('outro-motivo').value = '';
    
    // Abrir modal
    document.getElementById('modal-denuncia').classList.add('show');
}

// Fechar modal de denúncia
function fecharModalDenuncia() {
    document.getElementById('modal-denuncia').classList.remove('show');
    comentarioParaDenunciar = null;
    motivoSelecionado = null;
}

// Selecionar motivo de denúncia
function selecionarMotivo(motivo) {
    motivoSelecionado = motivo;
    document.getElementById('motivo-' + (motivo === 'discurso_odio' ? 'odio' : motivo === 'conteudo_inapropriado' ? 'inapropriado' : motivo === 'informacao_falsa' ? 'falsa' : motivo)).checked = true;
    
    // Mostrar/esconder campo "outro"
    if (motivo === 'outro') {
        document.getElementById('denuncia-outro-campo').classList.add('show');
    } else {
        document.getElementById('denuncia-outro-campo').classList.remove('show');
    }
}

// Enviar denúncia
function enviarDenuncia() {
    if (!motivoSelecionado) {
        alert('⚠️ Por favor, selecione um motivo para a denúncia');
        return;
    }
    
    let outro = '';
    if (motivoSelecionado === 'outro') {
        outro = document.getElementById('outro-motivo').value.trim();
        if (!outro) {
            alert('⚠️ Por favor, descreva o motivo da denúncia');
            return;
        }
    }
    
    fetch('backend/api/report_comment.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'comentario_id=' + comentarioParaDenunciar + '&motivo=' + motivoSelecionado + '&outro=' + encodeURIComponent(outro)
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            alert('✅ ' + data.message + '\n\nNossa equipe irá analisar a denúncia.');
            fecharModalDenuncia();
        } else {
            alert('❌ ' + data.message);
        }
    })
    .catch(err => {
        console.error(err);
        alert('❌ Erro ao enviar denúncia');
    });
}

// Carregar comentários (Real-Time)
function carregarComentarios() {
    const postId = <?php echo $postId; ?>;
    
    fetch('backend/api/get_comments.php?post_id=' + postId)
    .then(r => r.json())
    .then(data => {
        if (data.comentarios && data.comentarios.length > 0) {
            // Atualizar lista de comentários
            renderizarComentarios(data.comentarios);
            lastUpdateTimestamp = Math.floor(Date.now() / 1000);
        }
    })
    .catch(err => console.error('Erro ao carregar comentários:', err));
}

// Renderizar comentários (com estrutura hierárquica)
function renderizarComentarios(comentarios) {
    const container = document.getElementById('lista-comentarios');
    
    // Remover empty state se existir
    const emptyState = document.getElementById('empty-state');
    if (emptyState) {
        emptyState.remove();
    }
    
    // Criar estrutura hierárquica
    const comentariosMap = {};
    const comentariosPrincipais = [];
    
    comentarios.forEach(c => {
        comentariosMap[c.comentario_id] = { ...c, respostas: [] };
    });
    
    comentarios.forEach(c => {
        if (c.pai_id) {
            if (comentariosMap[c.pai_id]) {
                comentariosMap[c.pai_id].respostas.push(comentariosMap[c.comentario_id]);
            }
        } else {
            comentariosPrincipais.push(comentariosMap[c.comentario_id]);
        }
    });
    
    // Renderizar
    let html = '';
    comentariosPrincipais.forEach(c => {
        html += renderizarComentario(c, 0);
    });
    
    container.innerHTML = html;
}

// Renderizar um comentário individual
function renderizarComentario(comentario, nivel) {
    const marginLeft = nivel > 0 ? `margin-left: ${nivel * 40}px; border-left: 3px solid #FF6B35;` : '';
    const likes = comentario.likes ? comentario.likes.length : 0;
    const dislikes = comentario.dislikes ? comentario.dislikes.length : 0;
    
    <?php if (usuarioLogado()): ?>
    const ehMeu = comentario.usuario_id === <?php echo $_SESSION['usuario_id']; ?>;
    const userLiked = comentario.likes && comentario.likes.includes(<?php echo $_SESSION['usuario_id']; ?>);
    const userDisliked = comentario.dislikes && comentario.dislikes.includes(<?php echo $_SESSION['usuario_id']; ?>);
    <?php else: ?>
    const ehMeu = false;
    const userLiked = false;
    const userDisliked = false;
    <?php endif; ?>
    
    let html = `
        <div class="comment-box" style="${marginLeft}" id="comment-${comentario.comentario_id}" data-comment-id="${comentario.comentario_id}">
            <div class="comment-header">
                <div class="comment-author">${escapeHtml(comentario.nome)}</div>
    `;
    
    <?php if (usuarioLogado()): ?>
    html += `
                <div class="comment-menu">
                    <button class="comment-menu-btn" onclick="toggleCommentMenu(${comentario.comentario_id})">⋮</button>
                    <div class="comment-menu-dropdown" id="menu-${comentario.comentario_id}">
                        ${ehMeu ? `
                            <button class="comment-menu-item danger" onclick="confirmarApagarComentario(${comentario.comentario_id})">
                                <i class="bi bi-trash-fill"></i> Apagar Comentário
                            </button>
                        ` : `
                            <button class="comment-menu-item danger" onclick="abrirModalDenuncia(${comentario.comentario_id})">
                                <i class="bi bi-flag-fill"></i> Denunciar Comentário
                            </button>
                        `}
                    </div>
                </div>
    `;
    <?php endif; ?>
    
    html += `
            </div>
            <div class="comment-text">${formatarMencoes(comentario.comentario)}</div>
            <div class="comment-date">${formatarData(comentario.data)}</div>
            <div class="comment-actions">
    `;
    
    <?php if (usuarioLogado()): ?>
    html += `
                <button onclick="likeComment(${comentario.comentario_id})" class="btn-comment-action ${userLiked ? 'active' : ''}" id="like-btn-${comentario.comentario_id}">
                    <i class="bi bi-hand-thumbs-up-fill"></i> <span id="like-count-${comentario.comentario_id}">${likes}</span>
                </button>
                <button onclick="dislikeComment(${comentario.comentario_id})" class="btn-comment-action ${userDisliked ? 'active' : ''}" id="dislike-btn-${comentario.comentario_id}">
                    <i class="bi bi-hand-thumbs-down-fill"></i> <span id="dislike-count-${comentario.comentario_id}">${dislikes}</span>
                </button>
                <button onclick="toggleReply(${comentario.comentario_id}, '${escapeHtml(comentario.nome).replace(/'/g, "\\'")}')" class="btn-comment-action">
                    <i class="bi bi-reply-fill"></i> Responder
                </button>
            </div>
            <div class="reply-form" id="reply-form-${comentario.comentario_id}" style="display: none;">
                <div class="reply-to-info">Respondendo a <strong>@${escapeHtml(comentario.nome)}</strong></div>
                <textarea class="form-control" id="reply-text-${comentario.comentario_id}" rows="2" placeholder="@${escapeHtml(comentario.nome)} Escreva sua resposta..."></textarea>
                <button onclick="enviarResposta(${comentario.comentario_id}, '${escapeHtml(comentario.nome).replace(/'/g, "\\'")}')" class="btn btn-sm btn-primary-custom mt-2">Enviar Resposta</button>
            </div>
    `;
    <?php else: ?>
    html += `
                <button onclick="redirecionarLogin()" class="btn-comment-action">
                    <i class="bi bi-hand-thumbs-up-fill"></i> <span>${likes}</span>
                </button>
                <button onclick="redirecionarLogin()" class="btn-comment-action">
                    <i class="bi bi-hand-thumbs-down-fill"></i> <span>${dislikes}</span>
                </button>
            </div>
    `;
    <?php endif; ?>
    
    html += `</div>`;
    
    // Renderizar respostas
    if (comentario.respostas && comentario.respostas.length > 0) {
        comentario.respostas.forEach(resposta => {
            html += renderizarComentario(resposta, nivel + 1);
        });
    }
    
    return html;
}

// Funções auxiliares
function escapeHtml(text) {
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return text.replace(/[&<>"']/g, m => map[m]);
}

function formatarMencoes(texto) {
    texto = escapeHtml(texto);
    return texto.replace(/@(\w+)/g, '<strong class="mention">@$1</strong>');
}

function formatarData(timestamp) {
    const date = new Date(timestamp * 1000);
    const day = String(date.getDate()).padStart(2, '0');
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const year = date.getFullYear();
    const hours = String(date.getHours()).padStart(2, '0');
    const minutes = String(date.getMinutes()).padStart(2, '0');
    return `${day}/${month}/${year} ${hours}:${minutes}`;
}

// Verificar atualizações (Real-Time Polling)
function verificarAtualizacoes() {
    const postId = <?php echo $postId; ?>;
    
    fetch(`backend/api/check_updates.php?post_id=${postId}&last_update=${lastUpdateTimestamp}`)
    .then(r => r.json())
    .then(data => {
        if (data.hasUpdates) {
            carregarComentarios();
        }
    })
    .catch(err => console.error('Erro ao verificar atualizações:', err));
}

// Verificar suspensão (para forçar logout)
<?php if (usuarioLogado()): ?>
function verificarSuspensao() {
    fetch('backend/api/check_suspension.php')
    .then(r => r.json())
    .then(data => {
        if (data.suspended) {
            alert('🚫 ' + data.message + '\n\nVocê será redirecionado.');
            window.location.href = 'index.php';
        }
    })
    .catch(err => console.error('Erro ao verificar suspensão:', err));
}

// Verificar suspensão a cada 10 segundos
setInterval(verificarSuspensao, 10000);
<?php endif; ?>

// Verificar atualizações a cada 3 segundos
setInterval(verificarAtualizacoes, 3000);

// Redirecionar para login
function redirecionarLogin() {
    if (confirm('Você precisa fazer login para interagir. Deseja ir para a página de login?')) {
        window.location.href = 'backend/login.php';
    }
}

// Compartilhar
function compartilhar() {
    if (navigator.share) {
        navigator.share({
            title: '<?php echo addslashes($post['titulo']); ?>',
            url: window.location.href
        });
    } else {
        navigator.clipboard.writeText(window.location.href);
        alert("📋 Link copiado para área de transferência!");
    }
}

// Inicializar
document.addEventListener('DOMContentLoaded', function() {
    console.log('🚀 Sistema Real-Time iniciado');
});
</script>

<?php require 'includes/footer.php'; ?>