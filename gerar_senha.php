<?php
session_start();

// Verificar se o usuário está logado e é administrador
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_nivel'] !== 'admin') {
    header('Location: login.php');
    exit();
}

require_once 'conexao.php';

// Verificar se o usuário ainda existe no banco e é admin
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

// Processar exclusão de usuário
if (isset($_POST['excluir_usuario'])) {
    $id_excluir = (int)$_POST['excluir_usuario'];
    if ($id_excluir != $_SESSION['usuario_id']) { // Não permitir excluir o próprio usuário
        $stmt = $conn->prepare("DELETE FROM usuarios WHERE id = ?");
        $stmt->bind_param("i", $id_excluir);
        $stmt->execute();
    }
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit();
}

// Processar edição de usuário
if (isset($_POST['editar_usuario'])) {
    $id_editar = (int)$_POST['editar_usuario'];
    $nome = limpar_input($_POST['nome']);
    $email = limpar_input($_POST['email']);
    $nivel = limpar_input($_POST['nivel']);
    
    if (!empty($_POST['senha'])) {
        $senha = password_hash($_POST['senha'], PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE usuarios SET nome = ?, email = ?, senha = ?, nivel = ? WHERE id = ?");
        $stmt->bind_param("ssssi", $nome, $email, $senha, $nivel, $id_editar);
    } else {
        $stmt = $conn->prepare("UPDATE usuarios SET nome = ?, email = ?, nivel = ? WHERE id = ?");
        $stmt->bind_param("sssi", $nome, $email, $nivel, $id_editar);
    }
    
    $stmt->execute();
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit();
}

// Processar criação de novo usuário
if ($_SERVER["REQUEST_METHOD"] == "POST" && !isset($_POST['editar_usuario']) && !isset($_POST['excluir_usuario'])) {
    $nome = limpar_input($_POST['nome']);
    $email = limpar_input($_POST['email']);
    $senha = password_hash($_POST['senha'], PASSWORD_DEFAULT);
    $nivel = limpar_input($_POST['nivel']);

    // Verificar se o email já existe
    $stmt = $conn->prepare("SELECT id FROM usuarios WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $_SESSION['mensagem'] = "Este email já está cadastrado.";
        $_SESSION['tipo_mensagem'] = "danger";
    } else {
        // Inserir novo usuário
        $stmt = $conn->prepare("INSERT INTO usuarios (nome, email, senha, nivel) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $nome, $email, $senha, $nivel);

        if ($stmt->execute()) {
            $_SESSION['mensagem'] = "Usuário criado com sucesso!";
            $_SESSION['tipo_mensagem'] = "success";
        } else {
            $_SESSION['mensagem'] = "Erro ao criar usuário: " . $conn->error;
            $_SESSION['tipo_mensagem'] = "danger";
        }
    }
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit();
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Usuários - S.A.S.E</title>
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
        }

        .card-header {
            background: none;
            padding: 1.5rem;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        }

        .card-header h3 {
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

        .badge {
            padding: 0.5rem 1rem;
            border-radius: 9999px;
            font-weight: 500;
            font-size: 0.875rem;
        }

        .badge-admin {
            background: rgba(37, 99, 235, 0.1);
            color: var(--primary);
        }

        .badge-instrutor {
            background: rgba(245, 158, 11, 0.1);
            color: var(--warning);
        }

        .badge-aluno {
            background: rgba(34, 197, 94, 0.1);
            color: var(--success);
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

        .btn-danger {
            background: var(--error);
            border: none;
            color: white;
        }

        .btn-danger:hover {
            background: #dc2626;
            transform: translateY(-2px);
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

        .modal-content {
            border-radius: 20px;
            border: none;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
        }

        .modal-header {
            border-bottom: 1px solid #e2e8f0;
            padding: 1.5rem;
        }

        .modal-body {
            padding: 1.5rem;
        }

        .modal-footer {
            border-top: 1px solid #e2e8f0;
            padding: 1.5rem;
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
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="coped.php">
                            <i class="bi bi-speedometer2"></i>
                            Voltar
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php">
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
            <h1>Gerenciamento de Usuários</h1>
            <p>Gerencie os usuários do sistema, suas permissões e credenciais de acesso</p>
        </div>
    </header>

    <div class="dashboard-container">
        <?php if (isset($_SESSION['mensagem'])): ?>
            <div class="alert alert-<?php echo $_SESSION['tipo_mensagem']; ?> alert-dismissible fade show" role="alert">
                <?php 
                echo $_SESSION['mensagem'];
                unset($_SESSION['mensagem']);
                unset($_SESSION['tipo_mensagem']);
                ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        <!-- Lista de Usuários -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3>
                    <i class="bi bi-people"></i>
                    Usuários do Sistema
                </h3>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#novoUsuarioModal">
                    <i class="bi bi-person-plus"></i>
                    Novo Usuário
                </button>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table mb-0">
                        <thead>
                            <tr>
                                <th>Nome</th>
                                <th>Email</th>
                                <th>Nível</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $sql = "SELECT id, nome, email, nivel FROM usuarios ORDER BY nome";
                            $result = $conn->query($sql);
                            
                            if ($result->num_rows > 0) {
                                while($row = $result->fetch_assoc()) {
                                    $badge_class = match($row['nivel']) {
                                        'admin' => 'badge-admin',
                                        'instrutor' => 'badge-instrutor',
                                        default => 'badge-instrutor'
                                    };
                            ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['nome']); ?></td>
                                <td><?php echo htmlspecialchars($row['email']); ?></td>
                                <td>
                                    <span class="badge <?php echo $badge_class; ?>">
                                        <?php echo ucfirst($row['nivel']); ?>
                                    </span>
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-primary me-2" onclick="editarUsuario(<?php echo $row['id']; ?>)">
                                        <i class="bi bi-pencil"></i>
                                        Editar
                                    </button>
                                    <?php if ($row['id'] != $_SESSION['usuario_id']): ?>
                                    <button class="btn btn-sm btn-danger" onclick="excluirUsuario(<?php echo $row['id']; ?>)">
                                        <i class="bi bi-trash"></i>
                                        Excluir
                                    </button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php
                                }
                            } else {
                                echo "<tr><td colspan='4' class='text-center'>Nenhum usuário encontrado</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Novo Usuário -->
    <div class="modal fade" id="novoUsuarioModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Novo Usuário</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="formNovoUsuario" method="POST">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Nome</label>
                            <input type="text" class="form-control" name="nome" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" name="email" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Senha</label>
                            <input type="password" class="form-control" name="senha" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Nível</label>
                            <select class="form-control" name="nivel" required>
                                <option value="instrutor">Instrutor</option>
                                <option value="admin">Administrador</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-person-plus"></i>
                            Criar Usuário
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Editar Usuário -->
    <div class="modal fade" id="editarUsuarioModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Editar Usuário</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="formEditarUsuario" method="POST">
                    <input type="hidden" name="editar_usuario" id="editarUsuarioId">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Nome</label>
                            <input type="text" class="form-control" name="nome" id="editarNome" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" name="email" id="editarEmail" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Nova Senha (opcional)</label>
                            <input type="password" class="form-control" name="senha">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Nível</label>
                            <select class="form-control" name="nivel" id="editarNivel" required>
                                <option value="instrutor">Instrutor</option>
                                <option value="admin">Administrador</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-lg"></i>
                            Salvar Alterações
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function editarUsuario(id) {
            // Buscar dados do usuário via AJAX
            fetch(`buscar_usuario.php?id=${id}`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.erro) {
                        throw new Error(data.erro);
                    }
                    document.getElementById('editarUsuarioId').value = data.id;
                    document.getElementById('editarNome').value = data.nome;
                    document.getElementById('editarEmail').value = data.email;
                    document.getElementById('editarNivel').value = data.nivel;
                    
                    new bootstrap.Modal(document.getElementById('editarUsuarioModal')).show();
                })
                .catch(error => {
                    alert('Erro ao buscar dados do usuário: ' + error.message);
                });
        }

        function excluirUsuario(id) {
            if (confirm('Tem certeza que deseja excluir este usuário?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `<input type="hidden" name="excluir_usuario" value="${id}">`;
                document.body.appendChild(form);
                form.submit();
            }
        }

        // Verificar sessão a cada 5 minutos
        setInterval(() => {
            fetch('verificar_sessao.php')
                .then(response => response.json())
                .then(data => {
                    if (!data.sessao_ativa) {
                        window.location.href = 'login.php';
                    }
                });
        }, 300000);
    </script>
</body>
</html> 