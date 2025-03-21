<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Área do Aluno - S.A.S.E</title>
    <title>Área do Instrutor - S.A.S.E</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="assets/css/variables.css" rel="stylesheet">
    <link href="assets/css/components.css" rel="stylesheet">
    <link href="assets/css/aluno.css" rel="stylesheet">
    <link href="assets/css/instrutor.css" rel="stylesheet">
</head>




<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
        <a class="navbar-brand" href="index.php">
                <img src="assets/img/logo.png" alt="S.A.S.E Logo" class="navbar-logo me-2" style="height: 30px;">
                S.A.S.E
                <span class="navbar-text small ms-2 text-white-50">Sistema de Agendamento de Sala de Estudos</span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link btn btn-outline-light" href="logout.php">
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
            <div class="hero-icon mb-4">
                <i class="bi bi-mortarboard-fill display-1 mb-4"></i>
            </div>
            <h1 class="display-4 mb-4">S.A.S.E</h1>
            <h2 class="h3 mb-4">Sistema de Agendamento de Sala de Estudos</h2>
            <p class="lead mb-4"></p>Reserve seu computador no laboratório de forma rápida e simples
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
                                <label for="cpf" class="form-label">CPF</label>
                                <input type="text" class="form-control" id="cpf" name="cpf" pattern="\d{3}\.\d{3}\.\d{3}-\d{2}" placeholder="000.000.000-00" required>
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
                                           (SELECT COUNT(*) FROM computadores WHERE status != 'manutencao') - 
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
                                    
                                    // Verificar reservas de sala por período
                                    $sql = "SELECT periodo 
                                           FROM reservas_sala 
                                           WHERE DATE(inicio) = ? 
                                           AND status = 'aprovado'";
                                    
                                    $stmt = $conn->prepare($sql);
                                    $stmt->bind_param("s", $data);
                                    $stmt->execute();
                                    $result = $stmt->get_result();
                                    
                                    while($row = $result->fetch_assoc()) {
                                        switch($row['periodo']) {
                                            case 'manha':
                                                $horarios_ocupados = array_merge($horarios_ocupados, 
                                                    ['08:00', '09:00', '10:00', '11:00']);
                                                break;
                                            case 'tarde':
                                                $horarios_ocupados = array_merge($horarios_ocupados, 
                                                    ['14:00', '15:00', '16:00', '17:00']);
                                                break;
                                            case 'noite':
                                                $horarios_ocupados = array_merge($horarios_ocupados, 
                                                    ['19:00', '20:00', '21:00']);
                                                break;
                                        }
                                    }
                                    
                                    // Remover horários que já passaram hoje
                                    if ($data == date('Y-m-d')) {
                                        $hora_atual = date('H:i');
                                        foreach($horarios as $horario) {
                                            if ($horario <= $hora_atual) {
                                                $horarios_ocupados[] = $horario;
                                            }
                                        }
                                    }
                                    
                                    // Remover duplicatas
                                    $horarios_ocupados = array_unique($horarios_ocupados);
                                    
                                    return array_diff($horarios, $horarios_ocupados);
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
                        <h3 class="card-title">Consultar</h3>
                        <p class="card-text mb-4">Verifique suas reservas e mensagens</p>
                        <div id="formConsulta">
                            <form id="consultaForm" class="text-start needs-validation" novalidate>
                                <div class="mb-3">
                                    <label for="nome_consulta" class="form-label">Nome Completo</label>
                                    <input type="text" class="form-control" id="nome_consulta" name="nome" required>
                                </div>
                                <div class="mb-3">
                                    <label for="cpf_consulta" class="form-label">CPF</label>
                                    <input type="text" class="form-control" id="cpf_consulta" name="cpf" pattern="\d{3}\.\d{3}\.\d{3}-\d{2}" placeholder="000.000.000-00" required>
                                </div>
                                <button type="submit" class="btn btn-outline-primary w-100">Consultar</button>
                            </form>
                        </div>
                        <div id="resultadoConsulta" class="mt-4" style="display: none;">
                            <!-- Os resultados serão inseridos aqui via JavaScript -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    // Adicionar máscara ao CPF
    const cpfInputs = document.querySelectorAll('input[name="cpf"]');
    cpfInputs.forEach(input => {
        input.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length <= 11) {
                value = value.replace(/(\d{3})(\d{3})(\d{3})(\d{2})/, "$1.$2.$3-$4");
                e.target.value = value;
            }
        });
    });

    // Adicionar máscara ao telefone
    document.getElementById('telefone').addEventListener('input', function(e) {
        let value = e.target.value.replace(/\D/g, '');
        if (value.length <= 11) {
            if (value.length <= 10) {
                value = value.replace(/(\d{2})(\d{4})(\d{4})/, "($1) $2-$3");
            } else {
                value = value.replace(/(\d{2})(\d{5})(\d{4})/, "($1) $2-$3");
            }
            e.target.value = value;
        }
    });

    // Atualizar horários disponíveis quando a data mudar
    document.getElementById('data_pesquisa').addEventListener('change', function() {
        const data = this.value;
        const dataObj = new Date(data + 'T00:00:00'); // Adicionar horário para evitar problemas com timezone
        const diaSemana = dataObj.getDay();
        
        if (diaSemana === 0 || diaSemana === 6) {
            alert('Não é possível fazer reservas para sábados ou domingos.');
            this.value = '';
            return;
        }

        // Atualizar horários disponíveis via AJAX
        fetch('get_horarios.php?data=' + data)
            .then(response => response.json())
            .then(horarios => {
                const select = document.getElementById('inicio');
                select.innerHTML = '<option value="">Selecione o horário</option>';
                
                horarios.forEach(horario => {
                    const option = document.createElement('option');
                    option.value = horario;
                    option.textContent = horario;
                    select.appendChild(option);
                });
            })
            .catch(error => {
                console.error('Erro ao buscar horários:', error);
            });
    });

    // Validação e envio do formulário de reserva
    document.querySelector('form[action="cadastro.php"]').addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Validar dia da semana
        const data = document.getElementById('data_pesquisa').value;
        const dataObj = new Date(data + 'T00:00:00'); // Adicionar horário para evitar problemas com timezone
        const diaSemana = dataObj.getDay();
        
        if (diaSemana === 0 || diaSemana === 6) {
            alert('Não é possível fazer reservas para sábados ou domingos.');
            return;
        }

        // Verificar se todos os campos estão preenchidos
        const form = this;
        if (!form.checkValidity()) {
            e.stopPropagation();
            form.classList.add('was-validated');
            return;
        }
        
        // Mostrar loading no botão
        const btn = document.getElementById('btnReservar');
        const spinner = btn.querySelector('.spinner-border');
        const btnText = btn.querySelector('.btn-text');
        
        spinner.classList.remove('d-none');
        btnText.textContent = 'Reservando...';
        btn.disabled = true;

        // Enviar formulário
        fetch('cadastro.php', {
            method: 'POST',
            body: new FormData(this)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
                form.reset();
                form.classList.remove('was-validated');
            } else {
                alert(data.message || 'Erro ao fazer reserva');
            }
        })
        .catch(error => {
            alert('Erro ao processar a solicitação');
        })
        .finally(() => {
            spinner.classList.add('d-none');
            btnText.textContent = 'Reservar Agora';
            btn.disabled = false;
        });
    });

    // Validação e envio do formulário de consulta
    document.getElementById('consultaForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const form = this;
        if (!form.checkValidity()) {
            e.stopPropagation();
            form.classList.add('was-validated');
            return;
        }

        const formData = new FormData(this);
        
        // Buscar reservas
        fetch('consultar_reservas.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            const resultadoDiv = document.getElementById('resultadoConsulta');
            resultadoDiv.style.display = 'block';
            
            if (data.success) {
                let html = '<h4 class="mb-4">Suas Reservas</h4>';
                
                if (data.reservas && data.reservas.length > 0) {
                    html += '<div class="table-responsive"><table class="table">';
                    html += '<thead><tr><th>Data</th><th>Horário</th><th>Computador</th><th>Status</th></tr></thead>';
                    html += '<tbody>';
                    
                    data.reservas.forEach(reserva => {
                        html += `<tr>
                            <td>${reserva.data}</td>
                            <td>${reserva.horario}</td>
                            <td>${reserva.computador}</td>
                            <td><span class="badge bg-${reserva.status === 'aprovado' ? 'success' : 'warning'}">${reserva.status}</span></td>
                        </tr>`;
                    });
                    
                    html += '</tbody></table></div>';
                } else {
                    html += '<p class="text-muted">Nenhuma reserva encontrada.</p>';
                }
                
                resultadoDiv.innerHTML = html;
            } else {
                resultadoDiv.innerHTML = `<div class="alert alert-danger">${data.message}</div>`;
            }
        })
        .catch(error => {
            document.getElementById('resultadoConsulta').innerHTML = '<div class="alert alert-danger">Erro ao consultar reservas</div>';
        });

        // Buscar mensagens
        fetch('consultar_mensagens.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            const resultadoDiv = document.getElementById('resultadoConsulta');
            
            if (data.success) {
                let html = '<h4 class="mt-4 mb-4">Suas Mensagens</h4>';
                
                if (data.mensagens && data.mensagens.length > 0) {
                    data.mensagens.forEach(msg => {
                        html += `
                        <div class="card mb-3">
                            <div class="card-body">
                                <h5 class="card-title">${msg.assunto}</h5>
                                <h6 class="card-subtitle mb-2 text-muted">Enviada em: ${msg.data_envio}</h6>
                                <p class="card-text">${msg.mensagem}</p>
                                ${msg.resposta ? `
                                <div class="mt-3 p-3 bg-light rounded">
                                    <strong>Resposta:</strong><br>
                                    ${msg.resposta}
                                    <small class="d-block text-muted mt-2">Respondida em: ${msg.data_resposta}</small>
                                </div>` : ''}
                            </div>
                        </div>`;
                    });
                } else {
                    html += '<p class="text-muted">Nenhuma mensagem encontrada.</p>';
                }
                
                resultadoDiv.innerHTML += html;
            }
        })
        .catch(error => {
            console.error('Erro ao consultar mensagens:', error);
        });
    });
    </script>
</body>
</html>