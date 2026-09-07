<?php

require_once __DIR__ . '/functions.php';
require_login();

$title = 'Consulta de Salas';

/*
 * Somente instrutores podem alterar salas.
 */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    require_instructor();

    $id = (int)($_POST['id'] ?? 0);

    $st = db()->prepare("
        UPDATE salas
        SET
            nome = ?,
            bloco = ?,
            tipo = ?,
            capacidade = ?,
            descricao = ?,
            status = ?
        WHERE id = ?
    ");

    $st->execute([
        $_POST['nome'] ?? '',
        $_POST['bloco'] ?? '',
        $_POST['tipo'] ?? '',
        $_POST['capacidade'] ?? 0,
        $_POST['descricao'] ?? '',
        $_POST['status'] ?? '',
        $id
    ]);

    flash('success', 'Sala atualizada.');

    header('Location: salas.php');
    exit;
}

$filter = $_GET['status'] ?? '';

$where = $filter
    ? "WHERE status = ?"
    : '';

$st = db()->prepare("
    SELECT *
    FROM salas
    $where
    ORDER BY status, codigo
");

$st->execute(
    $filter
        ? [$filter]
        : []
);

$rows = $st->fetchAll();

$fl = flashes();

require 'partials/header.php';
require 'partials/sidebar.php';

?>

<section class="page-head">

    <div>

        <h1>Consulta de Salas</h1>

        <p>Disponibilidade, capacidade e recursos</p>

    </div>

    <?php if (is_instructor()): ?>

        <button
            class="btn primary"
            onclick="openModal('salaModal')"
        >
            ＋ Nova sala
        </button>

    <?php endif; ?>

</section>

<div class="tabs">

    <a
        class="<?= $filter === '' ? 'selected' : '' ?>"
        href="salas.php"
    >
        Todas
    </a>

    <a
        class="<?= $filter === 'DISPONIVEL' ? 'selected' : '' ?>"
        href="?status=DISPONIVEL"
    >
        Disponíveis
    </a>

    <a
        class="<?= $filter === 'OCUPADA' ? 'selected' : '' ?>"
        href="?status=OCUPADA"
    >
        Ocupadas
    </a>

    <a
        class="<?= $filter === 'RESERVADA' ? 'selected' : '' ?>"
        href="?status=RESERVADA"
    >
        Reservadas
    </a>

</div>

<?php foreach ($fl as $f): ?>

    <div class="alert <?= e($f['type']) ?>">
        <?= e($f['message']) ?>
    </div>

<?php endforeach; ?>

<div class="rooms">

    <?php foreach ($rows as $r): ?>

        <article class="room-card">

            <div class="room-icon">
                ▤
            </div>

            <div class="room-body">

                <div class="room-title">

                    <h3>
                        <?= e($r['nome']) ?>
                    </h3>

                    <span class="status <?= strtolower($r['status']) ?>">
                        <?= e(ucfirst(strtolower($r['status']))) ?>
                    </span>

                </div>

                <p>

                    <b>
                        <?= e($r['bloco']) ?>
                    </b>

                    ·

                    <?= e(ucfirst(strtolower($r['tipo']))) ?>

                    ·

                    <?= $r['capacidade'] ?>
                    lugares

                </p>

                <p class="muted">
                    <?= e($r['descricao']) ?>
                </p>

                <?php if (is_instructor()): ?>

                    <button
                        class="btn small"
                        onclick='editSala(<?= json_encode($r) ?>)'
                    >
                        ✎ Editar sala
                    </button>

                <?php endif; ?>

            </div>

        </article>

    <?php endforeach; ?>

</div>

<?php if (is_instructor()): ?>

    <div
        class="modal"
        id="salaModal"
    >

        <div class="modal-box">

            <button
                class="modal-close"
                onclick="closeModal('salaModal')"
            >
                ×
            </button>

            <h2>Nova/Editar Sala</h2>

            <form method="post">

                <input
                    type="hidden"
                    name="id"
                    id="sala_id"
                    value="0"
                >

                <label>
                    Nome

                    <input
                        name="nome"
                        id="sala_nome"
                        required
                    >
                </label>

                <label>
                    Bloco

                    <input
                        name="bloco"
                        id="sala_bloco"
                    >
                </label>

                <label>
                    Tipo

                    <select
                        name="tipo"
                        id="sala_tipo"
                    >

                        <option value="LABORATORIO">
                            LABORATÓRIO
                        </option>

                        <option value="SALA_TEORICA">
                            SALA TEÓRICA
                        </option>

                        <option value="AUDITORIO">
                            AUDITÓRIO
                        </option>

                    </select>

                </label>

                <label>
                    Capacidade

                    <input
                        type="number"
                        name="capacidade"
                        id="sala_cap"
                        value="30"
                    >
                </label>

                <label>
                    Descrição

                    <textarea
                        name="descricao"
                        id="sala_desc"
                    ></textarea>

                </label>

                <label>
                    Status

                    <select
                        name="status"
                        id="sala_status"
                    >

                        <option value="DISPONIVEL">
                            DISPONÍVEL
                        </option>

                        <option value="OCUPADA">
                            OCUPADA
                        </option>

                        <option value="RESERVADA">
                            RESERVADA
                        </option>

                        <option value="MANUTENCAO">
                            MANUTENÇÃO
                        </option>

                    </select>

                </label>

                <button class="btn primary full">
                    Salvar sala
                </button>

            </form>

        </div>

    </div>

<?php endif; ?>

<?php require 'partials/footer.php'; ?>
