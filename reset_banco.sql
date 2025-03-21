-- Excluir banco de dados se existir
DROP DATABASE IF EXISTS sase;

-- Criar banco de dados
CREATE DATABASE IF NOT EXISTS sase;

-- Usar o banco de dados
USE sase;

-- Configurar o charset e collation
SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- Deletar tabelas existentes se existirem
DROP TABLE IF EXISTS `usuarios`;
DROP TABLE IF EXISTS `reservas`;
DROP TABLE IF EXISTS `computadores`;
DROP TABLE IF EXISTS `mensagens`;
DROP TABLE IF EXISTS `reservas_sala`;
DROP TABLE IF EXISTS `mensagens_contato`;

-- Criar tabela de usuários
CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    senha VARCHAR(255) NOT NULL,
    nivel ENUM('admin', 'instrutor') NOT NULL,
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ultimo_acesso TIMESTAMP NULL,
    ativo BOOLEAN DEFAULT TRUE
);

-- Inserir usuário admin padrão (senha: admin123)
INSERT INTO usuarios (nome, email, senha, nivel) VALUES 
('Administrador', 'admin@sase.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');

-- Criar tabela de computadores
CREATE TABLE `computadores` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `computador_num` int(11) NOT NULL UNIQUE,
  `status` enum('disponivel', 'manutencao', 'ocupado') NOT NULL DEFAULT 'disponivel',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Criar tabela de reservas
