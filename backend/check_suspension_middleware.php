// ========================================
// backend/check_suspension_middleware.php
// ========================================
<?php
// Incluir no início de todas as páginas protegidas
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (isset($_SESSION['usuario_id'])) {
    $arquivoUsuarios = __DIR__ . '/../data/usuarios.json';
    if (file_exists($arquivoUsuarios)) {
        $usuarios = json_decode(file_get_contents($arquivoUsuarios), true);
        if (is_array($usuarios)) {
            foreach ($usuarios as $user) {
                if ($user['id'] == $_SESSION['usuario_id']) {
                    if (isset($user['suspenso_ate']) && strtotime($user['suspenso_ate']) > time()) {
                        $suspensaoAte = date('d/m/Y H:i', strtotime($user['suspenso_ate']));
                        session_destroy();
                        ?>
                        <!DOCTYPE html>
                        <html lang="pt-MZ">
                        <head>
                            <meta charset="UTF-8">
                            <meta name="viewport" content="width=device-width, initial-scale=1.0">
                            <title>Conta Suspensa</title>
                            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
                            <style>
                                body {
                                    display: flex;
                                    align-items: center;
                                    justify-content: center;
                                    min-height: 100vh;
                                    background: linear-gradient(135deg, #e74c3c, #c0392b);
                                    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                                }
                                .suspension-card {
                                    background: white;
                                    border-radius: 25px;
                                    padding: 50px;
                                    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
                                    max-width: 500px;
                                    text-align: center;
                                }
                                .suspension-icon {
                                    font-size: 5rem;
                                    margin-bottom: 25px;
                                }
                                .suspension-title {
                                    font-size: 2rem;
                                    font-weight: 800;
                                    color: #e74c3c;
                                    margin-bottom: 20px;
                                }
                                .suspension-message {
                                    font-size: 1.1rem;
                                    color: #666;
                                    margin-bottom: 30px;
                                    line-height: 1.6;
                                }
                                .suspension-date {
                                    background: #fff5f5;
                                    padding: 15px;
                                    border-radius: 12px;
                                    border-left: 4px solid #e74c3c;
                                    margin-bottom: 30px;
                                }
                                .btn-home {
                                    background: #3498db;
                                    color: white;
                                    padding: 15px 40px;
                                    border-radius: 50px;
                                    text-decoration: none;
                                    font-weight: 600;
                                    display: inline-block;
                                    transition: all 0.3s ease;
                                }
                                .btn-home:hover {
                                    background: #2980b9;
                                    transform: translateY(-3px);
                                    box-shadow: 0 10px 25px rgba(52, 152, 219, 0.4);
                                    color: white;
                                }
                            </style>
                        </head>
                        <body>
                            <div class="suspension-card">
                                <div class="suspension-icon">🚫</div>
                                <h1 class="suspension-title">Conta Suspensa</h1>
                                <p class="suspension-message">
                                    Sua conta foi temporariamente suspensa por violar as políticas da plataforma.
                                </p>
                                <div class="suspension-date">
                                    <strong>Suspensão ativa até:</strong><br>
                                    <span style="font-size: 1.3rem; color: #e74c3c; font-weight: 700;">
                                        <?php echo $suspensaoAte; ?>
                                    </span>
                                </div>
                                <a href="../index.php" class="btn-home">
                                    <i class="bi bi-house-door-fill"></i> Voltar ao Início
                                </a>
                            </div>
                        </body>
                        </html>
                        <?php
                        exit;
                    }
                    break;
                }
            }
        }
    }
}
?>