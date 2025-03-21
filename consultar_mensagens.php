<?php
require_once 'conexao.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $nome = limpar_input($_POST['nome']);
        $cpf = limpar_input($_POST['cpf']);
        
        // Validar formato do CPF
        if (!preg_match("/^\d{3}\.\d{3}\.\d{3}-\d{2}$/", $cpf)) {
            throw new Exception("CPF inválido. Use o formato: 000.000.000-00");
        }
        
        $sql = "SELECT * FROM mensagens 
                WHERE nome = ? AND cpf = ?
                ORDER BY data_envio DESC";
                
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $nome, $cpf);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $mensagens = [];
        while ($row = $result->fetch_assoc()) {
            $mensagens[] = [
                'assunto' => $row['assunto'],
                'mensagem' => $row['mensagem'],
                'data_envio' => date('d/m/Y H:i', strtotime($row['data_envio'])),
                'resposta' => $row['resposta'],
                'data_resposta' => $row['data_resposta'] ? date('d/m/Y H:i', strtotime($row['data_resposta'])) : null
            ];
        }
        
        echo json_encode([
            'success' => true,
            'mensagens' => $mensagens
        ]);
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Método não permitido'
    ]);
}
?> 