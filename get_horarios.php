<?php
require_once 'conexao.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $data = $_GET['data'] ?? date('Y-m-d');
    
    // Todos os horários possíveis
    $horarios = [
        '08:00', '09:00', '10:00', '11:00', 
        '14:00', '15:00', '16:00', '17:00',
        '19:00', '20:00', '21:00'
    ];
    
    // Verificar horários ocupados por reservas de computador
    $sql = "SELECT TIME_FORMAT(inicio, '%H:%i') as hora_inicio
            FROM reservas 
            WHERE DATE(inicio) = ? 
            AND status != 'recusado'
            GROUP BY hora_inicio
            HAVING COUNT(*) >= (SELECT COUNT(*) FROM computadores)";
            
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $data);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $horarios_ocupados = [];
    while ($row = $result->fetch_assoc()) {
        $horarios_ocupados[] = $row['hora_inicio'];
    }
    
    // Verificar horários ocupados por reservas de sala
    $sql = "SELECT TIME_FORMAT(inicio, '%H:%i') as inicio, 
                   TIME_FORMAT(fim, '%H:%i') as fim 
            FROM reservas_sala 
            WHERE DATE(inicio) = ? 
            AND status = 'aprovado'";
            
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $data);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $inicio = strtotime($row['inicio']);
        $fim = strtotime($row['fim']);
        
        foreach ($horarios as $horario) {
            $hora = strtotime($horario);
            if ($hora >= $inicio && $hora < $fim) {
                $horarios_ocupados[] = $horario;
            }
        }
    }
    
    // Remover horários que já passaram hoje
    if ($data == date('Y-m-d')) {
        $hora_atual = date('H:i');
        foreach ($horarios as $horario) {
            if ($horario <= $hora_atual) {
                $horarios_ocupados[] = $horario;
            }
        }
    }
    
    // Filtrar horários disponíveis
    $horarios_disponiveis = array_values(array_diff($horarios, array_unique($horarios_ocupados)));
    
    header('Content-Type: application/json');
    echo json_encode($horarios_disponiveis);
} else {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Método não permitido']);
}
?> 