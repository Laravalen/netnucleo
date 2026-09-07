<?php

require_once __DIR__ . '/functions.php';
require_login();

$title = 'Relatórios';

$freq = db()->query("
    SELECT
        a.id,
        u.nome aluno,
        t.codigo turma,
        d.nome disciplina,
        COUNT(f.id) total,
        COALESCE(SUM(f.status='PRESENTE'),0) presencas,
        COALESCE(
            SUM(f.status IN ('AUSENTE','JUSTIFICADA')),
            0
        ) faltas
    FROM alunos a
    JOIN usuarios u ON u.id=a.usuario_id
    JOIN matriculas m ON m.aluno_id=a.id
    JOIN turmas t ON t.id=m.turma_id
    JOIN turma_disciplinas td ON td.turma_id=t.id
    JOIN disciplinas d ON d.id=td.disciplina_id
    LEFT JOIN aulas au
        ON au.turma_id=t.id
        AND au.disciplina_id=d.id
    LEFT JOIN frequencias f
        ON f.aula_id=au.id
        AND f.aluno_id=a.id
    GROUP BY
        a.id,
        u.nome,
        t.codigo,
        d.nome
    LIMIT 30
")->fetchAll();

$carga = db()->query("
    SELECT
        i.id,
        u.nome,
        i.area,
        COUNT(a.id) aulas,
        COALESCE(
            SUM(
                TIME_TO_SEC(
                    TIMEDIFF(a.fim,a.inicio)
                )
            )/3600,
            0
        ) horas
    FROM instrutores i
    JOIN usuarios u ON u.id=i.usuario_id
    LEFT JOIN aulas a ON a.instrutor_id=i.id
    GROUP BY
        i.id,
        u.nome,
        i.area
    ORDER BY horas DESC
")->fetchAll();

$salas = db()->query("
    SELECT
        s.*,
        COUNT(a.id) aulas
    FROM salas s
    LEFT JOIN aulas a ON a.sala_id=s.id
    GROUP BY s.id
    ORDER BY aulas DESC
")->fetchAll();

$eventos = db()->query("
    SELECT *
    FROM eventos_calendario
    WHERE ativo=1
    ORDER BY data_inicio
")->fetchAll();

require 'partials/header.php';
require 'partials/sidebar.php';

?>

<section class="page-head">

    <div>
        <h1>Relatórios</h1>
        <p>Indicadores acadêmicos e operacionais</p>
    </div>

</section>

<div class="report-grid">

    <a class="report-card" href="#frequencia">
        <span>◔</span>
        <b>Frequência de aulas</b>
        <small>Presenças e faltas por aluno</small>
    </a>

    <a class="report-card" href="#carga">
        <span>◷</span>
        <b>Carga horária por instrutor</b>
        <small>Aulas e horas ministradas</small>
    </a>

    <a class="report-card" href="#salas">
        <span>▤</span>
        <b>Ocupação de salas</b>
        <small>Utilização dos ambientes</small>
    </a>

    <a class="report-card" href="#calendario">
        <span>□</span>
        <b>Calendário acadêmico</b>
        <small>Eventos e datas importantes</small>
    </a>

</div>

<section class="panel" id="frequencia">

    <div class="panel-head">

        <h2>Frequência de aulas</h2>

        <button
            class="btn light"
            onclick="window.print()"
        >
            Imprimir
        </button>

    </div>

    <div class="table-wrap">

        <table>

            <thead>

                <tr>
                    <th>ALUNO</th>
                    <th>TURMA</th>
                    <th>DISCIPLINA</th>
                    <th>REGISTROS</th>
                    <th>PRESENÇAS</th>
                    <th>FALTAS</th>
                </tr>

            </thead>

            <tbody>

                <?php foreach ($freq as $r): ?>

                    <tr>

                        <td><?= e($r['aluno']) ?></td>
                        <td><?= e($r['turma']) ?></td>
                        <td><?= e($r['disciplina']) ?></td>
                        <td><?= $r['total'] ?></td>

                        <td>
                            <span class="status green">
                                <?= $r['presencas'] ?>
                            </span>
                        </td>

                        <td><?= $r['faltas'] ?></td>

                    </tr>

                <?php endforeach; ?>

            </tbody>

        </table>

    </div>

</section>

<section class="panel" id="carga">

    <h2>Carga horária por instrutor</h2>

    <div class="bars">

        <?php foreach ($carga as $r): ?>

            <div class="bar-row">

                <div>

                    <b><?= e($r['nome']) ?></b>

                    <span>
                        <?= number_format(
                            (float)$r['horas'],
                            1,
                            ',',
                            '.'
                        ) ?>
                        h
                    </span>

                </div>

                <div class="bar">

                    <i
                        style="width:<?= min(
                            100,
                            (float)$r['horas'] * 2
                        ) ?>%"
                    ></i>

                </div>

            </div>

        <?php endforeach; ?>

    </div>

</section>

<section class="panel" id="salas">

    <h2>Ocupação de salas</h2>

    <div class="cards-grid">

        <?php foreach ($salas as $r): ?>

            <div class="entity-card">

                <b><?= e($r['codigo']) ?></b>

                <h3><?= e($r['nome']) ?></h3>

                <p>
                    <?= $r['aulas'] ?>
                    aulas agendadas
                </p>

                <small>
                    Capacidade:
                    <?= $r['capacidade'] ?>
                </small>

            </div>

        <?php endforeach; ?>

    </div>

</section>

<section class="panel" id="calendario">

    <h2>Calendário acadêmico</h2>

    <div class="calendar-list">

        <?php foreach ($eventos as $evento): ?>

            <div>

                <span>
                    <?= fmt_date($evento['data_inicio']) ?>
                </span>

                <b>
                    <?= e($evento['titulo']) ?>
                </b>

                <small>
                    <?= e($evento['descricao']) ?>
                </small>

            </div>

        <?php endforeach; ?>

    </div>

</section>

<?php require 'partials/footer.php'; ?>
