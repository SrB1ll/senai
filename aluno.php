<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Área do Aluno - Sistema de Reservas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <link href="assets/css/variables.css" rel="stylesheet">
    <link href="assets/css/components.css" rel="stylesheet">
    <link href="assets/css/aluno.css" rel="stylesheet">
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light bg-light shadow-sm">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="bi bi-pc-display"></i> LabReservas
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="#reservar">Reservar</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#consultar">Consultar</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">
                            <i class="bi bi-box-arrow-left"></i> Sair
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container text-center">
            <i class="bi bi-mortarboard-fill display-1 mb-4"></i>
            <h1 class="display-4 mb-4">Área do Aluno</h1>
            <p class="lead mb-4">Reserve seu computador no laboratório de forma rápida e simples</p>
        </div>
    </section>

    <!-- Main Content -->
    <div class="container my-5">
        <div class="row g-4">
            <!-- Reservar Card -->
            <div class="col-md-6" id="reservar">
                <div class="card feature-card h-100">
                    <div class="card-body text-center p-4">
                        <i class="bi bi-calendar-plus feature-icon student-icon"></i>
                        <h3 class="card-title">Fazer Reserva</h3>
                        <p class="card-text mb-4">Reserve um computador para seus estudos ou projetos</p>
                        <form action="cadastro.php" method="POST" class="text-start needs-validation" novalidate>
                            <div class="mb-3">
                                <label for="nome" class="form-label">Nome Completo</label>
                                <input type="text" class="form-control" id="nome" name="nome" required>
                            </div>
                            <div class="mb-3">
                                <label for="matricula" class="form-label">Matrícula</label>
                                <input type="text" class="form-control" id="matricula" name="matricula" required>
                            </div>
                            <div class="mb-3">
                                <label for="curso" class="form-label">Curso</label>
                                <select class="form-select" id="curso" name="curso" required>
                                    <option value="">Selecione seu curso</option>
                                    <option value="Computação">Computação</option>
                                    <option value="Engenharia">Engenharia</option>
                                    <option value="Sistemas">Sistemas</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="telefone" class="form-label">Telefone</label>
                                <input type="tel" class="form-control" id="telefone" name="telefone" required>
                            </div>
                            <div class="mb-3">
                                <label for="data_pesquisa" class="form-label">Data</label>
                                <input type="date" class="form-control" id="data_pesquisa" name="data_pesquisa" required>
                            </div>
                            <div class="mb-3">
                                <label for="inicio" class="form-label">Horário</label>
                                <select class="form-select" id="inicio" name="inicio" required>
                                    <option value="">Selecione o horário</option>
                                    <option value="08:00">08:00</option>
                                    <option value="09:00">09:00</option>
                                    <option value="10:00">10:00</option>
                                    <option value="11:00">11:00</option>
                                    <option value="14:00">14:00</option>
                                    <option value="15:00">15:00</option>
                                    <option value="16:00">16:00</option>
                                    <option value="17:00">17:00</option>
                                    <option value="19:00">19:00</option>
                                    <option value="20:00">20:00</option>
                                    <option value="21:00">21:00</option>
                                </select>
                            </div>
                            <button type="submit" class="btn btn-primary w-100" id="btnReservar">
                                <span class="spinner-border spinner-border-sm d-none" role="status"></span>
                                <span class="btn-text">Reservar Agora</span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Consultar Card -->
            <div class="col-md-6" id="consultar">
                <div class="card feature-card h-100">
                    <div class="card-body text-center p-4">
                        <i class="bi bi-search feature-icon student-icon"></i>
                        <h3 class="card-title">Consultar Reserva</h3>
                        <p class="card-text mb-4">Verifique suas reservas existentes</p>
                        <form action="consulta.php" method="POST" class="text-start needs-validation" novalidate>
                            <div class="mb-3">
                                <label for="nome_consulta" class="form-label">Nome Completo</label>
                                <input type="text" class="form-control" id="nome_consulta" name="nome" required>
                            </div>
                            <div class="mb-3">
                                <label for="matricula_consulta" class="form-label">Matrícula</label>
                                <input type="text" class="form-control" id="matricula_consulta" name="matricula" required>
                            </div>
                            <button type="submit" class="btn btn-outline-primary w-100">Consultar</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-light py-4 mt-5">
        <div class="container text-center">
            <p class="text-muted mb-0">© 2024 Sistema de Reservas de Laboratório. Todos os direitos reservados.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    document.querySelector('form[action="cadastro.php"]').addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Mostrar loading no botão
        const btn = document.getElementById('btnReservar');
        const spinner = btn.querySelector('.spinner-border');
        const btnText = btn.querySelector('.btn-text');
        
        spinner.classList.remove('d-none');
        btnText.textContent = 'Processando...';
        btn.disabled = true;

        // Enviar dados via fetch
        fetch('cadastro.php', {
            method: 'POST',
            body: new FormData(this)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.href = data.redirect;
            } else {
                alert(data.message);
                spinner.classList.add('d-none');
                btnText.textContent = 'Reservar Agora';
                btn.disabled = false;
            }
        })
        .catch(error => {
            alert('Erro ao processar a solicitação. Tente novamente.');
            spinner.classList.add('d-none');
            btnText.textContent = 'Reservar Agora';
            btn.disabled = false;
        });
    });
    </script>
</body>
</html> 