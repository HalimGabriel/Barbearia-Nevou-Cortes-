<?php
require_once 'inc/functions.php';

try {
    $pdo = get_db_connection();
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Verifica se já existem barbeiros cadastrados
    $check = $pdo->query("SELECT COUNT(*) FROM barbeiros");
    $exists = $check->fetchColumn();

    if ($exists > 0) {
        // Já há dados, redireciona direto para o site
        header("Location: index.php");
        exit;
    }

    echo "Conectado ao banco de dados 'barbearia' com sucesso.<br>";

    // --- Inserir barbeiros ---
    $barbeiros_data = [
        [
            'nome' => 'Marcos Style', 
            'especialidade' => 'Cortes Modernos', 
            'email' => 'marcos@barbearia.com', 
            'senha_pura' => 'Marcos123', 
            'role' => 'barbeiro'
        ],
        [
            'nome' => 'Bruno Navalha', 
            'especialidade' => 'Barbas e Clássicos', 
            'email' => 'bruno@barbearia.com', 
            'senha_pura' => 'Bruno123', 
            'role' => 'barbeiro'
        ],
        [
            'nome' => 'Thiago Tesoura', 
            'especialidade' => 'Penteados e Degradês', 
            'email' => 'thiago@barbearia.com', 
            'senha_pura' => 'Thiago123', 
            'role' => 'barbeiro'
        ],
        [
            'nome' => 'Admin Gerente', 
            'especialidade' => 'Gerenciamento', 
            'email' => 'admin@barbearia.com', 
            'senha_pura' => 'admin123', 
            'role' => 'admin'
        ]
    ];

    $stmt_barbeiros = $pdo->prepare("
        INSERT INTO barbeiros (nome, especialidade, email, senha, role)
        VALUES (:nome, :especialidade, :email, :senha_hash, :role)
    ");

    foreach ($barbeiros_data as $b) {
        $senha_hash = password_hash($b['senha_pura'], PASSWORD_DEFAULT);
        $stmt_barbeiros->execute([
            ':nome' => $b['nome'],
            ':especialidade' => $b['especialidade'],
            ':email' => $b['email'],
            ':senha_hash' => $senha_hash,
            ':role' => $b['role']
        ]);
    }

    // --- Inserir serviços ---
    $servicos_data = [
        ['nome_servico' => 'Corte Adulto', 'duracao_minutos' => 45, 'preco' => 45.00],
        ['nome_servico' => 'Corte Infantil', 'duracao_minutos' => 35, 'preco' => 35.00],
        ['nome_servico' => 'Barba Modelada', 'duracao_minutos' => 30, 'preco' => 20.00],
        ['nome_servico' => 'Corte + Barba', 'duracao_minutos' => 75, 'preco' => 65.00],
    ];

    $stmt_servicos = $pdo->prepare("
        INSERT INTO servicos (nome_servico, duracao_minutos, preco)
        VALUES (:nome_servico, :duracao_minutos, :preco)
    ");

    foreach ($servicos_data as $s) {
        $stmt_servicos->execute($s);
    }

    echo "<h2>Dados iniciais inseridos com sucesso!</h2>";

    // Redireciona após inserir
    header("Location: index.php");
    exit;

} catch (PDOException $e) {
    echo "Erro: " . $e->getMessage();
}

$pdo = null;
?>
