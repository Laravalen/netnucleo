<?php

declare(strict_types=1);

require_once __DIR__ . '/auth.php';

if (logged()) {
    header('Location: dashboard.php');
    exit;
}

$errors = [];

$cpf = '';
$nome = '';
$email = '';
$data_nascimento = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $cpf = trim($_POST['cpf'] ?? '');
    $nome = trim($_POST['nome'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $data_nascimento = trim($_POST['data_nascimento'] ?? '');
    $senha = $_POST['senha'] ?? '';
    $confirmar_senha = $_POST['confirmar_senha'] ?? '';

    /*
     * =========================================================
     * VALIDAÇÕES
     * =========================================================
     */

    $cpfNumeros = cpf_digits($cpf);

    if (strlen($cpfNumeros) !== 11) {
        $errors[] = 'Digite um CPF válido com 11 números.';
    }

    if ($nome === '') {
        $errors[] = 'Informe seu nome completo.';
    } elseif (mb_strlen($nome) < 3) {
        $errors[] = 'O nome deve possuir pelo menos 3 caracteres.';
    }

    if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Informe um e-mail válido.';
    }

    if ($senha === '') {
        $errors[] = 'Informe uma senha.';
    } elseif (strlen($senha) < 6) {
        $errors[] = 'A senha deve possuir pelo menos 6 caracteres.';
    }

    if ($senha !== $confirmar_senha) {
        $errors[] = 'As senhas não coincidem.';
    }

    /*
     * =========================================================
     * DATA DE NASCIMENTO
     * =========================================================
     */

    if ($data_nascimento !== '') {

        $data = DateTime::createFromFormat(
            'Y-m-d',
            $data_nascimento
        );

        if (
            !$data ||
            $data->format('Y-m-d') !== $data_nascimento
        ) {
            $errors[] = 'Informe uma data de nascimento válida.';
        }
    } else {
        $data_nascimento = null;
    }


    /*
     * =========================================================
     * PROCESSAMENTO DO CADASTRO
     * =========================================================
     */

    if (!$errors) {

        $pdo = null;

        try {

            $pdo = db();

            /*
             * -------------------------------------------------
             * Procura se o CPF pertence a um instrutor.
             * -------------------------------------------------
             */

            $stmt = $pdo->prepare(
                "SELECT
                    i.id AS instrutor_id,
                    i.usuario_id,
                    i.cpf,
                    i.ativo,
                    u.id AS user_id,
                    u.nome AS user_nome,
                    u.email AS user_email,
                    u.perfil,
                    u.status
                 FROM instrutores i
                 INNER JOIN usuarios u
                    ON u.id = i.usuario_id
                 WHERE REPLACE(
                         REPLACE(
                           REPLACE(i.cpf, '.', ''),
                         '-', ''),
                       ' ', '') = ?
                 LIMIT 1"
            );

            $stmt->execute([$cpfNumeros]);

            $instrutor = $stmt->fetch();


            /*
             * =================================================
             * CASO 1:
             * CPF PERTENCE A UM INSTRUTOR
             * =================================================
             */

            if ($instrutor) {

                /*
                 * O CPF já pertence à estrutura de instrutores.
                 *
                 * A conta existente será ativada/atualizada.
                 */

                $pdo->beginTransaction();

                $senhaHash = password_hash(
                    $senha,
                    PASSWORD_DEFAULT
                );

                $update = $pdo->prepare(
                    "UPDATE usuarios
                     SET
                        nome = ?,
                        email = ?,
                        senha_hash = ?,
                        perfil = 'INSTRUTOR',
                        status = 'ATIVO'
                     WHERE id = ?"
                );

                $update->execute([
                    $nome,
                    $email !== '' ? $email : null,
                    $senhaHash,
                    $instrutor['usuario_id']
                ]);


                /*
                 * Garante que o cadastro do instrutor
                 * também esteja ativo.
                 */

                $updateInstrutor = $pdo->prepare(
                    "UPDATE instrutores
                     SET
                        cpf = ?,
                        email = ?,
                        ativo = 1
                     WHERE id = ?"
                );

                $updateInstrutor->execute([
                    $cpf,
                    $email !== '' ? $email : null,
                    $instrutor['instrutor_id']
                ]);


                $pdo->commit();

                header('Location: index.php?cadastro=ok');
                exit;
            }


            /*
             * =================================================
             * CASO 2:
             * CPF JÁ EXISTE EM USUÁRIOS
             * =================================================
             *
             * Isso impede criar um segundo cadastro para
             * o mesmo CPF.
             */

            $stmt = $pdo->prepare(
                "SELECT id, perfil
                 FROM usuarios
                 WHERE REPLACE(
                         REPLACE(
                           REPLACE(cpf, '.', ''),
                         '-', ''),
                       ' ', '') = ?
                 LIMIT 1"
            );

            $stmt->execute([$cpfNumeros]);

            $usuarioExistente = $stmt->fetch();


            if ($usuarioExistente) {

                $errors[] =
                    'Este CPF já possui uma conta cadastrada. ' .
                    'Entre no sistema utilizando suas credenciais.';

            } else {

                /*
                 * =================================================
                 * VERIFICAÇÃO DE E-MAIL
                 * =================================================
                 */

                if ($email !== '') {

                    $stmt = $pdo->prepare(
                        "SELECT id
                         FROM usuarios
                         WHERE email = ?
                         LIMIT 1"
                    );

                    $stmt->execute([$email]);

                    if ($stmt->fetch()) {

                        $errors[] =
                            'Este e-mail já está cadastrado.';
                    }
                }


                /*
                 * =================================================
                 * CRIAÇÃO DO ALUNO
                 * =================================================
                 */

                if (!$errors) {

                    $pdo->beginTransaction();

                    /*
                     * Gera a senha protegida.
                     */

                    $senhaHash = password_hash(
                        $senha,
                        PASSWORD_DEFAULT
                    );


                    /*
                     * Cria o usuário.
                     */

                    $insertUsuario = $pdo->prepare(
                        "INSERT INTO usuarios
                        (
                            cpf,
                            nome,
                            email,
                            senha_hash,
                            perfil,
                            status
                        )
                        VALUES
                        (
                            ?,
                            ?,
                            ?,
                            ?,
                            'ALUNO',
                            'ATIVO'
                        )"
                    );

                    $insertUsuario->execute([
                        $cpf,
                        $nome,
                        $email !== '' ? $email : null,
                        $senhaHash
                    ]);

                    $usuarioId = (int)$pdo->lastInsertId();


                    /*
                     * -------------------------------------------------
                     * Geração da matrícula
                     * -------------------------------------------------
                     *
                     * Exemplo:
                     *
                     * ALU-2026-000001
                     */

                    $ano = date('Y');

                    do {

                        $numero = random_int(
                            100000,
                            999999
                        );

                        $matricula =
                            'ALU-' .
                            $ano .
                            '-' .
                            $numero;

                        $stmt = $pdo->prepare(
                            "SELECT id
                             FROM alunos
                             WHERE matricula = ?
                             LIMIT 1"
                        );

                        $stmt->execute([
                            $matricula
                        ]);

                        $matriculaExiste =
                            $stmt->fetchColumn();

                    } while ($matriculaExiste);


                    /*
                     * Cria o registro do aluno.
                     */

                    $insertAluno = $pdo->prepare(
                        "INSERT INTO alunos
                        (
                            usuario_id,
                            matricula,
                            data_nascimento
                        )
                        VALUES
                        (
                            ?,
                            ?,
                            ?
                        )"
                    );

                    $insertAluno->execute([
                        $usuarioId,
                        $matricula,
                        $data_nascimento
                    ]);


                    /*
                     * Finaliza a transação.
                     */

                    $pdo->commit();


                    /*
                     * Volta para a tela de login.
                     */

                    header('Location: index.php?cadastro=ok');
                    exit;
                }
            }

        } catch (Throwable $e) {

            /*
             * Se algo der errado durante a transação,
             * desfaz as alterações.
             */

            if (
                $pdo instanceof PDO &&
                $pdo->inTransaction()
            ) {
                $pdo->rollBack();
            }

            /*
             * Não mostramos detalhes internos do banco
             * ao usuário.
             */

            $errors[] =
                'Não foi possível concluir o cadastro. ' .
                'Verifique os dados e tente novamente.';
        }
    }
}

