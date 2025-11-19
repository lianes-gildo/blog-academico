<?php
session_start();
date_default_timezone_set("Africa/Maputo");

$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $senha = trim($_POST['senha']);

    $usuarios = json_decode(file_get_contents('../data/usuarios.json'), true);

    $usuarioValido = null;
    foreach ($usuarios as $usuario) {
        if ($usuario['email'] === $email && password_verify($senha, $usuario['senha'])) {
            $usuarioValido = $usuario;
            break;
        }
    }

    if ($usuarioValido) {
        $_SESSION['usuario'] = [
            'id' => $usuarioValido['id'],
            'nome' => $usuarioValido['nome'],
            'email' => $usuarioValido['email'],
            'papel' => $usuarioValido['papel'],
            'imagem' => $usuarioValido['imagem'] ?? null
        ];

        if($usuarioValido['papel'] === 'admin'){
            header('Location: painel.php');
        } else {
            header('Location: ../index.php');
        }
        exit;
    } else {
        $erro = "Email ou senha incorretos.";
    }
}
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Login - Blog Académico</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

<?php include '../includes/header.php'; ?>

<div class="form-container">
    <h2>Login</h2>
    <?php if($erro) echo "<p style='color:red;'>$erro</p>"; ?>
    <form method="POST">
        <input type="email" name="email" placeholder="Email" required>
        <input type="password" name="senha" placeholder="Senha" required>
        <button type="submit">Entrar</button>
    </form>
    <p>Não tem conta? <a href="registrar.php">Registar</a></p>
</div>

<?php include '../includes/footer.php'; ?>
</body>
</html>
