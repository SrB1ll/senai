<?php
header('Content-Type: application/json');
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $nome = $_POST['nome'] ?? '';
        $cpf = $_POST['cpf'] ?? '';
        $curso = $_POST['curso'] ?? '';
        $telefone = $_POST['telefone'] ?? '';
        $data = $_POST['data_pesquisa'] ?? '';
        $inicio = $_POST['inicio'] ?? '';

        // Validações básicas
        if (empty($nome) || empty($cpf) || empty($curso) || empty($telefone) || empty($data) || empty($inicio)) {
            throw new Exception("Todos os campos são obrigatórios.");
        }

        // Validar formato do CPF
        if (!preg_match("/^\d{3}\.\d{3}\.\d{3}-\d{2}$/", $cpf)) {
            throw new Exception("CPF inválido. Use o formato: 000.000.000-00");
        }

        // Validar se não é sábado ou domingo
        $dia_semana = date('w', strtotime($data));
        if ($dia_semana == 0 || $dia_semana == 6) {
            throw new Exception("Não é possível fazer reservas para sábados ou domingos.");
        }

        require_once 'conexao.php';

        // Verificar se o usuário já se cadastrou duas vezes no mesmo dia
        $sql_verificar = "SELECT COUNT(*) as total FROM reservas WHERE cpf = ? AND DATE(inicio) = ?";
        $stmt = $conn->prepare($sql_verificar);
        if (!$stmt) {
            throw new Exception("Erro ao preparar consulta: " . $conn->error);
        }
        $stmt->bind_param("ss", $cpf, $data);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();

        if ($row['total'] >= 2) {
            throw new Exception("Você já se cadastrou duas vezes para esta data.");
        }

        // Calcular horário de início e fim
        $datetime_inicio = $data . ' ' . $inicio;
        $datetime_fim = date('Y-m-d H:i:s', strtotime($datetime_inicio . ' +1 hour'));

        // Verificar se existe reserva de sala no mesmo horário
        $sql_verificar_sala = "SELECT id FROM reservas_sala 
                              WHERE DATE(inicio) = ? 
                              AND ((inicio <= ? AND fim > ?) 
                              OR (inicio < ? AND fim >= ?))
                              AND status = 'aprovado'";
        
        $stmt = $conn->prepare($sql_verificar_sala);
        $stmt->bind_param("sssss", $data, $datetime_inicio, $datetime_inicio, $datetime_fim, $datetime_fim);
        $stmt->execute();
        $result_sala = $stmt->get_result();
        
        if ($result_sala->num_rows > 0) {
            throw new Exception("Este horário está reservado para uso da sala pelo instrutor.");
        }

        // Encontrar um computador disponível
        $sql_computador = "SELECT c.computador_num 
                          FROM computadores c
                          WHERE NOT EXISTS (
                              SELECT 1 FROM reservas r
                              WHERE r.computador_num = c.computador_num
                              AND r.status != 'recusado'
                              AND ((r.inicio <= ? AND r.fim > ?)
                              OR (r.inicio < ? AND r.fim >= ?))
                          )
                          ORDER BY RAND() LIMIT 1";
        
        $stmt = $conn->prepare($sql_computador);
        $stmt->bind_param("ssss", $datetime_inicio, $datetime_inicio, $datetime_fim, $datetime_fim);
        $stmt->execute();
        $result_computador = $stmt->get_result();
        
        if ($result_computador && $result_computador->num_rows > 0) {
            $row = $result_computador->fetch_assoc();
            $computador_num = $row['computador_num'];

            // Inserir reserva
            $sql_inserir = "INSERT INTO reservas (nome, cpf, curso, telefone, computador_num, inicio, fim, status) 
                           VALUES (?, ?, ?, ?, ?, ?, ?, 'pendente')";
            
            $stmt = $conn->prepare($sql_inserir);
            if (!$stmt) {
                throw new Exception("Erro ao preparar inserção: " . $conn->error);
            }

            $stmt->bind_param("sssssss", 
                $nome, 
                $cpf, 
                $curso, 
                $telefone, 
                $computador_num, 
                $datetime_inicio, 
                $datetime_fim
            );

            if ($stmt->execute()) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Reserva realizada com sucesso! Aguarde a aprovação.'
                ]);
            } else {
                throw new Exception("Erro ao salvar reserva: " . $stmt->error);
            }
        } else {
            throw new Exception("Não há computadores disponíveis neste horário.");
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
