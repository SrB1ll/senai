<?php
header('Content-Type: application/json');
require_once 'conexao.php';

$data = json_decode(file_get_contents('php://input'), true);
$id = $data['id'] ?? 0;

try {
    if (empty($id)) {
        throw new Exception("ID da reserva não fornecido");
    }

    // Buscar informações da reserva
    $sql = "SELECT computador_num FROM reservas WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $reserva = $result->fetch_assoc();

    if (!$reserva) {
        throw new Exception("Reserva não encontrada");
    }

    // Atualizar status do computador para livre
    $sql_update_pc = "UPDATE computadores SET status = 'livre' WHERE computador_num = ?";
    $stmt = $conn->prepare($sql_update_pc);
    $stmt->bind_param("i", $reserva['computador_num']);
    $stmt->execute();

    // Deletar a reserva
    $sql_delete = "DELETE FROM reservas WHERE id = ?";
    $stmt = $conn->prepare($sql_delete);
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        echo json_encode([
            'success' => true,
            'message' => 'Reserva recusada com sucesso!'
        ]);
    } else {
        throw new Exception("Erro ao recusar reserva");
    }

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

$conn->close();
?> 