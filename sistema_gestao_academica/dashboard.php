<?php
require_once __DIR__.'/functions.php';
require_login();
$title='Painel Geral';

$aulas=count_value("SELECT COUNT(*) FROM aulas WHERE data_aula=CURDATE()");
$instrutores=count_value("SELECT COUNT(*) FROM instrutores WHERE ativo=1");
$salas=count_value("SELECT COUNT(*) FROM salas WHERE ativo=1 AND status='DISPONIVEL'");
$turmas=count_value("SELECT COUNT(*) FROM turmas WHERE status='ATIVA'");

$agenda=db()->query("SELECT a.*,d.nome materia,t.codigo turma,i.nome instrutor,s.nome sala FROM aulas a JOIN disciplinas d ON d.id=a.disciplina_id JOIN turmas t ON t.id=a.turma_id JOIN instrutores ii ON ii.id=a.instrutor_id JOIN usuarios i ON i.id=ii.usuario_id JOIN salas s ON s.id=a.sala_id WHERE a.data_aula=CURDATE() ORDER BY a.inicio LIMIT 6")->fetchAll();

/* ==========================================================
 * CALENDÁRIO ACADÊMICO
 * Todos podem consultar. A edição continua restrita ao ADMIN
 * pelas regras do servidor nas páginas que alteram dados.
 * ========================================================== */
$mesSolicitado = trim((string)($_GET['mes'] ?? date('Y-m')));
if (!preg_match('/^\d{4}-\d{2}$/', $mesSolicitado)) {
    $mesSolicitado = date('Y-m');
}

$mesData = DateTime::createFromFormat('!Y-m-d', $mesSolicitado . '-01');
if (!$mesData || $mesData->format('Y-m') !== $mesSolicitado) {
    $mesData = new DateTime('first day of this month');
    $mesSolicitado = $mesData->format('Y-m');
}

$inicioMes = $mesData->format('Y-m-01');
$fimMesData = (clone $mesData)->modify('last day of this month');
$fimMes = $fimMesData->format('Y-m-d');
$primeiroDiaSemana = (int)$mesData->format('w'); // 0 = domingo
$diasNoMes = (int)$mesData->format('t');

$meses = [
    1=>'janeiro',2=>'fevereiro',3=>'março',4=>'abril',5=>'maio',6=>'junho',
    7=>'julho',8=>'agosto',9=>'setembro',10=>'outubro',11=>'novembro',12=>'dezembro'
];
$diasSemana = ['Dom','Seg','Ter','Qua','Qui','Sex','Sáb'];

$eventosPorData = [];

/* Aulas: além da aula, o uso de uma sala/laboratório fica visível
 * no calendário. Para laboratórios, a descrição apresentada é
 * "Lab X reservado", conforme solicitado. */
$st = db()->prepare("\n    SELECT\n        a.id, a.data_aula, a.inicio, a.fim, a.tipo, a.status,\n        d.nome AS materia, t.codigo AS turma, s.nome AS sala, s.tipo AS tipo_sala\n    FROM aulas a\n    JOIN disciplinas d ON d.id=a.disciplina_id\n    JOIN turmas t ON t.id=a.turma_id\n    JOIN salas s ON s.id=a.sala_id\n    WHERE a.data_aula BETWEEN ? AND ?\n      AND UPPER(a.status) NOT IN ('CANCELADA','CANCELADO')\n    ORDER BY a.data_aula, a.inicio\n");
$st->execute([$inicioMes, $fimMes]);

foreach ($st->fetchAll() as $a) {
    $data = $a['data_aula'];
    $hora = substr((string)$a['inicio'], 0, 5);
    $fim = substr((string)$a['fim'], 0, 5);
    $ehLab = strtoupper((string)$a['tipo_sala']) === 'LABORATORIO';
    $nomeSala = trim((string)$a['sala']);
    $tituloSala = preg_match('/^lab\b/i', $nomeSala) ? $nomeSala : 'Lab ' . $nomeSala;

    if ($ehLab) {
        $titulo = $tituloSala . ' reservado';
        $detalhe = $hora . '–' . $fim . ' · ' . $a['turma'];
    } else {
        $titulo = $a['materia'];
        $detalhe = $hora . '–' . $fim . ' · ' . $nomeSala . ' · ' . $a['turma'];
    }

    $eventosPorData[$data][] = [
        'classe' => $ehLab ? 'calendar-event room' : 'calendar-event class',
        'titulo' => $titulo,
        'detalhe' => $detalhe,
        'href' => is_admin() ? 'horarios.php' : null
    ];
}

