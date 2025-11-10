<?php
session_start();
require_once '../inc/functions.php';

date_default_timezone_set('America/Sao_Paulo');
$timezone = new DateTimeZone('America/Sao_Paulo');

if (!is_logged()) {
    set_flash("Você precisa estar logado para agendar.");
    redirect('../login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    set_flash("Erro: Acesso inválido ao formulário de agendamento.");
    redirect('../index.php');
}

$id_cliente = $_SESSION['cliente_id'] ?? null;

$id_barbeiro = filter_input(INPUT_POST, 'id_barbeiro', FILTER_VALIDATE_INT);
$id_servico = filter_input(INPUT_POST, 'id_servico', FILTER_VALIDATE_INT);
$data_hora_str = trim(filter_input(INPUT_POST, 'data_hora', FILTER_UNSAFE_RAW) ?: '');

$data_hora_inicio = null;

if (!$id_cliente || !$id_barbeiro || !$id_servico || empty($data_hora_str)) {
    set_flash("Erro de agendamento: Por favor, selecione um barbeiro, um serviço e a data/hora.");
    redirect('../index.php#agendar');
}

try {
    error_log("DEBUG: String de data recebida (POST): '" . $data_hora_str . "'");
    
    $agendamento_dt = null;

    $agendamento_dt = DateTime::createFromFormat('Y-m-d\TH:i', $data_hora_str, $timezone);
    
    if (!$agendamento_dt) {
        $agendamento_dt = DateTime::createFromFormat('Y-m-d H:i', $data_hora_str, $timezone);
    }

    if (!$agendamento_dt) {
        $agendamento_dt = DateTime::createFromFormat('Y-m-d\TH:i:s', $data_hora_str, $timezone);
    }

    if (!$agendamento_dt) {
        $agendamento_dt = DateTime::createFromFormat('Y-m-d H:i:s', $data_hora_str, $timezone);
    }
    
    if (!$agendamento_dt) {
        throw new Exception("A string de data não corresponde a nenhum formato esperado (YYYY-MM-DDTHH:MM, YYYY-MM-DD HH:MM, YYYY-MM-DDTHH:MM:SS, ou YYYY-MM-DD HH:MM:SS).");
    }

    $data_hora_inicio = $agendamento_dt->format('Y-m-d H:i:s');
    
    
    $agora_dt = new DateTime('now', $timezone);
    
    $limite_agora_dt = clone $agora_dt;
    $limite_agora_dt->modify('+1 minute');
    
    $diferenca_segundos = $agendamento_dt->getTimestamp() - $agora_dt->getTimestamp();

    error_log("--- DEBUG AGENDAMENTO TIMEZONE/FUTURO ---");
    error_log("Data/Hora POST: " . $data_hora_str);
    error_log("Agendado (Formatado): " . $agendamento_dt->format('Y-m-d H:i:s T'));
    error_log("Atual (Servidor): " . $agora_dt->format('Y-m-d H:i:s T'));
    error_log("Diferença (Agendado - Atual) em segundos: " . $diferenca_segundos);
    error_log("O agendamento será rejeitado se Agendado < Limite (+1 min).");
    error_log("------------------------------------------");

    if ($agendamento_dt < $limite_agora_dt) {
        set_flash("❌ Erro de agendamento: A data e hora devem ser futuras e ter no mínimo 1 minuto de antecedência.");
        redirect('../index.php#agendar');
    }
    
    $hora_agendamento = (int)$agendamento_dt->format('H');
    if ($hora_agendamento < 9 || $hora_agendamento > 18) {
        set_flash("❌ Erro de agendamento: O horário de agendamento deve ser entre 09:00 e 18:00.");
        redirect('../index.php#agendar');
    }

} catch (Exception $e) {
    error_log("Erro de parsing de data: " . $e->getMessage());
    set_flash("❌ Erro interno: Formato de data inválido. Por favor, tente selecionar o horário novamente. Detalhe: Verifique o log do servidor.");
    redirect('../index.php#agendar');
}

try {
    $pdo = get_db_connection();

    $stmt_conflito = $pdo->prepare("
        SELECT COUNT(*) 
        FROM agendamentos 
        WHERE id_barbeiro = ? 
        AND data_hora_inicio = ?
        AND status = 'agendado'
    ");
    $stmt_conflito->execute([$id_barbeiro, $data_hora_inicio]);
    
    if ($stmt_conflito->fetchColumn() > 0) {
        set_flash("❌ Erro de agendamento: O barbeiro já está ocupado neste horário. Por favor, escolha outro horário.");
        redirect('../index.php#agendar');
    }

    $stmt_insert = $pdo->prepare("
        INSERT INTO agendamentos (id_cliente, id_barbeiro, id_servico, data_hora_inicio) 
        VALUES (?, ?, ?, ?)
    ");

    $stmt_insert->execute([
        $id_cliente,
        $id_barbeiro,
        $id_servico,
        $data_hora_inicio
    ]);

    set_flash("✅ Sucesso! Seu agendamento foi confirmado para " . $agendamento_dt->format('d/m/Y \à\s H:i') . ".");
    redirect('../index.php');

} catch (PDOException $e) {
    error_log("Erro de Agendamento no Banco de Dados: " . $e->getMessage());
    set_flash("❌ Erro de agendamento: Não foi possível completar sua solicitação. Tente novamente mais tarde.");
    redirect('../index.php#agendar');
}
?>