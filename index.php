<?php require 'includes/header.php'; ?>
<main class="container principal">
    <h2 class="titulo-secao">Últimos Artigos Acadêmicos</h2>
    <div class="grid-posts">
        <?php
        $posts = json_decode(file_get_contents('data/posts.json'), true);
        if ($posts) {
            // Ordenar por data (mais recente primeiro)
            usort($posts, function($a, $b) {
                return strtotime($b['data']) - strtotime($a['data']);
            });

            foreach ($posts as $post) {
                $postId = $post['id'];
                $visitas = json_decode(file_get_contents('data/estatisticas.json'), true)['visitas'][$postId] ?? 0;
                $gostos = $post['gostos'];
                ?>
                <article class="card-post">
                    <img src="<?php echo $post['imagem']; ?>" alt="<?php echo htmlspecialchars($post['titulo']); ?>">
                    <div class="conteudo-card">
                        <h3><?php echo htmlspecialchars($post['titulo']); ?></h3>
                        <p class="descricao-curta"><?php echo htmlspecialchars($post['descricao_curta']); ?></p>
                        <div class="info-post">
                            <span>Por <?php echo htmlspecialchars($post['autor']); ?></span>
                            <span><?php echo date('d/m/Y H:i', strtotime($post['data'])); ?></span>
                        </div>
                        <div class="estatisticas">
                            <span>❤️ <?php echo $gostos; ?></span>
                            <span>👁️ <?php echo $visitas; ?></span>
                        </div>
                        <a href="artigo.php?id=<?php echo $postId; ?>" class="btn-ler-mais">Ler mais</a>
                    </div>
                </article>
                <?php
            }
        } else {
            echo "<p>Nenhum post publicado ainda.</p>";
        }
        ?>
    </div>
</main>
<?php require 'includes/footer.php'; ?>