/* Eventos acadêmicos cadastrados na tabela própria. */
$st = db()->prepare("\n    SELECT id, titulo, descricao, data_inicio, data_fim, tipo\n    FROM eventos_calendario\n    WHERE ativo=1\n      AND data_inicio <= ?\n      AND COALESCE(data_fim, data_inicio) >= ?\n    ORDER BY data_inicio, titulo\n");
$st->execute([$fimMes, $inicioMes]);

foreach ($st->fetchAll() as $evento) {
    $inicioEvento = new DateTime($evento['data_inicio']);
    $fimEvento = new DateTime($evento['data_fim'] ?: $evento['data_inicio']);
    $limite = new DateTime($inicioMes);
    if ($inicioEvento < $limite) {
        $inicioEvento = $limite;
    }
    $limite = new DateTime($fimMes);
    if ($fimEvento > $limite) {
        $fimEvento = $limite;
    }

    for ($d = clone $inicioEvento; $d <= $fimEvento; $d->modify('+1 day')) {
        $data = $d->format('Y-m-d');
        $eventosPorData[$data][] = [
            'classe' => 'calendar-event academic',
            'titulo' => $evento['titulo'],
            'detalhe' => $evento['tipo'] . (!empty($evento['descricao']) ? ' · ' . $evento['descricao'] : ''),
            'href' => null
        ];
    }
}

/* Movimentações também possuem data e passam a aparecer no calendário. */
$st = db()->prepare("\n    SELECT id, tipo, status, data_movimentacao, motivo\n    FROM movimentacoes\n    WHERE data_movimentacao BETWEEN ? AND ?\n      AND status <> 'CANCELADO'\n    ORDER BY data_movimentacao, id\n");
$st->execute([$inicioMes, $fimMes]);

foreach ($st->fetchAll() as $mov) {
    $eventosPorData[$mov['data_movimentacao']][] = [
        'classe' => 'calendar-event movement',
        'titulo' => 'Movimentação: ' . str_replace('_', ' ', $mov['tipo']),
        'detalhe' => $mov['status'] . (!empty($mov['motivo']) ? ' · ' . $mov['motivo'] : ''),
        'href' => is_admin() ? 'movimentacoes.php' : null
    ];
}

$mesAnterior = (clone $mesData)->modify('-1 month')->format('Y-m');
$proximoMes = (clone $mesData)->modify('+1 month')->format('Y-m');

require 'partials/header.php';
require 'partials/sidebar.php';
?>
<section class="page-head">
    <div><h1>Painel Geral</h1>
        <?php
        $dias = ['Domingo','Segunda-feira','Terça-feira','Quarta-feira','Quinta-feira','Sexta-feira','Sábado'];
        $hoje = new DateTime('today');
        ?>
        <p><?= e($dias[(int)$hoje->format('w')] . ', ' . $hoje->format('d') . ' de ' . $meses[(int)$hoje->format('n')] . ' de ' . $hoje->format('Y')) ?></p>
    </div><a class="btn light" href="relatorios.php">▥ &nbsp; Relatórios</a>
</section>

<div class="stats">
    <div class="stat"><span>AULAS HOJE</span><strong><?=$aulas?></strong><small>Programadas para hoje</small></div>
    <div class="stat"><span>INSTRUTORES ATIVOS</span><strong><?=$instrutores?></strong><small>Instrutores disponíveis</small></div>
    <div class="stat"><span>SALAS DISPONÍVEIS</span><strong><?=$salas?></strong><small>Salas livres no momento</small></div>
    <div class="stat"><span>TURMAS EM ANDAMENTO</span><strong><?=$turmas?></strong><small>Turmas com status ativo</small></div>
</div>

