<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Reservas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <link href="assets/css/variables.css" rel="stylesheet">
    <link href="assets/css/components.css" rel="stylesheet">
    <link href="assets/css/index.css" rel="stylesheet">
    <link href="assets/css/contato.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #f6f7ff 0%, #e9eeff 100%);
            min-height: 100vh;
            padding: 2rem 0;
        }
        .coped-access {
            position: fixed;
            top: 1rem;
            right: 1rem;
            opacity: 0.3;
            transition: opacity 0.3s ease;
        }
        .coped-access:hover {
            opacity: 1;
        }
        /* Cor azul para os ícones */
        .logo-icon,
        .student-icon,
        .instructor-icon {
            color: #0d6efd !important;
        }
        /* Cor azul para os títulos dos cards */
        .access-title {
            color: #0d6efd !important;
        }
        /* Cor azul para o título principal */
        .logo-text {
            color: #0d6efd !important;
            margin-bottom: 0.2rem;
        }
        /* Estilo para o subtítulo */
        .logo-container p {
            font-size: 0.9rem;
            opacity: 0.8;
            margin-bottom: 0;
        }
        /* Cards com borda e sombra suave */
        .access-card {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
            border-radius: 16px;
            padding: 2.5rem 2rem;
            text-align: center;
            transition: all 0.3s ease;
            border: 1px solid rgba(37, 99, 235, 0.1);
            height: 100%;
        }
        /* Hover efeito nos cards */
        .access-card:hover {
            transform: translateY(-5px);
            border-color: rgba(37, 99, 235, 0.3);
            box-shadow: 0 20px 40px rgba(37, 99, 235, 0.1);
        }
        .logo-container {
            text-align: center;
            margin-bottom: 4rem;
            animation: fadeInDown 0.8s ease-out;
        }
        .logo-img {
            height: 90px;
            margin-bottom: 1.5rem;
            filter: drop-shadow(0 4px 6px rgba(0, 0, 0, 0.1));
        }
        .logo-text {
            font-size: 2.5rem;
            font-weight: 700;
            color: #2563eb;
            margin-bottom: 0.5rem;
            letter-spacing: -0.5px;
        }
        .subtitle {
            color: #6b7280;
            font-size: 1.1rem;
            margin-bottom: 3rem;
        }
        .access-icon {
            font-size: 3.5rem;
            margin-bottom: 1.5rem;
            color: #2563eb;
            transition: transform 0.3s ease;
        }
        .access-card:hover .access-icon {
            transform: scale(1.1);
        }
        .access-title {
            font-size: 1.75rem;
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 1rem;
        }
        .access-description {
            color: #6b7280;
            font-size: 1.1rem;
            line-height: 1.5;
        }
        .contact-btn {
            margin-top: 4rem;
            padding: 0.75rem 2rem;
            font-size: 1.1rem;
            border-radius: 12px;
            transition: all 0.3s ease;
        }
        .contact-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(37, 99, 235, 0.15);
        }
        @keyframes fadeInDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        .row {
            animation: fadeInUp 0.8s ease-out;
        }
    </style>
</head>
<body>
    <!-- Botão COPED -->
    <a href="coped_login.php" class="coped-access text-muted text-decoration-none">
        <i class="bi bi-gear"></i> COPED
    </a>

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="logo-container">
                    <img src="assets/img/logo.png" alt="S.A.S.E Logo" class="logo-img">
                    <h1 class="logo-text">S.A.S.E</h1>
                    <p class="subtitle">Sistema de Agendamento de Sala de Estudos</p>
                </div>
                
                <div class="row g-4">
                    <!-- Card Aluno -->
                    <div class="col-md-6">
                        <a href="aluno.php" class="text-decoration-none">
                            <div class="access-card">
                                <i class="bi bi-mortarboard-fill access-icon"></i>
                                <h3 class="access-title">Aluno</h3>
                                <p class="access-description">Acesso para reserva de computadores</p>
                            </div>
                        </a>
                    </div>

                    <!-- Card Instrutor -->
                    <div class="col-md-6">
                        <a href="login.php" class="text-decoration-none">
                            <div class="access-card">
                                <i class="bi bi-person-workspace access-icon"></i>
                                <h3 class="access-title">Instrutor</h3>
                                <p class="access-description">Acesso para gerenciamento</p>
                            </div>
                        </a>
                    </div>
                </div>

                <!-- Botão de Contato -->
                <div class="text-center">
                    <button class="btn btn-outline-primary contact-btn" data-bs-toggle="modal" data-bs-target="#contatoModal">
                        <i class="bi bi-envelope"></i> Contato
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Contato -->
    <div class="modal fade" id="contatoModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Contato</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="contatoForm" class="needs-validation" novalidate>
                        <div class="mb-3">
                            <label for="nome" class="form-label">Nome</label>
                            <input type="text" class="form-control" id="nome" name="nome" required>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                        <div class="mb-3">
                            <label for="assunto" class="form-label">Assunto</label>
                            <input type="text" class="form-control" id="assunto" name="assunto" required>
                        </div>
                        <div class="mb-3">
                            <label for="mensagem" class="form-label">Mensagem</label>
                            <textarea class="form-control" id="mensagem" name="mensagem" rows="4" required></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary w-100" id="btnEnviar">
                            <span class="spinner-border spinner-border-sm d-none" role="status"></span>
                            <span class="btn-text">Enviar Mensagem</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    document.getElementById('contatoForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const btn = document.getElementById('btnEnviar');
        const spinner = btn.querySelector('.spinner-border');
        const btnText = btn.querySelector('.btn-text');
        
        spinner.classList.remove('d-none');
        btnText.textContent = 'Enviando...';
        btn.disabled = true;

        fetch('contato.php', {
            method: 'POST',
            body: new FormData(this)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
                $('#contatoModal').modal('hide');
                this.reset();
            } else {
                alert(data.message);
            }
            spinner.classList.add('d-none');
            btnText.textContent = 'Enviar Mensagem';
            btn.disabled = false;
        })
        .catch(error => {
            console.error('Erro:', error);
            alert('Erro ao enviar mensagem. Por favor, tente novamente.');
            spinner.classList.add('d-none');
            btnText.textContent = 'Enviar Mensagem';
            btn.disabled = false;
        });
    });
    </script>
</body>
</html>
