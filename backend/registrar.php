<?php
session_start();
require '../includes/header.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome']);
    $email = trim($_POST['email']);
    $senha = $_POST['senha'];
    $confirma = $_POST['confirma'];

    if ($senha !== $confirma) {
        $erro = "Senhas não coincidem!";
    } else {
        $usuarios = json_decode(file_get_contents('../data/usuarios.json'), true);

        // Verifica se email já existe
        foreach ($usuarios as $u) {
            if ($u['email'] === $email) {
                $erro = "Email já registrado!";
                break;
            }
        }

        if (!isset($erro)) {
            // Novo usuário
            $novoId = empty($usuarios) ? 1 : max(array_column($usuarios, 'id')) + 1;

            $novoUsuario = [
                'id' => $novoId,
                'nome' => $nome,
                'email' => $email,
                'senha' => password_hash($senha, PASSWORD_DEFAULT),
                'papel' => 'usuario',
                'imagem' => 'assets/img/users/default.jpg'
            ];

            $usuarios[] = $novoUsuario;
            file_put_contents('../data/usuarios.json', json_encode($usuarios, JSON_PRETTY_PRINT));

            $_SESSION['usuario_id'] = $novoId;
            $_SESSION['nome'] = $nome;
            $_SESSION['papel'] = 'usuario';
            $_SESSION['imagem'] = $novoUsuario['imagem'];

            header('Location: ../index.php');
            exit;
        }
    }
}
?>

<main class="container formulario-central">
    <h2>Registrar</h2>
    <?php if (isset($erro)) echo "<p class='erro'>$erro</p>"; ?>
    <form method="POST">
        <input type="text" name="nome" placeholder="Nome completo" required>
        <input type="email" name="email" placeholder="Email" required>
        <input type="password" name="senha" placeholder="Senha" required>
        <input type="password" name="confirma" placeholder="Confirmar senha" required>
        <button type="submit">Registrar</button>
    </form>
    <p>Já tem conta? <a href="login.php">Login</a></p>
</main>

<?php require '../includes/footer.php'; ?>