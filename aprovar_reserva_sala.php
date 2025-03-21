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
    $sql_reserva = "SELECT * FROM reservas_sala WHERE id = ?";
    $stmt = $conn->prepare($sql_reserva);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $reserva = $result->fetch_assoc();

    if (!$reserva) {
        throw new Exception("Reserva não encontrada");
    }

    // Verificar se já existe outra reserva aprovada para o mesmo horário
    $sql_verificar = "SELECT id FROM reservas_sala 
                     WHERE id != ? 
                     AND ((inicio <= ? AND fim > ?) OR (inicio < ? AND fim >= ?))
                     AND status = 'aprovado'";
    
    $stmt = $conn->prepare($sql_verificar);
    $stmt->bind_param("issss", 
        $id, 
        $reserva['fim'], 
        $reserva['inicio'], 
        $reserva['fim'], 
        $reserva['inicio']
    );
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        throw new Exception("Já existe uma reserva aprovada para este horário");
    }

    // Atualizar status da reserva
    $sql_update = "UPDATE reservas_sala SET status = 'aprovado' WHERE id = ?";
    $stmt = $conn->prepare($sql_update);
    $stmt->bind_param("i", $id);
    
    if (!$stmt->execute()) {
        throw new Exception("Erro ao aprovar reserva");
    }

    // Confirmar transação
    $conn->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Reserva aprovada com sucesso!'
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