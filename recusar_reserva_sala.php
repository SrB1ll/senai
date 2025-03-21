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

    // Buscar informações da reserva para verificar se existe
    $sql_verificar = "SELECT id FROM reservas_sala WHERE id = ?";
    $stmt = $conn->prepare($sql_verificar);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        throw new Exception("Reserva não encontrada");
    }

    // Atualizar status da reserva para recusado
    $sql_update = "UPDATE reservas_sala SET status = 'recusado' WHERE id = ?";
    $stmt = $conn->prepare($sql_update);
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