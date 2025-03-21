<?php
header('Content-Type: application/json');
require_once 'conexao.php';

$data = json_decode(file_get_contents('php://input'), true);
$id = $data['id'] ?? 0;

try {
    if (empty($id)) {
        throw new Exception("ID da reserva não fornecido");
    }

    // Iniciar transação
    $conn->begin_transaction();

    // Buscar informações da reserva
    $sql_reserva = "SELECT computador_num FROM reservas WHERE id = ?";
    $stmt = $conn->prepare($sql_reserva);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $reserva = $result->fetch_assoc();

    if (!$reserva) {
        throw new Exception("Reserva não encontrada");
    }

    // Atualizar status da reserva
    $sql_update_reserva = "UPDATE reservas SET status = 'aprovado' WHERE id = ?";
    $stmt = $conn->prepare($sql_update_reserva);
    $stmt->bind_param("i", $id);
    
    if (!$stmt->execute()) {
        throw new Exception("Erro ao aprovar reserva");
    }

    // Atualizar status do computador
    $sql_update_pc = "UPDATE computadores SET status = 'ocupado' WHERE computador_num = ?";
    $stmt = $conn->prepare($sql_update_pc);
    $stmt->bind_param("i", $reserva['computador_num']);
    
    if (!$stmt->execute()) {
        throw new Exception("Erro ao atualizar status do computador");
    }

    // Confirmar transação
    $conn->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Reserva aprovada com sucesso!'
    ]);

} catch (Exception $e) {
    // Reverter transação em caso de erro
    $conn->rollback();
    
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

$conn->close();
?> 