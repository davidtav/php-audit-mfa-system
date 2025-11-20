<?php

require __DIR__ . '/config.php';

use App\AuthManager;

$auth = new AuthManager();
$auth->logout();

header("Location: {$basePath}/login");
exit;
