<?php

require_once __DIR__ . '/functions.php';
require_login();

$title = 'Consulta de Horários';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'update_aula') {
    require_editor();

    $id = (int)($_POST['id'] ?? 0);
    $turmaId = (int)($_POST['turma_id'] ?? 0);
    $disciplinaId = (int)($_POST['disciplina_id'] ?? 0);
    $instrutorId = (int)($_POST['instrutor_id'] ?? 0);
    $salaId = (int)($_POST['sala_id'] ?? 0);
    $dataAula = $_POST['data_aula'] ?? '';
    $periodo = $_POST['periodo'] ?? '';
    $inicio = $_POST['inicio'] ?? '';
    $fimAula = $_POST['fim'] ?? '';
    $tipo = $_POST['tipo'] ?? '';
    $status = trim($_POST['status'] ?? 'PLANEJADA');
    $conteudo = trim($_POST['conteudo'] ?? '');
    $observacoes = trim($_POST['observacoes'] ?? '');

    $periodosValidos = ['MANHA', 'TARDE', 'NOITE'];
    $tiposValidos = ['TEORICA', 'PRATICA'];

    if (
        $id <= 0 || $turmaId <= 0 || $disciplinaId <= 0 || $instrutorId <= 0 ||
        $salaId <= 0 || !$dataAula || !$inicio || !$fimAula ||
        !in_array($periodo, $periodosValidos, true) ||
        !in_array($tipo, $tiposValidos, true)
    ) {
        flash('danger', 'Preencha corretamente todos os campos obrigatórios da aula.');
        header('Location: horarios.php');
        exit;
    }

    try {
        $check = db()->prepare("SELECT * FROM aulas WHERE id = ? LIMIT 1");
        $check->execute([$id]);
        $old = $check->fetch();

        if (!$old) {
            flash('danger', 'Aula não encontrada.');
            header('Location: horarios.php');
            exit;
        }

        $update = db()->prepare("
            UPDATE aulas
            SET turma_id = ?, disciplina_id = ?, instrutor_id = ?, sala_id = ?,
                data_aula = ?, periodo = ?, inicio = ?, fim = ?, tipo = ?,
                status = ?, conteudo = ?, observacoes = ?
            WHERE id = ?
        ");

        $update->execute([
            $turmaId, $disciplinaId, $instrutorId, $salaId,
            $dataAula, $periodo, $inicio, $fimAula, $tipo,
            $status ?: 'PLANEJADA', $conteudo ?: null, $observacoes ?: null, $id
        ]);

        $check->execute([$id]);
        $new = $check->fetch();

        if (function_exists('audit')) {
            audit('aulas', $id, 'UPDATE', $old, $new ?: null);
        }

        flash('success', 'Aula atualizada com sucesso.');
    } catch (PDOException $e) {
        flash('danger', 'Não foi possível atualizar a aula. Verifique os dados informados.');
    }

    header('Location: horarios.php');
    exit;
}

/*
 * Filtros da consulta.
 * Se o usuário não informar datas, usamos o intervalo das aulas
 * cadastradas no banco para que a consulta não fique vazia apenas
 * por causa de datas antigas da carga inicial.
 */
$periodosValidos = ['', 'MANHA', 'TARDE', 'NOITE'];

$ini = trim($_GET['ini'] ?? '');
$fim = trim($_GET['fim'] ?? '');
$periodo = strtoupper(trim($_GET['periodo'] ?? ''));
$inst = trim($_GET['instrutor'] ?? '');
$turma = trim($_GET['turma'] ?? '');

function validar_data_filtro(string $data): bool {
    if ($data === '') {
        return true;
    }

    $d = DateTime::createFromFormat('Y-m-d', $data);

    return $d !== false && $d->format('Y-m-d') === $data;
}

if (!validar_data_filtro($ini) || !validar_data_filtro($fim)) {
    flash('danger', 'As datas informadas são inválidas.');
    header('Location: horarios.php');
    exit;
}

if (!in_array($periodo, $periodosValidos, true)) {
    flash('danger', 'Período inválido.');
    header('Location: horarios.php');
    exit;
}

if ($ini !== '' && $fim !== '' && $ini > $fim) {
    flash('danger', 'A data inicial não pode ser maior que a data final.');
    header('Location: horarios.php');
    exit;
}

if ($inst !== '' && (!ctype_digit($inst) || (int)$inst <= 0)) {
    flash('danger', 'Instrutor inválido.');
    header('Location: horarios.php');
    exit;
}

if ($turma !== '' && (!ctype_digit($turma) || (int)$turma <= 0)) {
    flash('danger', 'Turma inválida.');
    header('Location: horarios.php');
    exit;
}

/*
 * Sem datas, consulta todas as aulas cadastradas.
 * Isso também funciona com a carga de demonstração do projeto.
 */
$where = [];
$params = [];

if ($ini !== '') {
    $where[] = "a.data_aula >= ?";
    $params[] = $ini;
}

if ($fim !== '') {
    $where[] = "a.data_aula <= ?";
    $params[] = $fim;
}

if ($periodo !== '') {
    $where[] = "a.periodo = ?";
    $params[] = $periodo;
}

if ($inst !== '') {
    $where[] = "i.id = ?";
    $params[] = (int)$inst;
}

if ($turma !== '') {
    $where[] = "t.id = ?";
    $params[] = (int)$turma;
}

/*
 * ALUNO:
 * - pode consultar os horários;
 * - pode usar os filtros;
 * - pode imprimir/exportar;
 * - não recebe formulário de edição;
 * - qualquer POST de atualização passa por require_editor().
 *
 * Portanto, a restrição de edição é feita no servidor, e não somente
 * escondendo o botão no HTML.
 */

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
";

if ($where) {
    $sql .= " WHERE " . implode(' AND ', $where);
}

$sql .= " ORDER BY a.data_aula, a.inicio";

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

$messages = flashes();

?>

<section class="page-head">

    <div>
        <h1>Consulta de Horários</h1>
        <p><?= is_admin() ? 'Filtre, edite e gerencie os horários das aulas' : 'Consulte os horários das aulas — somente visualização' ?></p>
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
                    <?php if (is_admin()): ?>
                        <th>AÇÕES</th>
                    <?php endif; ?>
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

                        <?php if (is_admin()): ?>
                        <td>
                            <button
                                class="icon"
                                type="button"
                                onclick='editAula(<?= json_encode($r, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>)'
                                title="Editar aula"
                            >
                                ✎
                            </button>
                        </td>
                        <?php endif; ?>

                    </tr>

                <?php endforeach; ?>

            </tbody>

        </table>

    </div>

</div>


<?php if (is_admin()): ?>
<div class="modal" id="aulaModal">
    <div class="modal-box">
        <button class="modal-close" type="button" onclick="closeModal('aulaModal')" aria-label="Fechar">×</button>

        <h2 id="aulaModalTitle">Editar aula</h2>
        <p class="muted">Altere os dados da aula selecionada.</p>

        <form method="post">
            <input type="hidden" name="action" value="update_aula">
            <input type="hidden" name="id" id="aula_id">

            <div class="form-grid">
                <label>
                    DATA
                    <input type="date" name="data_aula" id="aula_data" required>
                </label>

                <label>
                    PERÍODO
                    <select name="periodo" id="aula_periodo" required>
                        <option value="MANHA">Manhã</option>
                        <option value="TARDE">Tarde</option>
                        <option value="NOITE">Noite</option>
                    </select>
                </label>

                <label>
                    TURMA
                    <select name="turma_id" id="aula_turma" required>
                        <?php foreach ($turmas as $t): ?>
                            <option value="<?= (int)$t['id'] ?>"><?= e($t['codigo']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>

                <label>
                    INSTRUTOR
                    <select name="instrutor_id" id="aula_instrutor" required>
                        <?php foreach ($insts as $i): ?>
                            <option value="<?= (int)$i['id'] ?>"><?= e($i['nome']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>

                <label>
                    DISCIPLINA
                    <select name="disciplina_id" id="aula_disciplina" required>
                        <?php
                        $disciplinas = db()->query("SELECT id, codigo, nome FROM disciplinas WHERE ativo = 1 ORDER BY nome")->fetchAll();
                        foreach ($disciplinas as $d):
                        ?>
                            <option value="<?= (int)$d['id'] ?>">
                                <?= e($d['codigo'].' - '.$d['nome']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </label>

                <label>
                    SALA
                    <select name="sala_id" id="aula_sala" required>
                        <?php
                        $salas = db()->query("SELECT id, nome FROM salas WHERE ativo = 1 ORDER BY nome")->fetchAll();
                        foreach ($salas as $s):
                        ?>
                            <option value="<?= (int)$s['id'] ?>"><?= e($s['nome']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>

                <label>
                    INÍCIO
                    <input type="time" name="inicio" id="aula_inicio" required>
                </label>

                <label>
                    FIM
                    <input type="time" name="fim" id="aula_fim" required>
                </label>

                <label>
                    TIPO
                    <select name="tipo" id="aula_tipo" required>
                        <option value="TEORICA">Teórica</option>
                        <option value="PRATICA">Prática</option>
                    </select>
                </label>

                <label>
                    STATUS
                    <input type="text" name="status" id="aula_status" maxlength="30" placeholder="PLANEJADA">
                </label>
            </div>

            <label>
                CONTEÚDO
                <textarea name="conteudo" id="aula_conteudo"></textarea>
            </label>

            <label>
                OBSERVAÇÕES
                <textarea name="observacoes" id="aula_observacoes"></textarea>
            </label>

            <div class="modal-actions">
                <button type="button" class="btn light" onclick="closeModal('aulaModal')">Cancelar</button>
                <button type="submit" class="btn primary">Salvar alterações</button>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

<?php if ($messages): ?>
    <script>
        <?php foreach ($messages as $message): ?>
            window.addEventListener('DOMContentLoaded', function () {
                showFlash(<?= json_encode($message['type']) ?>, <?= json_encode($message['message']) ?>);
            });
        <?php endforeach; ?>
    </script>
<?php endif; ?>

<?php require 'partials/footer.php'; ?>