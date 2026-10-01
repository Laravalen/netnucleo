USE gestao_academica;

-- Execute apenas se o banco já existir e você NÃO for importar o database.sql novamente.

ALTER TABLE usuarios
    ADD COLUMN senha_provisoria TINYINT(1) NOT NULL DEFAULT 1
    AFTER senha_hash;

-- Substitui a antiga senha inicial (123456) pela nova senha padrão.
-- Hash de: SesiSenai@2026
UPDATE usuarios
SET
    senha_hash = '$2y$12$ab9S100DDLckXCcvBdcPbeNqFYtZq181BcOUe8qS2Towe2IttVFZS',
    senha_provisoria = 1;

-- A partir daqui, novos alunos e instrutores devem ser inseridos
-- diretamente no banco de dados, nunca pela interface web.

-- EXEMPLO DE NOVO INSTRUTOR:
-- 1) Crie o usuário:
-- INSERT INTO usuarios
-- (cpf, nome, email, senha_hash, senha_provisoria, perfil, status)
-- VALUES
-- ('999.999.999-99', 'Nome do Instrutor', 'instrutor@senai.local',
--  '$2y$12$ab9S100DDLckXCcvBdcPbeNqFYtZq181BcOUe8qS2Towe2IttVFZS', 1, 'INSTRUTOR', 'ATIVO');
--
-- 2) Crie o registro de instrutor usando o mesmo CPF:
-- INSERT INTO instrutores (usuario_id, cpf, area, email)
-- SELECT id, cpf, 'Docência', email
-- FROM usuarios
-- WHERE cpf = '999.999.999-99';

-- EXEMPLO DE NOVO ALUNO:
-- INSERT INTO usuarios
-- (cpf, nome, email, senha_hash, senha_provisoria, perfil, status)
-- VALUES
-- ('888.888.888-88', 'Nome do Aluno', 'aluno@senai.local',
--  '$2y$12$ab9S100DDLckXCcvBdcPbeNqFYtZq181BcOUe8qS2Towe2IttVFZS', 1, 'ALUNO', 'ATIVO');
--
-- INSERT INTO alunos (usuario_id, matricula, data_nascimento)
-- SELECT id, 'ALU-2026-000001', '2009-01-15'
-- FROM usuarios
-- WHERE cpf = '888.888.888-88';
