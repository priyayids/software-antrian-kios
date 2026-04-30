<?php
$host     = getenv('DB_HOST') ?: 'db';
$username = getenv('DB_USER') ?: 'root';
$password = getenv('DB_PASS') ?: 'root';
$database = getenv('DB_NAME') ?: 'aplikasi_antrian';

$mysqli = mysqli_connect($host, $username, $password, $database);

if (!$mysqli) {
    die('Koneksi Database Gagal : ' . mysqli_connect_error());
}
