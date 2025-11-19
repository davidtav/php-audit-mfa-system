<?php

namespace App;

use PragmaRX\Google2FA\Google2FA;
use App\AuditLogger;


class AuthManager
{
    private string $authFile;
    private Google2FA $gAuth;

    public function __construct()
    {
        $this->authFile = __DIR__ . '/../data/auth.json';
        $this->gAuth = new Google2FA();


        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    /**
     * Tenta autenticar o usuário. Não faz logging de falhas; apenas retorna true/false.
     * O logging e o Rate Limiting são feitos pelo Controller chamador (login.php).
     */
    public function login(string $user, string $password, string $code): bool
    {
        $accounts = $this->getAccounts();
        $logger = new AuditLogger();


        if (!isset($accounts[$user])) {

            return false;
        }

        $account = $accounts[$user];


        if (!password_verify($password, $account['password_hash'])) {

            return false;
        }

        if (!$this->gAuth->verifyKey($account['mfa_secret'], $code)) {

            return false;
        }


        session_regenerate_id(true);
        $_SESSION['admin_user'] = $user;
        $_SESSION['ip'] = $_SERVER['REMOTE_ADDR'];
        $_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'];


        $logger->log('LOGIN_SUCESSO', ['usuario_alvo' => $user]);

        return true;
    }

    public function checkAuth(): void
    {

        if (
            !isset($_SESSION['admin_user']) ||
            ($_SESSION['ip'] ?? null) !== ($_SERVER['REMOTE_ADDR'] ?? null) ||
            ($_SESSION['user_agent'] ?? null) !== ($_SERVER['HTTP_USER_AGENT'] ?? null)
        ) {

            $this->logout();
            header('Location: login.php');
            exit;
        }
    }

    public function logout(): void
    {
        if (isset($_SESSION['admin_user'])) {
            $logger = new AuditLogger();
            $logger->log('LOGOUT_SUCESSO', ['usuario_alvo' => $_SESSION['admin_user']]);
        }
        session_unset();
        session_destroy();
    }


    public function createAdmin(string $user, string $password): array
    {

        $secret = $this->gAuth->generateSecretKey();

        $data = [
            $user => [
                'password_hash' => password_hash($password, PASSWORD_DEFAULT),
                'mfa_secret' => $secret
            ]
        ];

        file_put_contents($this->authFile, json_encode($data, JSON_PRETTY_PRINT));

        return ['secret' => $secret, 'user' => $user];
    }

    private function getAccounts(): array
    {
        if (!file_exists($this->authFile)) {
            return [];
        }
        return json_decode(file_get_contents($this->authFile), true) ?? [];
    }
}
