<?php
session_start();
date_default_timezone_set('Africa/Maputo');

$usuarioSessao = $_SESSION['usuario'] ?? null;
if (!$usuarioSessao || empty($usuarioSessao['id'])) {
    header('Location: /backend/login.php');
    exit;
}

$arquivoUsuarios = __DIR__ . '/../data/usuarios.json';
$usuarios = json_decode(file_get_contents($arquivoUsuarios), true) ?: [];

$usuarioId = $usuarioSessao['id'];
$indiceUsuario = null;

foreach ($usuarios as $indice => $u) {
    if ((int)$u['id'] === (int)$usuarioId) {
        $indiceUsuario = $indice;
        break;
    }
}

if ($indiceUsuario === null) {
    echo 'Usuário não encontrado.';
    exit;
}

$mensagem = null;
$tipoMensagem = 'sucesso';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $acao = $_POST['acao'] ?? '';

    if ($acao === 'atualizar') {
        $novoNome = trim($_POST['nome'] ?? '');
        $senhaAntiga = $_POST['senha_antiga'] ?? '';
        $senhaNova = $_POST['senha_nova'] ?? '';
        $senhaConfirma = $_POST['senha_confirma'] ?? '';

        if ($novoNome === '') {
            $mensagem = 'Nome não pode ser vazio.';
            $tipoMensagem = 'erro';
        } else {
            $usuarios[$indiceUsuario]['nome'] = $novoNome;

            // Alterar senha
            if ($senhaAntiga || $senhaNova || $senhaConfirma) {
                $hashAtual = $usuarios[$indiceUsuario]['senha'];
                if (!password_verify($senhaAntiga, $hashAtual)) {
                    $mensagem = 'Senha antiga incorreta.';
                    $tipoMensagem = 'erro';
                } elseif ($senhaNova !== $senhaConfirma) {
                    $mensagem = 'Nova senha e confirmação não conferem.';
                    $tipoMensagem = 'erro';
                } else {
                    $usuarios[$indiceUsuario]['senha'] = password_hash($senhaNova, PASSWORD_DEFAULT);
                }
            }

            // Upload imagem
            if (!empty($_FILES['imagem']['name']) && $_FILES['imagem']['error'] === UPLOAD_ERR_OK) {
                $ext = strtolower(pathinfo($_FILES['imagem']['name'], PATHINFO_EXTENSION));
                $permitidas = ['jpg','jpeg','png','gif','webp'];

                if (!in_array($ext, $permitidas)) {
                    $mensagem = 'Formato de imagem inválido.';
                    $tipoMensagem = 'erro';
                } else {
                    $nomeArquivo = time() . '_' . bin2hex(random_bytes(5)) . '.' . $ext;
                    $caminhoRel = 'assets/img/user/' . $nomeArquivo;
                    $caminhoAbs = __DIR__ . '/../' . $caminhoRel;
                    if (!is_dir(dirname($caminhoAbs))) mkdir(dirname($caminhoAbs), 0755, true);

                    if (move_uploaded_file($_FILES['imagem']['tmp_name'], $caminhoAbs)) {
                        $imgAntiga = $usuarios[$indiceUsuario]['imagem'] ?? '';
                        if ($imgAntiga && strpos($imgAntiga, 'avatar_padrao') === false) {
                            @unlink(__DIR__ . '/../' . $imgAntiga);
                        }
                        $usuarios[$indiceUsuario]['imagem'] = $caminhoRel;
                    } else {
                        $mensagem = 'Erro ao enviar imagem.';
                        $tipoMensagem = 'erro';
                    }
                }
            }

            if (!$mensagem || $tipoMensagem !== 'erro') {
                file_put_contents($arquivoUsuarios, json_encode($usuarios, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

                // Atualiza sessão
                $_SESSION['usuario'] = [
                    'id' => $usuarios[$indiceUsuario]['id'],
                    'nome' => $usuarios[$indiceUsuario]['nome'],
                    'email' => $usuarios[$indiceUsuario]['email'],
                    'papel' => $usuarios[$indiceUsuario]['papel'] ?? 'usuario',
                    'imagem' => $usuarios[$indiceUsuario]['imagem'] ?? 'assets/img/avatar_padrao.png'
                ];

                $mensagem = $mensagem ?? 'Perfil atualizado com sucesso.';
                $tipoMensagem = 'sucesso';
            }
        }
    }

    if ($acao === 'apagar') {
        $imagemExcluir = $usuarios[$indiceUsuario]['imagem'] ?? null;
        array_splice($usuarios, $indiceUsuario, 1);
        file_put_contents($arquivoUsuarios, json_encode($usuarios, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        if ($imagemExcluir && strpos($imagemExcluir, 'avatar_padrao') === false) {
            @unlink(__DIR__ . '/../' . $imagemExcluir);
        }
        session_unset();
        session_destroy();
        header('Location: /index.php');
        exit;
    }
}

// Recarrega usuário
$usuario = $_SESSION['usuario'];
?>
<!doctype html>
<html lang="pt-BR">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Meu Perfil</title>
<link rel="stylesheet" href="/assets/css/style.css">
<style>
/* Ajusta imagem de perfil */
.perfil-avatar-grande {
    width: 100px;
    height: 100px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid #ccc;
}
.linha-perfil-topo {
    display: flex;
    align-items: center;
    gap: 20px;
}
.box-avatar-upload {
    display: flex;
    flex-direction: column;
    gap: 5px;
}
</style>
</head>
<body>
<main class="conteudo conteudo-limitado">
<section class="secao-pagina-estatica">
<header class="cabecalho-secao">
<h1>Meu Perfil</h1>
<small class="texto-suave mt-1">Atualize suas informações pessoais, senha e foto de perfil.</small>
</header>

<div class="cartao-estatico">
<?php if ($mensagem): ?>
<div class="alerta <?php echo $tipoMensagem === 'erro' ? 'erro' : 'sucesso'; ?>">
<?php echo htmlspecialchars($mensagem); ?>
</div>
<?php endif; ?>

<form method="post" enctype="multipart/form-data" class="formulario-perfil">
<input type="hidden" name="acao" value="atualizar">
<div class="linha-perfil-topo">
    <div class="box-avatar-perfil">
        <img src="/<?php echo htmlspecialchars($usuario['imagem']); ?>" alt="Foto de perfil" class="perfil-avatar-grande" id="avatar-preview">
    </div>
    <div class="box-avatar-upload">
        <label for="imagem">Escolher nova foto</label>
        <input type="file" name="imagem" id="imagem" accept="image/*" onchange="previewAvatar(event)">
        <small class="texto-suave">Formatos: JPG, PNG, GIF, WEBP.</small>
    </div>
</div>

<div class="campo mt-2">
    <label for="nome">Nome</label>
    <input class="input" type="text" id="nome" name="nome" required value="<?php echo htmlspecialchars($usuario['nome']); ?>">
</div>

<div class="campo mt-1">
    <label>Email</label>
    <input class="input" type="email" value="<?php echo htmlspecialchars($usuario['email']); ?>" disabled>
</div>

<hr class="divisor-form">

<div class="campo mt-1">
<label for="senha_antiga">Senha antiga</label>
<input class="input" type="password" id="senha_antiga" name="senha_antiga" placeholder="Senha atual">
</div>
<div class="campo mt-1">
<label for="senha_nova">Nova senha</label>
<input class="input" type="password" id="senha_nova" name="senha_nova" placeholder="Nova senha">
</div>
<div class="campo mt-1">
<label for="senha_confirma">Confirmar nova senha</label>
<input class="input" type="password" id="senha_confirma" name="senha_confirma" placeholder="Repita nova senha">
</div>

<div class="linha-botoes mt-3">
<button class="botao-primario" type="submit">Atualizar perfil</button>
<a class="btn secundario" href="/index.php">Voltar</a>
</div>
</form>

<form method="post" class="form-apagar-conta" onsubmit="return confirm('Tem certeza que deseja apagar sua conta?');">
<input type="hidden" name="acao" value="apagar">
<button class="botao-danger" type="submit">Apagar conta</button>
</form>
</div>
</section>
</main>

<script>
// Preview da imagem antes de enviar
function previewAvatar(event) {
    const preview = document.getElementById('avatar-preview');
    preview.src = URL.createObjectURL(event.target.files[0]);
}
</script>
</body>
</html>
