<?php

session_start();
require_once '../inc/functions.php'; 


if (is_logged()) {
    redirect('../index.php');
}


if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    set_flash("Erro: Acesso inválido ao formulário de login.");
    redirect('../login.php');
}


$email = trim(filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL) ?: '');
$senha_pura = filter_input(INPUT_POST, 'senha', FILTER_UNSAFE_RAW) ?: '';


if (empty($email) || empty($senha_pura)) {
    set_flash("Erro de login: Preencha todos os campos.");
    redirect('../login.php');
}

try {
    $pdo = get_db_connection();
    $login_success = false;


    $stmt_barbeiro = $pdo->prepare("SELECT id_barbeiro, nome, senha, role FROM barbeiros WHERE email = ?");
    $stmt_barbeiro->execute([$email]);
    $barbeiro = $stmt_barbeiro->fetch(PDO::FETCH_ASSOC);

    if ($barbeiro && password_verify($senha_pura, $barbeiro['senha'])) {
        
  
        $_SESSION['user_id'] = $barbeiro['id_barbeiro'];
        $_SESSION['user_nome'] = $barbeiro['nome'];
        $_SESSION['user_role'] = $barbeiro['role']; 

        set_flash("Sucesso! Bem-vindo, {$barbeiro['nome']}! Você logou como {$barbeiro['role']}.");
        
   
        redirect('../agenda_barbeiro.php');
        $login_success = true;

    } 
    
 
    if (!$login_success) {
        
        $stmt_cliente = $pdo->prepare("SELECT id_cliente, nome, senha FROM clientes WHERE email = ?");
        $stmt_cliente->execute([$email]);
        $cliente = $stmt_cliente->fetch(PDO::FETCH_ASSOC);

        if ($cliente && password_verify($senha_pura, $cliente['senha'])) {
            
            // Sucesso no Login de Cliente
            $_SESSION['cliente_id'] = $cliente['id_cliente'];
            $_SESSION['cliente_nome'] = $cliente['nome'];
            
            set_flash("Sucesso! Bem-vindo de volta, {$cliente['nome']}!");
            

            redirect('../index.php');
            $login_success = true;
        }
    }


    if (!$login_success) {
        set_flash("Erro de login: E-mail ou senha inválidos.");
        redirect('../login.php');
    }

} catch (PDOException $e) {
    // Em caso de erro do servidor/banco de dados
    error_log("Erro de Login no PDO: " . $e->getMessage());
    set_flash("Erro de login: Não foi possível processar sua solicitação devido a uma falha no servidor.");
    redirect('../login.php');
}
