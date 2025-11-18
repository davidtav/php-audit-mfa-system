<?php
namespace App;

class AuditLogger
{
    private string $logFile;

    public function __construct()
    {
        $this->logFile = __DIR__ . '/../data/audit.log';
    }

    public function log(string $acao, array $detalhes = []): void
    {
        $logEntry = [
            'timestamp'  => date('Y-m-d H:i:s'),
            'ip_origem'  => $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'UNKNOWN',
            'acao'       => $acao,
            'detalhes'   => $detalhes
        ];

        $linhaLog = json_encode($logEntry, JSON_UNESCAPED_UNICODE);
        file_put_contents($this->logFile, $linhaLog . PHP_EOL, FILE_APPEND);
    }

    // --- NOVO MÉTODO PARA LEITURA ---
    public function getLogs(): array
    {
        if (!file_exists($this->logFile)) {
            return [];
        }

        // Lê o arquivo inteiro para um array (cada linha é um item)
        $linhas = file($this->logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        
        $logsFormatados = [];
        
        // Processa linha por linha
        foreach ($linhas as $linha) {
            $dados = json_decode($linha, true);
            if ($dados) {
                $logsFormatados[] = $dados;
            }
        }

        // Retorna invertido (o evento mais recente aparece primeiro)
        return array_reverse($logsFormatados);
    }
}