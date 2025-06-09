<?php
session_start();
include 'koneksi.php'; // koneksi ke database

$username = $_POST['username'];
$password = $_POST['password'];

// Cek user
$query = "SELECT * FROM users WHERE username = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if ($user) {
    if (password_verify($password, $user['password'])) {
        // Login berhasil
        $_SESSION['username'] = $user['username'];
        $_SESSION['email'] = $user['email'];
        header("Location: dashboard.php");
        exit();
    } else {
        header("Location: login.php?error=Password salah.");
        exit();
    }
} else {
    header("Location: login.php?error=Username tidak ditemukan.");
    exit();
}
