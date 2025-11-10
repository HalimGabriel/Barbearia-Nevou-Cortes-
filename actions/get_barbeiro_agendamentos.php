<?php
require_once '../inc/functions.php';
header('Content-Type: application/json; charset=utf-8');

if (!isset($_GET['id_barbeiro'])) {
    echo json_encode(['error' => 'ID do barbeiro não informado']);
    exit;
}

$id_barbeiro = intval($_GET['id_barbeiro']);

try {
    $pdo = get_db_connection();

    $sql = "
        SELECT 
            a.id_agendamento,
            a.data_hora_inicio,
            s.nome_servico,
            c.nome AS nome_cliente,
            COALESCE(c.telefone, 'Não informado') AS telefone,
            a.status
        FROM agendamentos a
        INNER JOIN clientes c ON a.id_cliente = c.id_cliente
        INNER JOIN servicos s ON a.id_servico = s.id_servico
        WHERE a.id_barbeiro = ?
        ORDER BY a.data_hora_inicio DESC
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id_barbeiro]);

    $agendamentos = [];

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $dateTime = new DateTime($row['data_hora_inicio']);
        $agendamentos[] = [
            'id' => $row['id_agendamento'],
            'data_formatada' => $dateTime->format('d/m/Y'),
            'hora_formatada' => $dateTime->format('H:i'),
            'nome_cliente' => $row['nome_cliente'],
            'nome_servico' => $row['nome_servico'],
            'telefone' => $row['telefone'],
            'status' => $row['status']
        ];
    }

    echo json_encode($agendamentos, JSON_UNESCAPED_UNICODE);

} catch (PDOException $e) {
    error_log("Erro ao buscar agendamentos do barbeiro: " . $e->getMessage());
    echo json_encode(['error' => 'Erro ao buscar agendamentos.']);
}
