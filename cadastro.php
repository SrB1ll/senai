<?php
header('Content-Type: application/json');
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $nome = $_POST['nome'] ?? '';
        $matricula = $_POST['matricula'] ?? '';
        $curso = $_POST['curso'] ?? '';
        $telefone = $_POST['telefone'] ?? '';
        $data = $_POST['data_pesquisa'] ?? '';
        $inicio = $_POST['inicio'] ?? '';

        // Validações básicas
        if (empty($nome) || empty($matricula) || empty($curso) || empty($telefone) || empty($data) || empty($inicio)) {
            throw new Exception("Todos os campos são obrigatórios.");
        }

        require_once 'conexao.php';

        // Verificar se o usuário já se cadastrou duas vezes no mesmo dia
        $sql_verificar = "SELECT COUNT(*) as total FROM reservas WHERE matricula = ? AND DATE(inicio) = ?";
        $stmt = $conn->prepare($sql_verificar);
        if (!$stmt) {
            throw new Exception("Erro ao preparar consulta: " . $conn->error);
        }
        $stmt->bind_param("ss", $matricula, $data);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();

        if ($row['total'] >= 2) {
            throw new Exception("Você já se cadastrou duas vezes para esta data.");
        }

        // Calcular horário de início e fim
        $datetime_inicio = $data . ' ' . $inicio;
        $datetime_fim = date('Y-m-d H:i:s', strtotime($datetime_inicio . ' +1 hour'));

        // Verificar disponibilidade do horário
        $sql_verificar_horario = "SELECT id FROM reservas 
                                 WHERE DATE(inicio) = ? 
                                 AND ((inicio <= ? AND fim > ?) 
                                 OR (inicio < ? AND fim >= ?))";
        
        $stmt = $conn->prepare($sql_verificar_horario);
        $stmt->bind_param("sssss", $data, $datetime_inicio, $datetime_inicio, $datetime_fim, $datetime_fim);
        $stmt->execute();
        $result = $stmt->get_result();

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

        // Selecionar computador disponível
        $sql_computador = "SELECT computador_num FROM computadores 
                          WHERE status = 'livre' 
                          ORDER BY RAND() LIMIT 1";
        
        $result_computador = $conn->query($sql_computador);
        
        if ($result_computador && $result_computador->num_rows > 0) {
            $row = $result_computador->fetch_assoc();
            $computador_num = $row['computador_num'];

            // Inserir reserva
            $sql_inserir = "INSERT INTO reservas (nome, matricula, curso, telefone, computador_num, inicio, fim, status) 
                           VALUES (?, ?, ?, ?, ?, ?, ?, 'pendente')";
            
            $stmt = $conn->prepare($sql_inserir);
            if (!$stmt) {
                throw new Exception("Erro ao preparar inserção: " . $conn->error);
            }

            $stmt->bind_param("sssssss", 
                $nome, 
                $matricula, 
                $curso, 
                $telefone, 
                $computador_num, 
                $datetime_inicio, 
                $datetime_fim
            );

            if ($stmt->execute()) {
                echo json_encode([
                    'success' => true,
                    'redirect' => "confirmacao.php?nome=".urlencode($nome)
                                ."&matricula=".urlencode($matricula)
                                ."&curso=".urlencode($curso)
                                ."&telefone=".urlencode($telefone)
                                ."&data=".urlencode($data)
                                ."&inicio=".urlencode($inicio)
                                ."&computador=".urlencode($computador_num)
                ]);
            } else {
                throw new Exception("Erro ao salvar reserva: " . $stmt->error);
            }
        } else {
            throw new Exception("Não há computadores disponíveis no momento.");
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
