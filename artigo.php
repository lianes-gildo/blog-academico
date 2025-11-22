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
    echo "Artigo não encontrado.";
    exit;
}

$estatisticas = json_decode(file_get_contents('data/estatisticas.json'), true);
$estatisticas['visitas'][$postId] = ($estatisticas['visitas'][$postId] ?? 0) + 1;
file_put_contents('data/estatisticas.json', json_encode($estatisticas, JSON_PRETTY_PRINT));

$gostou = false;
if (usuarioLogado()) {
    $gostosLista = $estatisticas['gostos'][$postId] ?? [];
    $gostou = in_array($_SESSION['usuario_id'], $gostosLista);
}
?>
<main class="container artigo-detalhe">
    <article>
        <h1><?php echo htmlspecialchars($post['titulo']); ?></h1>
        <img src="<?php echo $post['imagem']; ?>" alt="" class="img-artigo">
        <div class="info-artigo">
            <strong><?php echo htmlspecialchars($post['autor']); ?></strong> • 
            <?php echo date('d/m/Y H:i', strtotime($post['data'])); ?>
        </div>
        <p class="descricao-longa"><?php echo nl2br(htmlspecialchars($post['descricao_longa'])); ?></p>

        <div class="acoes-artigo">
            <button onclick="curtir(<?php echo $postId; ?>)" class="btn-gostar <?php echo $gostou ? 'gostou' : ''; ?>">
                ❤️ <span id="contador-gostos-<?php echo $postId; ?>"><?php echo $post['gostos']; ?></span>
            </button>
            <button onclick="compartilhar()" class="btn-compartilhar">Compartilhar</button>
            <span>👁️ <?php echo $estatisticas['visitas'][$postId]; ?></span>
        </div>
    </article>

    <section class="comentarios">
        <h3>Comentários</h3>
        <div id="lista-comentarios">
            <?php
            $todosComentarios = json_decode(file_get_contents('data/comentarios.json'), true) ?? [];
            foreach ($todosComentarios as $c) {
                if ($c['artigo_id'] == $postId) {
                    echo "<div class='comentario'><strong>" . htmlspecialchars($c['nome']) . "</strong>: " . htmlspecialchars($c['comentario']) . 
                         "<br><small>" . date('d/m/Y H:i', $c['data']) . "</small></div>";
                }
            }
            ?>
        </div>

        <?php if (usuarioLogado()): ?>
            <div class="adicionar-comentario">
                <textarea id="campo-comentario" placeholder="Escreva seu comentário..."></textarea>
                <button onclick="enviarComentario(<?php echo $postId; ?>)">Enviar</button>
                <p id="msg-comentario"></p>
            </div>
        <?php else: ?>
            <p><a href="backend/login.php">Faça login</a> para comentar.</p>
        <?php endif; ?>
    </section>
</main>

<script>
function curtir(id) {
    <?php if (!usuarioLogado()): ?>
        alert("Faça login para curtir!");
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
            const btn = document.querySelector('.btn-gostar');
            btn.classList.toggle('gostou');
            document.getElementById('contador-gostos-'+id).textContent = data.total;
        }
    });
}

function enviarComentario(id) {
    const texto = document.getElementById('campo-comentario').value.trim();
    if (!texto) return;

    fetch('backend/comentar.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'post_id=' + id + '&comentario=' + encodeURIComponent(texto)
    })
    .then(r => r.text())
    .then(msg => {
        document.getElementById('msg-comentario').textContent = msg;
        document.getElementById('campo-comentario').value = '';
        setTimeout(() => location.reload(), 1000);
    });
}

function compartilhar() {
    if (navigator.share) {
        navigator.share({
            title: '<?php echo addslashes($post['titulo']); ?>',
            url: window.location.href
        });
    } else {
        navigator.clipboard.writeText(window.location.href);
        alert("Link copiado para área de transferência!");
    }
}
</script>

<?php require 'includes/footer.php'; ?>