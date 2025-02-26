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
            background: linear-gradient(135deg,rgb(227, 223, 238) 0%,rgb(229, 227, 243) 100%);
            min-height: 100vh;
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
            background: white;
            border: 1px solid rgba(13, 110, 253, 0.1);
            box-shadow: 0 4px 6px rgba(13, 110, 253, 0.05);
        }
        /* Hover efeito nos cards */
        .access-card:hover {
            border-color: rgba(13, 110, 253, 0.3);
            box-shadow: 0 8px 15px rgba(13, 110, 253, 0.1);
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
                    <img src="assets/img/logo.png" alt="S.A.S.E Logo" class="logo-img mb-3" style="height: 80px;">
                    <h1 class="logo-text">S.A.S.E</h1>
                    <p class="text-muted small">Sistema de Agendamento de Sala de Estudos</p>
                </div>
                
                <h2 class="text-center mb-4">Selecione seu tipo de acesso</h2>
                
                <div class="row g-4">
                    <!-- Card Aluno -->
                    <div class="col-md-6">
                        <a href="aluno.php" class="text-decoration-none">
                            <div class="access-card">
                                <i class="bi bi-mortarboard-fill access-icon student-icon"></i>
                                <h3 class="access-title">Aluno</h3>
                                <p class="access-description">Acesso para reserva de computadores</p>
                            </div>
                        </a>
                    </div>

                    <!-- Card Instrutor -->
                    <div class="col-md-6">
                        <a href="login.php" class="text-decoration-none">
                            <div class="access-card">
                                <i class="bi bi-person-workspace access-icon instructor-icon"></i>
                                <h3 class="access-title">Instrutor</h3>
                                <p class="access-description">Acesso para gerenciamento</p>
                            </div>
                        </a>
                    </div>
                </div>

                <!-- Botão de Contato -->
                <div class="text-center mt-5">
                    <button class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#contatoModal">
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
