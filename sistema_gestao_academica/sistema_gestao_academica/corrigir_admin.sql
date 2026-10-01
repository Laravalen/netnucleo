-- CORREÇÃO RÁPIDA DO ADMINISTRADOR
-- Use este arquivo se você já possui o banco gestao_academica e não consegue editar.
-- CPF inicial: 123.456.789-00
-- Senha inicial: SesiSenai@2026

USE gestao_academica;

ALTER TABLE usuarios
    MODIFY COLUMN perfil ENUM('ALUNO','INSTRUTOR','ADMIN') NOT NULL DEFAULT 'ALUNO';

UPDATE usuarios
SET perfil = 'ADMIN', status = 'ATIVO'
WHERE REPLACE(REPLACE(REPLACE(cpf,'.',''),'-',''),' ','') = '12345678900';

INSERT INTO usuarios (cpf,nome,email,senha_hash,senha_provisoria,perfil,status)
SELECT
    '123.456.789-00',
    'Administrador do Sistema',
    NULL,
    '$2y$12$ab9S100DDLckXCcvBdcPbeNqFYtZq181BcOUe8qS2Towe2IttVFZS',
    1,
    'ADMIN',
    'ATIVO'
WHERE NOT EXISTS (
    SELECT 1 FROM usuarios
    WHERE REPLACE(REPLACE(REPLACE(cpf,'.',''),'-',''),' ','') = '12345678900'
);

UPDATE instrutores
SET ativo = 0
WHERE REPLACE(REPLACE(REPLACE(cpf,'.',''),'-',''),' ','') = '12345678900';

SELECT id, cpf, nome, perfil, status, senha_provisoria
FROM usuarios
WHERE REPLACE(REPLACE(REPLACE(cpf,'.',''),'-',''),' ','') = '12345678900';
