<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (empty($_SESSION['csrf_token'])) {    
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32)); 
}
include __DIR__ . '/../src/Requisicao.php';
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Cadastro de Usuários</title>
    <link rel="stylesheet" href="/public/style.css">
</head>
<body>
    <div class="main-container">
        <div class="form-container">
            <h1>Cadastro de Usuários</h1>

            <?php if ($message): ?>
                <div id="feedback" class="alert"><?= htmlspecialchars($message) ?></div>
            <?php endif; ?>

            <form action="" method="POST" id="userForm">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                <label>Nome:</label>
                <input type="text" name="nome" required>

                <label>Idade:</label>
                <input type="number" name="idade" required>

                <label>Email:</label>
                <input type="email" name="email" required>

                <label>Profissão:</label>
                <input type="text" name="profissao" required>

                <button type="submit">Cadastrar</button>
            </form>
        </div>

        <div class="table-container">
            <h2>Usuários Cadastrados</h2>
            <?php include __DIR__ . '/../src/table.php'; ?>
        </div>
    </div>
    <script src="./../public/script.js"></script>
</body>

</html>