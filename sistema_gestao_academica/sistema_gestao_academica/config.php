<?php
declare(strict_types=1);

const DB_HOST = '127.0.0.1';
const DB_PORT = '3307';
const DB_NAME = 'gestao_academica';
const DB_USER = 'gestao_app';
const DB_PASS = 'B4ancod4dad0s22028*!';

date_default_timezone_set('America/Sao_Paulo');

function db(): PDO
{
    static $pdo = null;

    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $pdo = new PDO(
        'mysql:host=' . DB_HOST .
        ';port=' . DB_PORT .
        ';dbname=' . DB_NAME .
        ';charset=utf8mb4',
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