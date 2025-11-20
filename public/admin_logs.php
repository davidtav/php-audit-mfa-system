<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/../vendor/autoload.php';

use App\AuditLogger;
use App\AuthManager;

$auth = new AuthManager();
$auth->checkAuth();

$logger = new AuditLogger();

// Parâmetros de filtro (opcional)
$filtroAcao = $_GET['acao'] ?? null;
$filtroUsuario = $_GET['usuario'] ?? null;
$limite = (int)($_GET['limite'] ?? 100);

// Lê os logs com filtros
$logs = $logger->readLogs($limite, $filtroAcao, $filtroUsuario);

// Estatísticas
$stats = $logger->getStats();
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logs de Auditoria do Sistema</title>
    <link rel="stylesheet" href="<?= $basePath ?>/public/admin_style.css">
    <link rel="stylesheet" href="<?= $basePath ?>/public/style_dashboard.css">
</head>
<body>

<div class="log-container">
    <h1>🛡️ Monitor de Auditoria (Forense)</h1>
    <p>Rastreamento de atividades em tempo real - Timezone: America/Sao_Paulo</p>

    <!-- Estatísticas -->
    <div class="stats-container">
        <div class="stat-card">
            <h3><?= number_format($stats['total']) ?></h3>
            <p>Total de Eventos</p>
        </div>
        <div class="stat-card success">
            <h3><?= number_format($stats['logins_sucesso']) ?></h3>
            <p>Logins Bem-Sucedidos</p>
        </div>
        <div class="stat-card danger">
            <h3><?= number_format($stats['logins_falhou']) ?></h3>
            <p>Tentativas Falhadas</p>
        </div>
        <div class="stat-card">
            <h3><?= number_format($stats['logout']) ?></h3>
            <p>Logouts</p>
        </div>
    </div>

    <!-- Filtros -->
    <div class="filter-bar">
        <form method="GET" style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
            <label>Ação:</label>
            <select name="acao" id="filtroAcao">
                <option value="">Todas</option>
                <option value="LOGIN_SUCESSO" <?= $filtroAcao === 'LOGIN_SUCESSO' ? 'selected' : '' ?>>Login Sucesso</option>
                <option value="LOGIN_FALHOU" <?= $filtroAcao === 'LOGIN_FALHOU' ? 'selected' : '' ?>>Login Falhou</option>
                <option value="LOGOUT_SUCESSO" <?= $filtroAcao === 'LOGOUT_SUCESSO' ? 'selected' : '' ?>>Logout</option>
            </select>

            <label>Usuário:</label>
            <input type="text" name="usuario" id="filtroUsuario" value="<?= htmlspecialchars($filtroUsuario ?? '') ?>" placeholder="Filtrar por usuário">

            <label>Limite:</label>
            <input type="number" name="limite" id="filtroLimite" value="<?= $limite ?>" min="10" max="1000" style="width: 80px;">

            <button type="submit" style="padding: 8px 15px; background: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer;">
                🔍 Filtrar
            </button>
            <a href="admin_logs.php" style="padding: 8px 15px; background: #6c757d; color: white; text-decoration: none; border-radius: 4px;">
                🔄 Limpar
            </a>
            
            <?php if ($filtroAcao || $filtroUsuario || $limite != 100): ?>
                <span style="margin-left: 10px; color: #28a745; font-weight: bold;">
                    ✓ Filtro ativo
                </span>
            <?php endif; ?>
        </form>
    </div>

    <!-- Tabela de Logs -->
    <?php if (empty($logs)): ?>
        <div class="empty-log" style="text-align: center; padding: 40px; background: #f8f9fa; border-radius: 8px;">
            📭 Nenhum registro de atividade encontrado.
        </div>
    <?php else: ?>
        <p style="color: #666; margin-bottom: 10px;">
            Exibindo <?= count($logs) ?> registro(s) mais recente(s)
        </p>
        <table style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr style="background: #343a40; color: white;">
                    <th style="padding: 12px; text-align: left;" width="15%">Data/Hora</th>
                    <th style="padding: 12px; text-align: left;" width="10%">IP Origem</th>
                    <th style="padding: 12px; text-align: left;" width="12%">Ação</th>
                    <th style="padding: 12px; text-align: left;" width="10%">Usuário</th>
                    <th style="padding: 12px; text-align: left;" width="28%">User Agent</th>
                    <th style="padding: 12px; text-align: left;" width="25%">Detalhes</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($logs as $entry): ?>
                <tr style="border-bottom: 1px solid #dee2e6;">
                    <td style="padding: 10px; font-family: monospace; font-size: 0.9em;">
                        <?= htmlspecialchars($entry['data_hora'] ?? $entry['timestamp']) ?>
                    </td>
                    
                    <td style="padding: 10px;" class="ip-addr">
                        <?php
                        $ip = $entry['ip'] ?? 'N/A';
                        echo $ip === '::1' ? '<span style="color: #28a745;">Localhost</span>' : htmlspecialchars($ip);
                        ?>
                    </td>
                    
                    <td style="padding: 10px;">
                        <?php
                        $acao = $entry['acao'] ?? 'DESCONHECIDO';
                        $badgeClass = match($acao) {
                            'LOGIN_SUCESSO' => 'badge-success',
                            'LOGIN_FALHOU' => 'badge-danger',
                            'LOGOUT_SUCESSO' => 'badge-info',
                            default => 'badge-warning'
                        };
                        ?>
                        <span class="badge <?= $badgeClass ?>">
                            <?= htmlspecialchars($acao) ?>
                        </span>
                    </td>

                    <td style="padding: 10px; font-weight: bold;">
                        <?= htmlspecialchars($entry['usuario_sessao'] ?? $entry['detalhes']['usuario_alvo'] ?? 'N/A') ?>
                    </td>
                    
                    <td style="padding: 10px; font-size: 0.85em; color: #666;" 
                        title="<?= htmlspecialchars($entry['user_agent'] ?? '') ?>">
                        <?= htmlspecialchars(substr($entry['user_agent'] ?? 'N/A', 0, 50)) ?>...
                    </td>
                    
                    <td style="padding: 10px;">
                        <div style="background: #f8f9fa; padding: 8px; border-radius: 4px; font-size: 0.85em;">
                            <?php 
                            if (!empty($entry['detalhes'])) {
                                foreach ($entry['detalhes'] as $chave => $valor) {
                                    echo "<strong>" . htmlspecialchars($chave) . ":</strong> " 
                                       . htmlspecialchars($valor) . "<br>";
                                }
                            } else {
                                echo '<em style="color: #999;">Sem detalhes</em>';
                            }
                            ?>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <div style="text-align: right; margin-top: 20px;">
        <a href="<?= $basePath ?>/logout" style="color: #ff6b6b; text-decoration: none; font-weight: bold;">
            🚪 Sair do Sistema
        </a>
    </div>
</div>

</body>
</html>