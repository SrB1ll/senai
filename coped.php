<?php
session_start();

// Verificação estrita de autenticação
if (!isset($_SESSION['coped_id']) || !isset($_SESSION['coped_nome'])) {
    // Limpar qualquer sessão existente
    session_unset();
    session_destroy();
    
    // Redirecionar para login com mensagem de erro
    header('Location: coped_login.php?error=unauthorized');
    exit();
}

require_once 'conexao.php';

// Verificação adicional de segurança
$sql_usuario = "SELECT id, nome FROM coped_usuarios WHERE id = ? AND status = 'ativo' LIMIT 1";
$stmt = $conn->prepare($sql_usuario);
$stmt->bind_param("i", $_SESSION['coped_id']);
$stmt->execute();
$result = $stmt->get_result();
$usuario = $result->fetch_assoc();

// Se não encontrar o usuário ou estiver inativo
if (!$usuario) {
    session_unset();
    session_destroy();
    header('Location: coped_login.php?error=invalid');
    exit();
}

// Atualizar a sessão com os dados mais recentes
$_SESSION['coped_nome'] = $usuario['nome'];

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
                <img src="assets/img/logo.png" alt="S.A.S.E Logo" class="navbar-logo me-2" style="height: 30px;">
                S.A.S.E
            </a>
            <div class="navbar-nav ms-auto">
                <span class="nav-item nav-link text-muted">
                    <i class="bi bi-person"></i> <?php echo htmlspecialchars($_SESSION['coped_nome']); ?>
                </span>
                <a class="nav-link" href="logout.php">
                    <i class="bi bi-box-arrow-left"></i> Sair
                </a>
            </div>
        </div>
    </nav>

    <div class="container my-5">
        <h2 class="mb-4">Painel de Controle COPED</h2>

        <!-- Mensagens de Contato -->
        <div class="card mb-4">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Mensagens de Contato</h5>
                <span class="badge bg-light text-primary" id="novas-mensagens">
                    <?php
                    $sql_count = "SELECT COUNT(*) as total FROM mensagens WHERE status = 'não_lida'";
                    $result_count = $conn->query($sql_count);
                    $count = $result_count->fetch_assoc();
                    echo $count['total'] . " nova(s)";
                    ?>
                </span>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Data</th>
                                <th>Nome</th>
                                <th>Email</th>
                                <th>Assunto</th>
                                <th>Status</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $sql_mensagens = "SELECT * FROM mensagens ORDER BY data_envio DESC";
                            $result_mensagens = $conn->query($sql_mensagens);

                            if ($result_mensagens && $result_mensagens->num_rows > 0):
                                while($msg = $result_mensagens->fetch_assoc()):
                            ?>
                            <tr>
                                <td><?php echo date('d/m/Y H:i', strtotime($msg['data_envio'])); ?></td>
                                <td><?php echo htmlspecialchars($msg['nome']); ?></td>
                                <td><?php echo htmlspecialchars($msg['email']); ?></td>
                                <td><?php echo htmlspecialchars($msg['assunto']); ?></td>
                                <td>
                                    <span class="badge bg-<?php echo $msg['status'] == 'não_lida' ? 'danger' : 'success'; ?>">
                                        <?php echo $msg['status']; ?>
                                    </span>
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-primary" 
                                            onclick="verMensagem(<?php echo $msg['id']; ?>)">
                                        <i class="bi bi-envelope-open"></i> 
                                    </button>
                                    <button class="btn btn-sm btn-danger excluir-mensagem" data-id="<?php echo $msg['id']; ?>">
                                        <i class="bi bi-trash"></i> 
                                    </button>
                                </td>
                            </tr>
                            <?php 
                                endwhile;
                            else:
                            ?>
                            <tr>
                                <td colspan="6" class="text-center">Nenhuma mensagem recebida</td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

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
                                    <?php if($reserva['status'] == 'pendente'): ?>
                                        <button class="btn btn-sm btn-success" onclick="aprovarReservaSala(<?php echo $reserva['id']; ?>)">
                                            <i class="bi bi-check-lg"></i> 
                                        </button>
                                        <button class="btn btn-sm btn-danger" onclick="recusarReservaSala(<?php echo $reserva['id']; ?>)">
                                            <i class="bi bi-x-lg"></i> 
                                        </button>
                                    <?php endif; ?>
                                    <button class="btn btn-sm btn-danger excluir-reserva-sala" data-id="<?php echo $reserva['id']; ?>">
                                        <i class="bi bi-trash"></i> 
                                    </button>
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
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Nome</th>
                                <th>Matrícula</th>
                                <th>Curso</th>
                                <th>Data</th>
                                <th>Horário</th>
                                <th>Computador</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($reserva = $result_pendentes->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($reserva['nome']); ?></td>
                                <td><?php echo htmlspecialchars($reserva['matricula']); ?></td>
                                <td><?php echo htmlspecialchars($reserva['curso']); ?></td>
                                <td><?php echo date('d/m/Y', strtotime($reserva['inicio'])); ?></td>
                                <td>
                                    <?php 
                                    echo date('H:i', strtotime($reserva['inicio'])) . ' - ' . 
                                         date('H:i', strtotime($reserva['fim']));
                                    ?>
                                </td>
                                <td>PC <?php echo $reserva['computador_num']; ?></td>
                                <td>
                                    <button class="btn btn-sm btn-success" onclick="aprovarReserva(<?php echo $reserva['id']; ?>)">
                                        <i class="bi bi-check-lg"></i> 
                                    </button>
                                    <button class="btn btn-sm btn-danger" onclick="recusarReserva(<?php echo $reserva['id']; ?>)">
                                        <i class="bi bi-x-lg"></i> 
                                    </button>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Lista de reservas aprovadas -->
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Reservas Aprovadas</h5>
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
                                    echo "<td>
                                            <span class='badge bg-{$status_class}'>" . ucfirst($row['status']) . "</span>
                                            <button class='btn btn-sm btn-danger excluir-reserva' data-id='{$row['id']}'>
                                                <i class='bi bi-trash'></i> 
                                            </button>
                                          </td>";
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

    <!-- Modal de Visualização de Mensagem -->
    <div class="modal fade" id="mensagemModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Mensagem</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="mensagem-content">
                        <!-- Conteúdo será carregado via AJAX -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Função para aprovar reserva
        window.aprovarReserva = async function(id) {
            if (!confirm('Confirmar aprovação da reserva?')) {
                return;
            }
            try {
                const response = await fetch('aprovar_reserva.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ id: id })
                });
                const data = await response.json();
                if (data.success) {
                    location.reload();
                } else {
                    alert('Erro ao aprovar reserva: ' + data.message);
                }
            } catch (error) {
                console.error('Erro:', error);
                alert('Erro ao aprovar reserva. Por favor, tente novamente.');
            }
        };

        // Função para recusar reserva
        window.recusarReserva = async function(id) {
            if (!confirm('Confirmar recusa da reserva?')) {
                return;
            }
            try {
                const response = await fetch('recusar_reserva.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ id: id })
                });
                const data = await response.json();
                if (data.success) {
                    location.reload();
                } else {
                    alert('Erro ao recusar reserva: ' + data.message);
                }
            } catch (error) {
                console.error('Erro:', error);
                alert('Erro ao recusar reserva. Por favor, tente novamente.');
            }
        };

        // Função para ver mensagem
        window.verMensagem = async function(id) {
            try {
                const response = await fetch('ver_mensagem.php?id=' + id);
                const data = await response.json();
                
                if (data.success) {
                    document.getElementById('mensagem-content').innerHTML = `
                        <div class="mb-3">
                            <strong>De:</strong> ${data.mensagem.nome} (${data.mensagem.email})
                        </div>
                        <div class="mb-3">
                            <strong>Assunto:</strong> ${data.mensagem.assunto}
                        </div>
                        <div class="mb-3">
                            <strong>Data:</strong> ${data.mensagem.data_formatada}
                        </div>
                        <div class="mensagem-texto p-3 bg-light rounded">
                            ${data.mensagem.mensagem}
                        </div>
                    `;
                    
                    const modal = new bootstrap.Modal(document.getElementById('mensagemModal'));
                    modal.show();
                    
                    const row = document.querySelector(`button[onclick="verMensagem(${id})"]`).closest('tr');
                    const statusBadge = row.querySelector('.badge');
                    statusBadge.classList.remove('bg-danger');
                    statusBadge.classList.add('bg-success');
                    statusBadge.textContent = 'lida';
                } else {
                    alert('Erro ao carregar mensagem: ' + data.message);
                }
            } catch (error) {
                console.error('Erro:', error);
                alert('Erro ao carregar mensagem. Por favor, tente novamente.');
            }
        };

        // Função para excluir mensagem
        document.querySelectorAll('.excluir-mensagem').forEach(button => {
            button.addEventListener('click', async function() {
                if (!confirm('Tem certeza que deseja excluir esta mensagem?')) {
                    return;
                }

                const mensagemId = this.dataset.id;
                const row = this.closest('tr');

                try {
                    const response = await fetch('excluir_mensagem.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: `id=${mensagemId}`
                    });

                    const data = await response.json();

                    if (data.success) {
                        row.remove();
                        const contador = document.getElementById('novas-mensagens');
                        const totalAtual = parseInt(contador.textContent);
                        if (totalAtual > 0) {
                            contador.textContent = `${totalAtual - 1} nova(s)`;
                        }
                    } else {
                        alert(data.message || 'Erro ao excluir mensagem');
                    }
                } catch (error) {
                    console.error('Erro:', error);
                    alert('Erro ao excluir mensagem. Por favor, tente novamente.');
                }
            });
        });

        // Função para aprovar reserva de sala
        window.aprovarReservaSala = async function(id) {
            if (!confirm('Deseja aprovar esta reserva de sala?')) {
                return;
            }
            try {
                const response = await fetch('aprovar_reserva_sala.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ id: id })
                });
                const data = await response.json();
                if (data.success) {
                    location.reload();
                } else {
                    alert('Erro ao aprovar reserva: ' + data.message);
                }
            } catch (error) {
                console.error('Erro:', error);
                alert('Erro ao aprovar reserva. Por favor, tente novamente.');
            }
        };

        // Função para recusar reserva de sala
        window.recusarReservaSala = async function(id) {
            if (!confirm('Deseja recusar esta reserva de sala?')) {
                return;
            }
            try {
                const response = await fetch('recusar_reserva_sala.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ id: id })
                });
                const data = await response.json();
                if (data.success) {
                    location.reload();
                } else {
                    alert('Erro ao recusar reserva: ' + data.message);
                }
            } catch (error) {
                console.error('Erro:', error);
                alert('Erro ao recusar reserva. Por favor, tente novamente.');
            }
        };

        // Função para excluir reserva de sala
        document.querySelectorAll('.excluir-reserva-sala').forEach(button => {
            button.addEventListener('click', async function() {
                if (!confirm('Tem certeza que deseja excluir esta reserva de sala?')) {
                    return;
                }

                const id = this.dataset.id;
                const row = this.closest('tr');

                try {
                    const response = await fetch('excluir_reserva_sala.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: `id=${id}`
                    });

                    const data = await response.json();

                    if (data.success) {
                        row.remove();
                    } else {
                        alert(data.message || 'Erro ao excluir reserva de sala');
                    }
                } catch (error) {
                    console.error('Erro:', error);
                    alert('Erro ao excluir reserva de sala. Por favor, tente novamente.');
                }
            });
        });

        // Função para excluir reserva aprovada
        document.querySelectorAll('.excluir-reserva').forEach(button => {
            button.addEventListener('click', async function() {
                if (!confirm('Tem certeza que deseja excluir esta reserva?')) {
                    return;
                }

                const id = this.dataset.id;
                const row = this.closest('tr');

                try {
                    const response = await fetch('excluir_reserva.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: `id=${id}`
                    });

                    const data = await response.json();

                    if (data.success) {
                        row.remove();
                    } else {
                        alert(data.message || 'Erro ao excluir reserva');
                    }
                } catch (error) {
                    console.error('Erro:', error);
                    alert('Erro ao excluir reserva. Por favor, tente novamente.');
                }
            });
        });
    });
    </script>
</body>
</html> 