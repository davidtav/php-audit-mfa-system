<?php
namespace App;
require_once __DIR__ . '/../vendor/autoload.php'; 
use App\UserManager;
use Rakit\Validation\Validator; 

$message = "";
$userManager = new UserManager();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {        
        http_response_code(403); 
        die('Erro de Segurança: Falha na verificação CSRF.');
    }
    
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
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