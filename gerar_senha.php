<?php
// Configuração de exibição de erros
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Conectar ao banco
$conn = new mysqli('localhost', 'root', '', 'teste');

// Primeiro, vamos limpar as tabelas e recriar os usuários
$conn->query("DELETE FROM professores");
$conn->query("DELETE FROM coped_usuarios");

// Senhas em texto puro
$senha_instrutor = 'instrutor123';
$senha_coped = 'coped123';

// Gerar hashes
$hash_instrutor = password_hash($senha_instrutor, PASSWORD_DEFAULT);
$hash_coped = password_hash($senha_coped, PASSWORD_DEFAULT);

try {
    // Inserir instrutor com a nova senha
    $sql = "INSERT INTO professores (nome, email, senha) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception("Erro ao preparar query instrutor: " . $conn->error);
    }
    $nome_instrutor = "Instrutor Lab";
    $email_instrutor = "instrutor@lab.com";
    $stmt->bind_param("sss", $nome_instrutor, $email_instrutor, $hash_instrutor);
    if (!$stmt->execute()) {
        throw new Exception("Erro ao inserir instrutor: " . $stmt->error);
    }
    
    // Inserir COPED com a nova senha
    $sql = "INSERT INTO coped_usuarios (nome, email, senha) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception("Erro ao preparar query COPED: " . $conn->error);
    }
    $nome_coped = "Administrador COPED";
    $email_coped = "admin@coped.com";
    $stmt->bind_param("sss", $nome_coped, $email_coped, $hash_coped);
    if (!$stmt->execute()) {
        throw new Exception("Erro ao inserir COPED: " . $stmt->error);
    }

    // Verificar se as senhas foram salvas corretamente
    $sql = "SELECT email, senha FROM professores WHERE email = 'instrutor@lab.com'";
    $result = $conn->query($sql);
    $instrutor = $result->fetch_assoc();
    
    $sql = "SELECT email, senha FROM coped_usuarios WHERE email = 'admin@coped.com'";
    $result = $conn->query($sql);
    $coped = $result->fetch_assoc();

    echo "<pre>";
    echo "Usuários criados com sucesso!\n\n";
    echo "Instrutor:\n";
    echo "Email: instrutor@lab.com\n";
    echo "Senha: instrutor123\n";
    echo "Hash: " . $instrutor['senha'] . "\n\n";
    echo "COPED:\n";
    echo "Email: admin@coped.com\n";
    echo "Senha: coped123\n";
    echo "Hash: " . $coped['senha'] . "\n";
    echo "</pre>";

} catch (Exception $e) {
    echo "Erro: " . $e->getMessage();
}
?> 