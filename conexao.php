<?php
// Desativa a exibição de erros
error_reporting(0);
ini_set('display_errors', 0);

// Configurar timezone para Brasil/São Paulo
date_default_timezone_set('America/Sao_Paulo');

// Configurar tempo da sessão para 8 horas
ini_set('session.gc_maxlifetime', 28800); // 8 horas em segundos
ini_set('session.cookie_lifetime', 28800);

// Função para limpar inputs
function limpar_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Configurações do banco de dados
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "sase";

// Cria a conexão
try {
    $conn = new mysqli($servername, $username, $password, $dbname);
    
    // Configura o charset para UTF-8
    $conn->set_charset("utf8");
    
    // Verifica a conexão
    if ($conn->connect_error) {
        throw new Exception("Erro de conexão com o banco de dados");
    }

} catch (Exception $e) {
    // Se houver erro, retorna JSON
    header('Content-Type: application/json');
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
    exit();
}
?>