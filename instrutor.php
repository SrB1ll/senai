<?php
session_start();

// Verificar se o professor está logado
if (!isset($_SESSION['professor_id'])) {
    header('Location: login.php');
    exit;
}

// Incluir conexão com o banco
require_once 'conexao.php';

// Debug - verificar dados da sessão
error_log("Dados da sessão: " . print_r($_SESSION, true));

// Buscar nome do professor
$sql_professor = "SELECT nome FROM professores WHERE id = ?";
$stmt = $conn->prepare($sql_professor);
$stmt->bind_param("i", $_SESSION['professor_id']);
$stmt->execute();
$result = $stmt->get_result();
$professor = $result->fetch_assoc();

// Verificar se encontrou o professor
if (!$professor) {
    // Limpar sessão e redirecionar para login
    session_destroy();
    header('Location: login.php');
    exit;
}

$_SESSION['professor_nome'] = $professor['nome'];

// Debug - verificar nome do professor
error_log("Nome do professor: " . $_SESSION['professor_nome']);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Área do Instrutor - S.A.S.E</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="assets/css/variables.css" rel="stylesheet">
    <link href="assets/css/components.css" rel="stylesheet">
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
                        <a class="nav-link btn btn-outline-light ms-2" href="index.php">
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
                <i class="bi bi-person-workspace"></i>
            </div>
            <h1 class="display-4 mb-4">S.A.S.E</h1>
            <h2 class="h3 mb-4">Sistema de Agendamento de Sala de Estudos</h2>
            <p class="lead mb-4">Gerencie as reservas e computadores da sala de estudos</p>
        </div>
    </section>

    <!-- Dashboard -->
    <div class="container my-5">
        <!-- Minhas Reservas -->
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Minhas Reservas</h5>
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
                            // Debug - mostrar a query
                            $professor_id = $_SESSION['professor_id'];
                            error_log("Buscando reservas para professor ID: " . $professor_id);

                            $sql_minhas_reservas = "SELECT * FROM reservas_sala 
                                                   WHERE professor_id = ? 
                                                   ORDER BY inicio DESC";
                            $stmt = $conn->prepare($sql_minhas_reservas);
                            $stmt->bind_param("i", $professor_id);
                            $stmt->execute();
                            $result_minhas_reservas = $stmt->get_result();

                            // Debug - mostrar número de resultados
                            error_log("Número de reservas encontradas: " . $result_minhas_reservas->num_rows);

                            if ($result_minhas_reservas->num_rows > 0):
                                while($reserva = $result_minhas_reservas->fetch_assoc()): 
                            ?>
                            <tr>
                                <td><?php echo date('d/m/Y', strtotime($reserva['inicio'])); ?></td>
                                <td>
                                    <?php 
                                    echo date('H:i', strtotime($reserva['inicio'])) . ' - ' . 
                                         date('H:i', strtotime($reserva['fim'])); 
                                    ?>
                                </td>
                                <td><?php echo $reserva['motivo']; ?></td>
                                <td>
                                    <span class="badge bg-<?php echo $reserva['status'] == 'aprovado' ? 'success' : 'warning'; ?>">
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

        <!-- Formulário de Reserva -->
        <div class="row g-4">
            <!-- Estatísticas -->
            <div class="col-lg-4 col-md-6 mx-auto">
                <div class="dashboard-card">
                    <div class="card-body text-center p-4">
                        <i class="bi bi-pc-display dashboard-icon"></i>
                        <div class="stats-number">10</div>
                        <h3 class="h5">Computadores</h3>
                    </div>
                </div>
            </div>

            <!-- Status dos Computadores -->
            <div class="col-lg-8 mx-auto">
                <div class="card dashboard-card">
                    <div class="card-body p-4">
                        <h3 class="mb-4 text-primary">
                            <i class="bi bi-calendar-plus me-2"></i>
                            Reservar Sala de Estudos
                        </h3>
                        <form action="reservar_sala.php" method="POST" class="needs-validation" novalidate>
                            <div class="mb-4">
                                <label for="nome" class="form-label">Nome do Instrutor</label>
                                <input type="text" class="form-control form-control-lg" id="nome" name="nome" 
                                       value="<?php echo htmlspecialchars($_SESSION['professor_nome']); ?>" required>
                            </div>
                            <div class="mb-4">
                                <label for="telefone" class="form-label">Telefone</label>
                                <input type="tel" class="form-control form-control-lg" id="telefone" name="telefone" required>
                            </div>
                            <div class="row mb-4">
                                <div class="col-md-4">
                                    <label for="data" class="form-label">Data</label>
                                    <input type="date" class="form-control form-control-lg" id="data" name="data" 
                                           min="<?php echo date('Y-m-d'); ?>" required>
                                </div>
                                <div class="col-md-4">
                                    <label for="inicio" class="form-label">Horário Início</label>
                                    <input type="time" class="form-control form-control-lg" id="inicio" name="inicio" required>
                                </div>
                                <div class="col-md-4">
                                    <label for="fim" class="form-label">Horário Fim</label>
                                    <input type="time" class="form-control form-control-lg" id="fim" name="fim" required>
                                </div>
                            </div>
                            <div class="mb-4">
                                <label for="motivo" class="form-label">Motivo da Reserva</label>
                                <textarea class="form-control form-control-lg" id="motivo" name="motivo" rows="3" required></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary btn-lg w-100">
                                <i class="bi bi-check-circle me-2"></i>
                                Confirmar Reserva
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    document.querySelector('form[action="reservar_sala.php"]').addEventListener('submit', function(e) {
        e.preventDefault();
        
        fetch('reservar_sala.php', {
            method: 'POST',
            body: new FormData(this)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
                window.location.href = data.redirect; // Redireciona após sucesso
            } else {
                alert(data.message);
            }
        })
        .catch(error => {
            alert('Erro ao processar a solicitação');
        });
    });
    </script>
</body>
</html> 