<div class="grid-2">
    <section class="panel">
        <div class="panel-head"><h2>Aulas de Hoje</h2><a href="horarios.php">Ver todas →</a></div>
        <div class="agenda">
            <?php if (!$agenda): ?>
                <p class="muted">Nenhuma aula programada para hoje.</p>
            <?php else: ?>
                <?php foreach($agenda as $a): ?>
                    <div class="agenda-row">
                        <div class="time"><?=substr($a['inicio'],0,5)?></div>
                        <div><b><?=e($a['materia'])?></b><p><?=e($a['tipo'])?> · <?=e($a['sala'])?></p></div>
                        <span class="tag"><?=e($a['turma'])?></span>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </section>

    <section class="panel">
        <div class="panel-head"><h2>Acesso Rápido</h2></div>
        <div class="quick">
            <a href="horarios.php">◷<b>Horários</b><small>Consultar aulas</small></a>
            <a href="instrutores.php">♙<b>Instrutores</b><small>Ver equipe</small></a>
            <a href="salas.php">▤<b>Salas</b><small>Disponibilidade</small></a>
            <a href="relatorios.php">▥<b>Relatórios</b><small>Indicadores</small></a>
            <?php if (is_admin()): ?><a href="administradores.php">♙<b>Usuários</b><small>Cadastrar aluno ou instrutor</small></a><?php endif; ?>
        </div>
    </section>
</div>

<section class="panel calendar-panel">
    <div class="panel-head calendar-heading">
        <div>
            <h2>Calendário acadêmico</h2>
            <p class="muted">Aulas, reservas de laboratórios, eventos e movimentações com data.</p>
        </div>
        <div class="calendar-nav">
            <a class="btn light" href="dashboard.php?mes=<?=e($mesAnterior)?>" aria-label="Mês anterior">‹</a>
            <strong><?=e(ucfirst($meses[(int)$mesData->format('n')]) . ' de ' . $mesData->format('Y'))?></strong>
            <a class="btn light" href="dashboard.php?mes=<?=e($proximoMes)?>" aria-label="Próximo mês">›</a>
        </div>
    </div>

    <div class="calendar-grid">
        <?php foreach ($diasSemana as $diaSemana): ?>
            <div class="calendar-weekday"><?=e($diaSemana)?></div>
        <?php endforeach; ?>

        <?php for ($v = 0; $v < $primeiroDiaSemana; $v++): ?>
            <div class="calendar-day empty"></div>
        <?php endfor; ?>

        <?php for ($dia = 1; $dia <= $diasNoMes; $dia++):
            $dataAtual = $mesData->format('Y-m') . '-' . str_pad((string)$dia, 2, '0', STR_PAD_LEFT);
            $ehHoje = $dataAtual === date('Y-m-d');
            $eventosDia = $eventosPorData[$dataAtual] ?? [];
        ?>
            <div class="calendar-day <?= $ehHoje ? 'today' : '' ?>">
                <div class="calendar-date"><?= $dia ?></div>
                <div class="calendar-events">
                    <?php foreach (array_slice($eventosDia, 0, 4) as $evento): ?>
                        <?php if ($evento['href']): ?>
                            <a class="<?=e($evento['classe'])?>" href="<?=e($evento['href'])?>" title="<?=e($evento['detalhe'])?>">
                                <b><?=e($evento['titulo'])?></b>
                                <small><?=e($evento['detalhe'])?></small>
                            </a>
                        <?php else: ?>
                            <div class="<?=e($evento['classe'])?>" title="<?=e($evento['detalhe'])?>">
                                <b><?=e($evento['titulo'])?></b>
                                <small><?=e($evento['detalhe'])?></small>
                            </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                    <?php if (count($eventosDia) > 4): ?>
                        <small class="calendar-more">+ <?=count($eventosDia)-4?> outro(s)</small>
                    <?php endif; ?>
                </div>
            </div>
        <?php endfor; ?>
    </div>

    <div class="calendar-legend">
        <span><i class="legend-dot class"></i>Aula</span>
        <span><i class="legend-dot room"></i>Laboratório reservado</span>
        <span><i class="legend-dot academic"></i>Evento acadêmico</span>
        <span><i class="legend-dot movement"></i>Movimentação</span>
    </div>
</section>

<?php require 'partials/footer.php'; ?>
