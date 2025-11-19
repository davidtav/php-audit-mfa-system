<?php

use App\AuthManager;

$auth = new AuthManager();
$auth->logout();
header('Location: login.php');
exit;