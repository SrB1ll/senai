<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Consulta de Cadastro</title>
    <link rel="stylesheet" href="consulta_dados.css">
</head>
<body>
    <div class="container">
        <h2>Consultar Cadastro</h2>
        <form action="consulta.php" method="GET">
            <input type="text" name="nome" placeholder="Nome" required>
            <input type="text" name="matricula" placeholder="Matrícula" required>
            <button type="submit">Consultar</button>
            <br><br>
        </form>
        <div class="button-container">
            <button onclick="window.location.href='index.php';">Voltar</button>
        </div>
    </div>
</body>
</html>
