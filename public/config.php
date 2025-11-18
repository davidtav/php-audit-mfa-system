<?php

/**
 * Configurações Globais do Sistema
 */

// Define o timezone padrão do Brasil (Brasília/São Paulo)
date_default_timezone_set('America/Sao_Paulo');

// Configuração de exibição de erros (ajuste conforme ambiente)
if (($_ENV['APP_ENV'] ?? 'production') === 'local') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// Configurações de sessão segura
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 1); // Ative apenas se usar HTTPS
ini_set('session.use_strict_mode', 1);

// Charset padrão
ini_set('default_charset', 'UTF-8');
mb_internal_encoding('UTF-8');