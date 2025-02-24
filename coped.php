<?php
session_start();
require_once 'conexao.php';

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
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>COPED - Sistema de Reservas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <link href="assets/css/variables.css" rel="stylesheet">
    <link href="assets/css/components.css" rel="stylesheet">
    <link href="assets/css/coped.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-light shadow-sm">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="bi bi-pc-display"></i> LabReservas
            </a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="index.php">
                    <i class="bi bi-box-arrow-left"></i> Sair
                </a>
            </div>
        </div>
    </nav>

    <div class="container my-5">
        <h2 class="mb-4">Painel de Controle COPED</h2>

        <!-- Reservas de Sala (Instrutores) -->
        <div class="card mb-4">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0">Reservas de Sala - Instrutores</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Instrutor</th>
                                <th>Telefone</th>
                                <th>Data</th>
                                <th>Horário</th>
                                <th>Motivo</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $sql_reservas_sala = "SELECT * FROM reservas_sala ORDER BY inicio DESC";
                            $result_reservas_sala = $conn->query($sql_reservas_sala);

                            if ($result_reservas_sala && $result_reservas_sala->num_rows > 0):
                                while($reserva = $result_reservas_sala->fetch_assoc()): 
                            ?>
                            <tr>
                                <td><?php echo $reserva['instrutor_nome']; ?></td>
                                <td><?php echo $reserva['telefone']; ?></td>
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
                                <td colspan="6" class="text-center">Nenhuma reserva de sala encontrada</td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Reservas Pendentes -->
        <div class="card mb-4">
            <div class="card-header bg-warning text-white">
                <h5 class="mb-0">Reservas Pendentes</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Aluno</th>
                                <th>Matrícula</th>
                                <th>Curso</th>
                                <th>Data</th>
                                <th>Horário</th>
                                <th>PC</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            if ($result_pendentes && $result_pendentes->num_rows > 0):
                                while($reserva = $result_pendentes->fetch_assoc()): 
                            ?>
                            <tr>
                                <td><?php echo htmlspecialchars($reserva['nome']); ?></td>
                                <td><?php echo htmlspecialchars($reserva['matricula']); ?></td>
                                <td><?php echo htmlspecialchars($reserva['curso']); ?></td>
                                <td><?php echo date('d/m/Y', strtotime($reserva['inicio'])); ?></td>
                                <td><?php echo date('H:i', strtotime($reserva['inicio'])); ?></td>
                                <td>PC <?php echo $reserva['computador_num']; ?></td>
                                <td>
                                    <button class="btn btn-success btn-sm" onclick="aprovarReserva(<?php echo $reserva['id']; ?>)">
                                        <i class="bi bi-check-lg"></i>
                                    </button>
                                    <button class="btn btn-danger btn-sm" onclick="recusarReserva(<?php echo $reserva['id']; ?>)">
                                        <i class="bi bi-x-lg"></i>
                                    </button>
                                </td>
                            </tr>
                            <?php 
                                endwhile; 
                            else:
                            ?>
                            <tr>
                                <td colspan="7" class="text-center">Nenhuma reserva pendente encontrada</td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Lista de Usuários Registrados -->
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Usuários Registrados</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Nome</th>
                                <th>Matrícula</th>
                                <th>Curso</th>
                                <th>Computador</th>
                                <th>Data/Hora</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $sql = "SELECT r.*, r.nome, r.matricula, r.curso, 
                                         r.computador_num, r.status, r.inicio as data_hora
                                  FROM reservas r 
                                  ORDER BY r.inicio DESC";
                            $result = $conn->query($sql);
                            
                            if ($result && $result->num_rows > 0) {
                                while ($row = $result->fetch_assoc()) {
                                    $status_class = $row['status'] == 'aprovado' ? 'success' : 
                                                  ($row['status'] == 'pendente' ? 'warning' : 'danger');
                                    echo "<tr>";
                                    echo "<td>{$row['nome']}</td>";
                                    echo "<td>{$row['matricula']}</td>";
                                    echo "<td>{$row['curso']}</td>";
                                    echo "<td>PC {$row['computador_num']}</td>";
                                    echo "<td>" . date('d/m/Y H:i', strtotime($row['inicio'])) . "</td>";
                                    echo "<td><span class='badge bg-{$status_class}'>" . 
                                         ucfirst($row['status']) . "</span></td>";
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='6' class='text-center'>Nenhum usuário registrado</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Monitor de Computadores -->
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Status dos Computadores</h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <?php
                    if ($result_computadores && $result_computadores->num_rows > 0):
                        while($pc = $result_computadores->fetch_assoc()):
                    ?>
                    <div class="col-md-3 col-sm-4 col-6">
                        <div class="card text-center">
                            <div class="card-body">
                                <i class="bi bi-pc-display h3"></i>
                                <h5>PC <?php echo $pc['computador_num']; ?></h5>
                                <span class="badge bg-<?php echo ($pc['esta_reservado'] > 0) ? 'danger' : 'success'; ?>">
                                    <?php echo ($pc['esta_reservado'] > 0) ? 'Ocupado' : 'Livre'; ?>
                                </span>
                            </div>
                        </div>
                    </div>
                    <?php 
                        endwhile;
                    else:
                    ?>
                    <div class="col-12">
                        <p class="text-center">Nenhum computador cadastrado</p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    function aprovarReserva(id) {
        if(confirm('Confirmar aprovação da reserva?')) {
            fetch('aprovar_reserva.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ id: id })
            })
            .then(response => response.json())
            .then(data => {
                if(data.success) {
                    location.reload();
                } else {
                    alert('Erro ao aprovar reserva: ' + data.message);
                }
            });
        }
    }

    function recusarReserva(id) {
        if(confirm('Confirmar recusa da reserva?')) {
            fetch('recusar_reserva.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ id: id })
            })
            .then(response => response.json())
            .then(data => {
                if(data.success) {
                    location.reload();
                } else {
                    alert('Erro ao recusar reserva: ' + data.message);
                }
            });
        }
    }
    </script>
</body>
</html> 