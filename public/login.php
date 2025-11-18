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
    <link rel="stylesheet" href="style.css"> <style>
        .login-box { max-width: 400px; margin: 100px auto; padding: 30px; background: white; box-shadow: 0 0 20px rgba(0,0,0,0.1); border-radius: 8px; }
        .alert { background: #ffdddd; color: #a00; padding: 10px; margin-bottom: 15px; border-radius: 4px; text-align: center; }
        input { width: 100%; margin-bottom: 15px; padding: 10px; box-sizing: border-box; }
        button { width: 100%; padding: 10px; background: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer; }
        button:hover { background: #0056b3; }
    </style>
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
            
            <label>Código MFA (Google Auth)</label>
            <input type="text" name="code" placeholder="000 000" pattern="\d*" inputmode="numeric" autocomplete="one-time-code" required>
            
            <button type="submit">Entrar</button>
        </form>
    </div>
</body>
</html>