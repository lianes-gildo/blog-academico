<?php
session_start();
date_default_timezone_set("Africa/Maputo");

$erro = '';

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $nome = trim($_POST['nome']);
    $email = trim($_POST['email']);
    $senha = trim($_POST['senha']);
    $senha_confirm = trim($_POST['senha_confirm']);

    if($senha !== $senha_confirm){
        $erro = "As senhas não coincidem.";
    } else {
        $usuarios = json_decode(file_get_contents('../data/usuarios.json'), true);

        foreach($usuarios as $usuario){
            if($usuario['email'] === $email){
                $erro = "Email já está em uso.";
                break;
            }
        }

        if(!$erro){
            $novoUsuario = [
                'id' => count($usuarios) > 0 ? $usuarios[count($usuarios)-1]['id']+1 : 1,
                'nome' => $nome,
                'email' => $email,
                'senha' => password_hash($senha, PASSWORD_DEFAULT),
                'papel' => 'usuario'
            ];
            $usuarios[] = $novoUsuario;
            file_put_contents('../data/usuarios.json', json_encode($usuarios, JSON_PRETTY_PRINT));

            header('Location: login.php');
            exit;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Registar - Blog Académico</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

<?php include '../includes/header.php'; ?>

<div class="form-container">
    <h2>Registar</h2>
    <?php if($erro) echo "<p style='color:red;'>$erro</p>"; ?>
    <form method="POST">
        <input type="text" name="nome" placeholder="Nome completo" required>
        <input type="email" name="email" placeholder="Email" required>
        <input type="password" name="senha" placeholder="Senha" required>
        <input type="password" name="senha_confirm" placeholder="Confirmar Senha" required>
        <button type="submit">Registar</button>
    </form>
    <p>Já tem conta? <a href="login.php">Login</a></p>
</div>

<?php include '../includes/footer.php'; ?>
</body>
</html>
