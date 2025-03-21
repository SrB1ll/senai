<?php
header('Content-Type: application/json');
session_start();

// Verificar autenticação
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_nivel'] !== 'admin') {
    echo json_encode([
        'success' => false,
        'message' => 'Acesso não autorizado'
    ]);
    exit;
}

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

    // Excluir a reserva
    $sql_delete = "DELETE FROM reservas WHERE id = ?";
    $stmt = $conn->prepare($sql_delete);
    $stmt->bind_param("i", $id);
    
    if (!$stmt->execute()) {
        throw new Exception("Erro ao excluir reserva");
    }

    // Confirmar transação
    $conn->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Reserva excluída com sucesso!'
    ]);

} catch (Exception $e) {
    // Reverter transação em caso de erro
    if (isset($conn)) {
        $conn->rollback();
    }
    
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

if (isset($conn)) {
    $conn->close();
}
?> 