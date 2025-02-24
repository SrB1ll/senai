<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Cria a estrutura de pastas correta
$pastas = [
    'assets',
    'assets/img',
    'assets/css',
];

foreach ($pastas as $pasta) {
    if (!file_exists($pasta)) {
        mkdir($pasta, 0777, true);
        echo "✅ Pasta {$pasta} criada<br>";
    } else {
        echo "✅ Pasta {$pasta} já existe<br>";
    }
}

// Move arquivos da pasta images para img se existirem
if (file_exists('assets/images')) {
    $arquivos = scandir('assets/images');
    foreach ($arquivos as $arquivo) {
        if ($arquivo != '.' && $arquivo != '..') {
            $origem = "assets/images/{$arquivo}";
            $destino = "assets/img/{$arquivo}";
            if (rename($origem, $destino)) {
                echo "✅ Arquivo {$arquivo} movido para assets/img/<br>";
            }
        }
    }
    
    // Remove a pasta images
    rmdir('assets/images');
    echo "✅ Pasta assets/images removida<br>";
}

echo "<br>Estrutura atual:<br>";
echo "<pre>";
function listarPasta($pasta, $nivel = 0) {
    $arquivos = scandir($pasta);
    foreach ($arquivos as $arquivo) {
        if ($arquivo != '.' && $arquivo != '..') {
            echo str_repeat("  ", $nivel) . "├── {$arquivo}<br>";
            if (is_dir($pasta . '/' . $arquivo)) {
                listarPasta($pasta . '/' . $arquivo, $nivel + 1);
            }
        }
    }
}
listarPasta('assets');
echo "</pre>";
?> 