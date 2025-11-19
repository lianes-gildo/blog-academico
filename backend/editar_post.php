<?php
session_start();
date_default_timezone_set('Africa/Maputo');

// Verifica se usuário logado
$usuario = $_SESSION['usuario'] ?? null;
if (!$usuario || ($usuario['papel'] ?? '') !== 'admin') {
    echo "Acesso negado.";
    exit;
}

$arquivoPosts = __DIR__ . '/../data/posts.json';
$posts = json_decode(file_get_contents($arquivoPosts), true) ?: [];

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$indice = null;
foreach ($posts as $k => $p) if ($p['id'] === $id) { $indice = $k; break; }
if ($indice === null) { echo "Post não encontrado."; exit; }

$mensagem = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titulo = trim($_POST['titulo'] ?? '');
    $descricao_curta = trim($_POST['descricao_curta'] ?? '');
    $descricao_longa = trim($_POST['descricao_longa'] ?? '');

    if (!$titulo || !$descricao_curta || !$descricao_longa) {
        $mensagem = 'Preencha todos os campos.';
    } else {
        $posts[$indice]['titulo'] = $titulo;
        $posts[$indice]['descricao_curta'] = $descricao_curta;
        $posts[$indice]['descricao_longa'] = $descricao_longa;

        // Upload nova imagem
        if (!empty($_FILES['imagem']['name']) && $_FILES['imagem']['error'] === UPLOAD_ERR_OK) {
            $ext = pathinfo($_FILES['imagem']['name'], PATHINFO_EXTENSION);
            $nomeArquivo = time() . '_' . bin2hex(random_bytes(5)) . '.' . $ext;
            $destinoRel = 'assets/img/posts/' . $nomeArquivo;
            $destinoAbs = __DIR__ . '/../' . $destinoRel;
            if (!is_dir(dirname($destinoAbs))) mkdir(dirname($destinoAbs), 0755, true);
            if (move_uploaded_file($_FILES['imagem']['tmp_name'], $destinoAbs)) {
                if (!empty($posts[$indice]['imagem']) && strpos($posts[$indice]['imagem'], 'default') === false) {
                    $antigo = __DIR__ . '/../' . $posts[$indice]['imagem'];
                    if (is_file($antigo)) @unlink($antigo);
                }
                $posts[$indice]['imagem'] = $destinoRel;
            }
        }

        file_put_contents($arquivoPosts, json_encode($posts, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        header('Location: /backend/painelAdmin.php');
        exit;
    }
}

$post = $posts[$indice];
?>
<!doctype html>
<html lang="pt-BR">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Editar Post</title>
<link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
<main class="conteudo" style="max-width:900px;margin:28px auto;">
<section class="formulario">
<h2>Editar Post #<?php echo $post['id']; ?></h2>
<?php if ($mensagem): ?><div style="color:var(--danger)"><?php echo htmlspecialchars($mensagem); ?></div><?php endif; ?>
<form method="post" enctype="multipart/form-data">
<label>Título</label>
<input class="input" type="text" name="titulo" value="<?php echo htmlspecialchars($post['titulo']); ?>" required>
<label>Descrição curta</label>
<input class="input" type="text" name="descricao_curta" value="<?php echo htmlspecialchars($post['descricao_curta']); ?>" required>
<label>Descrição longa</label>
<textarea class="input" name="descricao_longa" rows="8" required><?php echo htmlspecialchars($post['descricao_longa']); ?></textarea>
<label>Trocar imagem (opcional)</label>
<input id="input-imagem-preview" class="input" type="file" name="imagem" accept="image/*">
<div style="margin-top:8px;">
<img id="preview-imagem" src="/<?php echo htmlspecialchars($post['imagem']); ?>" alt="Preview" style="max-width:200px;border-radius:8px;">
</div>
<div style="display:flex;gap:8px;margin-top:12px;">
<button class="botao-primario" type="submit">Salvar</button>
<a class="btn secundario" href="/backend/painelAdmin.php" style="text-decoration:none;align-self:center;">Voltar</a>
</div>
</form>
</section>
</main>
<script src="/assets/js/script.js"></script>
</body>
</html>
