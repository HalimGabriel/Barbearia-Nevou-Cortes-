<?php
header('Content-Type: application/json');

require_once __DIR__ . '/../inc/functions.php';

if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Acesso negado. Usuário não autenticado.']);
    exit;
}

$logged_user_id = $_SESSION['user_id'];
$logged_user_role = $_SESSION['user_role'];
$is_admin = ($logged_user_role === 'admin');

$requestedBarberId = filter_input(INPUT_GET, 'id_barbeiro', FILTER_VALIDATE_INT);

if (!$requestedBarberId) {
    http_response_code(400);
    echo json_encode(['error' => 'ID do barbeiro inválido ou ausente.']);
    exit;
}

if (!$is_admin && $requestedBarberId !== $logged_user_id) {
    http_response_code(403);
    echo json_encode(['error' => 'Acesso negado. Você só pode visualizar sua própria agenda.']);
    exit;
}

try {
    $pdo = get_db_connection();
    $currentDateTime = date('Y-m-d H:i:s');

    $sql = "
        SELECT 
            a.id_agendamento, 
            a.data_hora_inicio, 
            s.nome_servico, 
            c.nome AS nome_cliente,
            c.telefone AS telefone_cliente,
            a.status
        FROM agendamentos a
        JOIN clientes c ON a.id_cliente = c.id_cliente
        JOIN servicos s ON a.id_servico = s.id_servico
        WHERE a.id_barbeiro = :barberId
          AND a.data_hora_inicio >= :currentDateTime 
        ORDER BY a.data_hora_inicio ASC
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':barberId', $requestedBarberId, PDO::PARAM_INT);
    $stmt->bindParam(':currentDateTime', $currentDateTime);
    $stmt->execute();

    $agendamentos = [];

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $dateTime = new DateTime($row['data_hora_inicio']);
        
        $agendamentos[] = [
            'id' => $row['id_agendamento'],
            'data_formatada' => $dateTime->format('d/m/Y'),
            'hora_formatada' => $dateTime->format('H:i'),
            'nome_cliente' => esc($row['nome_cliente']),
            'telefone' => esc($row['telefone']),
            'nome_servico' => esc($row['nome_servico']),
            'status' => esc($row['status']),
        ];
    }
    
    echo json_encode($agendamentos);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Erro interno do servidor: ' . $e->getMessage()]);
    exit;
}
