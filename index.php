<?php
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/public/config.php';

$uri = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');

// Arquivos estáticos
if (preg_match('/\.(css|js|png|jpg|jpeg|gif|ico)$/', $uri)) {
    return false;
}

switch ($uri) {

    case 'login':
        require __DIR__ . '/public/login.php';
        break;

    case 'dashboard':
        require __DIR__ . '/public/admin_logs.php';
        break;

    case 'logout':
        require __DIR__ . '/public/logout.php';
         header('Location: login');
        break;

    case '':
    case 'cadastro':
        require __DIR__ . '/public/index.php'; 
        break;

    default:
        http_response_code(404);
        echo 'Página não encontrada.';
        break;
}
