<?php
declare(strict_types=1);

const DB_HOST = '127.0.0.1';
const DB_NAME = 'gestao_academica';
const DB_USER = 'root';
const DB_PASS = '123';

date_default_timezone_set('America/Sao_Paulo');

function db(): PDO {
    static $pdo = null;
    if ($pdo instanceof PDO) return $pdo;

    $pdo = new PDO(
        'mysql:host='.DB_HOST.';dbname='.DB_NAME.';charset=utf8mb4',
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
    return $pdo;
}
