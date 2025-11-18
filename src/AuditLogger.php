<?php

namespace App;

class AuditLogger
{
    private string $logFile;
    private string $timezone;

    public function __construct(string $timezone = 'America/Sao_Paulo')
    {
        $this->logFile = __DIR__ . '/../data/audit.log';
        $this->timezone = $timezone;
        
        // Define o timezone se ainda não foi definido
        if (date_default_timezone_get() !== $this->timezone) {
            date_default_timezone_set($this->timezone);
        }
        
        // Garante que o diretório existe
        $dir = dirname($this->logFile);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
    }

    /**
     * Registra um evento no log de auditoria
     * 
     * @param string $acao Tipo de ação (LOGIN_SUCESSO, LOGIN_FALHOU, etc)
     * @param array $detalhes Detalhes adicionais do evento
     */
    public function log(string $acao, array $detalhes = []): void
    {
        $timestamp = $this->getFormattedTimestamp();
        
        $logEntry = [
            'timestamp' => $timestamp,
            'data_hora' => date('Y-m-d H:i:s'),
            'acao' => $acao,
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'DESCONHECIDO',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'DESCONHECIDO',
            'usuario_sessao' => $_SESSION['admin_user'] ?? 'NAO_AUTENTICADO',
            'detalhes' => $detalhes
        ];

        // Formata como JSON legível
        $jsonLine = json_encode($logEntry, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . PHP_EOL;
        
        // Grava no arquivo (com lock para evitar problemas de concorrência)
        file_put_contents($this->logFile, $jsonLine, FILE_APPEND | LOCK_EX);
    }

    /**
     * Retorna timestamp formatado com timezone
     */
    private function getFormattedTimestamp(): string
    {
        $dt = new \DateTime('now', new \DateTimeZone($this->timezone));
        return $dt->format('Y-m-d H:i:s T'); // Ex: 2025-11-18 02:02:00 -03
    }

    /**
     * Lê os logs com filtros opcionais
     * 
     * @param int $limit Quantidade máxima de registros
     * @param string|null $acao Filtrar por tipo de ação
     * @param string|null $usuario Filtrar por usuário
     * @return array
     */
    public function readLogs(int $limit = 100, ?string $acao = null, ?string $usuario = null): array
    {
        if (!file_exists($this->logFile)) {
            return [];
        }

        $lines = file($this->logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $logs = [];

        // Lê do mais recente para o mais antigo
        foreach (array_reverse($lines) as $line) {
            $entry = json_decode($line, true);
            
            if ($entry === null) {
                continue; // Linha corrompida, ignora
            }

            // Filtro por ação
            if ($acao !== null && !empty($acao)) {
                if (($entry['acao'] ?? '') !== $acao) {
                    continue;
                }
            }

            // Filtro por usuário (busca em múltiplos campos)
            if ($usuario !== null && !empty($usuario)) {
                $usuarioEncontrado = false;
                
                // Verifica no usuario_sessao
                if (isset($entry['usuario_sessao']) && 
                    stripos($entry['usuario_sessao'], $usuario) !== false) {
                    $usuarioEncontrado = true;
                }
                
                // Verifica no usuario_alvo dos detalhes
                if (isset($entry['detalhes']['usuario_alvo']) && 
                    stripos($entry['detalhes']['usuario_alvo'], $usuario) !== false) {
                    $usuarioEncontrado = true;
                }
                
                if (!$usuarioEncontrado) {
                    continue;
                }
            }

            $logs[] = $entry;

            if (count($logs) >= $limit) {
                break;
            }
        }

        return $logs;
    }

    /**
     * Retorna estatísticas dos logs
     */
    public function getStats(): array
    {
        $logs = $this->readLogs(PHP_INT_MAX); // Lê tudo
        
        $stats = [
            'total' => count($logs),
            'logins_sucesso' => 0,
            'logins_falhou' => 0,
            'logout' => 0,
            'por_usuario' => [],
            'por_ip' => [],
            'ultimos_7_dias' => []
        ];

        $sevenDaysAgo = strtotime('-7 days');

        foreach ($logs as $entry) {
            $acao = $entry['acao'] ?? '';
            $usuario = $entry['detalhes']['usuario_alvo'] ?? $entry['usuario_sessao'] ?? 'DESCONHECIDO';
            $ip = $entry['ip'] ?? 'DESCONHECIDO';
            $timestamp = strtotime($entry['data_hora'] ?? '');

            // Contadores por ação
            switch ($acao) {
                case 'LOGIN_SUCESSO':
                    $stats['logins_sucesso']++;
                    break;
                case 'LOGIN_FALHOU':
                    $stats['logins_falhou']++;
                    break;
                case 'LOGOUT_SUCESSO':
                    $stats['logout']++;
                    break;
            }

            // Por usuário
            if (!isset($stats['por_usuario'][$usuario])) {
                $stats['por_usuario'][$usuario] = 0;
            }
            $stats['por_usuario'][$usuario]++;

            // Por IP
            if (!isset($stats['por_ip'][$ip])) {
                $stats['por_ip'][$ip] = 0;
            }
            $stats['por_ip'][$ip]++;

            // Últimos 7 dias
            if ($timestamp >= $sevenDaysAgo) {
                $dia = date('Y-m-d', $timestamp);
                if (!isset($stats['ultimos_7_dias'][$dia])) {
                    $stats['ultimos_7_dias'][$dia] = 0;
                }
                $stats['ultimos_7_dias'][$dia]++;
            }
        }

        return $stats;
    }

    /**
     * Limpa logs antigos (manutenção)
     * 
     * @param int $dias Manter apenas logs dos últimos N dias
     */
    public function cleanOldLogs(int $dias = 90): int
    {
        if (!file_exists($this->logFile)) {
            return 0;
        }

        $lines = file($this->logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $cutoffDate = strtotime("-{$dias} days");
        $kept = [];
        $removed = 0;

        foreach ($lines as $line) {
            $entry = json_decode($line, true);
            
            if ($entry === null) {
                continue;
            }

            $timestamp = strtotime($entry['data_hora'] ?? '');
            
            if ($timestamp >= $cutoffDate) {
                $kept[] = $line;
            } else {
                $removed++;
            }
        }

        // Reescreve o arquivo
        file_put_contents($this->logFile, implode(PHP_EOL, $kept) . PHP_EOL, LOCK_EX);

        return $removed;
    }
}