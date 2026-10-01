<?php
declare(strict_types=1);

require_once __DIR__ . '/auth.php';

if (!logged()) {
    header('Location: index.php');
    exit;
}

if (!must_change_password()) {
    header('Location: dashboard.php');
    exit;
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $novaSenha = $_POST['nova_senha'] ?? '';
    $confirmarSenha = $_POST['confirmar_senha'] ?? '';

    if (strlen($novaSenha) < 8) {
        $errors[] = 'A nova senha deve possuir pelo menos 8 caracteres.';
    }

    if ($novaSenha === 'SesiSenai@2026') {
        $errors[] = 'A nova senha não pode ser igual à senha padrão.';
    }

    if ($novaSenha !== $confirmarSenha) {
        $errors[] = 'As senhas não coincidem.';
    }

    if (!$errors) {
        $hash = password_hash($novaSenha, PASSWORD_DEFAULT);

        $st = db()->prepare("
            UPDATE usuarios
            SET senha_hash = ?, senha_provisoria = 0
            WHERE id = ?
        ");
        $st->execute([$hash, user()['id']]);

        $_SESSION['user']['senha_provisoria'] = 0;

        header('Location: dashboard.php');
        exit;
    }
}
?>
<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Definir nova senha | Gestão Acadêmica</title>
    <link rel="stylesheet" href="assets/css/style.css?v=20260917">
</head>

<body class="login-page">

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
        <span>▣ &nbsp; Gerenciamento completo de cursos e turmas</span>
        <span>□ &nbsp; Controle de horários e frequência de aulas</span>
        <span>▥ &nbsp; Relatórios e indicadores de desempenho</span>
        <span>♢ &nbsp; Acesso seguro com perfis por cargo</span>
    </div>

    <div class="login-footer">
        © 2026 FIEMG · SESI · SENAI · Todos os direitos reservados
    </div>
</section>

<section class="login-card">
    <div class="card-inner">
        <h2>Crie sua senha pessoal</h2>

        <p class="muted">
            Por segurança, a senha padrão deve ser substituída
            antes de acessar o sistema.
        </p>

        <?php if ($errors): ?>
            <div class="alert danger" style="margin:0 0 18px">
                <ul style="margin:0;padding-left:20px">
                    <?php foreach ($errors as $error): ?>
                        <li><?= e($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="post" action="alterar_senha.php">
            <label>
                NOVA SENHA
                <div class="password">
                    <input
                        type="password"
                        name="nova_senha"
                        minlength="8"
                        required
                        autocomplete="new-password"
                    >
                    <button
                        type="button"
                        class="eye"
                        onclick="togglePassword(this)"
                        title="Mostrar senha"
                    >◉</button>
                </div>
            </label>

            <label>
                CONFIRMAR NOVA SENHA
                <div class="password">
                    <input
                        type="password"
                        name="confirmar_senha"
                        minlength="8"
                        required
                        autocomplete="new-password"
                    >
                    <button
                        type="button"
                        class="eye"
                        onclick="togglePassword(this)"
                        title="Mostrar senha"
                    >◉</button>
                </div>
            </label>

            <button type="submit" class="btn primary full">
                Salvar nova senha
            </button>
        </form>

        <p class="help-mail">
            Usuário: <b><?= e(user()['nome']) ?></b><br>
            CPF: <b><?= e(user()['cpf']) ?></b>
        </p>
    </div>
</section>

<script src="assets/js/app.js"></script>
</body>
</html>
