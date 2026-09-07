<?php

require_once __DIR__ . '/functions.php';
require_login();

$title = 'Consulta de Horários';

$ini = $_GET['ini'] ?? '2025-08-07';
$fim = $_GET['fim'] ?? '2025-08-08';
$periodo = $_GET['periodo'] ?? '';
$inst = $_GET['instrutor'] ?? '';
$turma = $_GET['turma'] ?? '';

$where = ["a.data_aula BETWEEN ? AND ?"];
$params = [$ini, $fim];

if ($periodo) {
    $where[] = "a.periodo = ?";
    $params[] = $periodo;
}

if ($inst) {
    $where[] = "i.id = ?";
    $params[] = $inst;
}

if ($turma) {
    $where[] = "t.id = ?";
    $params[] = $turma;
}

/*
 * O aluno só visualiza horários das turmas
 * em que está matriculado.
 */
if (!is_instructor()) {
    $where[] = "
        EXISTS (
            SELECT 1
            FROM matriculas m
            JOIN alunos al ON al.id = m.aluno_id
            WHERE al.usuario_id = ?
            AND m.turma_id = t.id
        )
    ";

    $params[] = user()['id'];
}

$sql = "
    SELECT
        a.*,
        d.nome AS materia,
        t.codigo AS turma,
        i.nome AS instrutor,
        s.nome AS sala
    FROM aulas a
    JOIN disciplinas d
        ON d.id = a.disciplina_id
    JOIN turmas t
        ON t.id = a.turma_id
    JOIN instrutores ii
        ON ii.id = a.instrutor_id
    JOIN usuarios i
        ON i.id = ii.usuario_id
    JOIN salas s
        ON s.id = a.sala_id
    WHERE " . implode(' AND ', $where) . "
    ORDER BY a.data_aula, a.inicio
";

$st = db()->prepare($sql);
$st->execute($params);
$rows = $st->fetchAll();

$insts = db()->query("
    SELECT
        i.id,
        u.nome
    FROM instrutores i
    JOIN usuarios u
        ON u.id = i.usuario_id
    WHERE i.ativo = 1
    ORDER BY u.nome
")->fetchAll();

$turmas = db()->query("
    SELECT
        id,
        codigo
    FROM turmas
    ORDER BY codigo
")->fetchAll();

require 'partials/header.php';
require 'partials/sidebar.php';

?>

<section class="page-head">

    <div>
        <h1>Consulta de Horários</h1>
        <p>Filtre, edite e gerencie os horários das aulas</p>
    </div>

    <div>
        <button
            class="btn light"
            onclick="window.print()"
        >
            ♧ Imprimir
        </button>

        <button
            class="btn light"
            onclick="exportTable('horariosTable','horarios.csv')"
        >
            ⇩ Exportar
        </button>
    </div>

</section>

<div class="panel">

    <h3>Filtros de Pesquisa</h3>

    <form class="filters">

        <label>
            DATA INICIAL
            <input
                type="date"
                name="ini"
                value="<?= e($ini) ?>"
            >
        </label>

        <label>
            DATA FINAL
            <input
                type="date"
                name="fim"
                value="<?= e($fim) ?>"
            >
        </label>

        <label>
            PERÍODO

            <select name="periodo">

                <option value="">
                    Todos os períodos
                </option>

                <option
                    value="MANHA"
                    <?= $periodo === 'MANHA' ? 'selected' : '' ?>
                >
                    Manhã
                </option>

                <option
                    value="TARDE"
                    <?= $periodo === 'TARDE' ? 'selected' : '' ?>
                >
                    Tarde
                </option>

                <option
                    value="NOITE"
                    <?= $periodo === 'NOITE' ? 'selected' : '' ?>
                >
                    Noite
                </option>

            </select>
        </label>

        <label>
            INSTRUTOR

            <select name="instrutor">

                <option value="">
                    Todos os instrutores
                </option>

                <?php foreach ($insts as $i): ?>

                    <option
                        value="<?= $i['id'] ?>"
                        <?= $inst == $i['id'] ? 'selected' : '' ?>
                    >
                        <?= e($i['nome']) ?>
                    </option>

                <?php endforeach; ?>

            </select>

        </label>

        <label>
            TURMA

            <select name="turma">

                <option value="">
                    Todas
                </option>

                <?php foreach ($turmas as $t): ?>

                    <option
                        value="<?= $t['id'] ?>"
                        <?= $turma == $t['id'] ? 'selected' : '' ?>
                    >
                        <?= e($t['codigo']) ?>
                    </option>

                <?php endforeach; ?>

            </select>

        </label>

        <button class="btn primary">
            Pesquisar
        </button>

        <a
            class="btn light"
            href="horarios.php"
        >
            Limpar
        </a>

    </form>

</div>

<div class="panel">

    <div class="panel-head">

        <h2>
            <?= count($rows) ?> resultados encontrados
        </h2>

    </div>

    <div class="table-wrap">

        <table id="horariosTable">

            <thead>

                <tr>
                    <th>AULA</th>
                    <th>DATA</th>
                    <th>DIA</th>
                    <th>INSTRUTOR</th>
                    <th>MATÉRIA</th>
                    <th>SALA</th>
                    <th>TURMA</th>
                    <th>TIPO</th>
                    <th>PERÍODO</th>
                    <th>HORÁRIO</th>
                    <th>AÇÕES</th>
                </tr>

            </thead>

            <tbody>

                <?php foreach ($rows as $r): ?>

                    <tr>

                        <td>
                            #<?= $r['id'] ?>
                        </td>

                        <td>
                            <?= fmt_date($r['data_aula']) ?>
                        </td>

                        <td>
                            <?= date('l', strtotime($r['data_aula'])) ?>
                        </td>

                        <td>
                            <?= e($r['instrutor']) ?>
                        </td>

                        <td>
                            <?= e($r['materia']) ?>
                        </td>

                        <td>
                            <?= e($r['sala']) ?>
                        </td>

                        <td>
                            <b><?= e($r['turma']) ?></b>
                        </td>

                        <td>
                            <?= e(ucfirst(strtolower($r['tipo']))) ?>
                        </td>

                        <td>
                            <?= e(ucfirst(strtolower($r['periodo']))) ?>
                        </td>

                        <td>
                            <?= substr($r['inicio'], 0, 5) ?>
                            –
                            <?= substr($r['fim'], 0, 5) ?>
                        </td>

                        <td>

                            <?php if (is_instructor()): ?>

                                <button
                                    class="icon"
                                    onclick="alert('Editor de aula: <?= e($r['materia']) ?>')"
                                    title="Editar aula"
                                >
                                    ✎
                                </button>

                            <?php else: ?>

                                <span class="muted">
                                    Visualizar
                                </span>

                            <?php endif; ?>

                        </td>

                    </tr>

                <?php endforeach; ?>

            </tbody>

        </table>

    </div>

</div>

<?php require 'partials/footer.php'; ?>