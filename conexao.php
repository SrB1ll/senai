<?php
// Configurar timezone para Brasil/São Paulo
date_default_timezone_set('America/Sao_Paulo');

// Configurações do banco de dados
$host = 'localhost';
$usuario = 'root';
$senha = '';
$banco = 'teste';

// Criar conexão
try {
    $conn = new mysqli($host, $usuario, $senha, $banco);
    
    // Verificar conexão
    if ($conn->connect_error) {
        throw new Exception("Erro na conexão com o banco de dados: " . $conn->connect_error);
    }

    // Configurar charset
    $conn->set_charset("utf8mb4");

} catch (Exception $e) {
    die("Erro: " . $e->getMessage());
}

// Função para limpar inputs
function limpar_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}
?> 