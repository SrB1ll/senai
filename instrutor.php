<?php
session_start();

// Verificar se o instrutor está logado e tem o nível correto
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_nivel'] !== 'instrutor') {
    header('Location: login.php');
    exit;
}

// Incluir conexão com o banco
require_once 'conexao.php';

// Debug - verificar dados da sessão
error_log("Dados da sessão: " . print_r($_SESSION, true));

// Buscar nome do instrutor
$sql_instrutor = "SELECT nome FROM usuarios WHERE id = ? AND nivel = 'instrutor'";
$stmt = $conn->prepare($sql_instrutor);
$stmt->bind_param("i", $_SESSION['usuario_id']);
$stmt->execute();
$result = $stmt->get_result();
$instrutor = $result->fetch_assoc();

// Verificar se encontrou o instrutor
if (!$instrutor) {
    // Limpar sessão e redirecionar para login
    session_destroy();
    header('Location: login.php');
    exit;
}

$_SESSION['professor_nome'] = $instrutor['nome'];

// Debug - verificar nome do instrutor
error_log("Nome do instrutor: " . $_SESSION['professor_nome']);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel do Instrutor - S.A.S.E</title>
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
            --warning: #f59e0b;
        }

        body {
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            background-color: var(--background);
            min-height: 100vh;
            margin: 0;
            padding-bottom: 2rem;
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

        .page-header {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            padding: 3rem 0;
            margin-bottom: 3rem;
            color: white;
            position: relative;
            overflow: hidden;
        }

        .page-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><circle cx="50" cy="50" r="40" fill="rgba(255,255,255,0.05)"/></svg>') 0 0/100px 100px;
            opacity: 0.3;
        }

        .page-header h1 {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
            position: relative;
        }

        .page-header p {
            font-size: 1.1rem;
            opacity: 0.9;
            max-width: 600px;
            margin: 0 auto;
            position: relative;
        }

        .dashboard-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 1rem;
        }

        .card {
            background: var(--surface);
            border-radius: 20px;
            border: none;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            margin-bottom: 2rem;
            overflow: hidden;
            transition: transform 0.3s ease;
        }

        .card:hover {
            transform: translateY(-5px);
        }

        .card-header {
            background: none;
            padding: 1.5rem;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        }

        .card-header h5 {
            margin: 0;
            color: var(--text);
            font-size: 1.25rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .card-body {
            padding: 1.5rem;
        }

        .table {
            margin: 0;
        }

        .table th {
            font-weight: 600;
            color: var(--secondary);
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.05em;
            padding: 1rem 1.5rem;
            background: #f8fafc;
            border-bottom: 2px solid #e2e8f0;
        }

        .table td {
            padding: 1rem 1.5rem;
            vertical-align: middle;
            color: var(--text);
            border-bottom: 1px solid #e2e8f0;
        }

        .table tr:hover {
            background: #f8fafc;
        }

        .status-badge {
            padding: 0.5rem 1rem;
            border-radius: 9999px;
            font-weight: 500;
            font-size: 0.875rem;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .status-badge.pendente {
            background: rgba(245, 158, 11, 0.1);
            color: var(--warning);
        }

        .status-badge.aprovado {
            background: rgba(34, 197, 94, 0.1);
            color: var(--success);
        }

        .status-badge.recusado {
            background: rgba(239, 68, 68, 0.1);
            color: var(--error);
        }

        .form-control {
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            padding: 0.75rem 1rem;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: #f8fafc;
        }

        .form-control:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.1);
            background: white;
        }

        .form-label {
            font-weight: 500;
            color: var(--text);
            margin-bottom: 0.5rem;
        }

        .btn {
            padding: 0.75rem 1.5rem;
            border-radius: 12px;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.3s ease;
        }

        .btn-primary {
            background: var(--primary);
            border: none;
            color: white;
        }

        .btn-primary:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
        }

        .btn-outline-primary {
            border: 2px solid var(--primary);
            color: var(--primary);
            background: transparent;
        }

        .btn-outline-primary:hover {
            background: var(--primary);
            color: white;
            transform: translateY(-2px);
        }

        .stats-card {
            text-align: center;
            padding: 2rem;
        }

        .stats-icon {
            font-size: 2.5rem;
            color: var(--primary);
            margin-bottom: 1rem;
        }

        .stats-number {
            font-size: 2rem;
            font-weight: 700;
            color: var(--text);
            margin-bottom: 0.5rem;
        }

        .stats-label {
            color: var(--secondary);
            font-size: 0.875rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        @media (max-width: 768px) {
            .page-header {
                padding: 2rem 0;
            }

            .page-header h1 {
                font-size: 2rem;
            }

            .card {
                margin: 1rem;
            }

            .table-responsive {
                margin: 0 -1rem;
            }

            .btn {
                width: 100%;
                justify-content: center;
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
                <ul class="navbar-nav ms-auto align-items-center">
                    <li class="nav-item me-3">
                        <span class="nav-link">
                            <i class="bi bi-person-circle me-2"></i>
                            <?php echo htmlspecialchars($_SESSION['professor_nome']); ?>
                        </span>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link btn btn-outline-primary" href="logout.php">
                            <i class="bi bi-box-arrow-right"></i>
                            Sair
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Page Header -->
    <header class="page-header text-center">
        <div class="container">
            <h1>Bem-vindo(a), <?php echo explode(' ', $_SESSION['professor_nome'])[0]; ?>!</h1>
            <p>Gerencie suas reservas de sala e acompanhe o status das solicitações</p>
        </div>
    </header>

    <div class="dashboard-container">
        <!-- Minhas Reservas -->
        <div class="card">
            <div class="card-header">
                <h5>
                    <i class="bi bi-calendar-check"></i>
                    Minhas Reservas
                </h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Data</th>
                                <th>Horário</th>
                                <th>Motivo</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $instrutor_id = $_SESSION['usuario_id'];
                            $sql_minhas_reservas = "SELECT * FROM reservas_sala 
                                                   WHERE usuario_id = ? 
                                                   ORDER BY inicio DESC";
                            $stmt = $conn->prepare($sql_minhas_reservas);
                            $stmt->bind_param("i", $instrutor_id);
                            $stmt->execute();
                            $result_minhas_reservas = $stmt->get_result();

                            if ($result_minhas_reservas->num_rows > 0):
                                while($reserva = $result_minhas_reservas->fetch_assoc()): 
                                    $status_class = match($reserva['status']) {
                                        'aprovado' => 'aprovado',
                                        'recusado' => 'recusado',
                                        default => 'pendente'
                                    };
                            ?>
                            <tr>
                                <td><?php echo date('d/m/Y', strtotime($reserva['inicio'])); ?></td>
                                <td>
                                    <?php 
                                    echo match($reserva['periodo']) {
                                        'manha' => 'Manhã (08:00 - 12:00)',
                                        'tarde' => 'Tarde (13:00 - 17:00)',
                                        'noite' => 'Noite (18:00 - 22:00)'
                                    };
                                    ?>
                                </td>
                                <td><?php echo htmlspecialchars($reserva['motivo']); ?></td>
                                <td>
                                    <span class="status-badge <?php echo $status_class; ?>">
                                        <i class="bi bi-<?php 
                                        echo match($reserva['status']) {
                                            'aprovado' => 'check-circle',
                                            'recusado' => 'x-circle',
                                            default => 'clock'
                                        };
                                        ?>"></i>
                                        <?php echo ucfirst($reserva['status']); ?>
                                    </span>
                                </td>
                            </tr>
                            <?php 
                                endwhile;
                            else:
                            ?>
                            <tr>
                                <td colspan="4" class="text-center">Nenhuma reserva encontrada</td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Nova Reserva -->
        <div class="card">
            <div class="card-header">
                <h5>
                    <i class="bi bi-calendar-plus"></i>
                    Nova Reserva de Sala
                </h5>
            </div>
            <div class="card-body">
                <form action="reservar_sala.php" method="POST" class="needs-validation" novalidate>
                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="mb-4">
                                <label for="nome" class="form-label">Nome do Instrutor</label>
                                <input type="text" class="form-control" id="nome" name="nome" 
                                       value="<?php echo htmlspecialchars($_SESSION['professor_nome']); ?>" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-4">
                                <label for="telefone" class="form-label">Telefone</label>
                                <input type="tel" class="form-control" id="telefone" name="telefone" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-4">
                                <label for="data" class="form-label">Data</label>
                                <input type="date" class="form-control" id="data" name="data" 
                                       min="<?php echo date('Y-m-d'); ?>" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-4">
                                <label for="periodo" class="form-label">Período</label>
                                <select class="form-control" id="periodo" name="periodo" required>
                                    <option value="">Selecione o período</option>
                                    <option value="manha">Manhã (08:00 - 12:00)</option>
                                    <option value="tarde">Tarde (13:00 - 17:00)</option>
                                    <option value="noite">Noite (18:00 - 22:00)</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="mb-4">
                                <label for="motivo" class="form-label">Motivo da Reserva</label>
                                <textarea class="form-control" id="motivo" name="motivo" rows="3" required></textarea>
                            </div>
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-calendar-plus"></i>
                                Solicitar Reserva
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    document.querySelector('form[action="reservar_sala.php"]').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const data = document.getElementById('data').value;
        const periodo = document.getElementById('periodo').value;
        const nome = document.getElementById('nome').value;
        const telefone = document.getElementById('telefone').value;
        const motivo = document.getElementById('motivo').value;

        if (!data || !periodo || !nome || !telefone || !motivo) {
            alert('Por favor, preencha todos os campos.');
            return;
        }

        // Enviar formulário via AJAX
        fetch('reservar_sala.php', {
            method: 'POST',
            body: new FormData(this),
            credentials: 'same-origin'
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Erro na resposta');
            }
            return response.json();
        })
        .then(data => {
            alert(data.message);
            if (data.success) {
                window.location.href = data.redirect;
            }
        })
        .catch(error => {
            if (error.message.includes('Erro na resposta')) {
                window.location.href = 'login.php';
            }
            console.error('Erro:', error);
            alert('Erro ao processar a solicitação. Por favor, tente novamente.');
        });
    });
    </script>
</body>
</html>