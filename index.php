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
                    <i class="bi bi-pc-display logo-icon"></i>
                    <h1 class="logo-text">Sistema de Reservas</h1>
                </div>
                
                <div class="text-end mb-4">
                    <button class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#contatoModal">
                        <i class="bi bi-envelope"></i> Contato
                    </button>
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
            alert('Erro ao enviar mensagem');
            spinner.classList.add('d-none');
            btnText.textContent = 'Enviar Mensagem';
            btn.disabled = false;
        });
    });
    </script>
</body>
</html>
