<?php
session_start();
require_once 'conexao.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = limpar_input($_POST['email']);
    $senha = $_POST['senha'];
    
    $sql = "SELECT id, nome, email, senha, nivel FROM usuarios WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $usuario = $result->fetch_assoc();
        if (password_verify($senha, $usuario['senha'])) {
            // Destruir qualquer sessão existente antes de criar uma nova
            session_unset();
            session_destroy();
            session_start();
            
            $_SESSION['usuario_id'] = $usuario['id'];
            $_SESSION['usuario_nome'] = $usuario['nome'];
            $_SESSION['usuario_nivel'] = $usuario['nivel'];
            $_SESSION['login_time'] = time();
            $_SESSION['ultimo_acesso'] = time();
            
            // Verificar o nível do usuário e redirecionar adequadamente
            if ($usuario['nivel'] === 'admin') {
                $redirect = 'coped.php';
            } elseif ($usuario['nivel'] === 'instrutor') {
                $redirect = 'instrutor.php';
            } else {
                $redirect = 'aluno.php';
            }
            
            $response = [
                'success' => true,
                'redirect' => $redirect,
                'session_id' => session_id(),
                'session_data' => $_SESSION
            ];
        } else {
            $response = [
                'success' => false,
                'message' => 'Email ou senha incorretos.'
            ];
        }
    } else {
        $response = [
            'success' => false,
            'message' => 'Email ou senha incorretos.'
        ];
    }
    
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - S.A.S.E</title>
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
            padding: 0;
            display: flex;
            overflow-x: hidden;
        }

        .split-screen {
            display: flex;
            width: 100%;
            height: 100vh;
        }

        .left-side {
            flex: 1;
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            padding: 2rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
            position: relative;
            overflow: hidden;
            display: none;
        }

        .left-side::before {
            content: '';
            position: absolute;
            width: 150%;
            height: 150%;
            background: url('assets/img/pattern.svg') repeat;
            opacity: 0.1;
            animation: move 20s linear infinite;
        }

        @keyframes move {
            0% { transform: translateX(0) translateY(0); }
            100% { transform: translateX(-20%) translateY(-20%); }
        }

        .left-content {
            position: relative;
            z-index: 1;
            color: white;
            max-width: 500px;
            margin: 0 auto;
        }

        .left-content h1 {
            font-size: 3rem;
            font-weight: 700;
            margin-bottom: 1.5rem;
            line-height: 1.2;
        }

        .left-content p {
            font-size: 1.1rem;
            opacity: 0.9;
            line-height: 1.6;
        }

        .feature-list {
            margin-top: 2rem;
            padding: 0;
            list-style: none;
        }

        .feature-list li {
            display: flex;
            align-items: center;
            margin-bottom: 1rem;
            font-size: 1.1rem;
        }

        .feature-list li i {
            margin-right: 1rem;
            font-size: 1.5rem;
        }

        .right-side {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }

        .login-container {
            width: 100%;
            max-width: 420px;
            margin: 0 auto;
        }

        .login-header {
            text-align: center;
            margin-bottom: 2.5rem;
        }

        .login-header img {
            height: 80px;
            margin-bottom: 1.5rem;
        }

        .login-header h2 {
            color: var(--text);
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .login-header p {
            color: var(--secondary);
            font-size: 1rem;
        }

        .login-form {
            background: var(--surface);
            padding: 2.5rem;
            border-radius: 20px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.08);
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

        .form-group input {
            width: 100%;
            padding: 0.75rem 1rem;
            padding-left: 3rem;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: #f8fafc;
        }

        .form-group input:focus {
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

        .form-group i.toggle-password {
            left: auto;
            right: 1rem;
            cursor: pointer;
        }

        .btn-login {
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

        .btn-login:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
        }

        .btn-login:active {
            transform: translateY(0);
        }

        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            color: var(--secondary);
            text-decoration: none;
            font-weight: 500;
            margin-top: 1.5rem;
            transition: all 0.3s ease;
        }

        .back-link:hover {
            color: var(--primary);
            gap: 0.75rem;
        }

        @media (min-width: 1024px) {
            .left-side {
                display: flex;
            }
        }

        @media (max-width: 1023px) {
            .right-side {
                background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            }
            
            .login-header h2,
            .login-header p {
                color: white;
            }
        }

        @media (max-width: 576px) {
            .login-form {
                padding: 1.5rem;
            }

            .right-side {
                padding: 1rem;
            }
        }

        .alert {
            position: fixed;
            top: 1rem;
            right: 1rem;
            padding: 1rem 1.5rem;
            border-radius: 12px;
            background: white;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            display: flex;
            align-items: center;
            gap: 0.75rem;
            transform: translateX(150%);
            transition: transform 0.3s ease;
            z-index: 1000;
        }

        .alert.show {
            transform: translateX(0);
        }

        .alert.error {
            border-left: 4px solid var(--error);
        }

        .alert.success {
            border-left: 4px solid var(--success);
        }
    </style>
</head>
<body>
    <div class="split-screen">
        <div class="left-side">
            <div class="left-content">
                <h1>Bem-vindo ao Sistema de Agendamento</h1>
                <p>Gerencie suas reservas de salas de estudo de forma simples e eficiente. Nossa plataforma oferece uma experiência intuitiva para alunos e instrutores.</p>
                
                <ul class="feature-list">
                    <li>
                        <i class="bi bi-calendar-check"></i>
                        Agendamento simplificado de salas
                    </li>
                    <li>
                        <i class="bi bi-clock"></i>
                        Disponibilidade em tempo real
                    </li>
                    <li>
                        <i class="bi bi-shield-check"></i>
                        Sistema seguro e confiável
                    </li>
                </ul>
            </div>
        </div>
        
        <div class="right-side">
            <div class="login-container">
                <div class="login-header">
                    <img src="assets/img/logo.png" alt="S.A.S.E Logo" class="img-fluid">
                    <h2>Acesse sua conta</h2>
                    <p>Entre com suas credenciais para continuar</p>
                </div>

                <div class="login-form">
                    <form id="loginForm" method="POST">
                        <div class="form-group">
                            <label for="email">Email institucional</label>
                            <i class="bi bi-envelope"></i>
                            <input type="email" id="email" name="email" placeholder="seu.email@senai.com.br" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="senha">Senha</label>
                            <i class="bi bi-lock"></i>
                            <i class="bi bi-eye-slash toggle-password" id="togglePassword"></i>
                            <input type="password" id="senha" name="senha" placeholder="Digite sua senha" required>
                        </div>

                        <button type="submit" class="btn-login">
                            <span class="spinner-border spinner-border-sm d-none" role="status"></span>
                            <i class="bi bi-box-arrow-in-right"></i>
                            <span class="btn-text">Entrar no sistema</span>
                        </button>
                    </form>
                </div>

                <div class="text-center">
                    <a href="index.php" class="back-link">
                        <i class="bi bi-arrow-left"></i>
                        <span>Voltar para página inicial</span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="alert" id="alert">
        <i class="bi"></i>
        <span class="alert-message"></span>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const togglePassword = document.querySelector('#togglePassword');
        const senha = document.querySelector('#senha');
        const alert = document.querySelector('#alert');

        function showAlert(message, type) {
            alert.className = 'alert ' + type;
            alert.querySelector('.bi').className = 'bi ' + (type === 'error' ? 'bi-x-circle' : 'bi-check-circle');
            alert.querySelector('.alert-message').textContent = message;
            alert.classList.add('show');
            
            setTimeout(() => {
                alert.classList.remove('show');
            }, 3000);
        }

        togglePassword.addEventListener('click', function() {
            const type = senha.getAttribute('type') === 'password' ? 'text' : 'password';
            senha.setAttribute('type', type);
            this.classList.toggle('bi-eye');
            this.classList.toggle('bi-eye-slash');
        });

        document.getElementById('loginForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const btn = document.querySelector('.btn-login');
            const spinner = btn.querySelector('.spinner-border');
            const btnText = btn.querySelector('.btn-text');
            const btnIcon = btn.querySelector('.bi');
            
            spinner.classList.remove('d-none');
            btnText.textContent = 'Entrando...';
            btnIcon.style.display = 'none';
            btn.disabled = true;

            fetch('login.php', {
                method: 'POST',
                body: new FormData(this)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlert('Login realizado com sucesso!', 'success');
                    setTimeout(() => {
                        window.location.href = data.redirect;
                    }, 1000);
                } else {
                    showAlert(data.message, 'error');
                    spinner.classList.add('d-none');
                    btnText.textContent = 'Entrar no sistema';
                    btnIcon.style.display = 'inline-block';
                    btn.disabled = false;
                }
            })
            .catch(error => {
                showAlert('Erro ao processar a solicitação. Tente novamente.', 'error');
                spinner.classList.add('d-none');
                btnText.textContent = 'Entrar no sistema';
                btnIcon.style.display = 'inline-block';
                btn.disabled = false;
            });
        });
    </script>
</body>
</html>