<?php
session_start();
require_once 'conexao.php';

// Verificar autenticação
if (!isset($_SESSION['coped_id'])) {
    echo json_encode(['success' => false, 'message' => 'Não autorizado']);
    exit;
}

if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    
    // Buscar mensagem
    $sql = "SELECT * FROM mensagens WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($mensagem = $result->fetch_assoc()) {
        // Marcar como lida
        $sql_update = "UPDATE mensagens SET status = 'lida' WHERE id = ?";
        $stmt = $conn->prepare($sql_update);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        
        // Formatar data
        $mensagem['data_formatada'] = date('d/m/Y H:i', strtotime($mensagem['data_envio']));
        
        echo json_encode([
            'success' => true,
            'mensagem' => $mensagem
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Mensagem não encontrada'
        ]);
    }
}
?> 