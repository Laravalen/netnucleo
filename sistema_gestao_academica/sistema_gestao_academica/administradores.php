<?php
declare(strict_types=1);

require_once __DIR__ . '/functions.php';
require_admin();

$title = 'Cadastro de Usuários';
$aba = $_GET['aba'] ?? 'instrutor';
if (!in_array($aba, ['instrutor', 'aluno'], true)) {
    $aba = 'instrutor';
}

const SENHA_INICIAL = 'SesiSenai@2026';

function validar_cpf(string $cpf): bool
{
    $cpf = cpf_digits($cpf);

    if (strlen($cpf) !== 11 || preg_match('/^(\d)\1{10}$/', $cpf)) {
        return false;
    }

    $soma = 0;
    for ($i = 0, $peso = 10; $i < 9; $i++, $peso--) {
        $soma += (int)$cpf[$i] * $peso;
    }
    $digito1 = ($soma * 10) % 11;
    if ($digito1 === 10) {
        $digito1 = 0;
    }

    $soma = 0;
    for ($i = 0, $peso = 11; $i < 10; $i++, $peso--) {
        $soma += (int)$cpf[$i] * $peso;
    }
    $digito2 = ($soma * 10) % 11;
    if ($digito2 === 10) {
        $digito2 = 0;
    }

    return (int)$cpf[9] === $digito1 && (int)$cpf[10] === $digito2;
}

