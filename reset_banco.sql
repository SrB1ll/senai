-- Resetar e recriar o banco de dados
DROP DATABASE IF EXISTS teste;
CREATE DATABASE teste;
USE teste;

-- Tabela de professores (para login dos instrutores)
CREATE TABLE professores (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    senha VARCHAR(255) NOT NULL
);

-- Tabela de usuários COPED
CREATE TABLE coped_usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    senha VARCHAR(255) NOT NULL
);

-- Tabela de computadores
CREATE TABLE computadores (
    id INT AUTO_INCREMENT PRIMARY KEY,
    computador_num INT NOT NULL UNIQUE,
    status ENUM('livre', 'ocupado') DEFAULT 'livre'
);

-- Tabela de reservas
CREATE TABLE reservas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    matricula VARCHAR(20) NOT NULL,
    curso VARCHAR(100) NOT NULL,
    telefone VARCHAR(20) NOT NULL,
    computador_num INT NOT NULL,
    inicio DATETIME NOT NULL,
    fim DATETIME NOT NULL,
    status ENUM('pendente', 'aprovado', 'recusado') DEFAULT 'pendente',
    FOREIGN KEY (computador_num) REFERENCES computadores(computador_num)
);

-- Tabela de reservas de sala (para instrutores)
CREATE TABLE reservas_sala (
    id INT AUTO_INCREMENT PRIMARY KEY,
    instrutor_nome VARCHAR(100) NOT NULL,
    professor_id INT NOT NULL,
    telefone VARCHAR(20) NOT NULL,
    inicio DATETIME NOT NULL,
    fim DATETIME NOT NULL,
    motivo TEXT NOT NULL,
    status ENUM('pendente', 'aprovado', 'recusado') DEFAULT 'pendente',
    FOREIGN KEY (professor_id) REFERENCES professores(id)
);

-- Inserir computadores iniciais
INSERT INTO computadores (computador_num, status) VALUES 
(1, 'livre'), (2, 'livre'), (3, 'livre'), (4, 'livre'), (5, 'livre'),
(6, 'livre'), (7, 'livre'), (8, 'livre'), (9, 'livre'), (10, 'livre');

-- Inserir usuário instrutor padrão
INSERT INTO professores (nome, email, senha) VALUES 
('Instrutor Lab', 'instrutor@lab.com', '$2y$10$8VQD/imK.UhJ7BkHGZLsAOyy6mI.tDH7vhkVVAVmVUE3oTaETkfbu');

-- Inserir usuário COPED padrão
INSERT INTO coped_usuarios (nome, email, senha) VALUES 
('Administrador COPED', 'admin@coped.com', '$2y$10$8VQD/imK.UhJ7BkHGZLsAOyy6mI.tDH7vhkVVAVmVUE3oTaETkfbu');

-- Senhas dos usuários acima:
-- instrutor@lab.com: instrutor123
-- admin@coped.com: coped123 