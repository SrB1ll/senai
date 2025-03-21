<?php
require_once 'conexao.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $nome = limpar_input($_POST['nome']);
        $cpf = limpar_input($_POST['cpf']);
        
        // Validar formato do CPF
        if (!preg_match("/^\d{3}\.\d{3}\.\d{3}-\d{2}$/", $cpf)) {
            throw new Exception("CPF inválido. Use o formato: 000.000.000-00");
        }
        
        $sql = "SELECT r.*, c.status as computador_status 
                FROM reservas r 
                LEFT JOIN computadores c ON r.computador_num = c.computador_num 
                WHERE r.nome = ? AND r.cpf = ? 
                ORDER BY r.inicio DESC";
                
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $nome, $cpf);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $reservas = [];
        while ($row = $result->fetch_assoc()) {
            $reservas[] = [
                'data' => date('d/m/Y', strtotime($row['inicio'])),
                'horario' => date('H:i', strtotime($row['inicio'])) . ' - ' . date('H:i', strtotime($row['fim'])),
                'computador' => 'Computador ' . $row['computador_num'],
                'status' => $row['status']
            ];
        }
        
        echo json_encode([
            'success' => true,
            'reservas' => $reservas
        ]);
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Método não permitido'
    ]);
}
?> 