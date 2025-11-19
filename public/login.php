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

// --- 1. CHECAGEM INICIAL DE RATE LIMITING ---
$limiter = new RateLimiter();
$check = $limiter->check();
$maxAttempts = 3; // O valor default, ajustado abaixo no else

if ($check['status'] === 'BLOQUEADO') {
    $min = ceil($check['timeLeft'] / 60);
    $erro = "Muitas tentativas falhas. Acesso bloqueado por mais $min minutos.";
} else {
    // Busca o valor real de maxAttempts da classe para a mensagem de erro
    $reflection = new \ReflectionClass($limiter);
    $maxAttempts = $reflection->getProperty('maxAttempts')->getValue($limiter);
}


// 2. Processamento do Formulário (Só se não estiver bloqueado)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $check['status'] === 'OK') {
    
    // 2.1. Bloco Anti-CSRF
    if (
        !isset($_POST['csrf_token']) ||
        !isset($_SESSION['csrf_token']) ||
        !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])
    ) {
        $logger = new AuditLogger();
        $logger->log('ALERTA_SEGURANCA_CSRF', [
            'motivo' => 'Token inválido ou ausente na tentativa de Login',
            'ip_atacante' => $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN',
            'user_tentado' => $_POST['user'] ?? 'N/A'
        ]);

        http_response_code(403);
        die('Erro de Segurança: Ação bloqueada por token inválido. O incidente foi registrado.');
    }
    
    // 2.2. Lógica de Autenticação
    $auth = new AuthManager();
    $user = $_POST['user'] ?? '';

    if ($auth->login($user, $_POST['pass'] ?? '', $_POST['code'] ?? '')) {
        
        // SUCESSO: Reseta as falhas e avança
        $limiter->resetAttempts(); 
        unset($_SESSION['csrf_token']);
        header('Location: admin_logs.php');
        exit;
        
    } else {
        
        // FALHA: Registra no Rate Limiter
        $limiter->recordFailure();
        
        // Puxa o status atualizado
        $novoCheck = $limiter->check();
        
        if ($novoCheck['status'] === 'BLOQUEADO') {
            // Se o último erro causou o bloqueio
            $min = ceil($novoCheck['timeLeft'] / 60);
            $erro = "Muitas tentativas falhas. Acesso bloqueado por mais $min minutos.";
        } else {
            // Se falhou, mas ainda há tentativas
            $tentativas_restantes = $maxAttempts - $novoCheck['tentativas'];
            $erro = "Credenciais inválidas ou código MFA incorreto. Você tem mais $tentativas_restantes tentativas antes do bloqueio.";
        }
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
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
            <label>Usuário</label>
            <input type="text" name="user" required autofocus>

            <label>Senha</label>
            <input type="password" name="pass" required>

            <label>Código MFA</label>
            <input type="text" name="code" placeholder="000 000" pattern="\d*" inputmode="numeric" autocomplete="one-time-code" required>

            <button type="submit" <?= $check['status'] === 'BLOQUEADO' ? 'disabled' : '' ?>>
                Entrar
            </button>
        </form>
    </div>
</body>

</html>