<?php
// registrar.php
// Formulário de registro e criação de novo usuário no arquivo data/usuarios.json
// - Recebe POST com nome, email, senha, confirma
// - Faz validações simples (senhas iguais, email único)
// - Salva senha com password_hash
// - Define papel = 'usuario' por padrão

session_start();
date_default_timezone_set('Africa/Maputo');

$erro = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $senha = $_POST['senha'] ?? '';
    $confirma = $_POST['confirma'] ?? '';

    if (!$nome || !$email || !$senha || !$confirma) {
        $erro = 'Preencha todos os campos.';
    } elseif ($senha !== $confirma) {
        $erro = 'As senhas não conferem.';
    } else {
        $arquivo = __DIR__ . '/../data/usuarios.json';
        $usuarios = json_decode(file_get_contents($arquivo), true) ?: [];

        // Verificar email existente
        foreach ($usuarios as $u) {
            if (strtolower($u['email']) === strtolower($email)) {
                $erro = 'Email já cadastrado.';
                break;
            }
        }

        if (!$erro) {
            // Calcular novo ID simples (ultimo id + 1)
            $ultimoId = 0;
            foreach ($usuarios as $u) if ($u['id'] > $ultimoId) $ultimoId = $u['id'];
            $novoId = $ultimoId + 1;

            $hash = password_hash($senha, PASSWORD_DEFAULT);
            $imagemPadrao = 'assets/img/user/default.png';

            $novoUsuario = [
                'id' => $novoId,
                'nome' => $nome,
                'email' => $email,
                'senha' => $hash,
                'papel' => 'usuario',
                'imagem' => $imagemPadrao
            ];

            $usuarios[] = $novoUsuario;
            file_put_contents($arquivo, json_encode($usuarios, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

            // Logar usuário recém-criado
            $_SESSION['usuario_id'] = $novoId;
            header('Location: /index.php');
            exit;
        }
    }
}
?>
<!doctype html>
<html lang="pt-BR">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Registrar - Blog-Acadêmico</title>
  <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
  <main class="conteudo" style="max-width:520px;margin:36px auto;">
    <section class="formulario">
      <h2>Registrar</h2>
      <?php if ($erro): ?><div style="color:var(--danger)"><?php echo htmlspecialchars($erro); ?></div><?php endif; ?>
      <form method="post" novalidate>
        <label>Nome</label>
        <input class="input" type="text" name="nome" required value="<?php echo htmlspecialchars($_POST['nome'] ?? '') ?>">
        <label>Email</label>
        <input class="input" type="email" name="email" required value="<?php echo htmlspecialchars($_POST['email'] ?? '') ?>">
        <label>Senha</label>
        <input class="input" type="password" name="senha" required>
        <label>Confirmar senha</label>
        <input class="input" type="password" name="confirma" required>
        <div style="display:flex;gap:8px;margin-top:12px;">
          <button class="botao-primario" type="submit">Criar conta</button>
          <a class="btn secundario" href="/index.php" style="align-self:center;text-decoration:none;">Voltar</a>
        </div>
      </form>
      <p style="margin-top:12px;">Já tem conta? <a href="/backend/login.php">Login</a></p>
    </section>
  </main>
</body>
</html>
