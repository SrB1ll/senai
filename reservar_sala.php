<?php
header('Content-Type: application/json');
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $nome = $_POST['nome'] ?? '';
        $telefone = $_POST['telefone'] ?? '';
        $data = $_POST['data'] ?? '';
        $periodo = $_POST['periodo'] ?? '';
        $motivo = $_POST['motivo'] ?? '';

        if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_nivel'] !== 'instrutor') {
            throw new Exception("Sessão expirada. Faça login novamente.");
        }

        if (empty($nome) || empty($telefone) || empty($data) || empty($periodo) || empty($motivo)) {
            throw new Exception("Todos os campos são obrigatórios.");
        }

        // Validar se não é sábado ou domingo
        $dia_semana = date('w', strtotime($data));
        if ($dia_semana == 0 || $dia_semana == 6) {
            throw new Exception("Não é possível fazer reservas para sábados ou domingos.");
        }

        require_once 'conexao.php';

        // Definir horários com base no período
        switch ($periodo) {
            case 'manha':
                $inicio = '08:00';
                $fim = '12:00';
                break;
            case 'tarde':
                $inicio = '13:00';
                $fim = '17:00';
                break;
            case 'noite':
                $inicio = '18:00';
                $fim = '22:00';
                break;
            default:
                throw new Exception("Período inválido.");
        }

        // Calcular horário de início e fim
        $datetime_inicio = $data . ' ' . $inicio;
        $datetime_fim = $data . ' ' . $fim;

        // Verificar se já existe reserva para o período
        $sql_verificar = "SELECT id FROM reservas_sala 
                         WHERE inicio < ? AND fim > ?
                         AND status = 'aprovado'
                         AND DATE(inicio) = ?";
        $stmt = $conn->prepare($sql_verificar);
        $stmt->bind_param("sss", $datetime_fim, $datetime_inicio, $data);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            throw new Exception("Já existe uma reserva para este período.");
        }

        // Inserir a reserva com status pendente
        $sql = "INSERT INTO reservas_sala (usuario_id, inicio, fim, motivo, status, periodo) 
                VALUES (?, ?, ?, ?, 'pendente', ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("issss", $_SESSION['usuario_id'], $datetime_inicio, $datetime_fim, $motivo, $periodo);
        
        if ($stmt->execute()) {
            echo json_encode([
                'success' => true,
                'message' => 'Reserva enviada para aprovação da COPED!',
                'redirect' => 'instrutor.php'
            ]);
        } else {
            // Capturar o erro específico do MySQL
            $error_message = $stmt->error;
            // Se for um erro de trigger (código 45000), pegar a mensagem personalizada
            if ($conn->errno == 1644) { // 1644 é o código para SQLSTATE '45000'
                $error_message = $stmt->error;
            }
            throw new Exception($error_message);
        }

    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
    exit;
}
?>