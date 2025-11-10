<?php

header('Content-Type: application/json');


require_once '../inc/functions.php';

$response = ['success' => false, 'error' => null];


$data = json_decode(file_get_contents('php://input'), true);


$agendamentoId = $data['id'] ?? null; 
$novoStatus = $data['status'] ?? null;

if (empty($agendamentoId) || empty($novoStatus)) {
    $response['error'] = "Dados de ID ou Status não fornecidos.";
    echo json_encode($response);
    exit;
}


$statusValidos = ['concluido', 'cancelado'];
if (!in_array($novoStatus, $statusValidos)) {
    $response['error'] = "Status inválido fornecido. Apenas 'concluido' ou 'cancelado' são permitidos.";
    echo json_encode($response);
    exit;
}

try {
   
    $db = get_db_connection();
    

    $sql = "UPDATE agendamentos SET status = :status WHERE id_agendamento = :id";
    
    $stmt = $db->prepare($sql);
    $stmt->bindParam(':status', $novoStatus);
    $stmt->bindParam(':id', $agendamentoId); 
    
    
    if ($stmt->execute()) {
        if ($stmt->rowCount() > 0) {
            $response['success'] = true;
        } else {
            
            $response['error'] = "Agendamento não encontrado ou status já era '{$novoStatus}'.";
        }
    } else {
        $response['error'] = "Falha na execução da consulta de atualização.";
    }

} catch (Exception $e) {
 
    $response['error'] = "Erro interno do servidor: " . $e->getMessage();
}

echo json_encode($response);
?>
