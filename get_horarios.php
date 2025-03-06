<?php
require_once 'conexao.php';

if (isset($_GET['data'])) {
    $data = $_GET['data'];
    
    // Todos os horários possíveis
    $horarios = [
        '08:00', '09:00', '10:00', '11:00', 
        '14:00', '15:00', '16:00', '17:00',
        '19:00', '20:00', '21:00'
    ];
    
    // Contar quantos computadores estão disponíveis em cada horário
    $sql = "SELECT TIME_FORMAT(h.hora, '%H:%i') as horario,
            (SELECT COUNT(*) FROM computadores) - 
            (SELECT COUNT(DISTINCT r.computador_num) 
             FROM reservas r 
             WHERE r.status != 'recusado'
             AND DATE(r.inicio) = ?
             AND TIME_FORMAT(r.inicio, '%H:%i') = TIME_FORMAT(h.hora, '%H:%i')
            ) as computadores_disponiveis
     FROM (
         SELECT STR_TO_DATE(?, '%Y-%m-%d') + INTERVAL (hora.numero - 1) HOUR as hora
         FROM (
             SELECT 8 as numero UNION SELECT 9 UNION SELECT 10 UNION SELECT 11
             UNION SELECT 14 UNION SELECT 15 UNION SELECT 16 UNION SELECT 17
             UNION SELECT 19 UNION SELECT 20 UNION SELECT 21
         ) as hora
     ) h";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $data, $data);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $horarios_ocupados = [];
    while($row = $result->fetch_assoc()) {
        if ($row['computadores_disponiveis'] <= 0) {
            $horarios_ocupados[] = $row['horario'];
        }
    }
    
    // Horários ocupados por reservas de sala
    $sql = "SELECT TIME_FORMAT(inicio, '%H:%i') as inicio, 
                   TIME_FORMAT(fim, '%H:%i') as fim 
            FROM reservas_sala 
            WHERE DATE(inicio) = ? 
            AND status = 'aprovado'";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $data);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while($row = $result->fetch_assoc()) {
        $inicio = strtotime($row['inicio']);
        $fim = strtotime($row['fim']);
        
        foreach($horarios as $horario) {
            $hora = strtotime($horario);
            if ($hora >= $inicio && $hora < $fim) {
                $horarios_ocupados[] = $horario;
            }
        }
    }
    
    // Remover horários que já passaram hoje
    if ($data == date('Y-m-d')) {
        $hora_atual = date('H:i');
        foreach($horarios as $key => $horario) {
            if ($horario <= $hora_atual) {
                $horarios_ocupados[] = $horario;
            }
        }
    }
    
    // Retornar horários disponíveis
    $horarios_disponiveis = array_values(array_diff($horarios, array_unique($horarios_ocupados)));
    header('Content-Type: application/json');
    echo json_encode($horarios_disponiveis);
}
?> 