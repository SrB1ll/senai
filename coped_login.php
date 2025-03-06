<?php
session_start();
require_once 'conexao.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $email = trim($_POST['email']);
        $senha = trim($_POST['senha']);

        // Debug
        error_log("Tentativa de login COPED - Email: " . $email);

        if (empty($email) || empty($senha)) {
            throw new Exception("Preencha todos os campos");
        }

        $sql = "SELECT * FROM coped_usuarios WHERE email = ?";
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            throw new Exception("Erro ao preparar consulta: " . $conn->error);
        }

        $stmt->bind_param("s", $email);
        if (!$stmt->execute()) {
            throw new Exception("Erro ao executar consulta: " . $stmt->error);
        }

        $result = $stmt->get_result();
        
        if ($result && $result->num_rows === 1) {
            $usuario = $result->fetch_assoc();
            
            // Debug
            error_log("Senha fornecida: " . $senha);
            error_log("Hash no banco: " . $usuario['senha']);
            
            if (password_verify($senha, $usuario['senha'])) {
                $_SESSION['coped_id'] = $usuario['id'];
                $_SESSION['coped_nome'] = $usuario['nome'];
                
                echo json_encode([
                    'success' => true,
                    'redirect' => 'coped.php'
                ]);
                exit;
            }
        }
        throw new Exception("Email ou senha incorretos");

    } catch (Exception $e) {
        error_log("Erro no login COPED: " . $e->getMessage());
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login COPED - Sistema de Reservas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="assets/css/variables.css" rel="stylesheet">
    <link href="assets/css/components.css" rel="stylesheet">
    <link href="assets/css/login.css" rel="stylesheet">
</head>
<body>
    <div class="login-container coped">
        <!-- Banner Lado Esquerdo -->
        <div class="login-banner">
            <div class="banner-content">
                <h1>Sistema de Reservas de Sala de Estudos</h1>
                <p>Gerencie as reservas e computadores do laboratório de forma eficiente e organizada.</p>
            </div>
        </div>

        <!-- Formulário Lado Direito -->
        <div class="login-form-container">
            <div class="login-form-content">
                <div class="login-header">
                    <i class="bi bi-gear-fill login-icon"></i>
                    <h2>COPED</h2>
                    <p>Acesso administrativo</p>
                </div>

                <form id="loginForm" method="POST" class="needs-validation" novalidate>
                    <div class="form-group">
                        <label for="email" class="form-label">Email</label>
                        <div class="input-group">
                            <i class="bi bi-envelope input-group-text"></i>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="senha" class="form-label">Senha</label>
                        <div class="input-group">
                            <i class="bi bi-key input-group-text"></i>
                            <input type="password" class="form-control" id="senha" name="senha" required>
                        </div>
                    </div>

                    <button type="submit" class="btn-login" id="btnLogin">
                        <span class="spinner-border spinner-border-sm d-none" role="status"></span>
                        <span class="btn-text">Entrar</span>
                    </button>

                    <a href="index.php" class="back-link">
                        <i class="bi bi-arrow-left"></i>
                        Voltar ao início
                    </a>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/login.js"></script>
</body>
</html> 