<?php
require_once __DIR__ . '/../vendor/autoload.php';

use App\AuditLogger;
use App\AuthManager;

$auth = new AuthManager();
$auth->checkAuth();


$logger = new AuditLogger();
$logs = $logger->getLogs();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Logs de Auditoria do Sistema</title>
    <link rel="stylesheet" href="style_dashboard.css">
</head>
<body>

<div class="log-container">
    <h1>🛡️ Monitor de Auditoria (Forense)</h1>
    <p>Rastreamento de atividades em tempo real.</p>

    <?php if (empty($logs)): ?>
        <div class="empty-log">Nenhum registro de atividade encontrado.</div>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th width="15%">Data/Hora</th>
                    <th width="10%">IP Origem</th>
                    <th width="15%">Ação</th>
                    <th width="30%">User Agent</th>
                    <th width="30%">Detalhes (JSON)</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($logs as $entry): ?>
                <tr>
                    <td><?= $entry['timestamp'] ?></td>
                    
                    <td class="ip-addr">
                        <?= $entry['ip_origem'] === '::1' ? 'Localhost' : htmlspecialchars($entry['ip_origem']) ?>
                    </td>
                    
                    <td>
                        <span class="badge badge-create"><?= htmlspecialchars($entry['acao']) ?></span>
                    </td>
                    
                    <td title="<?= htmlspecialchars($entry['user_agent']) ?>">
                        <?= htmlspecialchars(substr($entry['user_agent'], 0, 50)) ?>...
                    </td>
                    
                    <td>
                        <div class="detail-box">
                            <?php 
                                foreach ($entry['detalhes'] as $chave => $valor) {
                                    echo "<strong>$chave:</strong> " . htmlspecialchars($valor) . "<br>";
                                }
                            ?>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
<div style="text-align: right; margin-bottom: 20px;">
    <a href="logout.php" style="color: #ff6b6b; text-decoration: none;">[ Sair do Sistema ]</a>
</div>
</body>
</html>