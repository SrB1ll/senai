<?php
session_start();
require_once 'conexao.php';

// Verificar se o usuário está logado e é admin
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_nivel'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Acesso não autorizado']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? null;
    $status = $_POST['status'] ?? null;
    
    if (!$id || !$status || !in_array($status, ['disponivel', 'manutencao'])) {
        echo json_encode(['success' => false, 'message' => 'Parâmetros inválidos']);
        exit;
    }
    
    $stmt = $conn->prepare("UPDATE computadores SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $status, $id);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Erro ao atualizar status']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Método não permitido']);
}
?> 