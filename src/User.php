<?php
namespace App;

class User
{    
    public function __construct(
        private ?string $id,
        private string $nome,
        private int $idade,
        private string $email,
        private string $profissao
    ) {}

    
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'nome' => $this->nome,
            'idade' => $this->idade,
            'email' => $this->email,
            'profissao' => $this->profissao
        ];
    }
}