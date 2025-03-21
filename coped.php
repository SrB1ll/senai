<?php
session_start();

// Verificação de autenticação para o novo sistema unificado
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_nivel'] !== 'admin') {
    session_unset();
    session_destroy();
    header('Location: login.php');
    exit();
}

require_once 'conexao.php';

// Verificação do usuário no banco
$sql_usuario = "SELECT id, nome, nivel FROM usuarios WHERE id = ? AND nivel = 'admin' LIMIT 1";
$stmt = $conn->prepare($sql_usuario);
$stmt->bind_param("i", $_SESSION['usuario_id']);
$stmt->execute();
$result = $stmt->get_result();
$usuario = $result->fetch_assoc();

// Se não encontrar o usuário ou não for admin
if (!$usuario) {
    session_unset();
    session_destroy();
    header('Location: login.php');
    exit();
}

// Atualizar a sessão com os dados mais recentes
$_SESSION['usuario_nome'] = $usuario['nome'];
$_SESSION['ultimo_acesso'] = time();

// Verificar tempo de inatividade (30 minutos)
$tempo_inativo = time() - $_SESSION['ultimo_acesso'];
if ($tempo_inativo > 1800) { // 30 minutos = 1800 segundos
    session_unset();
    session_destroy();
    header('Location: login.php');
    exit();
}

