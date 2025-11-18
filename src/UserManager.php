<?php
namespace App;
use Ramsey\Uuid\Uuid;

class UserManager
{
    private string $filePath;

    public function __construct()
    {        
        $this->filePath = __DIR__ . '/../data/users.json';
        if (!file_exists($this->filePath)) {
            file_put_contents($this->filePath, json_encode([]));
        }
    }

    public function getAll(): array
    {
        $content = file_get_contents($this->filePath);
        $data = json_decode($content, true);
        return is_array($data) ? $data : [];
    }

    public function save(string $nome, int $idade, string $email, string $profissao): void
    {
        $users = $this->getAll();
        $uuid = Uuid::uuid4()->toString();    
        $user = new User($uuid, $nome, $idade, $email, $profissao);
        array_unshift($users, $user->toArray());
        
        file_put_contents(
            $this->filePath, 
            json_encode($users, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
        );
        $logger = new AuditLogger();
        $logger->log('CRIACAO_USUARIO', [
            'uuid_gerado' => $uuid,
            'email_alvo'  => $email
        ]);
    }
}