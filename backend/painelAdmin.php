<?php
session_start();
require '../includes/header.php';


if (!isset($_SESSION['papel']) || $_SESSION['papel'] !== 'admin') {
    header('Location: ../index.php');
    exit;
}


$posts = json_decode(file_get_contents('../data/posts.json'), true) ?? [];
$usuarios = json_decode(file_get_contents('../data/usuarios.json'), true) ?? [];

$totalPosts = count($posts);
$totalUsuarios = count($usuarios);
?>

<main class="container">
    <h2>Painel Administrativo</h2>

    <div class="cards-stats">
        <div class="card">
            <strong><?php echo $totalPosts; ?></strong>
            <span>Posts Publicados</span>
        </div>
        <div class="card">
            <strong><?php echo $totalUsuarios; ?></strong>
            <span>Usuários Registrados</span>
        </div>
    </div>

    <h3>Gerir Posts</h3>

        <div class="add-post-link">
        <a href="adicionar_post.php" class="btn btn-grande">+ Adicionar Novo Post</a>
    </div>
    <?php if (empty($posts)): ?>
        <p>Nenhum post publicado ainda.</p>
    <?php else: ?>
        <div class="tabela-responsiva">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Imagem</th>
                        <th>Título</th>
                        <th>Descrição Curta</th>
                        <th>Data</th>
                        <th>Likes</th>
                        <th>Visitas</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($posts as $post): 
                        $visitas = json_decode(file_get_contents('../data/estatisticas.json'), true)['visitas'][$post['id']] ?? 0;
                    ?>
                    <tr>
                        <td><?php echo $post['id']; ?></td>
                        <td><img src="<?php echo $post['imagem']; ?>" alt="Imagem do Post"></td>
                        <td><strong><?php echo htmlspecialchars($post['titulo']); ?></strong></td>
                        <td><?php echo htmlspecialchars(mb_substr($post['descricao_curta'], 0, 80)) . '...'; ?></td>
                        <td><?php echo date('d/m/Y H:i', strtotime($post['data'])); ?></td>
                        <td><?php echo $post['gostos']; ?></td>
                        <td><?php echo $visitas; ?></td>
                        <td class="acoes">
                            <a href="editar_post.php?id=<?php echo $post['id']; ?>" class="btn-pequeno">Editar</a>
                            <a href="apagar_post.php?id=<?php echo $post['id']; ?>" 
                               class="btn-pequeno btn-vermelho"
                               onclick="return confirm('Tens certeza que queres apagar este post?')">Apagar</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>

    <?php if (!empty($posts)): ?>
    <div class="graficos-admin">
        <div class="grafico-box">
            <h3>Likes por Artigo</h3>
            <canvas id="graficoLikes"></canvas>
        </div>
    </div>
    <?php endif; ?>
</main>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    <?php if (!empty($posts)): ?>
    const ctx = document.getElementById('graficoLikes').getContext('2d');

    const titulos = <?php 
        $t = array_column($posts, 'titulo');
        foreach($t as &$titulo) {
            $titulo = strlen($titulo) > 25 ? substr($titulo, 0, 25) . '...' : $titulo;
        }
        echo json_encode($t);
    ?>;

    const likes = <?php echo json_encode(array_column($posts, 'gostos')); ?>;

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: titulos,
            datasets: [{
                label: 'Likes',
                data: likes,
                backgroundColor: '#e74c3c',
                borderColor: '#c0392b',
                borderWidth: 1,
                borderRadius: 8,
                borderSkipped: false,
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        title: function(context) {
                            const titulosCompletos = <?php echo json_encode(array_column($posts, 'titulo')); ?>;
                            return titulosCompletos[context[0].dataIndex];
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                }
            }
        }
    });
    <?php endif; ?>
</script>

<?php require '../includes/footer.php'; ?>