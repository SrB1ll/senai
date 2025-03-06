<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Área do Aluno - S.A.S.E</title>
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
                <img src="assets/img/logo.png" alt="S.A.S.E Logo" class="navbar-logo me-2" style="height: 30px;">
                S.A.S.E
                <span class="navbar-text small ms-2 text-muted">Sistema de Agendamento de Sala de Estudos</span>
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
            <h1 class="display-4 mb-4">S.A.S.E</h1>
            <h2 class="h3 text-muted mb-4">Sistema de Agendamento de Sala de Estudos</h2>
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
                                <input type="date" class="form-control" id="data_pesquisa" name="data_pesquisa" 
                                       min="<?php echo date('Y-m-d'); ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="inicio" class="form-label">Horário</label>
                                <?php
                                require_once 'conexao.php';
                                
                                function getHorariosDisponiveis($data) {
                                    global $conn;
                                    
                                    // Todos os horários possíveis
                                    $horarios = [
                                        '08:00', '09:00', '10:00', '11:00', 
                                        '14:00', '15:00', '16:00', '17:00',
                                        '19:00', '20:00', '21:00'
                                    ];
                                    
                                    // Contar quantos computadores estão disponíveis em cada horário
                                    $sql = "SELECT TIME_FORMAT(h.hora, '%H:%i') as horario,
                                           (SELECT COUNT(*) FROM computadores) - 
                                           (SELECT COUNT(DISTINCT r.computador_num) 
                                            FROM reservas r 
                                            WHERE r.status != 'recusado'
                                            AND DATE(r.inicio) = ?
                                            AND TIME_FORMAT(r.inicio, '%H:%i') = TIME_FORMAT(h.hora, '%H:%i')
                                           ) as computadores_disponiveis
                                    FROM (
                                        SELECT STR_TO_DATE(?, '%Y-%m-%d') + INTERVAL (hora.numero - 1) HOUR as hora
                                        FROM (
                                            SELECT 8 as numero UNION SELECT 9 UNION SELECT 10 UNION SELECT 11
                                            UNION SELECT 14 UNION SELECT 15 UNION SELECT 16 UNION SELECT 17
                                            UNION SELECT 19 UNION SELECT 20 UNION SELECT 21
                                        ) as hora
                                    ) h";
                                    
                                    $stmt = $conn->prepare($sql);
                                    $stmt->bind_param("ss", $data, $data);
                                    $stmt->execute();
                                    $result = $stmt->get_result();
                                    
                                    $horarios_ocupados = [];
                                    while($row = $result->fetch_assoc()) {
                                        if ($row['computadores_disponiveis'] <= 0) {
                                            $horarios_ocupados[] = $row['horario'];
                                        }
                                    }
                                    
                                    // Horários ocupados por reservas de sala
                                    $sql = "SELECT TIME_FORMAT(inicio, '%H:%i') as inicio, 
                                                  TIME_FORMAT(fim, '%H:%i') as fim 
                                           FROM reservas_sala 
                                           WHERE DATE(inicio) = ? 
                                           AND status = 'aprovado'";
                                    
                                    $stmt = $conn->prepare($sql);
                                    $stmt->bind_param("s", $data);
                                    $stmt->execute();
                                    $result = $stmt->get_result();
                                    
                                    while($row = $result->fetch_assoc()) {
                                        $inicio = strtotime($row['inicio']);
                                        $fim = strtotime($row['fim']);
                                        
                                        foreach($horarios as $horario) {
                                            $hora = strtotime($horario);
                                            if ($hora >= $inicio && $hora < $fim) {
                                                $horarios_ocupados[] = $horario;
                                            }
                                        }
                                    }
                                    
                                    // Remover horários que já passaram hoje
                                    if ($data == date('Y-m-d')) {
                                        $hora_atual = date('H:i');
                                        foreach($horarios as $key => $horario) {
                                            if ($horario <= $hora_atual) {
                                                $horarios_ocupados[] = $horario;
                                            }
                                        }
                                    }
                                    
                                    // Filtrar horários disponíveis
                                    return array_diff($horarios, array_unique($horarios_ocupados));
                                }
                                ?>
                                <select class="form-select" id="inicio" name="inicio" required>
                                    <option value="">Selecione o horário</option>
                                    <?php
                                    $data_selecionada = date('Y-m-d');
                                    $horarios_disponiveis = getHorariosDisponiveis($data_selecionada);
                                    
                                    foreach($horarios_disponiveis as $horario): ?>
                                        <option value="<?php echo $horario; ?>"><?php echo $horario; ?></option>
                                    <?php endforeach; ?>
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



    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    // Adicionar validação de dias da semana
    document.getElementById('data_pesquisa').addEventListener('change', function() {
        const data = this.value;
        const diaSemana = new Date(data).getDay();
        
        if (diaSemana === 0 || diaSemana === 6) {
            alert('Não é possível fazer reservas para sábados ou domingos.');
            this.value = '';
            return;
        }
    });

    document.querySelector('form[action="cadastro.php"]').addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Validar dia da semana
        const data = document.getElementById('data_pesquisa').value;
        const diaSemana = new Date(data).getDay();
        
        if (diaSemana === 0 || diaSemana === 6) {
            alert('Não é possível fazer reservas para sábados ou domingos.');
            return;
        }
        
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

    document.getElementById('data_pesquisa').addEventListener('change', function() {
        const data = this.value;
        const select = document.getElementById('inicio');
        
        if (!data) return; // Se não houver data selecionada, não faz nada
        
        // Desabilitar o select enquanto carrega
        select.disabled = true;
        
        // Buscar horários disponíveis via AJAX
        fetch(`get_horarios.php?data=${data}`)
            .then(response => response.json())
            .then(horarios => {
                // Limpar opções atuais
                select.innerHTML = '<option value="">Selecione o horário</option>';
                
                if (horarios.length === 0) {
                    const option = document.createElement('option');
                    option.value = "";
                    option.textContent = "Nenhum horário disponível";
                    select.appendChild(option);
                } else {
                    // Adicionar novos horários
                    horarios.forEach(horario => {
                        const option = document.createElement('option');
                        option.value = horario;
                        option.textContent = horario;
                        select.appendChild(option);
                    });
                }
                
                // Reabilitar o select
                select.disabled = false;
            })
            .catch(error => {
                console.error('Erro:', error);
                select.innerHTML = '<option value="">Erro ao carregar horários</option>';
                select.disabled = false;
            });
    });
    </script>
</body>
</html> 