<?php
session_start();
require '../includes/header.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $senha = $_POST['senha'] ?? '';

    $usuarios = json_decode(file_get_contents('../data/usuarios.json'), true);

    foreach ($usuarios as $usuario) {
        if ($usuario['email'] === $email && password_verify($senha, $usuario['senha'])) {
            // Login sucesso
            $_SESSION['usuario_id'] = $usuario['id'];
            $_SESSION['nome'] = $usuario['nome'];
            $_SESSION['papel'] = $usuario['papel'];
            $_SESSION['imagem'] = $usuario['imagem'] ?? '';

            header('Location: ../index.php');
            exit;
        }
    }
    $erro = "Email ou senha incorretos!";
}
?>

<main class="container formulario-central">
    <h2>Login</h2>
    <?php if (isset($erro)) echo "<p class='erro'>$erro</p>"; ?>
    <form method="POST">
        <input type="email" name="email" placeholder="Email" required>
        <input type="password" name="senha" placeholder="Senha" required>
        <button type="submit">Entrar</button>
    </form>
    <p>Não tem conta? <a href="registrar.php">Registar</a></p>
</main>

<?php require '../includes/footer.php'; ?>