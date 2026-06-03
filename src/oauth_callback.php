<?php
declare(strict_types=1);
require_once __DIR__ . '/AuthHelper.php';

$auth = new AuthHelper();

if (isset($_GET['code'])) {
    $userInfo = $auth->authenticateWithCode($_GET['code']);
    
    if ($userInfo && isset($userInfo['email'])) {
        $email = $userInfo['email'];
        
        if ($auth->isEmailAuthorized($email)) {
            $_SESSION['user_email'] = $email;
            $_SESSION['user_name'] = $userInfo['name'] ?? 'Usuário';
            header('Location: index.php');
            exit;
        } else {
            $error = "O e-mail {$email} não possui autorização de acesso.";
        }
    } else {
        $error = "Falha ao obter dados do Google.";
    }
} else {
    $error = "Acesso negado ou fluxo interrompido.";
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Erro de Autenticação</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <main class="login-container">
        <div class="login-box" role="alert">
            <h2>Acesso Restrito</h2>
            <p style="color: #d32f2f;"><?php echo htmlspecialchars($error); ?></p>
            <a href="login.php" class="btn-login">Voltar ao Login</a>
        </div>
    </main>
</body>
</html>