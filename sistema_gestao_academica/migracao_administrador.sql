-- Migração segura para habilitar o perfil ADMINISTRADOR.
-- Execute uma única vez em um banco já existente.
-- Esta versão também cria a conta administrativa caso o CPF inicial ainda não exista.

USE gestao_academica;

ALTER TABLE usuarios
    MODIFY COLUMN perfil ENUM('ALUNO','INSTRUTOR','ADMIN') NOT NULL DEFAULT 'ALUNO';

-- Se o administrador inicial já existir, apenas promove o perfil e ativa a conta.
UPDATE usuarios
SET perfil = 'ADMIN',
    status = 'ATIVO'
WHERE REPLACE(REPLACE(REPLACE(cpf,'.',''),'-',''),' ','') = '12345678900';

-- Se ainda não existir, cria a conta administrativa com a senha padrão.
-- A senha padrão é: SesiSenai@2026
INSERT INTO usuarios (cpf, nome, email, senha_hash, senha_provisoria, perfil, status)
SELECT
    '123.456.789-00',
    'Administrador do Sistema',
    NULL,
    '$2y$12$ab9S100DDLckXCcvBdcPbeNqFYtZq181BcOUe8qS2Towe2IttVFZS',
    1,
    'ADMIN',
    'ATIVO'
WHERE NOT EXISTS (
    SELECT 1
    FROM usuarios
    WHERE REPLACE(REPLACE(REPLACE(cpf,'.',''),'-',''),' ','') = '12345678900'
);

-- O administrador não deve permanecer com registro de instrutor ativo.
UPDATE instrutores
SET ativo = 0
WHERE REPLACE(REPLACE(REPLACE(cpf,'.',''),'-',''),' ','') = '12345678900';