function validar_data_nascimento(string $data): bool
{
    $dt = DateTime::createFromFormat('!Y-m-d', $data);
    $erros = DateTime::getLastErrors();

    if ($dt === false || ($erros !== false && ($erros['warning_count'] > 0 || $erros['error_count'] > 0))) {
        return false;
    }

    $hoje = new DateTime('today');
    return $dt <= $hoje;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $acao = $_POST['acao'] ?? '';

    // Ativa/desativa alunos e instrutores sem excluir o cadastro.
    if ($acao === 'toggle_status') {
        try {
            $usuarioId = (int)($_POST['usuario_id'] ?? 0);
            if ($usuarioId <= 0) {
                throw new InvalidArgumentException('Usuário inválido.');
            }

            $db = db();
            $st = $db->prepare("SELECT id, nome, perfil, status FROM usuarios WHERE id = ? AND perfil IN ('INSTRUTOR','ALUNO') LIMIT 1");
            $st->execute([$usuarioId]);
            $usuario = $st->fetch();

            if (!$usuario) {
                throw new InvalidArgumentException('Usuário não encontrado.');
            }

            $novoStatus = $usuario['status'] === 'ATIVO' ? 'INATIVO' : 'ATIVO';
            $st = $db->prepare('UPDATE usuarios SET status = ? WHERE id = ?');
            $st->execute([$novoStatus, $usuarioId]);

            // Mantém o status da tabela específica do instrutor sincronizado.
            if ($usuario['perfil'] === 'INSTRUTOR') {
                $st = $db->prepare('UPDATE instrutores SET ativo = ? WHERE usuario_id = ?');
                $st->execute([$novoStatus === 'ATIVO' ? 1 : 0, $usuarioId]);
            }

            audit('usuarios', $usuarioId, 'UPDATE', ['status' => $usuario['status']], ['status' => $novoStatus]);
            flash('success', $novoStatus === 'ATIVO' ? 'Usuário ativado com sucesso.' : 'Usuário desativado com sucesso.');
        } catch (Throwable $e) {
            flash('danger', $e->getMessage());
        }

        header('Location: administradores.php?aba=' . urlencode($_POST['aba'] ?? 'instrutor'));
        exit;
    }

    $tipo = $_POST['tipo'] ?? '';

    try {
        if (!in_array($tipo, ['INSTRUTOR', 'ALUNO'], true)) {
            throw new InvalidArgumentException('Tipo de usuário inválido.');
        }

        $cpfInformado = trim((string)($_POST['cpf'] ?? ''));
        $cpf = cpf_digits($cpfInformado);
        $nome = trim((string)($_POST['nome'] ?? ''));
        $email = trim((string)($_POST['email'] ?? ''));

        if ($nome === '') {
            throw new InvalidArgumentException('O nome é obrigatório.');
        }

        if (mb_strlen($nome) < 3 || mb_strlen($nome) > 150) {
            throw new InvalidArgumentException('O nome deve possuir entre 3 e 150 caracteres.');
        }

        if (!validar_cpf($cpfInformado)) {
            throw new InvalidArgumentException('Informe um CPF válido.');
        }

        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($email) > 180) {
            throw new InvalidArgumentException('Informe um e-mail válido.');
        }

        $db = db();

        $st = $db->prepare("SELECT id FROM usuarios WHERE REPLACE(REPLACE(REPLACE(cpf,'.',''),'-',''),' ','') = ? LIMIT 1");
        $st->execute([$cpf]);
        if ($st->fetch()) {
            throw new InvalidArgumentException('Este CPF já possui acesso ao sistema.');
        }

        $st = $db->prepare("SELECT id FROM usuarios WHERE LOWER(email) = LOWER(?) LIMIT 1");
        $st->execute([$email]);
        if ($st->fetch()) {
            throw new InvalidArgumentException('Este e-mail já está cadastrado.');
        }

        $senhaHash = password_hash(SENHA_INICIAL, PASSWORD_DEFAULT);
        if ($senhaHash === false) {
            throw new RuntimeException('Não foi possível gerar a senha inicial.');
        }

        $db->beginTransaction();

        $st = $db->prepare("\n            INSERT INTO usuarios\n                (cpf, nome, email, senha_hash, senha_provisoria, perfil, status)\n            VALUES\n                (?, ?, ?, ?, 1, ?, 'ATIVO')\n        ");
        $st->execute([$cpf, $nome, $email, $senhaHash, $tipo]);
        $usuarioId = (int)$db->lastInsertId();

        if ($tipo === 'INSTRUTOR') {
            $area = trim((string)($_POST['area'] ?? ''));
            if ($area === '') {
                throw new InvalidArgumentException('A área do instrutor é obrigatória.');
            }
            if (mb_strlen($area) > 120) {
                throw new InvalidArgumentException('A área deve possuir no máximo 120 caracteres.');
            }

            $st = $db->prepare("\n                INSERT INTO instrutores (usuario_id, cpf, area, email)\n                VALUES (?, ?, ?, ?)\n            ");
            $st->execute([$usuarioId, $cpf, $area, $email]);

            audit('usuarios', $usuarioId, 'INSERT', null, [
                'cpf' => $cpf,
                'nome' => $nome,
                'email' => $email,
                'perfil' => 'INSTRUTOR'
            ]);

            $db->commit();
            flash('success', 'Instrutor cadastrado com sucesso. A senha inicial é SesiSenai@2026.');
            header('Location: administradores.php?aba=instrutor');
            exit;
        }

        $matricula = trim((string)($_POST['matricula'] ?? ''));
        $dataNascimento = trim((string)($_POST['data_nascimento'] ?? ''));

        if ($matricula === '' || mb_strlen($matricula) > 40) {
            throw new InvalidArgumentException('A matrícula é obrigatória e deve possuir no máximo 40 caracteres.');
        }

        $st = $db->prepare("SELECT id FROM alunos WHERE matricula = ? LIMIT 1");
        $st->execute([$matricula]);
        if ($st->fetch()) {
            throw new InvalidArgumentException('Esta matrícula já está cadastrada.');
        }

        if ($dataNascimento !== '' && !validar_data_nascimento($dataNascimento)) {
            throw new InvalidArgumentException('Informe uma data de nascimento válida.');
        }

        $st = $db->prepare("\n            INSERT INTO alunos (usuario_id, matricula, data_nascimento)\n            VALUES (?, ?, ?)\n        ");
        $st->execute([$usuarioId, $matricula, $dataNascimento !== '' ? $dataNascimento : null]);

        audit('usuarios', $usuarioId, 'INSERT', null, [
            'cpf' => $cpf,
            'nome' => $nome,
            'email' => $email,
            'perfil' => 'ALUNO',
            'matricula' => $matricula
        ]);

        $db->commit();
        flash('success', 'Aluno cadastrado com sucesso. A senha inicial é SesiSenai@2026.');
        header('Location: administradores.php?aba=aluno');
        exit;

    } catch (Throwable $e) {
        if (isset($db) && $db instanceof PDO && $db->inTransaction()) {
            $db->rollBack();
        }

        if ((int)$e->getCode() === 23000) {
            flash('danger', 'CPF, e-mail ou matrícula já cadastrados.');
        } else {
            flash('danger', $e->getMessage());
        }

        header('Location: administradores.php?aba=' . ($tipo === 'ALUNO' ? 'aluno' : 'instrutor'));
        exit;
    }
}

