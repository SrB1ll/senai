<?php
header('Content-Type: application/json');
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $nome = $_POST['nome'] ?? '';
        // Debug - verificar nome do professor
        error_log("Nome do professor ao reservar: " . $nome);

        $telefone = $_POST['telefone'] ?? '';
        $data = $_POST['data'] ?? '';
        $inicio = $_POST['inicio'] ?? '';
        $fim = $_POST['fim'] ?? '';
        $motivo = $_POST['motivo'] ?? '';

        if (!isset($_SESSION['professor_id'])) {
            throw new Exception("Sessão expirada. Faça login novamente.");
        }

        if (empty($nome) || empty($telefone) || empty($data) || empty($inicio) || empty($fim) || empty($motivo)) {
            throw new Exception("Todos os campos são obrigatórios.");
        }

        // Validar se não é sábado ou domingo
        $dia_semana = date('w', strtotime($data));
        if ($dia_semana == 0 || $dia_semana == 6) {
            throw new Exception("Não é possível fazer reservas para sábados ou domingos.");
        }

        require_once 'conexao.php';

        // Calcular horário de início e fim
        $datetime_inicio = $data . ' ' . $inicio;
        $datetime_fim = $data . ' ' . $fim;

        // Verificar se já existe reserva para o período
        $sql_verificar = "SELECT id FROM reservas_sala 
                         WHERE inicio < ? AND fim > ?
                         AND status = 'aprovado'";
        $stmt = $conn->prepare($sql_verificar);
        $stmt->bind_param("ss", $datetime_fim, $datetime_inicio);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            throw new Exception("Já existe uma reserva para este horário.");
        }

        // Inserir a reserva com status pendente
        $sql = "INSERT INTO reservas_sala (instrutor_nome, professor_id, telefone, inicio, fim, motivo, status) 
                VALUES (?, ?, ?, ?, ?, ?, 'pendente')";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sissss", $nome, $_SESSION['professor_id'], $telefone, $datetime_inicio, $datetime_fim, $motivo);
        
        if ($stmt->execute()) {
            echo json_encode([
                'success' => true,
                'message' => 'Reserva enviada para aprovação da COPED!',
                'redirect' => 'instrutor.php'
            ]);
        } else {
            throw new Exception("Erro ao reservar sala de estudos.");
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