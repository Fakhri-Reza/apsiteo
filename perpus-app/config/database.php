<?php
// config/database.php

$host    = getenv('MYSQLHOST') ?: '127.0.0.1';
$db      = getenv('MYSQLDATABASE') ?: 'perpustakaan_db';
$user    = getenv('MYSQLUSER') ?: 'root';
$pass    = getenv('MYSQLPASSWORD') ?: ''; // Password default MySQL pada Laragon kosong
$port    = getenv('MYSQLPORT') ?: '3306';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;port=$port;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    die("Koneksi database gagal: " . $e->getMessage());
}
