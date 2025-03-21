<?php
// Configuração de exibição de erros
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Conectar ao banco
$conn = new mysqli('localhost', 'root', '');

// Ler o conteúdo do arquivo SQL
$sql = file_get_contents('reset_banco.sql');

// Executar os comandos SQL
if ($conn->multi_query($sql)) {
    do {
        // Armazenar o primeiro resultado
        if ($result = $conn->store_result()) {
            $result->free();
        }
        // Preparar próxima query
    } while ($conn->more_results() && $conn->next_result());
    
    echo "Banco de dados resetado com sucesso!";
} else {
    echo "Erro ao executar script SQL: " . $conn->error;
}

$conn->close();
?> 