$usuarios = db()->query("\n    SELECT\n        u.id, u.cpf, u.nome, u.email, u.perfil, u.status, u.senha_provisoria,\n        i.area, a.matricula, a.data_nascimento\n    FROM usuarios u\n    LEFT JOIN instrutores i ON i.usuario_id = u.id\n    LEFT JOIN alunos a ON a.usuario_id = u.id\n    WHERE u.perfil IN ('INSTRUTOR', 'ALUNO')\n    ORDER BY u.nome\n")->fetchAll();

$fl = flashes();
require 'partials/header.php';
require 'partials/sidebar.php';
?>

<section class="page-head">
    <div>
        <h1>Cadastro de Usuários</h1>
        <p>Área exclusiva do administrador para adicionar instrutores e alunos.</p>
    </div>
</section>

<?php foreach ($fl as $f): ?>
    <div class="alert <?= e($f['type']) ?> flash-fixed"><?= e($f['message']) ?></div>
<?php endforeach; ?>

<section class="panel admin-panel">
    <div class="panel-head">
        <div>
            <h2>Novo acesso ao sistema</h2>
            <p class="muted" style="margin:5px 0 0">Todos os novos usuários recebem inicialmente a senha padrão e deverão trocá-la no primeiro acesso.</p>
        </div>
        <span class="status green">Administrador</span>
    </div>

    <div class="tabs admin-tabs">
        <a href="administradores.php?aba=instrutor" class="<?= $aba === 'instrutor' ? 'selected' : '' ?>">♙ &nbsp; Instrutor</a>
        <a href="administradores.php?aba=aluno" class="<?= $aba === 'aluno' ? 'selected' : '' ?>">♙ &nbsp; Aluno</a>
    </div>

    <div class="admin-form-wrap">
        <?php if ($aba === 'instrutor'): ?>
            <form method="post" class="form-grid admin-form" autocomplete="off">
                <input type="hidden" name="tipo" value="INSTRUTOR">

                <label>CPF
                    <input name="cpf" placeholder="000.000.000-00" maxlength="14" required>
                </label>

                <label>Nome completo
                    <input name="nome" maxlength="150" required>
                </label>

                <label>E-mail
                    <input type="email" name="email" maxlength="180" placeholder="nome@senai.local" required>
                </label>

                <label>Área de atuação
                    <input name="area" maxlength="120" placeholder="Ex.: Tecnologia da Informação" required>
                </label>

                <div class="admin-password-note">
                    <b>Senha inicial</b>
                    <span>SesiSenai@2026</span>
                    <small>Será armazenada somente como hash. No primeiro acesso, o usuário deverá criar uma nova senha.</small>
                </div>

                <div class="admin-form-action">
                    <button class="btn primary" type="submit">Cadastrar instrutor</button>
                </div>
            </form>
        <?php else: ?>
            <form method="post" class="form-grid admin-form" autocomplete="off">
                <input type="hidden" name="tipo" value="ALUNO">

                <label>CPF
                    <input name="cpf" placeholder="000.000.000-00" maxlength="14" required>
                </label>

                <label>Nome completo
                    <input name="nome" maxlength="150" required>
                </label>

                <label>E-mail
                    <input type="email" name="email" maxlength="180" placeholder="nome@senai.local" required>
                </label>

                <label>Matrícula
                    <input name="matricula" maxlength="40" placeholder="ALU-2026-000003" required>
                </label>

                <label>Data de nascimento
                    <input type="date" name="data_nascimento" max="<?= date('Y-m-d') ?>">
                </label>

                <div class="admin-password-note">
                    <b>Senha inicial</b>
                    <span>SesiSenai@2026</span>
                    <small>Será armazenada somente como hash. No primeiro acesso, o aluno deverá criar uma nova senha.</small>
                </div>

                <div class="admin-form-action">
                    <button class="btn primary" type="submit">Cadastrar aluno</button>
                </div>
            </form>
        <?php endif; ?>
    </div>
