<?php
session_start(); 
require_once '../inc/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    set_flash("Erro: Acesso inválido ao formulário de cadastro.");
    redirect('../login.php');
}

$nome = trim(filter_input(INPUT_POST, 'nome', FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?: '');
$email = trim(filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL) ?: '');
$telefone = trim(filter_input(INPUT_POST, 'telefone', FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?: '');
$senha_pura = filter_input(INPUT_POST, 'senha', FILTER_UNSAFE_RAW) ?: ''; 

if (empty($nome) || empty($email) || empty($telefone) || empty($senha_pura)) {
    set_flash("Erro de cadastro: Preencha todos os campos.");
    redirect('../login.php?action=register');
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    set_flash("Erro de cadastro: O formato do e-mail é inválido.");
    redirect('../login.php?action=register');
}

if (strlen($senha_pura) < 6) {
    set_flash("Erro de cadastro: A senha deve ter no mínimo 6 caracteres.");
    redirect('../login.php?action=register');
}

$senha_hash = password_hash($senha_pura, PASSWORD_DEFAULT);

try {
    $pdo = get_db_connection();

    $stmt = $pdo->prepare("SELECT id_cliente FROM clientes WHERE email = ?");
    $stmt->execute([$email]);
    
    if ($stmt->rowCount() > 0) {
        set_flash("Erro de cadastro: O e-mail '{$email}' já está cadastrado. Tente fazer login.");
        redirect('../login.php?action=register');
    }

    $stmt = $pdo->prepare("INSERT INTO clientes (nome, email, telefone, senha) VALUES (?, ?, ?, ?)");
    $stmt->execute([$nome, $email, $telefone, $senha_hash]);

    $novo_cliente_id = $pdo->lastInsertId();
    $_SESSION['cliente_id'] = $novo_cliente_id;
    $_SESSION['cliente_nome'] = $nome;
    
    set_flash("Sucesso! Cadastro realizado e login efetuado automaticamente.");
    redirect('../index.php');

} catch (PDOException $e) {
    set_flash("Erro de cadastro: Falha no servidor. Detalhes: " . $e->getMessage()); 
    redirect('../login.php?action=register');
}
?>
