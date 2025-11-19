<?php
namespace App;

class RateLimiter
{
    private string $rateFile;
    public int $maxAttempts = 3; // Tentativas permitidas antes do bloqueio
    private int $blockDuration = 900; // 900 segundos = 15 minutos de bloqueio

    public function __construct()
    {
        // Usa um arquivo separado para não misturar com credenciais MFA
        $this->rateFile = __DIR__ . '/../../data/rate_limits.json'; 
        if (!file_exists($this->rateFile)) {
            file_put_contents($this->rateFile, json_encode([]));
        }
    }

    private function getIp(): string
    {
        // Função segura para obter o IP (incluindo tratamento para localhost)
        return $_SERVER['REMOTE_ADDR'] ?? '::1';
    }

    private function getData(): array
    {
        $content = file_get_contents($this->rateFile);
        return json_decode($content, true) ?? [];
    }

    private function saveData(array $data): void
    {
        file_put_contents($this->rateFile, json_encode($data, JSON_PRETTY_PRINT));
    }

    public function check(): array
    {
        $ip = $this->getIp();
        $data = $this->getData();
        $now = time();

        if (!isset($data[$ip])) {
            return ['status' => 'OK', 'tentativas' => 0];
        }

        $ipData = $data[$ip];

        // 1. Verifica se o bloqueio expirou
        if ($ipData['bloqueado_ate'] > $now) {
            $timeLeft = $ipData['bloqueado_ate'] - $now;
            return ['status' => 'BLOQUEADO', 'timeLeft' => $timeLeft];
        }

        // 2. Verifica se as tentativas antigas expiraram (reseta após 1 hora sem erro)
        if ($ipData['ultimo_erro'] < $now - 3600) { 
            $this->resetAttempts(); // Reseta se a última falha foi há mais de 1h
            return ['status' => 'OK', 'tentativas' => 0];
        }

        return ['status' => 'OK', 'tentativas' => $ipData['tentativas']];
    }

    public function recordFailure(): void
    {
        $ip = $this->getIp();
        $data = $this->getData();
        $now = time();

        $ipData = $data[$ip] ?? ['tentativas' => 0, 'ultimo_erro' => 0, 'bloqueado_ate' => 0];
        
        // Incrementa a tentativa
        $ipData['tentativas']++;
        $ipData['ultimo_erro'] = $now;

        // Se o limite foi atingido, aplica o bloqueio
        if ($ipData['tentativas'] >= $this->maxAttempts) {
            $ipData['bloqueado_ate'] = $now + $this->blockDuration;
            $ipData['tentativas'] = 0; // Opcional: Reseta a contagem após o bloqueio
        }

        $data[$ip] = $ipData;
        $this->saveData($data);
    }

    public function resetAttempts(): void
    {
        $ip = $this->getIp();
        $data = $this->getData();
        
        // Remove completamente o registro do IP para limpar todas as falhas
        unset($data[$ip]);
        $this->saveData($data);
    }
}