<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

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

// Resetar e criar a tabela de computadores
DROP TABLE IF EXISTS computadores;
CREATE TABLE computadores (
    id INT AUTO_INCREMENT PRIMARY KEY,
    computador_num INT NOT NULL,
    status ENUM('livre', 'ocupado') DEFAULT 'livre'
);

// Inserir 10 computadores como exemplo
INSERT INTO computadores (computador_num, status) VALUES 
(1, 'livre'), (2, 'livre'), (3, 'livre'), (4, 'livre'), (5, 'livre'),
(6, 'livre'), (7, 'livre'), (8, 'livre'), (9, 'livre'), (10, 'livre');
?> 