CREATE DATABASE IF NOT EXISTS gestao_academica
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE gestao_academica;

SET FOREIGN_KEY_CHECKS=0;
DROP TABLE IF EXISTS auditoria, notas, avaliacoes, frequencias, movimentacoes,
eventos_calendario, aulas, matriculas, turma_disciplinas, salas, turmas,
disciplinas, cursos, alunos, instrutores, usuarios;
SET FOREIGN_KEY_CHECKS=1;

CREATE TABLE usuarios (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    cpf VARCHAR(14) NOT NULL UNIQUE,
    nome VARCHAR(150) NOT NULL,
    email VARCHAR(180) UNIQUE,
    senha_hash VARCHAR(255) NOT NULL,
    perfil ENUM('ALUNO','INSTRUTOR') NOT NULL DEFAULT 'ALUNO',
    status ENUM('ATIVO','INATIVO') NOT NULL DEFAULT 'ATIVO',
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    atualizado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE instrutores (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    usuario_id BIGINT UNSIGNED NOT NULL UNIQUE,
    cpf VARCHAR(14) NOT NULL UNIQUE,
    area VARCHAR(120) NOT NULL,
    email VARCHAR(180),
    ativo TINYINT(1) NOT NULL DEFAULT 1,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE alunos (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    usuario_id BIGINT UNSIGNED NOT NULL UNIQUE,
    matricula VARCHAR(40) NOT NULL UNIQUE,
    data_nascimento DATE,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE cursos (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    codigo VARCHAR(40) NOT NULL UNIQUE,
    nome VARCHAR(150) NOT NULL,
    descricao TEXT,
    carga_horaria INT,
    ativo TINYINT(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB;

CREATE TABLE disciplinas (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    codigo VARCHAR(40) NOT NULL UNIQUE,
    nome VARCHAR(150) NOT NULL,
    descricao TEXT,
    carga_horaria INT,
    ativo TINYINT(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB;

CREATE TABLE turmas (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    codigo VARCHAR(40) NOT NULL UNIQUE,
    nome VARCHAR(150) NOT NULL,
    curso_id BIGINT UNSIGNED NOT NULL,
    periodo ENUM('MANHA','TARDE','NOITE') NOT NULL,
    data_inicio DATE NOT NULL,
    data_fim DATE,
    capacidade INT NOT NULL DEFAULT 30,
    status ENUM('ATIVA','ENCERRADA','CANCELADA') NOT NULL DEFAULT 'ATIVA',
    encerrada_em DATE,
    FOREIGN KEY (curso_id) REFERENCES cursos(id)
) ENGINE=InnoDB;

CREATE TABLE turma_disciplinas (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    turma_id BIGINT UNSIGNED NOT NULL,
    disciplina_id BIGINT UNSIGNED NOT NULL,
    instrutor_id BIGINT UNSIGNED,
    carga_horaria INT,
    UNIQUE KEY uq_turma_disciplina (turma_id, disciplina_id),
    FOREIGN KEY (turma_id) REFERENCES turmas(id) ON DELETE CASCADE,
    FOREIGN KEY (disciplina_id) REFERENCES disciplinas(id),
    FOREIGN KEY (instrutor_id) REFERENCES instrutores(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE matriculas (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    aluno_id BIGINT UNSIGNED NOT NULL,
    turma_id BIGINT UNSIGNED NOT NULL,
    data_matricula DATE NOT NULL,
    status ENUM('ATIVA','CONCLUIDA','CANCELADA') NOT NULL DEFAULT 'ATIVA',
    data_saida DATE,
    UNIQUE KEY uq_aluno_turma (aluno_id, turma_id),
    FOREIGN KEY (aluno_id) REFERENCES alunos(id) ON DELETE CASCADE,
    FOREIGN KEY (turma_id) REFERENCES turmas(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE salas (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    codigo VARCHAR(30) NOT NULL UNIQUE,
    nome VARCHAR(100) NOT NULL,
    bloco VARCHAR(50),
    tipo ENUM('LABORATORIO','SALA_TEORICA','AUDITORIO') NOT NULL,
    capacidade INT NOT NULL,
    descricao TEXT,
    status ENUM('DISPONIVEL','OCUPADA','RESERVADA','MANUTENCAO') NOT NULL DEFAULT 'DISPONIVEL',
    ativo TINYINT(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB;

CREATE TABLE aulas (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    turma_id BIGINT UNSIGNED NOT NULL,
    disciplina_id BIGINT UNSIGNED NOT NULL,
    instrutor_id BIGINT UNSIGNED NOT NULL,
    sala_id BIGINT UNSIGNED NOT NULL,
    data_aula DATE NOT NULL,
    periodo ENUM('MANHA','TARDE','NOITE') NOT NULL,
    inicio TIME NOT NULL,
    fim TIME NOT NULL,
    tipo ENUM('TEORICA','PRATICA') NOT NULL,
    status VARCHAR(30) NOT NULL DEFAULT 'PLANEJADA',
    conteudo TEXT,
    observacoes TEXT,
    FOREIGN KEY (turma_id) REFERENCES turmas(id),
    FOREIGN KEY (disciplina_id) REFERENCES disciplinas(id),
    FOREIGN KEY (instrutor_id) REFERENCES instrutores(id),
    FOREIGN KEY (sala_id) REFERENCES salas(id),
    INDEX idx_aula_data (data_aula),
    INDEX idx_aula_instrutor (instrutor_id,data_aula),
    INDEX idx_aula_sala (sala_id,data_aula)
) ENGINE=InnoDB;

CREATE TABLE frequencias (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    aula_id BIGINT UNSIGNED NOT NULL,
    aluno_id BIGINT UNSIGNED NOT NULL,
    status ENUM('PRESENTE','AUSENTE','JUSTIFICADA') NOT NULL,
    observacao TEXT,
    registrada_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_frequencia (aula_id,aluno_id),
    FOREIGN KEY (aula_id) REFERENCES aulas(id) ON DELETE CASCADE,
    FOREIGN KEY (aluno_id) REFERENCES alunos(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE avaliacoes (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    turma_disciplina_id BIGINT UNSIGNED NOT NULL,
    titulo VARCHAR(120) NOT NULL,
    data_avaliacao DATE,
    peso DECIMAL(6,2) NOT NULL DEFAULT 1,
    nota_maxima DECIMAL(6,2) NOT NULL DEFAULT 10,
    FOREIGN KEY (turma_disciplina_id) REFERENCES turma_disciplinas(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE notas (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    avaliacao_id BIGINT UNSIGNED NOT NULL,
    aluno_id BIGINT UNSIGNED NOT NULL,
    nota DECIMAL(6,2) NOT NULL,
    observacao TEXT,
    UNIQUE KEY uq_nota (avaliacao_id,aluno_id),
    FOREIGN KEY (avaliacao_id) REFERENCES avaliacoes(id) ON DELETE CASCADE,
    FOREIGN KEY (aluno_id) REFERENCES alunos(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE movimentacoes (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tipo ENUM('SUBSTITUICAO','TROCA_HORARIO','CANCELAMENTO','REAGENDAMENTO','OUTRA') NOT NULL,
    status ENUM('PENDENTE','APROVADO','CANCELADO','AGUARDANDO') NOT NULL DEFAULT 'PENDENTE',
    data_movimentacao DATE NOT NULL,
    turma_id BIGINT UNSIGNED NOT NULL,
    aula_id BIGINT UNSIGNED,
    instrutor_de BIGINT UNSIGNED,
    instrutor_para BIGINT UNSIGNED,
    sala_de BIGINT UNSIGNED,
    sala_para BIGINT UNSIGNED,
    motivo TEXT NOT NULL,
    criado_por BIGINT UNSIGNED NOT NULL,
    aprovado_por BIGINT UNSIGNED,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    aprovado_em TIMESTAMP NULL,
    FOREIGN KEY (turma_id) REFERENCES turmas(id),
    FOREIGN KEY (aula_id) REFERENCES aulas(id) ON DELETE SET NULL,
    FOREIGN KEY (instrutor_de) REFERENCES instrutores(id) ON DELETE SET NULL,
    FOREIGN KEY (instrutor_para) REFERENCES instrutores(id) ON DELETE SET NULL,
    FOREIGN KEY (sala_de) REFERENCES salas(id) ON DELETE SET NULL,
    FOREIGN KEY (sala_para) REFERENCES salas(id) ON DELETE SET NULL,
    FOREIGN KEY (criado_por) REFERENCES usuarios(id),
    FOREIGN KEY (aprovado_por) REFERENCES usuarios(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE eventos_calendario (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(180) NOT NULL,
    descricao TEXT,
    data_inicio DATE NOT NULL,
    data_fim DATE,
    tipo VARCHAR(60) NOT NULL,
    turma_id BIGINT UNSIGNED,
    ativo TINYINT(1) NOT NULL DEFAULT 1,
    FOREIGN KEY (turma_id) REFERENCES turmas(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE auditoria (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    usuario_id BIGINT UNSIGNED,
    tabela VARCHAR(80) NOT NULL,
    registro_id BIGINT,
    operacao VARCHAR(10) NOT NULL,
    dados_anteriores JSON,
    dados_novos JSON,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- Login inicial do instrutor solicitado:
-- CPF: 123.456.789-00 | Senha inicial: 123456
INSERT INTO usuarios (cpf,nome,email,senha_hash,perfil)
VALUES ('123.456.789-00','Instrutor Administrador','admin@senai.local',
'$2y$12$QoIY7.zH/0My9HuHHqtzi.UjZVT3jEw9qIBMrjxQnuwVPdtZuAjoq','INSTRUTOR');

INSERT INTO instrutores (usuario_id,cpf,area,email)
SELECT id,cpf,'Administração','admin@senai.local'
FROM usuarios WHERE cpf='123.456.789-00';

INSERT INTO usuarios (cpf,nome,email,senha_hash,perfil)
VALUES ('111.111.111-11','Aluno Demonstração','aluno@senai.local',
'$2y$12$QoIY7.zH/0My9HuHHqtzi.UjZVT3jEw9qIBMrjxQnuwVPdtZuAjoq','ALUNO');

INSERT INTO alunos (usuario_id,matricula,data_nascimento)
SELECT id,'ALU-2025-001','2008-05-10'
FROM usuarios WHERE cpf='111.111.111-11';

INSERT INTO cursos(codigo,nome,carga_horaria) VALUES
('ELE','Técnico em Eletrotécnica',1200),
('MEC','Técnico em Mecânica',1200),
('TI','Técnico em Informática',1200),
('SEG','Técnico em Segurança',1200);

INSERT INTO disciplinas(codigo,nome,carga_horaria) VALUES
('ELE01','Eletrotécnica Industrial',80),
('CNC01','Programação CNC',80),
('RED01','Redes de Computadores',80),
('DES01','Desenho Técnico',60),
('AUT01','Automação Industrial',80),
('INS01','Instalações Elétricas',80);

INSERT INTO turmas(codigo,nome,curso_id,periodo,data_inicio,data_fim,capacidade,status) VALUES
('ELE-2025-A','ELE-2025-A',(SELECT id FROM cursos WHERE codigo='ELE'),'MANHA','2025-02-03','2025-11-28',30,'ATIVA'),
('MEC-2025-A','MEC-2025-A',(SELECT id FROM cursos WHERE codigo='MEC'),'TARDE','2025-02-03','2025-11-28',30,'ATIVA'),
('MEC-2025-B','MEC-2025-B',(SELECT id FROM cursos WHERE codigo='MEC'),'MANHA','2025-02-03','2025-11-28',30,'ATIVA'),
('TI-2025-A','TI-2025-A',(SELECT id FROM cursos WHERE codigo='TI'),'NOITE','2025-02-05','2025-12-05',30,'ATIVA'),
('TI-2025-B','TI-2025-B',(SELECT id FROM cursos WHERE codigo='TI'),'NOITE','2025-02-05','2025-12-05',30,'ATIVA'),
('SEG-2025-A','SEG-2025-A',(SELECT id FROM cursos WHERE codigo='SEG'),'NOITE','2025-02-07','2025-11-21',30,'ATIVA'),
('ELE-2024-B','ELE-2024-B',(SELECT id FROM cursos WHERE codigo='ELE'),'TARDE','2024-02-05','2024-11-29',30,'ENCERRADA');

INSERT INTO salas(codigo,nome,bloco,tipo,capacidade,descricao,status) VALUES
('LAB03','Lab 03','Bloco A','LABORATORIO',30,'Bancadas, multímetros, fontes de tensão','DISPONIVEL'),
('LABTI02','Lab TI 02','Bloco C','LABORATORIO',28,'30 computadores, servidor de laboratório','DISPONIVEL'),
('LABMEC01','Lab Mec 01','Bloco B','LABORATORIO',28,'Tornos, fresadoras, ferramentas manuais','OCUPADA'),
('LABTI01','Lab TI 01','Bloco C','LABORATORIO',24,'Laboratório de informática','RESERVADA'),
('SALA05','Sala 05','Bloco D','SALA_TEORICA',40,'Projetor 4K, quadro branco, ar-condicionado','DISPONIVEL'),
('AUD01','Auditório','Bloco Principal','AUDITORIO',120,'Auditório principal','DISPONIVEL');

INSERT INTO usuarios(cpf,nome,email,senha_hash,perfil) VALUES
('222.222.222-22','Carlos Mendes','carlos@senai.local','$2y$12$QoIY7.zH/0My9HuHHqtzi.UjZVT3jEw9qIBMrjxQnuwVPdtZuAjoq','INSTRUTOR'),
('333.333.333-33','Ana Paula Souza','ana@senai.local','$2y$12$QoIY7.zH/0My9HuHHqtzi.UjZVT3jEw9qIBMrjxQnuwVPdtZuAjoq','INSTRUTOR'),
('444.444.444-44','Roberto Lima','roberto@senai.local','$2y$12$QoIY7.zH/0My9HuHHqtzi.UjZVT3jEw9qIBMrjxQnuwVPdtZuAjoq','INSTRUTOR'),
('555.555.555-55','Fernanda Castro','fernanda@senai.local','$2y$12$QoIY7.zH/0My9HuHHqtzi.UjZVT3jEw9qIBMrjxQnuwVPdtZuAjoq','INSTRUTOR'),
('666.666.666-66','Marcos Oliveira','marcos@senai.local','$2y$12$QoIY7.zH/0My9HuHHqtzi.UjZVT3jEw9qIBMrjxQnuwVPdtZuAjoq','INSTRUTOR'),
('777.777.777-77','Juliana Ferreira','juliana@senai.local','$2y$12$QoIY7.zH/0My9HuHHqtzi.UjZVT3jEw9qIBMrjxQnuwVPdtZuAjoq','INSTRUTOR');

INSERT INTO instrutores(usuario_id,cpf,area,email)
SELECT id,cpf,'Docência',email FROM usuarios WHERE cpf IN
('222.222.222-22','333.333.333-33','444.444.444-44','555.555.555-55','666.666.666-66','777.777.777-77');

INSERT INTO turma_disciplinas(turma_id,disciplina_id,instrutor_id,carga_horaria)
SELECT t.id,d.id,i.id,d.carga_horaria
FROM turmas t
JOIN disciplinas d ON
 (t.codigo='ELE-2025-A' AND d.codigo IN ('ELE01','INS01'))
JOIN instrutores i ON i.cpf='222.222.222-22'
WHERE t.codigo='ELE-2025-A';

INSERT INTO aulas(turma_id,disciplina_id,instrutor_id,sala_id,data_aula,periodo,inicio,fim,tipo,status)
VALUES
((SELECT id FROM turmas WHERE codigo='ELE-2025-A'),(SELECT id FROM disciplinas WHERE codigo='ELE01'),(SELECT id FROM instrutores WHERE cpf='222.222.222-22'),(SELECT id FROM salas WHERE codigo='LAB03'),'2025-08-07','MANHA','07:00','08:40','TEORICA','PLANEJADA'),
((SELECT id FROM turmas WHERE codigo='TI-2025-A'),(SELECT id FROM disciplinas WHERE codigo='CNC01'),(SELECT id FROM instrutores WHERE cpf='333.333.333-33'),(SELECT id FROM salas WHERE codigo='LABMEC01'),'2025-08-07','MANHA','07:00','08:40','PRATICA','PLANEJADA'),
((SELECT id FROM turmas WHERE codigo='TI-2025-A'),(SELECT id FROM disciplinas WHERE codigo='RED01'),(SELECT id FROM instrutores WHERE cpf='444.444.444-44'),(SELECT id FROM salas WHERE codigo='LABTI02'),'2025-08-07','MANHA','07:00','08:40','PRATICA','PLANEJADA'),
((SELECT id FROM turmas WHERE codigo='MEC-2025-A'),(SELECT id FROM disciplinas WHERE codigo='DES01'),(SELECT id FROM instrutores WHERE cpf='555.555.555-55'),(SELECT id FROM salas WHERE codigo='SALA05'),'2025-08-07','TARDE','13:00','14:40','TEORICA','PLANEJADA'),
((SELECT id FROM turmas WHERE codigo='MEC-2025-B'),(SELECT id FROM disciplinas WHERE codigo='AUT01'),(SELECT id FROM instrutores WHERE cpf='666.666.666-66'),(SELECT id FROM salas WHERE codigo='LABMEC01'),'2025-08-07','TARDE','13:00','14:40','PRATICA','PLANEJADA'),
((SELECT id FROM turmas WHERE codigo='ELE-2025-A'),(SELECT id FROM disciplinas WHERE codigo='INS01'),(SELECT id FROM instrutores WHERE cpf='222.222.222-22'),(SELECT id FROM salas WHERE codigo='LAB03'),'2025-08-08','MANHA','07:00','08:40','PRATICA','PLANEJADA');

INSERT INTO eventos_calendario(titulo,descricao,data_inicio,data_fim,tipo)
VALUES
('Início das aulas','Início do calendário letivo','2025-02-03','2025-02-03','ACADEMICO'),
('Feriado municipal','Não haverá aulas','2025-08-09','2025-08-09','FERIADO'),
('Encerramento das turmas','Encerramento do período letivo','2025-11-28','2025-11-28','ACADEMICO');
