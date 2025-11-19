<?php
session_start();
date_default_timezone_set('Africa/Maputo');

// Verifica se usuário logado
$usuario = $_SESSION['usuario'] ?? null;
if (!$usuario || ($usuario['papel'] ?? '') !== 'admin') {
    echo "Acesso negado. Apenas admin pode adicionar posts.";
    exit;
}

$mensagem = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titulo = trim($_POST['titulo'] ?? '');
    $descricao_curta = trim($_POST['descricao_curta'] ?? '');
    $descricao_longa = trim($_POST['descricao_longa'] ?? '');

    if (!$titulo || !$descricao_curta || !$descricao_longa) {
        $mensagem = 'Preencha todos os campos.';
    } else {
        $arquivoPosts = __DIR__ . '/../data/posts.json';
        $posts = json_decode(file_get_contents($arquivoPosts), true) ?: [];

        // Define novo ID
        $ultimoId = 0;
        foreach ($posts as $p) if ($p['id'] > $ultimoId) $ultimoId = $p['id'];
        $novoId = $ultimoId + 1;

        // Upload de imagem
        $caminhoImagem = 'assets/img/posts/default.jpg';
        if (!empty($_FILES['imagem']['name']) && $_FILES['imagem']['error'] === UPLOAD_ERR_OK) {
            $ext = pathinfo($_FILES['imagem']['name'], PATHINFO_EXTENSION);
            $nomeArquivo = time() . '_' . bin2hex(random_bytes(5)) . '.' . $ext;
            $destinoRel = 'assets/img/posts/' . $nomeArquivo;
            $destinoAbs = __DIR__ . '/../' . $destinoRel;
            if (!is_dir(dirname($destinoAbs))) mkdir(dirname($destinoAbs), 0755, true);
            if (move_uploaded_file($_FILES['imagem']['tmp_name'], $destinoAbs)) {
                $caminhoImagem = $destinoRel;
            }
        }

        $novoPost = [
            'id' => $novoId,
            'titulo' => $titulo,
            'autor' => $usuario['nome'],
            'data' => date('Y-m-d H:i:s'),
            'imagem' => $caminhoImagem,
            'descricao_curta' => $descricao_curta,
            'descricao_longa' => $descricao_longa,
            'gostos' => 0,
            'visitas' => 0
        ];

        $posts[] = $novoPost;
        file_put_contents($arquivoPosts, json_encode($posts, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        // Inicializa estatísticas
        $arquivoEstat = __DIR__ . '/../data/estatisticas.json';
        $estat = json_decode(file_get_contents($arquivoEstat), true) ?: ['gostos'=>[],'visitas'=>[],'compartilhamentos'=>[]];
        $estat['visitas'][(string)$novoId] = 0;
        file_put_contents($arquivoEstat, json_encode($estat, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        header('Location: /backend/painelAdmin.php');
        exit;
    }
}
?>
<!doctype html>
<html lang="pt-BR">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Adicionar Post - Dashboard Admin</title>
  <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
  <main class="conteudo" style="max-width:900px;margin:28px auto;">
    <section class="formulario">
      <h2>Adicionar Post</h2>
      <?php if ($mensagem): ?><div style="color:var(--danger)"><?php echo htmlspecialchars($mensagem); ?></div><?php endif; ?>
      <form method="post" enctype="multipart/form-data">
        <label>Título</label>
        <input class="input" type="text" name="titulo" required>
        <label>Descrição curta</label>
        <input class="input" type="text" name="descricao_curta" required>
        <label>Descrição longa</label>
        <textarea class="input" name="descricao_longa" rows="8" required></textarea>
        <label>Imagem (opcional)</label>
        <input id="input-imagem-preview" class="input" type="file" name="imagem" accept="image/*">
        <div style="margin-top:8px;">
          <img id="preview-imagem" src="/assets/img/posts/default.jpg" alt="Preview" style="max-width:200px;border-radius:8px;">
        </div>
        <div style="display:flex;gap:8px;margin-top:12px;">
          <button class="botao-primario" type="submit">Publicar</button>
          <a class="btn secundario" href="/backend/painelAdmin.php" style="text-decoration:none;align-self:center;">Voltar</a>
        </div>
      </form>
    </section>
  </main>
  <script src="/assets/js/script.js"></script>
</body>
</html>
