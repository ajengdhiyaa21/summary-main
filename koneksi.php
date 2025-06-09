<?php
$host = "localhost";
$user = "root";
$pass = ""; // sesuaikan dengan password MySQL Anda
$db   = "kai"; // ganti dengan nama database Anda

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}
