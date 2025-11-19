<?php
namespace App;
require_once __DIR__ . '/../vendor/autoload.php'; 
use App\UserManager;
use App\AuditLogger; 
use Rakit\Validation\Validator; 

$message = "";
$userManager = new UserManager();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
     if (!isset($_POST['csrf_token']) || 
        !isset($_SESSION['csrf_token']) || 
        !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token']) 
    ) {
        // A. Registro Forense do Incidente
        $logger = new AuditLogger();
        $logger->log('ALERTA_SEGURANCA_CSRF', [
            'motivo' => 'Token inválido ou ausente na tentativa de Cadastro',
            'ip_atacante' => $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'UNKNOWN',
            'dados_suspeitos' => $_POST['email'] ?? 'N/A' 
        ]);

        // B. Bloqueio Imediato
        http_response_code(403);
        die('Erro de Segurança: O formulário de cadastro foi bloqueado. Token inválido.');
    }   
   
    unset($_SESSION['csrf_token']);   
   
    $validator = new Validator();
    
    $validation = $validator->make($_POST, [
        'nome'      => 'required|min:3',
        'idade'     => 'required|numeric|min:18', 
        'email'     => 'required|email',
        'profissao' => 'required'
    ]);

    $validation->setMessages([
        'required' => 'O campo :attribute é obrigatório.',
        'email' => 'O :attribute deve ser um endereço válido.',
        'min' => 'A :attribute deve ser de no mínimo :min caracteres.',
        'numeric' => 'A :attribute deve ser um número.'
    ]);

    $validation->validate();

    if ($validation->fails()) {
        $errors = $validation->errors();
        $message = $errors->firstOfAll()[array_key_first($errors->firstOfAll())];        
    } else {
        $userManager->save(
            $_POST['nome'],
            (int)$_POST['idade'],
            $_POST['email'],
            $_POST['profissao']
        );
        $message = "Usuário cadastrado com sucesso!";
    }
}

$users = $userManager->getAll();