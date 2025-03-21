<?php
session_start();
require_once 'conexao.php';

$mensagem = '';
$tipo = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Validar e limpar inputs
        $nome = limpar_input($_POST['nome'] ?? '');
        $email = limpar_input($_POST['email'] ?? '');
        $telefone = limpar_input($_POST['telefone'] ?? '');
        $cpf = limpar_input($_POST['cpf'] ?? '');
        $assunto = limpar_input($_POST['assunto'] ?? '');
        $mensagem_texto = limpar_input($_POST['mensagem'] ?? '');

        // Validar campos obrigatórios
        if (empty($nome) || empty($email) || empty($telefone) || empty($cpf) || empty($assunto) || empty($mensagem_texto)) {
            throw new Exception("Todos os campos são obrigatórios.");
        }

        // Validar formato do telefone
        if (!preg_match("/^\(\d{2}\)\s\d{5}-\d{4}$/", $telefone)) {
            throw new Exception("Telefone inválido. Use o formato: (00) 00000-0000");
        }

        // Validar formato do CPF
        if (!preg_match("/^\d{3}\.\d{3}\.\d{3}-\d{2}$/", $cpf)) {
            throw new Exception("CPF inválido. Use o formato: 000.000.000-00");
        }

        // Primeiro, vamos dropar a tabela existente para recriar com a estrutura correta
        $conn->query("DROP TABLE IF EXISTS mensagens");

        // Criar tabela com a estrutura atualizada
        $sql = "CREATE TABLE mensagens (
            id INT AUTO_INCREMENT PRIMARY KEY,
            nome VARCHAR(255) NOT NULL,
            email VARCHAR(255) NOT NULL,
            telefone VARCHAR(20) NOT NULL,
            cpf VARCHAR(14) NOT NULL,
            assunto VARCHAR(255) NOT NULL,
            mensagem TEXT NOT NULL,
            data_envio TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            respondido BOOLEAN DEFAULT FALSE,
            resposta TEXT,
            data_resposta TIMESTAMP NULL
        )";
        
        if (!$conn->query($sql)) {
            throw new Exception("Erro ao criar tabela: " . $conn->error);
        }

        // Inserir mensagem
        $stmt = $conn->prepare("INSERT INTO mensagens (nome, email, telefone, cpf, assunto, mensagem) VALUES (?, ?, ?, ?, ?, ?)");
        if (!$stmt) {
            throw new Exception("Erro ao preparar consulta: " . $conn->error);
        }

        $stmt->bind_param("ssssss", $nome, $email, $telefone, $cpf, $assunto, $mensagem_texto);
        
        if (!$stmt->execute()) {
            throw new Exception("Erro ao enviar mensagem: " . $stmt->error);
        }

        // Enviar email
        $para = "seu-email@exemplo.com"; // Substitua pelo seu email
        $headers = "From: $email\r\n";
        $headers .= "Reply-To: $email\r\n";
        $headers .= "X-Mailer: PHP/" . phpversion();

        $corpo_email = "Nova mensagem de contato:\n\n";
        $corpo_email .= "Nome: $nome\n";
        $corpo_email .= "Email: $email\n";
        $corpo_email .= "Telefone: $telefone\n";
        $corpo_email .= "CPF: $cpf\n";
        $corpo_email .= "Assunto: $assunto\n\n";
        $corpo_email .= "Mensagem:\n$mensagem_texto";

        mail($para, "Nova mensagem de contato: $assunto", $corpo_email, $headers);

        $mensagem = "Mensagem enviada com sucesso!";
        $tipo = "success";
        
        // Limpar o formulário
        $_POST = array();
        
    } catch (Exception $e) {
        $mensagem = $e->getMessage();
        $tipo = "danger";
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contato - S.A.S.E</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root {
            --primary: #2563eb;
            --primary-dark: #1d4ed8;
            --secondary: #475569;
            --background: #f1f5f9;
            --surface: #ffffff;
            --text: #1e293b;
            --error: #ef4444;
            --success: #22c55e;
        }

        body {
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            background-color: var(--background);
            min-height: 100vh;
            margin: 0;
            padding: 0rem 0;
        }

        .navbar {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            padding: 1rem 0;
            margin-bottom: 2rem;
        }

        .navbar-brand {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .navbar-brand img {
            height: 35px;
        }

        .contact-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 0 1rem;
        }

        .contact-header {
            text-align: center;
            margin-bottom: 3rem;
        }

        .contact-header h1 {
            color: var(--text);
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
        }

        .contact-header p {
            color: var(--secondary);
            font-size: 1.1rem;
            max-width: 600px;
            margin: 0 auto;
        }

        .contact-form {
            background: var(--surface);
            padding: 3rem;
            border-radius: 24px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            position: relative;
            overflow: hidden;
        }

        .contact-form::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 6px;
            background: linear-gradient(90deg, var(--primary), var(--primary-dark));
        }

        .form-group {
            margin-bottom: 1.5rem;
            position: relative;
        }

        .form-group label {
            display: block;
            color: var(--text);
            font-weight: 500;
            margin-bottom: 0.5rem;
        }

        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 0.875rem 1rem;
            padding-left: 2.5rem;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: #f8fafc;
        }

        .form-group textarea {
            min-height: 120px;
            resize: vertical;
        }

        .form-group input:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: var(--primary);
            background: white;
            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.1);
        }

        .form-group i {
            position: absolute;
            left: 1rem;
            top: 2.5rem;
            color: var(--secondary);
            font-size: 1.2rem;
        }

        .btn-submit {
            width: 100%;
            padding: 1rem;
            border: none;
            border-radius: 12px;
            background: var(--primary);
            color: white;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .btn-submit:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
        }

        .btn-submit:active {
            transform: translateY(0);
        }

        .alert {
            border-radius: 12px;
            padding: 1rem 1.5rem;
            margin-bottom: 2rem;
            border: none;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .alert-success {
            background: #f0fdf4;
            color: #166534;
            border-left: 4px solid var(--success);
        }

        .alert-danger {
            background: #fef2f2;
            color: #991b1b;
            border-left: 4px solid var(--error);
        }

        @media (max-width: 768px) {
            .contact-form {
                padding: 2rem;
            }

            .contact-header h1 {
                font-size: 2rem;
            }
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <img src="assets/img/logo.png" alt="S.A.S.E Logo">
                S.A.S.E
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">
                            <i class="bi bi-house"></i>
                            Início
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="login.php">
                            <i class="bi bi-box-arrow-in-right"></i>
                            Entrar
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="contact-container">
        <div class="contact-header">
            <h1>Entre em Contato</h1>
            <p>Tem alguma dúvida ou sugestão? Preencha o formulário abaixo e entraremos em contato o mais breve possível.</p>
        </div>

        <?php if (!empty($mensagem)): ?>
            <div class="alert alert-<?php echo $tipo; ?>" role="alert">
                <i class="bi bi-<?php echo $tipo === 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
                <?php echo $mensagem; ?>
            </div>
        <?php endif; ?>

        <div class="contact-form">
            <form method="POST" action="">
                <div class="form-group">
                    <label for="nome">Nome Completo</label>
                    <i class="bi bi-person"></i>
                    <input type="text" id="nome" name="nome" required>
                </div>

                <div class="form-group">
                    <label for="email">Email</label>
                    <i class="bi bi-envelope"></i>
                    <input type="email" id="email" name="email" required>
                </div>

                <div class="form-group">
                    <label for="telefone">Telefone</label>
                    <i class="bi bi-phone"></i>
                    <input type="tel" id="telefone" name="telefone" placeholder="(00) 00000-0000" required>
                </div>

                <div class="form-group">
                    <label for="cpf">CPF</label>
                    <i class="bi bi-card-text"></i>
                    <input type="text" id="cpf" name="cpf" placeholder="000.000.000-00" required>
                </div>

                <div class="form-group">
                    <label for="assunto">Assunto</label>
                    <i class="bi bi-chat"></i>
                    <input type="text" id="assunto" name="assunto" required>
                </div>

                <div class="form-group">
                    <label for="mensagem">Mensagem</label>
                    <i class="bi bi-pencil"></i>
                    <textarea id="mensagem" name="mensagem" required></textarea>
                </div>

                <button type="submit" class="btn-submit">
                    <i class="bi bi-send"></i>
                    Enviar Mensagem
                </button>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#telefone').mask('(00) 00000-0000');
            $('#cpf').mask('000.000.000-00');
        });
    </script>
</body>
</html> 