USE gestao_academica;

/*
   DADOS DE DEMONSTRAÇÃO DO SGA
   Execute uma vez no banco que já está em uso.
   Os registros criados por este arquivo usam datas relativas a CURDATE(),
   portanto continuam aparecendo no calendário mesmo depois que o projeto
   for instalado em outra data.
*/

SET @admin_id := (SELECT id FROM usuarios WHERE REPLACE(REPLACE(REPLACE(cpf,'.',''),'-',''),' ','')='12345678900' LIMIT 1);
SET @inst1 := (SELECT id FROM instrutores WHERE cpf='222.222.222-22' LIMIT 1);
SET @inst2 := (SELECT id FROM instrutores WHERE cpf='333.333.333-33' LIMIT 1);
SET @inst3 := (SELECT id FROM instrutores WHERE cpf='444.444.444-44' LIMIT 1);
SET @turma1 := (SELECT id FROM turmas WHERE codigo='ELE-2025-A' LIMIT 1);
SET @turma2 := (SELECT id FROM turmas WHERE codigo='MEC-2025-A' LIMIT 1);
SET @turma3 := (SELECT id FROM turmas WHERE codigo='TI-2025-A' LIMIT 1);
SET @disc1 := (SELECT id FROM disciplinas WHERE codigo='ELE01' LIMIT 1);
SET @disc2 := (SELECT id FROM disciplinas WHERE codigo='DES01' LIMIT 1);
SET @disc3 := (SELECT id FROM disciplinas WHERE codigo='RED01' LIMIT 1);
SET @salaLab03 := (SELECT id FROM salas WHERE codigo='LAB03' LIMIT 1);
SET @salaLabTI := (SELECT id FROM salas WHERE codigo='LABTI02' LIMIT 1);
SET @sala05 := (SELECT id FROM salas WHERE codigo='SALA05' LIMIT 1);

/* Remove somente os exemplos deste arquivo, caso ele seja executado novamente. */
DELETE FROM movimentacoes WHERE motivo LIKE 'DADOS_EXEMPLO_SGA:%';
DELETE FROM aulas WHERE observacoes='DADOS_EXEMPLO_SGA';
DELETE FROM eventos_calendario WHERE titulo LIKE 'DEMO SGA:%';

/* Aulas de hoje e dos próximos dias. */
INSERT INTO aulas
(turma_id,disciplina_id,instrutor_id,sala_id,data_aula,periodo,inicio,fim,tipo,status,conteudo,observacoes)
VALUES
(@turma1,@disc1,@inst1,@salaLab03,CURDATE(),'MANHA','07:00','08:40','TEORICA','PLANEJADA','Circuitos elétricos e segurança em laboratório','DADOS_EXEMPLO_SGA'),
(@turma3,@disc3,@inst2,@salaLabTI,DATE_ADD(CURDATE(),INTERVAL 1 DAY),'NOITE','19:00','20:40','PRATICA','PLANEJADA','Configuração de redes locais','DADOS_EXEMPLO_SGA'),
(@turma2,@disc2,@inst3,@sala05,DATE_ADD(CURDATE(),INTERVAL 2 DAY),'TARDE','13:00','14:40','TEORICA','PLANEJADA','Leitura e interpretação de desenho técnico','DADOS_EXEMPLO_SGA'),
(@turma1,@disc1,@inst1,@salaLab03,DATE_ADD(CURDATE(),INTERVAL 3 DAY),'MANHA','07:00','08:40','PRATICA','PLANEJADA','Medições com multímetro','DADOS_EXEMPLO_SGA'),
(@turma3,@disc3,@inst2,@salaLabTI,DATE_ADD(CURDATE(),INTERVAL 6 DAY),'NOITE','19:00','20:40','PRATICA','PLANEJADA','Montagem de rede de laboratório','DADOS_EXEMPLO_SGA');

/* Eventos para preencher o calendário com informações não relacionadas a aulas. */
INSERT INTO eventos_calendario(titulo,descricao,data_inicio,data_fim,tipo,ativo)
VALUES
('DEMO SGA: Reunião pedagógica','Reunião de acompanhamento das turmas.',CURDATE(),CURDATE(),'PEDAGOGICO',1),
('DEMO SGA: Avaliação prática','Avaliação prática prevista para a turma.',DATE_ADD(CURDATE(),INTERVAL 4 DAY),DATE_ADD(CURDATE(),INTERVAL 4 DAY),'AVALIACAO',1),
('DEMO SGA: Semana acadêmica','Atividades acadêmicas e palestras.',DATE_ADD(CURDATE(),INTERVAL 8 DAY),DATE_ADD(CURDATE(),INTERVAL 10 DAY),'ACADEMICO',1);

/* Uma movimentação de exemplo para aparecer no calendário e na tela de movimentações. */
INSERT INTO movimentacoes
(tipo,status,data_movimentacao,turma_id,aula_id,sala_de,sala_para,motivo,criado_por)
SELECT
    'TROCA_HORARIO','PENDENTE',DATE_ADD(CURDATE(),INTERVAL 5 DAY),
    @turma1,
    a.id,
    @salaLab03,
    @sala05,
    'DADOS_EXEMPLO_SGA: troca de sala para atividade especial.',
    @admin_id
FROM aulas a
WHERE a.observacoes='DADOS_EXEMPLO_SGA'
ORDER BY a.data_aula
LIMIT 1;

/* Deixa o laboratório de exemplo marcado como reservado. */
UPDATE salas
SET status='RESERVADA'
WHERE codigo='LAB03';

SELECT 'Dados de demonstração inseridos com sucesso.' AS resultado;
