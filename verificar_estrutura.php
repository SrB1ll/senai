<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'conexao.php';

// Estrutura esperada
$estrutura = [
    'assets' => [
        'css' => [
            'components.css',
            'variables.css',
            'login.css',
            'index.css',
            'instrutor.css',
            'contato.css'
        ],
        'js' => [
            'login.js',
            'main.js'
        ]
    ]
];

// Verifica e cria a estrutura
function verificarEstrutura($base, $estrutura) {
    foreach ($estrutura as $pasta => $conteudo) {
        $caminho = $base . '/' . $pasta;
        
        // Cria pasta se não existir
        if (!file_exists($caminho)) {
            mkdir($caminho, 0777, true);
            echo "✅ Pasta criada: {$caminho}<br>";
        }
        
        // Se for array, é uma pasta com conteúdo
        if (is_array($conteudo)) {
            verificarEstrutura($caminho, $conteudo);
        }
    }
}

// Executa a verificação
verificarEstrutura('.', $estrutura);

// Lista a estrutura atual
echo "<br>Estrutura atual do projeto:<br>";
function listarEstrutura($pasta, $nivel = 0) {
    $arquivos = scandir($pasta);
    foreach ($arquivos as $arquivo) {
        if ($arquivo != '.' && $arquivo != '..') {
            echo str_repeat("  ", $nivel) . "├── {$arquivo}<br>";
            if (is_dir($pasta . '/' . $arquivo)) {
                listarEstrutura($pasta . '/' . $arquivo, $nivel + 1);
            }
        }
    }
}
listarEstrutura('assets');

// Criar tabela de computadores se não existir
try {
    // Verificar se a tabela já existe
    $result = $conn->query("SHOW TABLES LIKE 'computadores'");
    if ($result->num_rows == 0) {
        // Criar a tabela computadores
        $sql = "CREATE TABLE computadores (
            id INT AUTO_INCREMENT PRIMARY KEY,
            computador_num INT NOT NULL,
            status ENUM('livre', 'ocupado') DEFAULT 'livre'
        )";
        
        if ($conn->query($sql)) {
            echo "<br>✅ Tabela 'computadores' criada com sucesso!<br>";
            
            // Inserir 10 computadores como exemplo
            $sql = "INSERT INTO computadores (computador_num, status) VALUES 
                   (1, 'livre'), (2, 'livre'), (3, 'livre'), (4, 'livre'), (5, 'livre'),
                   (6, 'livre'), (7, 'livre'), (8, 'livre'), (9, 'livre'), (10, 'livre')";
            
            if ($conn->query($sql)) {
                echo "✅ Computadores inseridos com sucesso!<br>";
            } else {
                throw new Exception("Erro ao inserir computadores: " . $conn->error);
            }
        } else {
            throw new Exception("Erro ao criar tabela: " . $conn->error);
        }
    } else {
        echo "<br>ℹ️ Tabela 'computadores' já existe.<br>";
    }
} catch (Exception $e) {
    echo "<br>❌ Erro: " . $e->getMessage() . "<br>";
}

$conn->close();
?> 