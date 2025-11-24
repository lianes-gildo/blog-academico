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
    }
    
    .comment-author {
        font-weight: 700;
        color: var(--primary-blue);
        margin-bottom: 8px;
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
            
            // Função para formatar @menções em negrito
            function formatarMencoes($texto) {
                // Proteger contra XSS
                $texto = htmlspecialchars($texto);
                // Substituir @usuario por versão em negrito
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
                
                $marginLeft = $nivel > 0 ? ' style="margin-left: ' . ($nivel * 40) . 'px; border-left: 3px solid #FF6B35;"' : '';
                
                echo '<div class="comment-box"' . $marginLeft . ' id="comment-' . $comentarioId . '">';
                echo '<div class="comment-author">' . $nomeAutor . '</div>';
                echo '<div class="comment-text">' . formatarMencoes($c['comentario']) . '</div>';
                echo '<div class="comment-date">' . date('d/m/Y H:i', $c['data']) . '</div>';
                
                // Ações de comentário
                echo '<div class="comment-actions">';
                
                // Botões de like/dislike (visíveis para todos, mas só clicáveis se logado)
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
                    // Usuário não logado vê contadores mas não pode clicar
                    echo '<button onclick="redirecionarLogin()" class="btn-comment-action">';
                    echo '<i class="bi bi-hand-thumbs-up-fill"></i> <span>' . $likes . '</span>';
                    echo '</button>';
                    
                    echo '<button onclick="redirecionarLogin()" class="btn-comment-action">';
                    echo '<i class="bi bi-hand-thumbs-down-fill"></i> <span>' . $dislikes . '</span>';
                    echo '</button>';
                }
                
                echo '</div>';
                
                // Formulário de resposta (só para logados)
                if ($usuarioLogado) {
                    echo '<div class="reply-form" id="reply-form-' . $comentarioId . '" style="display: none;">';
                    echo '<div class="reply-to-info">Respondendo a <strong>@' . $nomeAutor . '</strong></div>';
                    echo '<textarea class="form-control" id="reply-text-' . $comentarioId . '" rows="2" placeholder="@' . $nomeAutor . ' Escreva sua resposta..."></textarea>';
                    echo '<button onclick="enviarResposta(' . $comentarioId . ', \'' . addslashes($c['nome']) . '\')" class="btn btn-sm btn-primary-custom mt-2">Enviar Resposta</button>';
                    echo '</div>';
                }
                
                echo '</div>'; // Fecha comment-box
                
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
                echo '<div class="text-center py-5">';
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

<script>
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
        setTimeout(() => location.reload(), 1000);
    });
}

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

function enviarResposta(comentarioId, nomeAutor) {
    const textarea = document.getElementById('reply-text-' + comentarioId);
    let texto = textarea.value.trim();
    
    if (!texto) {
        alert("✍️ Escreva uma resposta!");
        return;
    }
    
    // Garantir que começa com @nome
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
            // Limpar campo
            textarea.value = '';
            document.getElementById('reply-form-' + comentarioId).style.display = 'none';
            
            // Recarregar para mostrar resposta
            location.reload();
        } else {
            alert('❌ Erro: ' + data.mensagem);
        }
    })
    .catch(err => {
        console.error(err);
        alert('❌ Erro ao enviar resposta');
    });
}

function redirecionarLogin() {
    if (confirm('Você precisa fazer login para interagir. Deseja ir para a página de login?')) {
        window.location.href = 'backend/login.php';
    }
}

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
</script>

<?php require 'includes/footer.php'; ?>