<?php
require_once 'conexao.php';

// Função para processar o envio da mensagem
function processarMensagem($conn) {
    header('Content-Type: application/json');
    
    try {
        $nome = $_POST['nome'] ?? '';
        $email = $_POST['email'] ?? '';
        $assunto = $_POST['assunto'] ?? '';
        $mensagem = $_POST['mensagem'] ?? '';

        if (empty($nome) || empty($email) || empty($assunto) || empty($mensagem)) {
            throw new Exception("Todos os campos são obrigatórios.");
        }

        $sql = "INSERT INTO mensagens (nome, email, assunto, mensagem) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        
        if (!$stmt) {
            throw new Exception("Erro ao preparar consulta: " . $conn->error);
        }
        
        $stmt->bind_param("ssss", $nome, $email, $assunto, $mensagem);
        
        if (!$stmt->execute()) {
            throw new Exception("Erro ao enviar mensagem: " . $stmt->error);
        }

        return [
            'success' => true,
            'message' => 'Mensagem enviada com sucesso!'
        ];

    } catch (Exception $e) {
        return [
            'success' => false,
            'message' => $e->getMessage()
        ];
    }
}

// Se for uma requisição POST, processa a mensagem
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    echo json_encode(processarMensagem($conn));
    exit;
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contato</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }
        header {
            background-color: #333;
            color: white;
            text-align: center;
            padding: 10px 0;
        }
        .container {
            width: 80%;
            margin: auto;
            overflow: hidden;
        }
        form {
            background-color: white;
            padding: 20px;
            margin-top: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
        }
        input[type="text"], input[type="email"], textarea {
            width: 100%;
            padding: 10px;
            margin-bottom: 20px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        button {
            background-color: #333;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        button:hover {
            background-color: #555;
        }
    </style>
</head>
<body>
    <header>
        <h1>Contato</h1>
    </header>

    <div class="container">
        <h2>Precisa de ajuda? Envie sua solicitação!</h2>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#contatoModal">
            Abrir Formulário de Contato
        </button>
    </div>

    <div class="modal fade" id="contatoModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Contato</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="contatoForm" class="needs-validation" novalidate>
                        <div class="mb-3">
                            <label for="nome" class="form-label">Nome</label>
                            <input type="text" class="form-control" id="nome" name="nome" required>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                        <div class="mb-3">
                            <label for="assunto" class="form-label">Assunto</label>
                            <input type="text" class="form-control" id="assunto" name="assunto" required>
                        </div>
                        <div class="mb-3">
                            <label for="mensagem" class="form-label">Mensagem</label>
                            <textarea class="form-control" id="mensagem" name="mensagem" rows="4" required></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary w-100" id="btnEnviar">
                            <span class="spinner-border spinner-border-sm d-none" role="status"></span>
                            <span class="btn-text">Enviar Mensagem</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('contatoForm');
        const btn = document.getElementById('btnEnviar');
        const spinner = btn.querySelector('.spinner-border');
        const btnText = btn.querySelector('.btn-text');
        const modal = new bootstrap.Modal(document.getElementById('contatoModal'));

        form.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            // Desabilita o botão e mostra o spinner
            spinner.classList.remove('d-none');
            btnText.textContent = 'Enviando...';
            btn.disabled = true;

            try {
                const formData = new FormData(this);
                const response = await fetch('contato.php', {
                    method: 'POST',
                    body: formData
                });

                const data = await response.json();

                if (data.success) {
                    alert(data.message);
                    modal.hide();
                    this.reset();
                } else {
                    throw new Error(data.message);
                }
            } catch (error) {
                alert(error.message || 'Erro ao enviar mensagem. Por favor, tente novamente.');
            } finally {
                // Restaura o estado do botão
                spinner.classList.add('d-none');
                btnText.textContent = 'Enviar Mensagem';
                btn.disabled = false;
            }
        });
    });
    </script>
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
