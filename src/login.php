<?php
declare(strict_types=1);
require_once __DIR__ . '/AuthHelper.php';

$auth = new AuthHelper();

if (isset($_SESSION['user_email'])) {
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Histórico de Mensagens</title>
    <link rel="icon" href="icon.png" type="image/png">
    <link rel="stylesheet" href="style.css">
</head>
<body class="login-body">
    <main class="login-container">
        <section class="login-box" aria-labelledby="loginTitle">
            <h1 id="loginTitle">Visualizador de Conversas</h1>
            <p>Faça login com uma conta autorizada para acessar os dados sensíveis.</p>
            <a href="<?php echo htmlspecialchars($auth->getLoginUrl()); ?>" class="btn-login" aria-label="Entrar com o Google">
                Entrar com Google
            </a>
        </section>
    </main>
</body>
</html>
