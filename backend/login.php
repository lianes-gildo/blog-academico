<?php
// =========================================
// login.php
// Página de login. Verifica usuário em data/usuarios.json e cria sessão.
//
// Regras:
// - Formulário POST envia 'email' e 'senha'.
// - Valida preenchimento obrigatório dos campos.
// - Se email/senha inválidos: mostra "Email ou Senha inválidos!".
// - Se sucesso: salva $_SESSION['usuario'] (id, nome, email, papel, imagem)
//   e redireciona para /index.php.
// - Senhas comparadas com password_verify (hash).
// =========================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

date_default_timezone_set('Africa/Maputo');

// Se já estiver logado, não faz sentido ver a página de login
if (isset($_SESSION['usuario']) && !empty($_SESSION['usuario']['id'])) {
    header('Location: /index.php');
    exit;
}

$mensagemErro = null;
$emailPreenchido = ''; // para manter o email no input depois de erro

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Lê campos do formulário
    $email = trim($_POST['email'] ?? '');
    $senha = $_POST['senha'] ?? '';

    $emailPreenchido = $email;

    // Validação de preenchimento obrigatório
    if ($email === '' || $senha === '') {
        $mensagemErro = 'Por favor, preencha o email e a senha.';
    } else {
        // Caminho do arquivo de usuários
        $arquivoUsuarios = __DIR__ . '/../data/usuarios.json';

        if (!file_exists($arquivoUsuarios)) {
            $mensagemErro = 'Erro interno: arquivo de usuários não encontrado.';
        } else {
            $conteudo = file_get_contents($arquivoUsuarios);
            $usuarios = json_decode($conteudo, true);

            if (!is_array($usuarios)) {
                $mensagemErro = 'Erro interno: dados de usuários inválidos.';
            } else {
                // Procura usuário pelo email (case-insensitive)
                $usuarioEncontrado = null;
                foreach ($usuarios as $u) {
                    if (!isset($u['email'], $u['senha'])) {
                        continue;
                    }
                    if (strtolower($u['email']) === strtolower($email)) {
                        $usuarioEncontrado = $u;
                        break;
                    }
                }

                // Valida existência e senha (hash)
                if (!$usuarioEncontrado || !password_verify($senha, $usuarioEncontrado['senha'])) {
                    // Mensagem pedida
                    $mensagemErro = 'Email ou Senha inválidos!';
                } else {
                    // =========================================
                    // LOGIN BEM-SUCEDIDO
                    // - Guardamos o usuário completo na sessão
                    //   para ser usado no header/perfil/etc.
                    // =========================================
                    $_SESSION['usuario'] = [
                        'id'     => $usuarioEncontrado['id'],
                        'nome'   => $usuarioEncontrado['nome'],
                        'email'  => $usuarioEncontrado['email'],
                        'papel'  => $usuarioEncontrado['papel'] ?? 'usuario',
                        'imagem' => $usuarioEncontrado['imagem'] ?? '/assets/img/avatar_padrao.png'
                    ];

                    // Regenera ID da sessão por segurança
                    session_regenerate_id(true);

                    // Redireciona para a página inicial
                    header('Location: /index.php');
                    exit;
                }
            }
        }
    }
}

// A partir daqui é apenas o HTML do formulário
?>
<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Entrar - Blog-Acadêmico</title>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body class="pagina-autenticacao">

<main class="conteudo conteudo-limitado">
    <section class="cartao-autenticacao">
        <h1>Entrar</h1>
        <p class="texto-suave mb-2">
            Acesse sua conta para comentar, curtir postagens e gerenciar seu perfil.
        </p>

        <?php if ($mensagemErro): ?>
            <div class="alerta erro">
                <?php echo htmlspecialchars($mensagemErro); ?>
            </div>
        <?php endif; ?>

        <form method="post" class="formulario" novalidate>
            <div class="campo">
                <label for="email">Email</label>
                <input
                    class="input"
                    type="email"
                    id="email"
                    name="email"
                    required
                    placeholder="seuemail@exemplo.com"
                    value="<?php echo htmlspecialchars($emailPreenchido); ?>">
            </div>

            <div class="campo">
                <label for="senha">Senha</label>
                <input
                    class="input"
                    type="password"
                    id="senha"
                    name="senha"
                    required
                    placeholder="Sua senha">
            </div>

            <div class="linha-botoes mt-2">
                <button class="botao-primario" type="submit">
                    Entrar
                </button>
                <a class="btn secundario" href="/index.php">
                    Voltar
                </a>
            </div>
        </form>

        <p class="texto-suave mt-2">
            Não tem conta?
            <a href="/backend/registrar.php">Registrar</a>
        </p>
    </section>
</main>

</body>
</html>