?>

<!doctype html>

<html lang="pt-BR">

<head>

    <meta charset="utf-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1"
    >

    <title>
        Criar conta | Gestão Acadêmica
    </title>

    <link
        rel="stylesheet"
        href="assets/css/style.css?v=20260907"
    >

</head>

<body class="login-page">


    <!-- =====================================================
         PAINEL ESQUERDO
         ===================================================== -->

    <section class="login-brand">

        <div class="brand">

            <span class="brand-mark">
                ⌂
            </span>

            <div>

                <b>
                    SESI SENAI
                </b>

                <small>
                    Sistema de Gestão Acadêmica
                </small>

            </div>

        </div>


        <div class="login-quote">

            "Educação profissional que transforma vidas
            e impulsiona o desenvolvimento do Brasil."

        </div>


        <div class="login-features">

            <span>
                ▣ &nbsp;
                Gerenciamento completo de cursos e turmas
            </span>

            <span>
                □ &nbsp;
                Controle de horários e frequência de aulas
            </span>

            <span>
                ▥ &nbsp;
                Relatórios e indicadores de desempenho
            </span>

            <span>
                ♢ &nbsp;
                Acesso seguro com perfis por cargo
            </span>

        </div>


        <div class="login-footer">

            © 2025 FIEMG · SESI · SENAI ·
            Todos os direitos reservados

        </div>

    </section>


    <!-- =====================================================
         FORMULÁRIO
         ===================================================== -->

    <section class="login-card">

        <div class="card-inner">

            <div class="mini-logo">
                ⌂
            </div>


            <h2>
                Criar nova conta
            </h2>


            <p class="muted">

                Preencha seus dados para acessar
                o sistema acadêmico.

            </p>


            <!-- =================================================
                 ERROS
                 ================================================= -->

            <?php if ($errors): ?>

                <div
                    class="alert danger"
                    style="margin: 0 0 18px"
                >

                    <ul
                        style="
                            margin:0;
                            padding-left:20px;
                        "
                    >

                        <?php foreach ($errors as $error): ?>

                            <li>
                                <?= e($error) ?>
                            </li>

                        <?php endforeach; ?>

                    </ul>

                </div>

            <?php endif; ?>


            <!-- =================================================
                 FORMULÁRIO DE CADASTRO
                 ================================================= -->

            <form
                method="post"
                action="criar_conta.php"
            >


                <!-- CPF -->

                <label>

                    CPF

                    <input
                        type="text"
                        name="cpf"
                        placeholder="000.000.000-00"
                        value="<?= e($cpf) ?>"
                        maxlength="14"
                        required
                        autofocus
                    >

                </label>


                <!-- NOME -->

                <label>

                    NOME COMPLETO

                    <input
                        type="text"
                        name="nome"
                        placeholder="Digite seu nome completo"
                        value="<?= e($nome) ?>"
                        maxlength="150"
                        required
                    >

                </label>


                <!-- E-MAIL -->

                <label>

                    E-MAIL

                    <input
                        type="email"
                        name="email"
                        placeholder="seu.email@exemplo.com"
                        value="<?= e($email) ?>"
                        maxlength="180"
                    >

                </label>


                <!-- DATA DE NASCIMENTO -->

                <label>

                    DATA DE NASCIMENTO

                    <input
                        type="date"
                        name="data_nascimento"
                        value="<?= e($data_nascimento ?? '') ?>"
                    >

                </label>


                <!-- SENHA -->

                <label>

                    SENHA

                    <div class="password">

                        <input
                            type="password"
                            name="senha"
                            placeholder="Mínimo de 6 caracteres"
                            required
                            autocomplete="new-password"
                        >

                        <button
                            type="button"
                            class="eye"
                            onclick="togglePassword(this)"
                            title="Mostrar senha"
                        >
                            ◉
                        </button>

                    </div>

                </label>


                <!-- CONFIRMAÇÃO -->

                <label>

                    CONFIRMAR SENHA

                    <div class="password">

                        <input
                            type="password"
                            name="confirmar_senha"
                            placeholder="Digite a senha novamente"
                            required
                            autocomplete="new-password"
                        >

                        <button
                            type="button"
                            class="eye"
                            onclick="togglePassword(this)"
                            title="Mostrar senha"
                        >
                            ◉
                        </button>

                    </div>

                </label>


                <!-- BOTÃO -->

                <button
                    type="submit"
                    class="btn primary full"
                >
                    ＋ &nbsp;
                    Criar conta
                </button>


            </form>


            <!-- =================================================
                 VOLTAR PARA LOGIN
                 ================================================= -->

            <div
                style="
                    text-align:center;
                    margin-top:18px;
                "
            >

                <a href="index.php">

                    ← Voltar para o login

                </a>

            </div>


            <p class="help-mail">

                Problemas?

                <b>
                    ti@senai.org.br
                </b>

            </p>

        </div>

    </section>


    <script src="assets/js/app.js"></script>

</body>

</html>