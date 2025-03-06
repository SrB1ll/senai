<?php
header('Content-Type: application/json');
session_start();

if (!isset($_SESSION['coped_id'])) {
    echo json_encode(['success' => false, 'message' => 'Acesso não autorizado']);
    exit;
}

if (isset($_GET['id'])) {
    require_once 'conexao.php';
    
    try {
        $id = intval($_GET['id']);
        
        // Buscar a mensagem
        $sql = "SELECT * FROM mensagens WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $mensagem = $result->fetch_assoc();
            
            // Atualizar status para 'lida'
            $sql_update = "UPDATE mensagens SET status = 'lida' WHERE id = ?";
            $stmt = $conn->prepare($sql_update);
            $stmt->bind_param("i", $id);
            $stmt->execute();
            
            echo json_encode([
                'success' => true,
                'mensagem' => [
                    'nome' => $mensagem['nome'],
                    'email' => $mensagem['email'],
                    'assunto' => $mensagem['assunto'],
                    'mensagem' => nl2br($mensagem['mensagem']),
                    'data_formatada' => date('d/m/Y H:i', strtotime($mensagem['data_envio']))
                ]
            ]);
        } else {
            throw new Exception("Mensagem não encontrada");
        }
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'ID não fornecido']);
}
?> 