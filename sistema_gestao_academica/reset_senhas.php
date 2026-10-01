<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';

$senhaProvisoria = 'SesiSenai@2026';

try {
    $db = db();

    // Gera um hash compatível com password_verify()
    $hash = password_hash($senhaProvisoria, PASSWORD_DEFAULT);

    if ($hash === false) {
        throw new RuntimeException('Não foi possível gerar o hash da senha.');
    }

    // Atualiza TODOS os usuários
    $stmt = $db->prepare("
        UPDATE usuarios
        SET senha_hash = ?,
            senha_provisoria = 1
    ");

    $stmt->execute([$hash]);

    echo '<h2>Senhas resetadas com sucesso!</h2>';
    echo '<p>Todos os usuários da tabela <b>usuarios</b> foram redefinidos.</p>';
    echo '<p>Senha provisória: <b>SesiSenai@2026</b></p>';
    echo '<p>Quantidade de usuários afetados: <b>' . $stmt->rowCount() . '</b></p>';
    echo '<p>Todos deverão criar uma nova senha no próximo acesso.</p>';

} catch (Throwable $e) {
    http_response_code(500);

    echo '<h2>Erro ao resetar as senhas</h2>';
    echo '<pre>' . htmlspecialchars($e->getMessage()) . '</pre>';
}