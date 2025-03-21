<?php
session_start();

// Verificar se o usuário está logado e é administrador
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_nivel'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['erro' => 'Acesso negado']);
    exit();
}

require_once 'conexao.php';

if (!isset($_GET['id'])) {
    http_response_code(400);
    echo json_encode(['erro' => 'ID não fornecido']);
    exit();
}

$id = (int)$_GET['id'];

// Buscar dados do usuário
$stmt = $conn->prepare("SELECT id, nome, email, nivel FROM usuarios WHERE id = ? LIMIT 1");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$usuario = $result->fetch_assoc();

if (!$usuario) {
    http_response_code(404);
    echo json_encode(['erro' => 'Usuário não encontrado']);
    exit();
}

// Retornar dados em JSON
header('Content-Type: application/json');
echo json_encode($usuario); 