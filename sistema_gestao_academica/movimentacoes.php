<?php

require_once __DIR__ . '/functions.php';

require_instructor();

$title = 'Movimentação';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $id = (int) ($_POST['id'] ?? 0);
    $action = $_POST['action'] ?? '';

    try {

        if ($action === 'save') {

            $st = db()->prepare("
                INSERT INTO movimentacoes
                (
                    tipo,
                    status,
                    data_movimentacao,
                    turma_id,
                    motivo,
                    criado_por
                )
                VALUES (?, ?, ?, ?, ?, ?)
            ");

            $st->execute([
                $_POST['tipo'] ?? '',
                $_POST['status'] ?? '',
                $_POST['data'] ?? date('Y-m-d'),
                (int) ($_POST['turma_id'] ?? 0),
                trim($_POST['motivo'] ?? ''),
                user()['id']
            ]);

            flash('success', 'Movimentação criada.');
        }

        if ($action === 'status') {

            $status = $_POST['status'] ?? '';

            $st = db()->prepare("
                UPDATE movimentacoes
                SET
                    status = ?,
                    aprovado_por = ?,
                    aprovado_em = NOW()
                WHERE id = ?
            ");

            $st->execute([
                $status,
                user()['id'],
                $id
            ]);

            flash('success', 'Status atualizado.');
        }

    } catch (Throwable $e) {

        flash('danger', $e->getMessage());
    }

    header('Location: movimentacoes.php');
    exit;
}


/*
|--------------------------------------------------------------------------
| MOVIMENTAÇÕES
|--------------------------------------------------------------------------
*/

$sql = "
    SELECT
        m.*,
        t.codigo AS turma,
        u.nome AS criador,

        COALESCE(ud.nome, '-') AS instrutor_de_nome,
        COALESCE(up.nome, '-') AS instrutor_para_nome,

        COALESCE(sd.nome, '-') AS sala_de_nome,
        COALESCE(sp.nome, '-') AS sala_para_nome

    FROM movimentacoes m

    JOIN turmas t
        ON t.id = m.turma_id

    JOIN usuarios u
        ON u.id = m.criado_por

    LEFT JOIN instrutores idf
        ON idf.id = m.instrutor_de

    LEFT JOIN usuarios ud
        ON ud.id = idf.usuario_id

    LEFT JOIN instrutores ip
        ON ip.id = m.instrutor_para

    LEFT JOIN usuarios up
        ON up.id = ip.usuario_id

    LEFT JOIN salas sd
        ON sd.id = m.sala_de

    LEFT JOIN salas sp
        ON sp.id = m.sala_para

    ORDER BY
        m.data_movimentacao DESC,
        m.id DESC
";

$rows = db()->query($sql)->fetchAll();


/*
|--------------------------------------------------------------------------
| TURMAS
|--------------------------------------------------------------------------
*/

$turmas = db()->query("
    SELECT
        id,
        codigo
    FROM turmas
    ORDER BY codigo
")->fetchAll();


$fl = flashes();

require 'partials/header.php';
require 'partials/sidebar.php';

?>

<section class="page-head">

    <div>

        <h1>Movimentação</h1>

        <p>
            Substituições, trocas de sala, cancelamentos e reagendamentos
        </p>

    </div>

    <button
        class="btn primary"
        onclick="openModal('movModal')"
    >
        ＋ Nova movimentação
    </button>

</section>


<?php foreach ($fl as $f): ?>

    <div class="alert <?= e($f['type']) ?>">
        <?= e($f['message']) ?>
    </div>

<?php endforeach; ?>


<div class="summary-strip">

    <div>
        <b><?= count($rows) ?></b>
        <span>Pendentes</span>
    </div>

    <div>
        <b>
            <?= count(
                array_filter(
                    $rows,
                    fn($r) => $r['status'] === 'APROVADO'
                )
            ) ?>
        </b>

        <span>Aprovadas</span>
    </div>

    <div>
        <b>
            <?= count(
                array_filter(
                    $rows,
                    fn($r) => $r['status'] === 'AGUARDANDO'
                )
            ) ?>
        </b>

        <span>Aguardando</span>
    </div>

</div>


<div class="panel">

    <div class="tabs">

        <button class="selected">
            Todas
        </button>

        <button>
            Pendentes
        </button>

        <button>
            Aprovadas
        </button>

    </div>


    <div class="table-wrap">

        <table>

            <thead>

                <tr>

                    <th>ID</th>
                    <th>TIPO</th>
                    <th>DATA</th>
                    <th>TURMA</th>
                    <th>DE</th>
                    <th>PARA</th>
                    <th>MOTIVO</th>
                    <th>STATUS</th>
                    <th>AÇÕES</th>

                </tr>

            </thead>


            <tbody>

                <?php foreach ($rows as $r): ?>

                    <?php

                    /*
                     * Define o que será mostrado na coluna "DE".
                     * Se houver instrutor, mostra o instrutor.
                     * Caso contrário, mostra a sala.
                     */

                    if (
                        !empty($r['instrutor_de_nome']) &&
                        $r['instrutor_de_nome'] !== '-'
                    ) {
                        $de = $r['instrutor_de_nome'];
                    } else {
                        $de = $r['sala_de_nome'];
                    }


                    /*
                     * Define o que será mostrado na coluna "PARA".
                     */

                    if (
                        !empty($r['instrutor_para_nome']) &&
                        $r['instrutor_para_nome'] !== '-'
                    ) {
                        $para = $r['instrutor_para_nome'];
                    } else {
                        $para = $r['sala_para_nome'];
                    }

                    ?>

                    <tr>

                        <td>
                            #<?= str_pad(
                                $r['id'],
                                3,
                                '0',
                                STR_PAD_LEFT
                            ) ?>
                        </td>


                        <td>
                            <?= e(
                                ucfirst(
                                    strtolower(
                                        str_replace(
                                            '_',
                                            ' ',
                                            $r['tipo']
                                        )
                                    )
                                )
                            ) ?>
                        </td>


                        <td>
                            <?= fmt_date($r['data_movimentacao']) ?>
                        </td>


                        <td>
                            <b>
                                <?= e($r['turma']) ?>
                            </b>
                        </td>


                        <td>
                            <?= e($de) ?>
                        </td>


                        <td>
                            <?= e($para) ?>
                        </td>


                        <td>
                            <?= e($r['motivo']) ?>
                        </td>


                        <td>

                            <span
                                class="status <?= strtolower(
                                    $r['status']
                                ) ?>"
                            >
                                <?= e($r['status']) ?>
                            </span>

                        </td>


                        <td>

                            <button
                                class="icon"
                                onclick='viewMov(<?= json_encode(
                                    $r,
                                    JSON_HEX_TAG |
                                    JSON_HEX_APOS |
                                    JSON_HEX_QUOT |
                                    JSON_HEX_AMP
                                ) ?>)'
                                title="Visualizar"
                            >
                                ⌕
                            </button>


                            <?php if ($r['status'] !== 'APROVADO'): ?>

                                <form
                                    class="inline"
                                    method="post"
                                >

                                    <input
                                        type="hidden"
                                        name="action"
                                        value="status"
                                    >

                                    <input
                                        type="hidden"
                                        name="id"
                                        value="<?= $r['id'] ?>"
                                    >

                                    <input
                                        type="hidden"
                                        name="status"
                                        value="APROVADO"
                                    >

                                    <button
                                        class="icon"
                                        title="Aprovar"
                                    >
                                        ✓
                                    </button>

                                </form>

                            <?php endif; ?>

                        </td>

                    </tr>

                <?php endforeach; ?>

            </tbody>

        </table>

    </div>

