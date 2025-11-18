<?php

namespace App;

use PragmaRX\Google2FA\Google2FA;


class AuthManager
{
    private string $authFile;
    private Google2FA $gAuth;

    public function __construct()
    {
        $this->authFile = __DIR__ . '/../data/auth.json';
        $this->gAuth = new Google2FA();

        // Inicia a sessão se ainda não estiver iniciada
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public function login(string $user, string $password, string $code): bool
    {
        $accounts = $this->getAccounts();

        // 1. Verifica se o usuário existe
        if (!isset($accounts[$user])) {
            return false;
        }

        $account = $accounts[$user];

        // 2. Verifica a senha (Hash seguro)
        if (!password_verify($password, $account['password_hash'])) {
            return false;
        }

        // 3. Verifica o código MFA (TOTP)
        // O código é válido por 30 segundos. A biblioteca verifica o atual e o anterior para evitar delays.
        if (!$this->gAuth->verifyKey($account['mfa_secret'], $code)) {
            return false;
        }

        // 4. Login Sucesso: Regenera ID da sessão (Proteção contra Session Fixation)
        session_regenerate_id(true);
        $_SESSION['admin_user'] = $user;
        $_SESSION['ip'] = $_SERVER['REMOTE_ADDR'];
        $_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'];

        return true;
    }

    public function checkAuth(): void
    {
        // Verifica se está logado E se o IP/UserAgent batem (Proteção contra roubo de sessão)
        if (
            !isset($_SESSION['admin_user']) ||
            $_SESSION['ip'] !== $_SERVER['REMOTE_ADDR'] ||
            $_SESSION['user_agent'] !== $_SERVER['HTTP_USER_AGENT']
        ) {

            $this->logout();
            header('Location: login.php');
            exit;
        }
    }

    public function logout(): void
    {
        session_unset();
        session_destroy();
    }

    // Método auxiliar para criar o usuário inicial (usaremos no setup)
    public function createAdmin(string $user, string $password): array
    {
        // Gera um segredo único para o 2FA
        $secret = $this->gAuth->generateSecretKey();

        $data = [
            $user => [
                'password_hash' => password_hash($password, PASSWORD_DEFAULT), // Bcrypt ou Argon2 automático
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