try {
    // Buscar todas as reservas pendentes
    $sql_pendentes = "SELECT * FROM reservas WHERE status = 'pendente' ORDER BY inicio DESC";
    $result_pendentes = $conn->query($sql_pendentes);
    if (!$result_pendentes) {
        throw new Exception("Erro ao consultar reservas pendentes: " . $conn->error);
    }

    // Buscar todas as reservas aprovadas
    $sql_aprovadas = "SELECT * FROM reservas WHERE status = 'aprovado' ORDER BY inicio DESC";
    $result_aprovadas = $conn->query($sql_aprovadas);
    if (!$result_aprovadas) {
        throw new Exception("Erro ao consultar reservas aprovadas: " . $conn->error);
    }

    // Buscar computadores
    $sql_computadores = "SELECT c.*, 
                        (SELECT COUNT(*) FROM reservas r 
                         WHERE r.computador_num = c.computador_num 
                         AND r.status = 'aprovado' 
                         AND r.inicio <= NOW() 
                         AND r.fim > NOW()) as esta_reservado
                        FROM computadores c 
                        ORDER BY c.computador_num";
    $result_computadores = $conn->query($sql_computadores);
    if (!$result_computadores) {
        throw new Exception("Erro ao consultar computadores: " . $conn->error);
    }

} catch (Exception $e) {
    die("Erro no banco de dados: " . $e->getMessage());
}
    // Verificar se o usuário está logado e é admin
    if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_nivel'] != 'admin') {
        header("Location: login.php");
        exit();
    }

    // Processar prolongamento de tempo
    if (isset($_POST['prolongar_tempo'])) {
        $reserva_id = $_POST['reserva_id'];
        $novo_horario = $_POST['novo_horario'];
        
        // Buscar a data atual da reserva
        $stmt = $conn->prepare("SELECT inicio FROM reservas WHERE id = ?");
        $stmt->bind_param("i", $reserva_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $reserva = $result->fetch_assoc();
        
        if ($reserva) {
            // Combinar a data atual com o novo horário
            $data_atual = date('Y-m-d', strtotime($reserva['inicio']));
            $novo_datetime = $data_atual . ' ' . $novo_horario;
            
            // Atualizar o horário final
            $stmt = $conn->prepare("UPDATE reservas SET fim = ? WHERE id = ?");
            $stmt->bind_param("si", $novo_datetime, $reserva_id);
            $stmt->execute();
        }
    }

    // Processar prolongamento de tempo da sala
    if (isset($_POST['prolongar_tempo_sala'])) {
        $reserva_id = $_POST['reserva_sala_id'];
        $novo_horario = $_POST['novo_horario_sala'];
        
        // Buscar a data atual da reserva
        $stmt = $conn->prepare("SELECT inicio FROM reservas_sala WHERE id = ?");
        $stmt->bind_param("i", $reserva_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $reserva = $result->fetch_assoc();
        
        if ($reserva) {
            // Combinar a data atual com o novo horário
            $data_atual = date('Y-m-d', strtotime($reserva['inicio']));
            $novo_datetime = $data_atual . ' ' . $novo_horario;
            
            // Atualizar o horário final
            $stmt = $conn->prepare("UPDATE reservas_sala SET fim = ? WHERE id = ?");
            $stmt->bind_param("si", $novo_datetime, $reserva_id);
            $stmt->execute();
        }
    }
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel COPED - S.A.S.E</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <link href="assets/css/instrutor.css" rel="stylesheet">
    <link href="assets/css/estilo.css" rel="stylesheet">
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
        .form-control, .form-select {
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            padding: 0.75rem 1rem;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: #f8fafc;
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.1);
            background: white;
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

        @media (max-width: 768px) {
            .page-header {
                padding: 2rem 0;
            }

            .page-header h1 {
                font-size: 2rem;
            }
        }

        .card {
            background: white;
            border-radius: 16px;
            border: 1px solid rgba(37, 99, 235, 0.1);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            margin-bottom: 2rem;
        }

        .nav-pills {
            background: var(--surface);
            padding: 0.5rem;
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            margin-bottom: 2rem;
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
        }

        .nav-pills .nav-link {
            color: var(--text);
            padding: 1rem 1.5rem;
            border-radius: 12px;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.3s ease;
        }

        .nav-pills .nav-link i {
            font-size: 1.2rem;
        }


        .table th {
            font-weight: 600;
            color: #1f2937;
        }

        .btn-action {
            padding: 0.5rem;
            border-radius: 6px;
        }

        .message-card {
            border-left: 4px solid #0d6efd;
            padding: 1rem;
            margin-bottom: 1rem;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }

        .filter-section {
            background: var(--surface);
            border-radius: 20px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
        }

        .aba-section {
            background: white;
            border-radius: 12px;
            padding: 1rem;
            margin-bottom: 2rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
        }

        .navbar {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            padding: 1rem 0;
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

        @media (max-width: 768px) {
            .nav-pills {
                flex-wrap: nowrap;
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
                padding-bottom: 0.5rem;
            }

            .nav-pills .nav-link {
                white-space: nowrap;
                margin: 0 0.25rem;
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

        .form-label {
            font-weight: 500;
            color: var(--text);
            margin-bottom: 0.5rem;
        }

        .modal-content {
            border-radius: 20px;
            border: none;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.1);
        }

        .modal-header {
            padding: 1.5rem;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: white;
            border-radius: 20px 20px 0 0;
        }

        .modal-title {
            font-weight: 600;
        }

        .modal-body {
            padding: 2rem;
        }

        .modal-footer {
            padding: 1.5rem;
            border-top: 1px solid rgba(0, 0, 0, 0.05);
            background: #f8fafc;
            border-radius: 0 0 20px 20px;
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
                            <i class="bi bi-person-badge me-2"></i>
                            <?php echo htmlspecialchars($_SESSION['usuario_nome']); ?>
                        </span>
                    </li>
                    <li class="nav-item me-3">
                        <a class="nav-link btn btn-outline-primary" href="gerar_senha.php">
                            <i class="bi bi-person-plus"></i>
                            Novo Usuário
                        </a>
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
            <h1>Painel de Controle COPED</h1>
            <p>Gerencie reservas, computadores e mensagens do sistema</p>
        </div>
    </header>

    <div class="container">
        <!-- Filtros -->
        <div class="filter-section">
            <h5>
                <i class="bi bi-funnel"></i>
                Filtros
            </h5>
            <form method="GET" class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Data Início</label>
                    <input type="date" class="form-control" name="data_inicio" value="<?php echo $_GET['data_inicio'] ?? ''; ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Data Fim</label>
                    <input type="date" class="form-control" name="data_fim" value="<?php echo $_GET['data_fim'] ?? ''; ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Horário</label>
                    <select class="form-select" name="horario">
                        <option value="">Todos</option>
                        <option value="manha" <?php echo ($_GET['horario'] ?? '') == 'manha' ? 'selected' : ''; ?>>Manhã (8h-12h)</option>
                        <option value="tarde" <?php echo ($_GET['horario'] ?? '') == 'tarde' ? 'selected' : ''; ?>>Tarde (14h-18h)</option>
                        <option value="noite" <?php echo ($_GET['horario'] ?? '') == 'noite' ? 'selected' : ''; ?>>Noite (19h-22h)</option>
                    </select>
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-search"></i>
                        Filtrar
                    </button>
                    <a href="coped.php" class="btn btn-outline-primary ms-2">
                        <i class="bi bi-x-circle"></i>
                        Limpar
                    </a>
                </div>
            </form>
        </div>

        <!-- Abas -->
        <ul class="nav nav-pills mb-4" role="tablist">
            <li class="nav-item">
                <a class="nav-link <?php echo (!isset($_GET['tab']) || $_GET['tab'] == 'reservas') ? 'active' : ''; ?>" 
                   data-bs-toggle="tab" 
                   href="#reservas">
                   <i class="bi bi-pc-display"></i>
                   Reservas de Computador
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo (isset($_GET['tab']) && $_GET['tab'] == 'reservas_sala') ? 'active' : ''; ?>" 
                   data-bs-toggle="tab" 
                   href="#reservas_sala">
                   <i class="bi bi-building"></i>
                   Reservas de Sala
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo (isset($_GET['tab']) && $_GET['tab'] == 'computadores') ? 'active' : ''; ?>" 
                   data-bs-toggle="tab" 
                   href="#computadores">
                   <i class="bi bi-hdd-rack"></i>
                   Computadores
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo (isset($_GET['tab']) && $_GET['tab'] == 'mensagens') ? 'active' : ''; ?>" 
                   data-bs-toggle="tab" 
                   href="#mensagens">
                   <i class="bi bi-envelope"></i>
                   Mensagens
                </a>
            </li>
        </ul>

        <div class="tab-content">
            <!-- Aba de Reservas de Computador -->
            <div class="tab-pane fade <?php echo (!isset($_GET['tab']) || $_GET['tab'] == 'reservas') ? 'show active' : ''; ?>" id="reservas">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title mb-4">Reservas Ativas</h5>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Aluno</th>
                                        <th>CPF</th>
                                        <th>Data</th>
                                        <th>Horário</th>
                                        <th>Computador</th>
                                        <th>Status</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $where_conditions = ["1=1"];
                                    $params = [];
                                    $types = "";

                                    if (!empty($_GET['data_inicio'])) {
                                        $where_conditions[] = "DATE(inicio) >= ?";
                                        $params[] = $_GET['data_inicio'];
                                        $types .= "s";
                                    }

                                    if (!empty($_GET['data_fim'])) {
                                        $where_conditions[] = "DATE(inicio) <= ?";
                                        $params[] = $_GET['data_fim'];
                                        $types .= "s";
                                    }

                                    if (!empty($_GET['horario'])) {
                                        $hora_inicio = '';
                                        $hora_fim = '';
                                        switch($_GET['horario']) {
                                            case 'manha':
                                                $hora_inicio = '08:00:00';
                                                $hora_fim = '12:00:00';
                                                break;
                                            case 'tarde':
                                                $hora_inicio = '14:00:00';
                                                $hora_fim = '18:00:00';
                                                break;
                                            case 'noite':
                                                $hora_inicio = '19:00:00';
                                                $hora_fim = '22:00:00';
                                                break;
                                        }
                                        if ($hora_inicio && $hora_fim) {
                                            $where_conditions[] = "TIME(inicio) BETWEEN ? AND ?";
                                            $params[] = $hora_inicio;
                                            $params[] = $hora_fim;
                                            $types .= "ss";
                                        }
                                    }

                                    $sql = "SELECT * FROM reservas WHERE " . implode(" AND ", $where_conditions) . " ORDER BY inicio DESC";
                                    $stmt = $conn->prepare($sql);
                                    
                                    if (!empty($params)) {
                                        $stmt->bind_param($types, ...$params);
                                    }
                                    
                                    $stmt->execute();
                                    $result = $stmt->get_result();

                                    while ($row = $result->fetch_assoc()):
                                    ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($row['nome']); ?></td>
                                        <td><?php echo htmlspecialchars($row['cpf']); ?></td>
                                        <td><?php echo date('d/m/Y', strtotime($row['inicio'])); ?></td>
                                        <td>
                                            <?php 
                                            echo date('H:i', strtotime($row['inicio'])) . ' - ' . 
                                                 date('H:i', strtotime($row['fim'])); 
                                            ?>
                                        </td>
                                        <td><?php echo htmlspecialchars($row['computador_num']); ?></td>
                                        <td>
                                            <span class="status-badge <?php 
                                            echo $row['status'] == 'aprovado' ? 'aprovado' : 
                                                ($row['status'] == 'pendente' ? 'pendente' : 'recusado'); 
                                            ?>">
                                                <i class="bi bi-<?php 
                                                echo $row['status'] == 'aprovado' ? 'check-circle' : 
                                                    ($row['status'] == 'pendente' ? 'clock' : 'x-circle'); 
                                                ?>"></i>
                                                <?php echo ucfirst($row['status']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if ($row['status'] == 'pendente'): ?>
                                            <button onclick="aprovarReserva(<?php echo $row['id']; ?>)" class="btn btn-success btn-sm btn-action">
                                                <i class="bi bi-check-lg"></i>
                                            </button>
                                            <button onclick="recusarReserva(<?php echo $row['id']; ?>)" class="btn btn-danger btn-sm btn-action">
                                                <i class="bi bi-x-lg"></i>
                                            </button>
                                            <?php endif; ?>
                                            <?php if ($row['status'] == 'aprovado'): ?>
                                            <button type="button" class="btn btn-primary btn-sm btn-action" data-bs-toggle="modal" data-bs-target="#prolongarModal<?php echo $row['id']; ?>">
                                                <i class="bi bi-clock"></i>
                                            </button>
                                            <?php endif; ?>
                                            <button onclick="excluirReserva(<?php echo $row['id']; ?>)" class="btn btn-danger btn-sm btn-action">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </td>
                                    </tr>

                                    <!-- Modal de Prolongamento -->
                                    <div class="modal fade" id="prolongarModal<?php echo $row['id']; ?>" tabindex="-1">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Prolongar Tempo</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <form method="POST">
                                                    <div class="modal-body">
                                                        <input type="hidden" name="reserva_id" value="<?php echo $row['id']; ?>">
                                                        <div class="mb-3">
                                                            <label class="form-label">Novo Horário de Término</label>
                                                            <input type="time" class="form-control" name="novo_horario" required>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                                        <button type="submit" name="prolongar_tempo" class="btn btn-primary">Prolongar</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Aba de Reservas de Sala -->
            <div class="tab-pane fade <?php echo (isset($_GET['tab']) && $_GET['tab'] == 'reservas_sala') ? 'show active' : ''; ?>" id="reservas_sala">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title mb-4">Reservas de Sala</h5>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Instrutor</th>
                                        <th>Data</th>
                                        <th>Período</th>
                                        <th>Motivo</th>
                                        <th>Status</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $where_conditions_sala = ["1=1"];
                                    $params_sala = [];
                                    $types_sala = "";

                                    if (!empty($_GET['data_inicio'])) {
                                        $where_conditions_sala[] = "DATE(rs.inicio) >= ?";
                                        $params_sala[] = $_GET['data_inicio'];
                                        $types_sala .= "s";
                                    }

                                    if (!empty($_GET['data_fim'])) {
                                        $where_conditions_sala[] = "DATE(rs.inicio) <= ?";
                                        $params_sala[] = $_GET['data_fim'];
                                        $types_sala .= "s";
                                    }

                                    if (!empty($_GET['horario'])) {
                                        $where_conditions_sala[] = "rs.periodo = ?";
                                        $params_sala[] = $_GET['horario'];
                                        $types_sala .= "s";
                                    }

                                    $sql = "SELECT rs.*, u.nome as instrutor_nome 
                                           FROM reservas_sala rs 
                                           JOIN usuarios u ON rs.usuario_id = u.id 
                                           WHERE " . implode(" AND ", $where_conditions_sala) . " 
                                           ORDER BY rs.inicio DESC";
                                    
                                    $stmt = $conn->prepare($sql);
                                    if (!empty($params_sala)) {
                                        $stmt->bind_param($types_sala, ...$params_sala);
                                    }
                                    $stmt->execute();
                                    $result = $stmt->get_result();

                                    while ($row = $result->fetch_assoc()):
                                        $periodo_texto = [
                                            'manha' => 'Manhã (08:00 - 12:00)',
                                            'tarde' => 'Tarde (13:00 - 17:00)',
                                            'noite' => 'Noite (18:00 - 22:00)'
                                        ][$row['periodo']] ?? $row['periodo'];
                                    ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($row['instrutor_nome']); ?></td>
                                        <td><?php echo date('d/m/Y', strtotime($row['inicio'])); ?></td>
                                        <td><?php echo $periodo_texto; ?></td>
                                        <td><?php echo htmlspecialchars($row['motivo']); ?></td>
                                        <td>
                                            <span class="status-badge <?php 
                                            echo $row['status'] == 'aprovado' ? 'aprovado' : 
                                                ($row['status'] == 'pendente' ? 'pendente' : 'recusado'); 
                                            ?>">
                                                <i class="bi bi-<?php 
                                                echo $row['status'] == 'aprovado' ? 'check-circle' : 
                                                    ($row['status'] == 'pendente' ? 'clock' : 'x-circle'); 
                                                ?>"></i>
                                                <?php echo ucfirst($row['status']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if ($row['status'] == 'pendente'): ?>
                                            <button onclick="aprovarReservaSala(<?php echo $row['id']; ?>)" class="btn btn-success btn-sm btn-action">
                                                <i class="bi bi-check-lg"></i>
                                            </button>
                                            <button onclick="recusarReservaSala(<?php echo $row['id']; ?>)" class="btn btn-danger btn-sm btn-action">
                                                <i class="bi bi-x-lg"></i>
                                            </button>
                                            <?php endif; ?>
                                            <button onclick="excluirReservaSala(<?php echo $row['id']; ?>)" class="btn btn-danger btn-sm btn-action">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Aba de Mensagens -->
            <div class="tab-pane fade <?php echo (isset($_GET['tab']) && $_GET['tab'] == 'mensagens') ? 'show active' : ''; ?>" id="mensagens">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title mb-4">Mensagens Recebidas</h5>
                        <?php
                        $sql = "SELECT * FROM mensagens ORDER BY data_envio DESC";
                        $result = $conn->query($sql);

                        while ($mensagem = $result->fetch_assoc()):
                        ?>
                        <div class="card message-card mb-3">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <div>
                                        <h6 class="mb-1"><?php echo htmlspecialchars($mensagem['nome']); ?></h6>
                                        <small class="text-muted">
                                            <?php echo htmlspecialchars($mensagem['email']); ?> - 
                                            <?php echo date('d/m/Y H:i', strtotime($mensagem['data_envio'])); ?>
                                        </small>
                                    </div>
                                    <?php if (!$mensagem['respondido']): ?>
                                    <span class="status-badge pendente">
                                        <i class="bi bi-clock"></i>
                                        Não Respondida
                                    </span>
                                    <?php else: ?>
                                    <span class="status-badge aprovado">
                                        <i class="bi bi-check-circle"></i>
                                        Respondida
                                    </span>
                                    <?php endif; ?>
                                </div>
                                
                                <p class="mb-3"><?php echo nl2br(htmlspecialchars($mensagem['mensagem'])); ?></p>
                                
                                <?php if ($mensagem['respondido']): ?>
                                <div class="bg-light p-3 rounded">
                                    <h6 class="mb-2">Resposta:</h6>
                                    <p class="mb-0"><?php echo nl2br(htmlspecialchars($mensagem['resposta'])); ?></p>
                                </div>
                                <?php else: ?>
                                <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#responderModal<?php echo $mensagem['id']; ?>">
                                    Responder
                                </button>
                                <?php endif; ?>
                                <button onclick="excluirMensagem(<?php echo $mensagem['id']; ?>)" class="btn btn-danger btn-sm">
                                    <i class="bi bi-trash"></i> Excluir
                                </button>
                            </div>
                        </div>

                        <!-- Modal de Resposta -->
                        <div class="modal fade" id="responderModal<?php echo $mensagem['id']; ?>" tabindex="-1">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Responder Mensagem</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <form method="POST">
                                        <div class="modal-body">
                                            <input type="hidden" name="mensagem_id" value="<?php echo $mensagem['id']; ?>">
                                            <div class="mb-3">
                                                <label class="form-label">Sua Resposta</label>
                                                <textarea class="form-control" name="resposta" rows="4" required></textarea>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                            <button type="submit" name="responder_mensagem" class="btn btn-primary">Enviar Resposta</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <?php endwhile; ?>
                    </div>
                </div>
            </div>

            <!-- Aba de Computadores -->
            <div class="tab-pane fade <?php echo (isset($_GET['tab']) && $_GET['tab'] == 'computadores') ? 'show active' : ''; ?>" id="computadores">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title mb-4">Status dos Computadores</h5>
                        <div class="row g-4">
                            <?php
                            $sql = "SELECT c.*, 
                                    (SELECT COUNT(*) FROM reservas r 
                                     WHERE r.computador_num = c.computador_num 
                                     AND r.status = 'aprovado' 
                                     AND r.inicio <= NOW() 
                                     AND r.fim > NOW()) as em_uso
                                    FROM computadores c 
                                    ORDER BY c.computador_num";
                            $result = $conn->query($sql);

                            while ($computador = $result->fetch_assoc()):
                                $status_class = '';
                                $status_text = '';
                                
                                if ($computador['status'] == 'manutencao') {
                                    $status_class = 'recusado';
                                    $status_text = 'Em Manutenção';
                                } elseif ($computador['em_uso'] > 0) {
                                    $status_class = 'pendente';
                                    $status_text = 'Em Uso';
                                } else {
                                    $status_class = 'aprovado';
                                    $status_text = 'Disponível';
                                }
                            ?>
                            <div class="col-md-3">
                                <div class="card">
                                    <div class="card-body text-center">
                                        <h5 class="card-title">PC <?php echo $computador['computador_num']; ?></h5>
                                        <span class="status-badge <?php echo $status_class; ?> d-block p-2 mb-2">
                                            <i class="bi bi-<?php 
                                            echo $computador['status'] == 'manutencao' ? 'wrench' : 
                                                ($computador['em_uso'] > 0 ? 'person' : 'check-circle'); 
                                            ?>"></i>
                                            <?php echo $status_text; ?>
                                        </span>
                                        <button class="btn btn-sm btn-outline-<?php echo $status_class; ?>"
                                                onclick="alterarStatusPC(<?php echo $computador['id']; ?>, '<?php echo $computador['status'] == 'disponivel' ? 'manutencao' : 'disponivel'; ?>')">
                                            <?php echo $computador['status'] == 'disponivel' ? 'Marcar Manutenção' : 'Marcar Disponível'; ?>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <?php endwhile; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    // Função para preservar a aba atual
    function getCurrentTab() {
        const urlParams = new URLSearchParams(window.location.search);
        return urlParams.get('tab') || 'reservas';
    }

    // Função para atualizar o conteúdo sem perder a aba atual
    function refreshContent() {
        const currentTab = getCurrentTab();
        fetch(window.location.pathname + '?tab=' + currentTab, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.text())
        .then(html => {
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');
            
            // Atualizar apenas o conteúdo das abas
            const newContent = doc.querySelector('.tab-content');
            if (newContent) {
                document.querySelector('.tab-content').innerHTML = newContent.innerHTML;
            }

            // Manter a aba atual ativa
            document.querySelectorAll('.nav-link').forEach(link => {
                link.classList.remove('active');
                if (link.getAttribute('href') === '#' + currentTab) {
                    link.classList.add('active');
                }
            });
            document.querySelectorAll('.tab-pane').forEach(pane => {
                pane.classList.remove('show', 'active');
                if (pane.id === currentTab) {
                    pane.classList.add('show', 'active');
                }
            });
        })
        .catch(error => console.error('Erro ao atualizar:', error));
    }

    // Atualizar a cada 30 segundos
    setInterval(refreshContent, 30000);

    // Funções existentes
    function alterarStatusPC(id, novoStatus) {
        if (confirm('Deseja alterar o status deste computador?')) {
            fetch('alterar_status_pc.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `id=${id}&status=${novoStatus}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Erro ao alterar status do computador');
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                alert('Erro ao processar a solicitação');
            });
        }
    }

    function aprovarReserva(id) {
        if (confirm('Deseja aprovar esta reserva?')) {
            fetch('aprovar_reserva.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ id: id })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert(data.message || 'Erro ao aprovar reserva');
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                alert('Erro ao processar a solicitação');
            });
        }
    }

    function recusarReserva(id) {
        if (confirm('Deseja recusar esta reserva?')) {
            fetch('recusar_reserva.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ id: id })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert(data.message || 'Erro ao recusar reserva');
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                alert('Erro ao processar a solicitação');
            });
        }
    }

    function aprovarReservaSala(id) {
        if (confirm('Deseja aprovar esta reserva de sala?')) {
            fetch('aprovar_reserva_sala.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ id: id })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert(data.message || 'Erro ao aprovar reserva');
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                alert('Erro ao processar a solicitação');
            });
        }
    }

    function recusarReservaSala(id) {
        if (confirm('Deseja recusar esta reserva de sala?')) {
            fetch('recusar_reserva_sala.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ id: id })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert(data.message || 'Erro ao recusar reserva');
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                alert('Erro ao processar a solicitação');
            });
        }
    }

    function excluirReserva(id) {
        if (confirm('Tem certeza que deseja excluir esta reserva? Esta ação não pode ser desfeita.')) {
            fetch('excluir_reserva.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ id: id })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert(data.message || 'Erro ao excluir reserva');
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                alert('Erro ao processar a solicitação');
            });
        }
    }

    function excluirReservaSala(id) {
        if (confirm('Tem certeza que deseja excluir esta reserva de sala? Esta ação não pode ser desfeita.')) {
            fetch('excluir_reserva_sala.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ id: id })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert(data.message || 'Erro ao excluir reserva');
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                alert('Erro ao processar a solicitação');
            });
        }
    }

    function excluirMensagem(id) {
        if (confirm('Tem certeza que deseja excluir esta mensagem? Esta ação não pode ser desfeita.')) {
            fetch('excluir_mensagem.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ id: id })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert(data.message || 'Erro ao excluir mensagem');
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                alert('Erro ao processar a solicitação');
            });
        }
    }

    // Adicionar código para manter a aba ativa após recarregar
    document.addEventListener('DOMContentLoaded', function() {
        // Verificar se há uma aba específica na URL
        const urlParams = new URLSearchParams(window.location.search);
        const tab = urlParams.get('tab');
        
        if (tab) {
            // Ativar a aba correspondente
            const tabElement = document.querySelector(`[data-bs-toggle="tab"][href="#${tab}"]`);
            if (tabElement) {
                const tab = new bootstrap.Tab(tabElement);
                tab.show();
            }
        }
    });

    // Atualizar URL quando mudar de aba
    document.querySelectorAll('[data-bs-toggle="tab"]').forEach(tab => {
        tab.addEventListener('shown.bs.tab', function (e) {
            const target = e.target.getAttribute('href').substring(1);
            const url = new URL(window.location.href);
            url.searchParams.set('tab', target);
            window.history.pushState({}, '', url);
        });
    });
    </script>
</body>
</html>