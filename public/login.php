<?php

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/../vendor/autoload.php';

use App\AuthManager;
use App\AuditLogger;
use App\RateLimiter;


if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$erro = '';

$limiter = new RateLimiter();
$check = $limiter->check();

if ($check['status'] === 'BLOQUEADO') {
    $min = ceil($check['timeLeft'] / 60);
    $erro = "Muitas tentativas falhas. Acesso bloqueado por mais $min minutos.";
}


// 4. Processamento do Formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $check['status'] === 'OK') {
    if (
        !isset($_POST['csrf_token']) ||
        !isset($_SESSION['csrf_token']) ||
        !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])
    ) {

        $logger = new AuditLogger();
        $logger->log('ALERTA_SEGURANCA_CSRF', [
            'motivo' => 'Token inválido ou ausente na tentativa de Login',
            'ip_atacante' => $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'UNKNOWN',
            'user_tentado' => $_POST['user'] ?? 'N/A'
        ]);


        http_response_code(403);
        die('Erro de Segurança: Ação bloqueada por token inválido. O incidente foi registrado.');
    }

    $auth = new AuthManager();
    $user = $_POST['user'] ?? '';
    $pass = $_POST['pass'] ?? '';
    $code = $_POST['code'] ?? ''; // O código do Authenticator

    if ($auth->login($user, $pass, $code)) {
        $limiter->resetAttempts();
        unset($_SESSION['csrf_token']);
        header('Location: admin_logs.php');
        exit;
    } else {
        $limiter->recordFailure();
        $erro = "Credenciais inválidas ou código MFA incorreto. Tentativas restantes: " . ($limiter->maxAttempts - ($check['tentativas'] + 1));
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <title>Login Seguro</title>
    <link rel="stylesheet" href="login_style.css" />
</head>

<body>
    <div class="login-box">
        <h2 style="text-align:center">Acesso Restrito</h2>

        <?php if ($erro): ?>
            <div class="alert"><?= $erro ?></div>
        <?php endif; ?>

        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
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