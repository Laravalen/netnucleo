<?php
require_once __DIR__.'/functions.php'; require_login(); $title='Painel Geral';
$aulas=count_value("SELECT COUNT(*) FROM aulas WHERE data_aula=CURDATE()");
$instrutores=count_value("SELECT COUNT(*) FROM instrutores WHERE ativo=1");
$salas=count_value("SELECT COUNT(*) FROM salas WHERE ativo=1 AND status='DISPONIVEL'");
$turmas=count_value("SELECT COUNT(*) FROM turmas WHERE status='ATIVA'");
$agenda=db()->query("SELECT a.*,d.nome materia,t.codigo turma,i.nome instrutor,s.nome sala FROM aulas a JOIN disciplinas d ON d.id=a.disciplina_id JOIN turmas t ON t.id=a.turma_id JOIN instrutores ii ON ii.id=a.instrutor_id JOIN usuarios i ON i.id=ii.usuario_id JOIN salas s ON s.id=a.sala_id ORDER BY a.data_aula,a.inicio LIMIT 6")->fetchAll();
require 'partials/header.php'; require 'partials/sidebar.php';
?>
<section class="page-head"><div><h1>Painel Geral</h1><p>Domingo, 6 de setembro de 2026</p></div><a class="btn light" href="relatorios.php">▥ &nbsp; Relatórios</a></section>
<div class="stats">
<div class="stat"><span>AULAS HOJE</span><strong><?=$aulas?></strong><small>+3 em relação a ontem</small></div>
<div class="stat"><span>INSTRUTORES ATIVOS</span><strong><?=$instrutores?></strong><small>5 afastados</small></div>
<div class="stat"><span>SALAS DISPONÍVEIS</span><strong><?=$salas?>/<?=$salas+5?></strong><small>5 ocupadas ou reservadas</small></div>
<div class="stat"><span>TURMAS EM ANDAMENTO</span><strong><?=$turmas?></strong><small>8 encerram em novembro</small></div>
</div>
<div class="grid-2">
<section class="panel"><div class="panel-head"><h2>Aulas de Hoje</h2><a href="horarios.php">Ver todas →</a></div>
<div class="agenda"><?php foreach($agenda as $a): ?><div class="agenda-row"><div class="time"><?=substr($a['inicio'],0,5)?></div><div><b><?=e($a['materia'])?></b><p><?=e($a['tipo'])?> · <?=e($a['sala'])?></p></div><span class="tag"><?=e($a['turma'])?></span></div><?php endforeach; ?></div></section>
<section class="panel"><div class="panel-head"><h2>Acesso Rápido</h2></div><div class="quick">
<a href="horarios.php">◷<b>Horários</b><small>Consultar aulas</small></a><a href="instrutores.php">♙<b>Instrutores</b><small>Ver equipe</small></a><a href="salas.php">▤<b>Salas</b><small>Disponibilidade</small></a><a href="relatorios.php">▥<b>Relatórios</b><small>Indicadores</small></a>
</div></section></div>
<section class="panel notice"><h2>Avisos</h2><div class="notice-item"><b>Calendário acadêmico</b><span>Confira os próximos eventos e encerramentos no relatório de calendário.</span></div><div class="notice-item"><b>Movimentações</b><span>Existem solicitações que precisam de acompanhamento.</span></div></section>
<?php require 'partials/footer.php'; ?>
