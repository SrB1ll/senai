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
        throw new Exception("ID da mensagem não fornecido");
    }

    // Iniciar transação
    $conn->begin_transaction();

    // Buscar informações da mensagem
    $sql_mensagem = "SELECT id FROM mensagens WHERE id = ?";
    $stmt = $conn->prepare($sql_mensagem);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $mensagem = $result->fetch_assoc();

    if (!$mensagem) {
        throw new Exception("Mensagem não encontrada");
    }

    // Excluir a mensagem
    $sql_delete = "DELETE FROM mensagens WHERE id = ?";
    $stmt = $conn->prepare($sql_delete);
    $stmt->bind_param("i", $id);
    
    if (!$stmt->execute()) {
        throw new Exception("Erro ao excluir mensagem");
    }

    // Confirmar transação
    $conn->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Mensagem excluída com sucesso!'
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