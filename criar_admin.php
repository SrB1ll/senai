<?php
require_once 'conexao.php';

// Dados do usuário admin
$nome = "Administrador";
$email = "admin@sase.com";
$senha = "admin123";
$nivel = "admin";

// Gerar hash da senha
$senha_hash = password_hash($senha, PASSWORD_DEFAULT);

// Verificar se o usuário já existe
$stmt = $conn->prepare("SELECT id FROM usuarios WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    // Atualizar usuário existente
    $stmt = $conn->prepare("UPDATE usuarios SET senha = ?, nivel = ? WHERE email = ?");
    $stmt->bind_param("sss", $senha_hash, $nivel, $email);
} else {
    // Inserir novo usuário
    $stmt = $conn->prepare("INSERT INTO usuarios (nome, email, senha, nivel) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $nome, $email, $senha_hash, $nivel);
}

if ($stmt->execute()) {
    echo "Usuário admin criado/atualizado com sucesso!<br>";
    echo "Email: " . $email . "<br>";
    echo "Senha: " . $senha . "<br>";
} else {
    echo "Erro ao criar/atualizar usuário: " . $conn->error;
}

$stmt->close();
$conn->close();
?> 