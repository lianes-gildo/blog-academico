<?php
session_start();
if(!isset($_SESSION['usuario'])) header('Location: login.php');

$usuario = $_SESSION['usuario'];
$usuariosFile = '../data/usuarios.json';

if(!file_exists($usuariosFile)) file_put_contents($usuariosFile, json_encode([]));
$usuarios = json_decode(file_get_contents($usuariosFile), true);
if(!is_array($usuarios)) $usuarios = [];

// Atualizar perfil
if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $nome = trim($_POST['nome']);
    $senha = $_POST['senha'];
    $imagem = $usuario['imagem'] ?? '';

    if(isset($_FILES['imagem']) && $_FILES['imagem']['error'] == 0){
        $ext = pathinfo($_FILES['imagem']['name'], PATHINFO_EXTENSION);
        $nomeImagem = uniqid().'.'.$ext;
        move_uploaded_file($_FILES['imagem']['tmp_name'], "../assets/img/".$nomeImagem);
        $imagem = "../assets/img/".$nomeImagem;
    }

    foreach($usuarios as &$u){
        if($u['id'] === $usuario['id']){
            $u['nome'] = $nome;
            if(!empty($senha)) $u['senha'] = password_hash($senha, PASSWORD_DEFAULT);
            $u['imagem'] = $imagem;
            $_SESSION['usuario'] = $u;
            break;
        }
    }
    file_put_contents($usuariosFile, json_encode($usuarios, JSON_PRETTY_PRINT));

    // Redireciona para home após atualizar o perfil
    header('Location: ../index.php');
    exit;
}

// Apagar conta
if(isset($_GET['apagar']) && $_GET['apagar'] === 'sim'){
    $usuarios = array_filter($usuarios, fn($u) => $u['id'] !== $usuario['id']);
    file_put_contents($usuariosFile, json_encode(array_values($usuarios), JSON_PRETTY_PRINT));
    session_unset();
    session_destroy();
    header('Location: ../index.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="pt">
<head>
<meta charset="UTF-8">
<title>Perfil</title>
<link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<?php include '../includes/header.php'; ?>

<main class="form-container">
    <h2>Editar Perfil</h2>
    <form method="POST" enctype="multipart/form-data">
        <img src="<?= $usuario['imagem'] ?? '../assets/img/default_user.png' ?>" alt="Perfil" class="perfil-img" style="width:100px; height:100px; border-radius:50%; margin-bottom:10px;">
        <input type="text" name="nome" value="<?= htmlspecialchars($usuario['nome']) ?>" required>
        <input type="password" name="senha" placeholder="Nova senha (deixe vazio para não mudar)">
        <small>Foto de Perfil</small>
        <input type="file" name="imagem" accept="image/*">
        <button type="submit">Atualizar</button>
    </form>
    <p><a href="perfil.php?apagar=sim" onclick="return confirm('Tem certeza que deseja apagar sua conta?');" style="color:red;">Apagar conta</a></p>
</main>

<?php include '../includes/footer.php'; ?>
</body>
</html>