</div>


<!-- =========================================================
     MODAL — NOVA MOVIMENTAÇÃO
========================================================= -->

<div
    class="modal"
    id="movModal"
>

    <div class="modal-box">

        <button
            class="modal-close"
            onclick="closeModal('movModal')"
        >
            ×
        </button>


        <h2>
            Nova Movimentação
        </h2>


        <form method="post">

            <input
                type="hidden"
                name="action"
                value="save"
            >


            <div class="form-grid">


                <label>

                    Tipo

                    <select name="tipo">

                        <option value="SUBSTITUICAO">
                            SUBSTITUIÇÃO
                        </option>

                        <option value="TROCA_HORARIO">
                            TROCA DE HORÁRIO
                        </option>

                        <option value="CANCELAMENTO">
                            CANCELAMENTO
                        </option>

                        <option value="REAGENDAMENTO">
                            REAGENDAMENTO
                        </option>

                    </select>

                </label>


                <label>

                    Data

                    <input
                        type="date"
                        name="data"
                        value="<?= date('Y-m-d') ?>"
                    >

                </label>


                <label>

                    Turma

                    <select name="turma_id" required>

                        <?php foreach ($turmas as $t): ?>

                            <option
                                value="<?= $t['id'] ?>"
                            >
                                <?= e($t['codigo']) ?>
                            </option>

                        <?php endforeach; ?>

                    </select>

                </label>


                <label>

                    Status

                    <select name="status">

                        <option value="PENDENTE">
                            PENDENTE
                        </option>

                        <option value="AGUARDANDO">
                            AGUARDANDO
                        </option>

                    </select>

                </label>

            </div>


            <label>

                Motivo

                <textarea
                    name="motivo"
                    required
                    placeholder="Informe o motivo da movimentação"
                ></textarea>

            </label>


            <button
                class="btn primary full"
                type="submit"
            >
                Criar movimentação
            </button>

        </form>

    </div>

</div>


<!-- =========================================================
     MODAL — DETALHES
========================================================= -->

<div
    class="modal"
    id="viewMov"
>

    <div class="modal-box">

        <button
            class="modal-close"
            onclick="closeModal('viewMov')"
        >
            ×
        </button>


        <h2>
            Detalhes da movimentação
        </h2>


        <div id="movDetails"></div>

    </div>

</div>


<?php require 'partials/footer.php'; ?>