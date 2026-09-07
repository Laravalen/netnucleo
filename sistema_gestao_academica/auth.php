<?php
declare(strict_types=1);
session_start();
require_once __DIR__.'/config.php';

function e($value): string { return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8'); }
function cpf_digits(string $cpf): string { return preg_replace('/\D+/', '', $cpf) ?? ''; }
function logged(): bool { return isset($_SESSION['user']); }
function user(): ?array { return $_SESSION['user'] ?? null; }
function is_instructor(): bool { return logged() && ($_SESSION['user']['perfil'] ?? '') === 'INSTRUTOR'; }

function require_login(): void {
    if (!logged()) { header('Location: index.php'); exit; }
}
function require_instructor(): void {
    require_login();
    if (!is_instructor()) {
        http_response_code(403);
        exit('Acesso negado.');
    }
}
function flash(string $type, string $message): void { $_SESSION['flash'][]=['type'=>$type,'message'=>$message]; }
function flashes(): array {
    $f=$_SESSION['flash']??[]; unset($_SESSION['flash']); return $f;
}
function login_user(string $cpf,string $senha): bool {
    $st=db()->prepare("SELECT * FROM usuarios WHERE REPLACE(REPLACE(REPLACE(cpf,'.',''),'-',''),' ','')=? AND status='ATIVO' LIMIT 1");
    $st->execute([cpf_digits($cpf)]);
    $u=$st->fetch();
    if ($u && password_verify($senha,$u['senha_hash'])) {
        $_SESSION['user']=['id'=>$u['id'],'nome'=>$u['nome'],'cpf'=>$u['cpf'],'perfil'=>$u['perfil']];
        return true;
    }
    return false;
}
