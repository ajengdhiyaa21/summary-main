<?php
// Koneksi database
$conn = new mysqli("localhost", "root", "", "kai");
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    echo "ID tidak valid.";
    exit;
}

// Hapus data investasi berdasarkan id
$stmt = $conn->prepare("DELETE FROM investasi WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$stmt->close();

header("Location: investasi.php");
exit;
?>
