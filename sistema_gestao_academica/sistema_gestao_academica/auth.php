<?php
declare(strict_types=1);

session_start();
require_once __DIR__.'/config.php';

function e($value): string { return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8'); }
function cpf_digits(string $cpf): string { return preg_replace('/\D+/', '', $cpf) ?? ''; }
function logged(): bool { return isset($_SESSION['user']); }
function user(): ?array { return $_SESSION['user'] ?? null; }
function is_admin(): bool { return logged() && ($_SESSION['user']['perfil'] ?? '') === 'ADMIN'; }
function is_instructor(): bool { return logged() && ($_SESSION['user']['perfil'] ?? '') === 'INSTRUTOR'; }
function can_edit(): bool { return is_admin(); }

/*
 * Retorna true quando o usuário ainda precisa trocar a senha padrão.
 */
function must_change_password(): bool {
    return logged() && !empty($_SESSION['user']['senha_provisoria']);
}

function require_login(): void {
    if (!logged()) {
        header('Location: index.php');
        exit;
    }

    // Atualiza o perfil diretamente do banco em cada requisição.
    // Isso evita que uma promoção para ADMIN fique presa em uma sessão
    // antiga que ainda esteja marcada como ALUNO/INSTRUTOR.
    try {
        $st = db()->prepare("SELECT id, nome, cpf, perfil, status, senha_provisoria FROM usuarios WHERE id = ? LIMIT 1");
        $st->execute([(int)($_SESSION['user']['id'] ?? 0)]);
        $u = $st->fetch();

        if (!$u || $u['status'] !== 'ATIVO') {
            $_SESSION = [];
            session_destroy();
            header('Location: index.php');
            exit;
        }

        $_SESSION['user']['id'] = $u['id'];
        $_SESSION['user']['nome'] = $u['nome'];
        $_SESSION['user']['cpf'] = $u['cpf'];
        $_SESSION['user']['perfil'] = $u['perfil'];
        $_SESSION['user']['senha_provisoria'] = (int)$u['senha_provisoria'];
    } catch (Throwable $e) {
        // Se a sessão já está autenticada, não a invalida por um erro
        // transitório de sincronização; as páginas continuarão protegidas.
    }

    /*
     * Depois do primeiro login, o usuário só pode acessar
     * a tela de troca de senha até definir sua senha pessoal.
     */
    if (must_change_password() && basename($_SERVER['PHP_SELF']) !== 'alterar_senha.php') {
        header('Location: alterar_senha.php');
        exit;
    }
}

function require_admin(): void {
    require_login();

    if (!is_admin()) {
        http_response_code(403);
        exit('Acesso negado.');
    }
}

function require_instructor(): void {
    require_login();

    if (!is_instructor()) {
        http_response_code(403);
        exit('Acesso negado.');
    }
}

function require_editor(): void {
    require_login();

    if (!can_edit()) {
        http_response_code(403);
        exit('Acesso negado. Somente administradores podem editar o sistema.');
    }
}

function flash(string $type, string $message): void {
    $_SESSION['flash'][]=['type'=>$type,'message'=>$message];
}

function flashes(): array {
    $f=$_SESSION['flash']??[];
    unset($_SESSION['flash']);
    return $f;
}

function login_user(string $cpf,string $senha): bool {
    $st=db()->prepare("
        SELECT *
        FROM usuarios
        WHERE REPLACE(REPLACE(REPLACE(cpf,'.',''),'-',''),' ','')=?
          AND status='ATIVO'
        LIMIT 1
    ");
    $st->execute([cpf_digits($cpf)]);
    $u=$st->fetch();

    if ($u && password_verify($senha,$u['senha_hash'])) {
        $_SESSION['user']=[
            'id'=>$u['id'],
            'nome'=>$u['nome'],
            'cpf'=>$u['cpf'],
            'perfil'=>$u['perfil'],
            'senha_provisoria'=>(int)($u['senha_provisoria'] ?? 0)
        ];

        return true;
    }

    return false;
}
