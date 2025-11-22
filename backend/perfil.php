<?php
session_start();
require '../includes/header.php';

$usuarioId = $_SESSION['usuario_id'];
$usuarios = json_decode(file_get_contents('../data/usuarios.json'), true);

$usuario = null;
foreach ($usuarios as $u) if ($u['id'] == $usuarioId) { $usuario = $u; break; }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $novoNome = trim($_POST['nome']);
    $senhaAntiga = $_POST['senha_antiga'] ?? '';
    $senhaNova = $_POST['senha_nova'] ?? '';

    if (!empty($senhaNova) && !password_verify($senhaAntiga, $usuario['senha'])) {
        $erro = "Senha antiga incorreta!";
    } else {
        if (!empty($senhaNova)) {
            $usuario['senha'] = password_hash($senhaNova, PASSWORD_DEFAULT);
        }
        $usuario['nome'] = $novoNome;

        if (isset($_FILES['foto']) && $_FILES['foto']['error'] === 0) {

            $ext = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));

            $nomeFoto = "user_" . $usuarioId . "." . $ext;

            $caminhoDestino = "../assets/img/users/" . $nomeFoto;

            if (move_uploaded_file($_FILES['foto']['tmp_name'], $caminhoDestino)) {

                $usuario['imagem'] = "assets/img/users/" . $nomeFoto;
                
            } else {
                $usuario['imagem'] = $usuario['imagem'] ?? null;
            }
        }


        foreach ($usuarios as &$u) {
            if ($u['id'] == $usuarioId) {
                $u = $usuario;
                break;
            }
        }
        file_put_contents('../data/usuarios.json', json_encode($usuarios, JSON_PRETTY_PRINT));

        $_SESSION['nome'] = $novoNome;
        $_SESSION['imagem'] = $usuario['imagem'];

        $sucesso = "Perfil atualizado!";
    }
}
?>

<main class="container formulario-central">
    <h2>Perfil</h2>
    <?php if (isset($sucesso)) echo "<p class='sucesso'>$sucesso</p>"; ?>
    <?php if (isset($erro)) echo "<p class='erro'>$erro</p>"; ?>

    <div class="perfil-imagem">
        <img src="<?php echo $usuario['/imagem']; ?>" alt="Foto de Perfil">
    </div>

    <form method="POST" enctype="multipart/form-data">
        <label for="nome">Nome:</label>
        <input type="text" id="nome" name="nome" value="<?php echo htmlspecialchars($usuario['nome']); ?>" required>

        <label for="senha_antiga">Senha antiga (deixe vazio para manter):</label>
        <input type="password" id="senha_antiga" name="senha_antiga">

        <label for="senha_nova">Nova senha:</label>
        <input type="password" id="senha_nova" name="senha_nova">

        <label for="foto">Foto de perfil:</label>
        <input type="file" id="foto" name="foto" accept="image/*">

        <button type="submit">Atualizar Perfil</button>
    </form>

    <form method="POST" onsubmit="return confirm('Tem certeza que quer apagar a conta?')">
        <input type="hidden" name="apagar_conta" value="1">
        <button type="submit" class="btn-vermelho">Apagar Conta</button>
    </form>

    <?php
    if (isset($_POST['apagar_conta'])) {
        $usuarios = array_filter($usuarios, fn($u) => $u['id'] != $usuarioId);
        $usuarios = array_values($usuarios); // reindex
        file_put_contents('../data/usuarios.json', json_encode($usuarios, JSON_PRETTY_PRINT));
        session_destroy();
        header('Location: ../index.php');
        exit;
    }
    ?>
</main>

<?php require '../includes/footer.php'; ?>