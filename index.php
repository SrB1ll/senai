<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>S.A.S.E - Sistema de Agendamento de Salas de Estudo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root {
            --primary-color:rgb(50, 42, 197);
            --primary-light:rgb(49, 52, 200);
            --primary-dark: #4338ca;
            --secondary-color: #64748b;
            --success-color: #22c55e;
            --background-gradient: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
        }

        body {
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            background: var(--background-gradient);
            min-height: 100vh;
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
            color: var(--primary-color);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .navbar-brand i {
            font-size: 1.75rem;
        }

        .nav-link {
            font-weight: 500;
            color: var(--secondary-color);
            transition: all 0.3s ease;
            border-radius: 8px;
            padding: 0.5rem 1rem;
        }

        .nav-link:hover {
            color: var(--primary-color);
            background: rgba(99, 102, 241, 0.1);
            transform: translateY(-1px);
        }

        .hero-section {
            padding: 6rem 0;
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-light) 100%);
            color: white;
            position: relative;
            overflow: hidden;
        }

        .hero-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><circle cx="50" cy="50" r="40" fill="rgba(255,255,255,0.05)"/></svg>') 0 0/100px 100px;
            opacity: 0.4;
            pointer-events: none;
            z-index: 2;   
        }

        .hero-title {
            font-size: 3.5rem;
            font-weight: 800;
            margin-bottom: 1.5rem;
            line-height: 1.2;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .hero-subtitle {
            font-size: 1.25rem;
            opacity: 0.9;
            margin-bottom: 2rem;
            max-width: 600px;
        }

        .btn-hero {
            padding: 1rem 2rem;
            font-size: 1.1rem;
            font-weight: 600;
            border-radius: 12px;
            background: white;
            color: var(--primary-color);
            border: none;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-hero:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
            background: var(--primary-dark);
            color: white;
        }

        .features-section {
            padding: 5rem 0;
        }

        .feature-card {
            background: white;
            border-radius: 20px;
            padding: 2rem;
            height: 100%;
            transition: all 0.3s ease;
            border: 1px solid rgba(148, 163, 184, 0.1);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        }

        .feature-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 24px rgba(0, 0, 0, 0.1);
        }

        .feature-icon {
            width: 48px;
            height: 48px;
            background: rgba(99, 102, 241, 0.1);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1.5rem;
            color: var(--primary-color);
            font-size: 1.5rem;
        }

        .feature-title {
            font-size: 1.25rem;
            font-weight: 600;
            margin-bottom: 1rem;
            color: #1e293b;
        }

        .feat-title {
        font-size: 1.25rem;
        font-weight: 600;
        margin-bottom: -1rem;
        color: #1e293b;
        text-decoration: none; /* Remove o sublinhado */
        margin-left: 0; /* Remover o espaçamento lateral do link */
        display: inline-flex; /* Para alinhar ícone e texto */
        align-items: center; /* Centralizar verticalmente */
        }

        .feat-title i {
        margin-left: 20px; /* Espaço entre o texto e a seta */
        }

        .feat-card {
            background: white;
            border-radius: 20px;
            padding: 2rem;
            height: 100%;
            transition: all 0.3s ease;
            border: 1px solid rgba(148, 163, 184, 0.1);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            width: 45%;  /* Largura fixa */ 
        }

        .feature-description {
            color: var(--secondary-color);
            line-height: 1.6;
            margin-bottom: 0;
        }

        .footer {
            background: white;
            padding: 3rem 0;
            margin-top: 4rem;
            border-top: 1px solid rgba(148, 163, 184, 0.1);
        }

        .footer-title {
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 1rem;
        }

        .footer-link {
            color: var(--secondary-color);
            text-decoration: none;
            display: block;
            margin-bottom: 0.5rem;
            transition: all 0.3s ease;
            
        }

        .footer-link:hover {
            color: var(--primary-color);
            transform: translateX(5px);
        }

        .footer-bottom {
            margin-top: 3rem;
            padding-top: 1.5rem;
            border-top: 1px solid rgba(148, 163, 184, 0.1);
            text-align: center;
            color: var(--secondary-color);
        }

        .foot-link {
            color: var(--secondary-color);
            text-decoration: none;
            display: inline-flex;
            margin-bottom: 0.5rem;
            transition: all 0.3s ease;
            
        }

        @media (max-width: 768px) {
            .hero-title {
                font-size: 2.5rem;
            }

            .hero-section {
                padding: 4rem 0;
            }

            .features-section {
                padding: 3rem 0;
            }

            .feature-card {
                margin-bottom: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg sticky-top">
        <div class="container">
            <a class="navbar-brand" href="#">
                <i class="bi bi-calendar-check"></i>
                S.A.S.E
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="login.php">
                            <i class="bi bi-box-arrow-in-right"></i>
                            Entrar
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="contato.php">
                            <i class="bi bi-chat-dots"></i>
                            Contato
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <section class="hero-section">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-8">
                    <h1 class="hero-title">Sistema de Agendamento de Salas de Estudo</h1>
                    <p class="hero-subtitle">
                        Simplifique o processo de reserva de salas de estudo. 
                        Gerencie seus horários de forma eficiente e organize seus estudos com facilidade.
                    </p>
                    
                    <div class="feat-card">
                        <a href="aluno.php" class="feat-title">
                        Faça o seu agendamento aqui
                        <i class="bi bi-arrow-right"></i> <!-- Ícone de seta para a direita -->
                    </a>
                </div>


            </div>
        </div>
    </section>

    <section class="features-section">
        <div class="container">
            <div class="row">
                <div class="col-md-4 mb-4">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="bi bi-calendar-check"></i>
                        </div>
                        <h3 class="feature-title">Agendamento Simplificado</h3>
                        <p class="feature-description">
                            Reserve salas de estudo com poucos cliques. 
                            Sistema intuitivo e fácil de usar.
                        </p>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="bi bi-clock"></i>
                        </div>
                        <h3 class="feature-title">Disponibilidade em Tempo Real</h3>
                        <p class="feature-description">
                            Visualize horários disponíveis instantaneamente.
                            Atualizações em tempo real.
                        </p>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="bi bi-shield-check"></i>
                        </div>
                        <h3 class="feature-title">Gestão Segura</h3>
                        <p class="feature-description">
                            Sistema seguro e confiável para gerenciar suas reservas.
                            Controle total sobre seus agendamentos.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <footer class="footer">
        <div class="container">
            <div class="row">
                <div class="col-md-4 mb-4">
                    <h4 class="footer-title">Sobre o S.A.S.E</h4>
                    <p class="text-muted">
                        Sistema desenvolvido para facilitar o agendamento 
                        e gerenciamento de salas de estudo no SENAI.
                    </p>
                </div>
                <div class="col-md-4 mb-4">
                    <h4 class="footer-title">Links Úteis</h4>
                    <a href="aluno.php" class="footer-link">Aluno</a>
                    <a href="login.php" class="footer-link">Login</a>
                    <a href="contato.php" class="footer-link">Contato</a>
                </div>
                <div class="col-md-4 mb-4">
                    <h4 class="footer-title">Contato</h4>
                    <p class="text-muted">
                        <i class="bi bi-telephone me-2"></i>
                        <a class="foot-link" href="https://wa.me/5563999943057?text=Preciso%20de%20ajuda%20com%20o%20sistema%20SUSE">Contato</a>
                    </p>
                </div>
            </div>
            <div class="footer-bottom">
                <p class="mb-0">&copy; 2024 S.A.S.E - Todos os direitos reservados</p>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
