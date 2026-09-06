CREATE DATABASE IF NOT EXISTS gestao_academica DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE gestao_academica;

-- Tabela de Usuários (Login e Permissões)
CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    senha VARCHAR(255) NOT NULL, -- Recomenda-se armazenar hash (password_hash)
    tipo_usuario ENUM('instrutor', 'aluno') NOT NULL DEFAULT 'aluno',
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabela de Cursos / Disciplinas
CREATE TABLE disciplinas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    codigo VARCHAR(20) NOT NULL UNIQUE,
    carga_horaria INT NOT NULL,
    descricao TEXT,
    instrutor_id INT,
    FOREIGN KEY (instrutor_id) REFERENCES usuarios(id) ON DELETE SET NULL
);

-- Tabela de Matrículas (Vínculo Aluno - Disciplina)
CREATE TABLE matriculas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    aluno_id INT NOT NULL,
    disciplina_id INT NOT NULL,
    nota_final DECIMAL(4,2) DEFAULT NULL,
    frequencia DECIMAL(5,2) DEFAULT NULL,
    FOREIGN KEY (aluno_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (disciplina_id) REFERENCES disciplinas(id) ON DELETE CASCADE,
    UNIQUE KEY aluno_disciplina_unique (aluno_id, disciplina_id)
);

-- Inserção de Dados para Testes
INSERT INTO usuarios (nome, email, senha, tipo_usuario) VALUES 
('Prof. Carlos Andrade', 'instrutor@escola.com', '$2y$10$e0MYzXyjpJS7Pd0RVvHwHe1f8x5X/0zJ2P24h1Lg.V5X22/7r6E.', 'instrutor'), -- senha: 123
('João Silva', 'aluno@escola.com', '$2y$10$e0MYzXyjpJS7Pd0RVvHwHe1f8x5X/0zJ2P24h1Lg.V5X22/7r6E.', 'aluno'); -- senha: 123

INSERT INTO disciplinas (nome, codigo, carga_horaria, descricao, instrutor_id) VALUES 
('Desenvolvimento Web', 'WEB101', 80, 'Aulas de HTML, CSS, JS e PHP', 1),
('Banco de Dados Relacionais', 'BD201', 60, 'Modelagem e MySQL', 1);

INSERT INTO matriculas (aluno_id, disciplina_id, nota_final, frequencia) VALUES 
(2, 1, 9.5, 92.00),
(2, 2, 8.0, 88.50);
