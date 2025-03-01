<?php
session_start();

// Verificação de autenticação
if (!isset($_SESSION['coped_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Acesso não autorizado']);
    exit;
}

require_once 'conexao.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $mensagem_id = isset($_POST['id']) ? intval($_POST['id']) : 0;
        
        if ($mensagem_id <= 0) {
            throw new Exception('ID de mensagem inválido');
        }

        // Excluir a mensagem
        $sql = "DELETE FROM mensagens WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $mensagem_id);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Mensagem excluída com sucesso']);
        } else {
            throw new Exception('Erro ao excluir mensagem');
        }

    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Método não permitido']);
}
?> 