CREATE TABLE `reservas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(255) NOT NULL,
  `cpf` varchar(14) NOT NULL,
  `curso` varchar(100) NOT NULL,
  `telefone` varchar(20) NOT NULL,
  `computador_num` int(11) NOT NULL,
  `inicio` datetime NOT NULL,
  `fim` datetime NOT NULL,
  `status` enum('pendente', 'aprovado', 'recusado') NOT NULL DEFAULT 'pendente',
  `data_criacao` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`computador_num`) REFERENCES `computadores`(`computador_num`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Criar tabela de mensagens
CREATE TABLE `mensagens` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `assunto` varchar(255) NOT NULL,
  `mensagem` text NOT NULL,
  `resposta` text,
  `respondido` tinyint(1) NOT NULL DEFAULT 0,
  `data_envio` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `data_resposta` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Criar tabela de reservas de sala
CREATE TABLE `reservas_sala` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `usuario_id` int(11) NOT NULL,
  `inicio` datetime NOT NULL,
  `fim` datetime NOT NULL,
  `motivo` text NOT NULL,
  `periodo` enum('manha', 'tarde', 'noite') NOT NULL,
  `status` enum('pendente', 'aprovado', 'recusado') NOT NULL DEFAULT 'pendente',
  `data_criacao` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`usuario_id`) REFERENCES `usuarios`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Criar tabela de mensagens de contato
CREATE TABLE IF NOT EXISTS mensagens_contato (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    telefone VARCHAR(20) NOT NULL,
    assunto VARCHAR(200) NOT NULL,
    mensagem TEXT NOT NULL,
    data_envio DATETIME NOT NULL,
    lida BOOLEAN DEFAULT FALSE
);

-- Inserir computadores padrão (10 computadores)
INSERT INTO `computadores` (`computador_num`, `status`) VALUES
(1, 'disponivel'),
(2, 'disponivel'),
(3, 'disponivel'),
(4, 'disponivel'),
(5, 'disponivel'),
(6, 'disponivel'),
(7, 'disponivel'),
(8, 'disponivel'),
(9, 'disponivel'),
(10, 'disponivel');

-- Criar trigger para atualizar status dos computadores quando uma reserva de sala é aprovada
DELIMITER //

-- Função para determinar o período com base no horário
CREATE FUNCTION get_periodo(hora TIME) 
RETURNS VARCHAR(10)
DETERMINISTIC
BEGIN
    RETURN CASE
        WHEN hora BETWEEN '07:00:00' AND '12:59:59' THEN 'manha'
        WHEN hora BETWEEN '13:00:00' AND '17:59:59' THEN 'tarde'
        WHEN hora BETWEEN '18:00:00' AND '22:00:00' THEN 'noite'
        ELSE NULL
    END;
END //

-- Trigger para verificar conflitos antes de inserir uma reserva de sala
CREATE TRIGGER before_reserva_sala_insert
BEFORE INSERT ON reservas_sala
FOR EACH ROW
BEGIN
    DECLARE conflito_aluno INT;
    DECLARE periodo_inicio VARCHAR(10);
    DECLARE periodo_fim VARCHAR(10);
    
    -- Determinar períodos das horas de início e fim
    SET periodo_inicio = get_periodo(TIME(NEW.inicio));
    SET periodo_fim = get_periodo(TIME(NEW.fim));
    
    -- Verificar se já existe reserva de aluno para o mesmo período
    SELECT COUNT(*) INTO conflito_aluno
    FROM reservas
    WHERE status = 'aprovado'
    AND DATE(inicio) = DATE(NEW.inicio)
    AND (
        (get_periodo(TIME(inicio)) = NEW.periodo)
        OR 
        (get_periodo(TIME(fim)) = NEW.periodo)
    );
    
    -- Se houver conflito, impedir a inserção
    IF conflito_aluno > 0 THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Não é possível reservar a sala neste período pois já existe uma reserva de aluno aprovada. Por favor, escolha outro período ou entre em contato com a coordenação.';
    END IF;
END //

-- Trigger para verificar conflitos antes de atualizar uma reserva de sala
CREATE TRIGGER before_reserva_sala_update
BEFORE UPDATE ON reservas_sala
FOR EACH ROW
BEGIN
    DECLARE conflito_aluno INT;
    
    -- Só verificar conflitos se a reserva estiver sendo aprovada
    IF NEW.status = 'aprovado' AND OLD.status != 'aprovado' THEN
        -- Verificar se já existe reserva de aluno para o mesmo período
        SELECT COUNT(*) INTO conflito_aluno
        FROM reservas
        WHERE status = 'aprovado'
        AND DATE(inicio) = DATE(NEW.inicio)
        AND (
            (get_periodo(TIME(inicio)) = NEW.periodo)
            OR 
            (get_periodo(TIME(fim)) = NEW.periodo)
        );
        
        -- Se houver conflito, impedir a atualização
        IF conflito_aluno > 0 THEN
            SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Não é possível aprovar a reserva da sala neste período pois já existe uma reserva de aluno aprovada. Por favor, escolha outro período ou entre em contato com a coordenação.';
        END IF;
    END IF;
END //

-- Trigger para verificar conflitos antes de inserir uma reserva de aluno
CREATE TRIGGER before_reserva_insert
BEFORE INSERT ON reservas
FOR EACH ROW
BEGIN
    DECLARE conflito_sala INT;
    DECLARE periodo_inicio VARCHAR(10);
    DECLARE periodo_fim VARCHAR(10);
    
    -- Determinar períodos das horas de início e fim
    SET periodo_inicio = get_periodo(TIME(NEW.inicio));
    SET periodo_fim = get_periodo(TIME(NEW.fim));
    
    -- Verificar se já existe reserva de sala para os períodos
    SELECT COUNT(*) INTO conflito_sala
    FROM reservas_sala
    WHERE status = 'aprovado'
    AND DATE(inicio) = DATE(NEW.inicio)
    AND (
        periodo = periodo_inicio
        OR 
        periodo = periodo_fim
    );
    
    -- Se houver conflito, impedir a inserção
    IF conflito_sala > 0 THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Não é possível fazer a reserva neste horário pois a sala já está reservada por um instrutor para todo o período. Por favor, escolha outro horário ou entre em contato com a coordenação.';
    END IF;
END //

-- Trigger para verificar conflitos antes de atualizar uma reserva de aluno
CREATE TRIGGER before_reserva_update
BEFORE UPDATE ON reservas
FOR EACH ROW
BEGIN
    DECLARE conflito_sala INT;
    DECLARE periodo_inicio VARCHAR(10);
    DECLARE periodo_fim VARCHAR(10);
    
    -- Só verificar conflitos se a reserva estiver sendo aprovada
    IF NEW.status = 'aprovado' AND OLD.status != 'aprovado' THEN
        -- Determinar períodos das horas de início e fim
        SET periodo_inicio = get_periodo(TIME(NEW.inicio));
        SET periodo_fim = get_periodo(TIME(NEW.fim));
        
        -- Verificar se já existe reserva de sala para os períodos
        SELECT COUNT(*) INTO conflito_sala
        FROM reservas_sala
        WHERE status = 'aprovado'
        AND DATE(inicio) = DATE(NEW.inicio)
        AND (
            periodo = periodo_inicio
            OR 
            periodo = periodo_fim
        );
        
        -- Se houver conflito, impedir a atualização
        IF conflito_sala > 0 THEN
            SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Não é possível aprovar esta reserva pois a sala já está reservada por um instrutor para todo o período. Por favor, escolha outro horário ou entre em contato com a coordenação.';
        END IF;
    END IF;
END //

CREATE TRIGGER after_reserva_sala_update
AFTER UPDATE ON reservas_sala
FOR EACH ROW
BEGIN
    IF NEW.status = 'aprovado' AND OLD.status != 'aprovado' THEN
        -- Marca todos os computadores como ocupados durante o período da reserva
        UPDATE computadores SET status = 'ocupado';
    ELSEIF NEW.status != 'aprovado' AND OLD.status = 'aprovado' THEN
        -- Libera os computadores quando a reserva é cancelada/recusada
        UPDATE computadores SET status = 'disponivel' WHERE status = 'ocupado';
    END IF;
END //

DELIMITER ;

SET FOREIGN_KEY_CHECKS = 1;