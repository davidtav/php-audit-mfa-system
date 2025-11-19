<?php

require_once __DIR__ .'/config.php';
require_once __DIR__ . '/../vendor/autoload.php';

use App\AuthManager;

$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $auth = new AuthManager();
    $user = $_POST['user'] ?? '';
    $pass = $_POST['pass'] ?? '';
    $code = $_POST['code'] ?? ''; // O código do Authenticator

    if ($auth->login($user, $pass, $code)) {
        header('Location: admin_logs.php');
        exit;
    } else {
        $erro = "Credenciais inválidas ou código MFA incorreto.";
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Login Seguro</title>
    <link rel="stylesheet" href="login_style.css"/> 
</head>
<body>
    <div class="login-box">
        <h2 style="text-align:center">Acesso Restrito</h2>
        
        <?php if ($erro): ?>
            <div class="alert"><?= $erro ?></div>
        <?php endif; ?>

        <form method="POST">
            <label>Usuário</label>
            <input type="text" name="user" required autofocus>
            
            <label>Senha</label>
            <input type="password" name="pass" required>
            
            <label>Código MFA</label>
            <input type="text" name="code" placeholder="000 000" pattern="\d*" inputmode="numeric" autocomplete="one-time-code" required>
            
            <button type="submit">Entrar</button>
        </form>
    </div>
</body>
</html>