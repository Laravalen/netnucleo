<?php
require_once __DIR__ . '/../functions.php';

/*
 * Evita que páginas autenticadas sejam reapresentadas pelo cache
 * do navegador depois do logout.
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache');
header('Expires: 0');
?>
<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e(page_title($title ?? 'Gestão Acadêmica')) ?></title>

    <link rel="stylesheet" href="assets/css/style.css?v=20260907">

    <script defer src="assets/js/app.js"></script>
</head>
<body>
<div class="app">
