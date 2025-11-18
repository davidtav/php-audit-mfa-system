<?php
require_once __DIR__ . '/../vendor/autoload.php';
use App\AuthManager;

$auth = new AuthManager();
$auth->logout();
header('Location: login.php');
exit;