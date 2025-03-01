<?php
// Verificar se os parâmetros foram passados corretamente pela URL
if (isset($_GET['nome']) && isset($_GET['matricula']) && isset($_GET['curso']) && isset($_GET['telefone']) && isset($_GET['data']) && isset($_GET['inicio']) && isset($_GET['computador'])) {
    $nome = htmlspecialchars($_GET['nome']);
    $matricula = htmlspecialchars($_GET['matricula']);
    $curso = htmlspecialchars($_GET['curso']);
    $telefone = htmlspecialchars($_GET['telefone']);
    $data = htmlspecialchars($_GET['data']);
    $inicio = htmlspecialchars($_GET['inicio']);
    $computador_num = htmlspecialchars($_GET['computador']);
} else {
    header("Location: index.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmação de Reserva</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <link href="assets/css/variables.css" rel="stylesheet">
    <link href="assets/css/components.css" rel="stylesheet">
    <link href="assets/css/confirmacao.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="confirmacao-container animate-slide-in">
        <div class="success-icon">
            <i class="bi bi-check-lg"></i>
        </div>
        <h2 class="confirmacao-titulo">Reserva Confirmada!</h2>
        <p class="confirmacao-mensagem">Sua reserva foi realizada com sucesso.</p>

        <div class="detalhes-reserva">
            <div class="detalhe-item">
                <span>Nome:</span>
                <strong><?php echo $nome; ?></strong>
            </div>
            <div class="detalhe-item">
                <span>Matrícula:</span>
                <strong><?php echo $matricula; ?></strong>
            </div>
            <div class="detalhe-item">
                <span>Curso:</span>
                <strong><?php echo $curso; ?></strong>
            </div>
            <div class="detalhe-item">
                <span>Telefone:</span>
                <strong><?php echo $telefone; ?></strong>
            </div>
            <div class="detalhe-item">
                <span>Data:</span>
                <strong><?php echo date('d/m/Y', strtotime($data)); ?></strong>
            </div>
            <div class="detalhe-item">
                <span>Horário:</span>
                <strong><?php echo $inicio; ?></strong>
            </div>
            <div class="detalhe-item">
                <span>Computador:</span>
                <strong>PC <?php echo $computador_num; ?></strong>
            </div>
        </div>

        <div class="acoes-container">
            <a href="index.php" class="btn btn-primary">Voltar ao Início</a>
            <button onclick="window.print()" class="btn btn-outline-primary">
                <i class="bi bi-printer"></i> Imprimir
            </button>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
