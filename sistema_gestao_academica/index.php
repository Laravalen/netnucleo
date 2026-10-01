<?php

require_once __DIR__ . '/auth.php';

if (logged()) {
    header('Location: dashboard.php');
    exit;
}

$err = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $cpf = $_POST['cpf'] ?? '';
    $senha = $_POST['senha'] ?? '';

    if (login_user($cpf, $senha)) {
        if (must_change_password()) {
            header('Location: alterar_senha.php');
        } else {
            header('Location: dashboard.php');
        }
        exit;
    }

    $err = 'Usuário/CPF ou senha inválidos.';
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
        Acesso | Gestão Acadêmica
    </title>

    <link
        rel="stylesheet"
        href="assets/css/style.css?v=20260917b"
    >

</head>

<body class="login-page">

    <!-- =====================================================
         PAINEL ESQUERDO
         ===================================================== -->

    <section class="login-brand">

        <div class="brand login-brand-logos">
            <div class="brand-logos" aria-label="SESI e SENAI">
                <img src="assets/img/logo-sesi.png" class="brand-logo brand-logo-sesi" alt="SESI">
                <img src="assets/img/logo-senai.png" class="brand-logo brand-logo-senai" alt="SENAI">
            </div>

            <div class="brand-copy">
                <small>Sistema de Gestão Acadêmica</small>
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
         PAINEL DE LOGIN
         ===================================================== -->

    <section class="login-card">

        <div class="card-inner">

            <h2>
                Acesse sua conta
            </h2>


            <p class="muted">
                Informe seu CPF e sua senha para acessar o sistema.
            </p>


            <!-- Mensagem de erro -->

            <?php if ($err): ?>

                <div
                    class="alert danger"
                    style="margin: 0 0 18px"
                >
                    <?= e($err) ?>
                </div>

            <?php endif; ?>


            <!-- Mensagem de cadastro realizado -->

            <?php if (isset($_GET['cadastro']) && $_GET['cadastro'] === 'ok'): ?>

                <div
                    class="alert success"
                    style="margin: 0 0 18px"
                >
                    Conta criada com sucesso!
                    Agora você pode entrar no sistema.
                </div>

            <?php endif; ?>


            <!-- =================================================
                 FORMULÁRIO DE LOGIN
                 ================================================= -->

            <form
                method="post"
                action="index.php"
            >

                <label>

                    CPF

                    <input
                        type="text"
                        name="cpf"
                        placeholder="000.000.000-00"
                        value="<?= e($_POST['cpf'] ?? '') ?>"
                        required
                        autofocus
                        autocomplete="username"
                    >

                </label>


                <label>

                    SENHA

                    <div class="password">

                        <input
                            type="password"
                            name="senha"
                            placeholder="••••••••"
                            required
                            autocomplete="current-password"
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


                <div class="row-between">

                    <label class="check">

                        <input
                            type="checkbox"
                            name="remember"
                        >

                        Lembrar acesso

                    </label>


                    <a
                        href="#"
                        onclick="alert('Procure um instrutor ou administrador para redefinir sua senha.'); return false;"
                    >
                        Esqueci a senha
                    </a>

                </div>


                <button
                    type="submit"
                    class="btn primary full"
                >
                    ♙ &nbsp;
                    Entrar no sistema
                </button>

            </form>

            <p class="help-mail">

                Problemas?

                <b>
                    ti@senai.org.br
                </b>

            </p>

        </div>

    </section>


    <script src="assets/js/app.js"></script>
<script src="assets/js/app.js"></script>

<!-- VLibras -->
<div vw class="enabled">
    <div vw-access-button class="active"></div>

    <div vw-plugin-wrapper>
        <div class="vw-plugin-top-wrapper"></div>
    </div>
</div>

<script src="https://vlibras.gov.br/app/vlibras-plugin.js"></script>

<script>
    new window.VLibras.Widget('https://vlibras.gov.br/app');
</script>

</body>

</html>
