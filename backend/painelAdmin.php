<?php
// =========================================
// painelAdmin.php
// Painel administrativo para gerenciar posts e ver estatísticas.
// =========================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

date_default_timezone_set('Africa/Maputo');

// Verifica login e papel
$usuarioLogado = $_SESSION['usuario'] ?? null;
if (!$usuarioLogado) {
    header('Location: /backend/login.php');
    exit;
}
if (($usuarioLogado['papel'] ?? 'usuario') !== 'admin') {
    echo 'Acesso negado. Esta área é restrita a administradores.';
    exit;
}

// Carrega usuários
$arquivoUsuarios = __DIR__ . '/../data/usuarios.json';
$usuarios = file_exists($arquivoUsuarios) ? json_decode(file_get_contents($arquivoUsuarios), true) : [];

// Carrega posts
$arquivoPosts = __DIR__ . '/../data/posts.json';
$posts = file_exists($arquivoPosts) ? json_decode(file_get_contents($arquivoPosts), true) : [];

// Estatísticas
$arquivoEstat = __DIR__ . '/../data/estatisticas.json';
$estatisticas = file_exists($arquivoEstat) ? json_decode(file_get_contents($arquivoEstat), true) : ['gostos'=>[], 'visitas'=>[], 'compartilhamentos'=>[]];

// Totais
$totalPosts = count($posts);
$totalUsuarios = count($usuarios);

// Média likes
$somaLikes = 0;
foreach ($posts as $p) $somaLikes += (int)($p['gostos'] ?? 0);
$mediaLikes = $totalPosts ? round($somaLikes / $totalPosts, 2) : 0;

// Inclui header compartilhado
require __DIR__ . '/../includes/header.php';
?>

<main class="conteudo conteudo-limitado">
<section class="cabecalho-pagina-admin">
    <div>
        <h1>Dashboard Admin</h1>
        <p class="texto-suave">Gerencie posts, usuários e acompanhe estatísticas do blog.</p>
    </div>
    <div class="acoes-topo-admin">
        <a class="botao-primario" href="/backend/adicionar_post.php">Adicionar post</a>
        <a class="btn secundario" href="/index.php">Ver site</a>
    </div>
</section>

<section class="cards-dashboard">
    <article class="card-stat">
        <h3>Total de Posts</h3>
        <p class="numero-stat"><?php echo $totalPosts; ?></p>
        <span class="legenda-stat">Conteúdos publicados</span>
    </article>

    <article class="card-stat">
        <h3>Total de Usuários</h3>
        <p class="numero-stat"><?php echo $totalUsuarios; ?></p>
        <span class="legenda-stat">Contas registradas</span>
    </article>

    <article class="card-stat">
        <h3>Média de Likes</h3>
        <p class="numero-stat"><?php echo $mediaLikes; ?></p>
        <span class="legenda-stat">Por post publicado</span>
    </article>
</section>

<section class="secao-tabela-posts">
    <header class="cabecalho-secao">
        <h2>Gerir posts</h2>
        <p class="texto-suave">Edite ou apague posts existentes. Use o botão "Adicionar post" para criar novos conteúdos.</p>
    </header>

    <div class="tabela-container">
        <table class="tabela-gerir">
            <thead>
            <tr>
                <th>#ID</th>
                <th>Imagem</th>
                <th>Título</th>
                <th>Descrição curta</th>
                <th>Gostos</th>
                <th>Visitas</th>
                <th>Ações</th>
            </tr>
            </thead>
            <tbody>
            <?php if (empty($posts)): ?>
                <tr><td colspan="7" style="text-align:center;">Nenhum post cadastrado ainda.</td></tr>
            <?php else: ?>
                <?php foreach ($posts as $p): ?>
                    <tr>
                        <td><?php echo (int)$p['id']; ?></td>
                        <td class="coluna-imagem">
                            <?php if (!empty($p['imagem'])): ?>
                                <img 
                                    src="<?php echo htmlspecialchars('/' . ltrim($p['imagem'], '/')); ?>" 
                                    alt="Imagem do post" 
                                    class="miniatura-post">
                            <?php else: ?>
                                <span class="texto-suave">Sem imagem</span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo htmlspecialchars($p['titulo']); ?></td>
                        <td><?php echo htmlspecialchars($p['descricao_curta']); ?></td>
                        <td><?php echo (int)($p['gostos'] ?? 0); ?></td>
                        <td><?php echo (int)($p['visitas'] ?? 0); ?></td>
                        <td class="coluna-acoes">
                            <a class="btn-acoes editar" href="/backend/editar_post.php?id=<?php echo (int)$p['id']; ?>">Editar</a>
                            <a class="btn-acoes apagar" href="/backend/apagar_post.php?id=<?php echo (int)$p['id']; ?>" onclick="return confirm('Apagar post #<?php echo (int)$p['id']; ?>?');">Apagar</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>
</main>

<style>
/* ========================== */
/* Ajuste das miniaturas de posts */
.miniatura-post {
    width: 80px;       /* largura fixa */
    height: 50px;      /* altura fixa */
    object-fit: cover; /* mantém proporção, corta se necessário */
    border-radius: 4px;
    border: 1px solid #ccc;
}
.coluna-imagem {
    text-align: center;
    width: 100px;
}
.coluna-acoes a {
    margin-right: 4px;
    padding: 4px 6px;
    font-size: 0.85rem;
    border-radius: 4px;
    text-decoration: none;
}
.btn-acoes.editar { background-color: #4CAF50; color: #fff; }
.btn-acoes.apagar { background-color: #f44336; color: #fff; }
</style>

<?php require __DIR__ . '/../includes/footer.php'; ?>
