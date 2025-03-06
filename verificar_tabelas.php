<?php
require_once 'conexao.php';

// Verifica se as tabelas existem
$tabelas = [
    'computadores',
    'reservas',
    'professores',
    'coped_usuarios'
];

foreach ($tabelas as $tabela) {
    $result = $conn->query("SHOW TABLES LIKE '$tabela'");
    if ($result->num_rows == 0) {
        echo "❌ Tabela {$tabela} não existe<br>";
    } else {
        echo "✅ Tabela {$tabela} existe<br>";
    }
}

// Mostra a estrutura das tabelas
foreach ($tabelas as $tabela) {
    $result = $conn->query("DESCRIBE $tabela");
    if ($result) {
        echo "<br><strong>Estrutura da tabela $tabela:</strong><br>";
        while ($row = $result->fetch_assoc()) {
            echo "{$row['Field']} - {$row['Type']}<br>";
        }
    }
}
?> 