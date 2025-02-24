<?php
require_once 'conexao.php';

$mensagem = null;
$reservas = null;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $nome = trim($_POST['nome'] ?? '');
        $matricula = trim($_POST['matricula'] ?? '');

        if (empty($nome) || empty($matricula)) {
            throw new Exception("Nome e matrícula são obrigatórios");
        }

        // Consultar reservas do aluno
        $sql = "SELECT r.*, c.status as pc_status 
                FROM reservas r 
                LEFT JOIN computadores c ON r.computador_num = c.computador_num 
                WHERE r.nome = ? AND r.matricula = ?
                ORDER BY r.inicio DESC";
                
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            throw new Exception("Erro ao preparar consulta");
        }

        $stmt->bind_param("ss", $nome, $matricula);
        
        if (!$stmt->execute()) {
            throw new Exception("Erro ao executar consulta");
        }

        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $reservas = $result->fetch_all(MYSQLI_ASSOC);
        } else {
            $mensagem = "Nenhuma reserva encontrada para este nome e matrícula.";
        }

    } catch (Exception $e) {
        $mensagem = $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Consulta de Reservas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <link href="assets/css/variables.css" rel="stylesheet">
    <link href="assets/css/components.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow-sm">
                    <div class="card-body p-4">
                        <h2 class="text-center mb-4">Consulta de Reservas</h2>

                        <?php if ($mensagem): ?>
                            <div class="alert alert-warning" role="alert">
                                <?php echo $mensagem; ?>
                            </div>
                        <?php endif; ?>

                        <?php if (!$reservas): ?>
                            <form method="POST" class="needs-validation" novalidate>
                                <div class="mb-3">
                                    <label for="nome" class="form-label">Nome Completo</label>
                                    <input type="text" class="form-control" id="nome" name="nome" required>
                                </div>
                                <div class="mb-3">
                                    <label for="matricula" class="form-label">Matrícula</label>
                                    <input type="text" class="form-control" id="matricula" name="matricula" required>
                                </div>
                                <button type="submit" class="btn btn-primary w-100">Consultar Reservas</button>
                            </form>
                        <?php endif; ?>

                        <?php if ($reservas): ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Data</th>
                                            <th>Horário</th>
                                            <th>Computador</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($reservas as $reserva): ?>
                                            <tr>
                                                <td><?php echo date('d/m/Y', strtotime($reserva['inicio'])); ?></td>
                                                <td>
                                                    <?php 
                                                    echo date('H:i', strtotime($reserva['inicio'])) . ' - ' . 
                                                         date('H:i', strtotime($reserva['fim'])); 
                                                    ?>
                                                </td>
                                                <td>PC <?php echo $reserva['computador_num']; ?></td>
                                                <td>
                                                    <?php
                                                    $status_class = match($reserva['status']) {
                                                        'aprovado' => 'success',
                                                        'pendente' => 'warning',
                                                        'recusado' => 'danger',
                                                        default => 'secondary'
                                                    };
                                                    ?>
                                                    <span class="badge bg-<?php echo $status_class; ?>">
                                                        <?php echo ucfirst($reserva['status']); ?>
                                                    </span>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            <div class="mt-3">
                                <a href="consulta.php" class="btn btn-outline-primary">Nova Consulta</a>
                                <a href="index.php" class="btn btn-link">Voltar ao Início</a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