</section>

<section class="panel">
    <div class="panel-head">
        <h2>Usuários cadastrados</h2>
        <span class="muted"><?= count($usuarios) ?> usuários</span>
    </div>

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>NOME</th>
                    <th>CPF</th>
                    <th>PERFIL</th>
                    <th>E-MAIL</th>
                    <th>INFORMAÇÃO</th>
                    <th>STATUS</th>
                    <th>AÇÕES</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($usuarios as $u): ?>
                    <tr>
                        <td><b><?= e($u['nome']) ?></b></td>
                        <td><?= e($u['cpf']) ?></td>
                        <td><?= e($u['perfil']) ?></td>
                        <td><?= e($u['email'] ?? '-') ?></td>
                        <td><?= $u['perfil'] === 'INSTRUTOR' ? e($u['area'] ?? '-') : e($u['matricula'] ?? '-') ?></td>
                        <td><span class="status <?= $u['status'] === 'ATIVO' ? 'green' : 'danger' ?>"><?= e(ucfirst(strtolower($u['status']))) ?></span></td>
                        <td>
                            <form method="post" class="user-status-form" onsubmit="return confirm('Deseja realmente <?= $u['status'] === 'ATIVO' ? 'desativar' : 'ativar' ?> este usuário?');">
                                <input type="hidden" name="acao" value="toggle_status">
                                <input type="hidden" name="usuario_id" value="<?= (int)$u['id'] ?>">
                                <input type="hidden" name="aba" value="<?= e($aba) ?>">
                                <button type="submit" class="btn small <?= $u['status'] === 'ATIVO' ? 'danger-outline' : 'success-outline' ?>">
                                    <?= $u['status'] === 'ATIVO' ? 'Desativar' : 'Ativar' ?>
                                </button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>

<style>
.admin-panel{margin-bottom:16px}
.admin-tabs{padding:0 32px 20px}
.admin-form-wrap{padding:0 32px 28px}
.admin-form{align-items:end}
.admin-form-action{grid-column:1/-1;display:flex;justify-content:flex-end;margin-top:4px}
.admin-password-note{background:#f3f6fb;border:1px solid #e0e6f0;border-radius:9px;padding:12px 14px;display:flex;flex-direction:column;gap:3px;min-height:78px}
.admin-password-note span{font-weight:800;color:#0b3d8e}
.admin-password-note small{color:#718096;line-height:1.35}
.user-status-form{margin:0}
.danger-outline,.success-outline{background:#fff;border:1px solid currentColor}
.danger-outline{color:#b33a35}
.success-outline{color:#16815a}
.user-status-form .btn.small{min-width:88px}
@media(max-width:800px){.admin-form-wrap{padding:0 18px 20px}.admin-tabs{padding-left:18px;padding-right:18px}.admin-form-action{grid-column:auto}}
</style>

<?php require 'partials/footer.php'; ?>
