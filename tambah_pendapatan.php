<?php
include 'koneksi.php';

$conn = open_connection();

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $no = $_POST['no'] ?? '';
    $uraian = $_POST['uraian'] ?? '';
    $realisasi_2024 = $_POST['realisasi_2024'] ?? 0;
    $anggaran_2025 = $_POST['anggaran_2025'] ?? 0;
    $realisasi_2025 = $_POST['realisasi_2025'] ?? 0;
    $ach_64 = $_POST['ach_64'] ?? 0;
    $growth = $_POST['growth'] ?? 0;
    $analisis_vertical = $_POST['analisis_vertical'] ?? '';

    // Validate required fields
    if (empty($no)) $errors[] = "NO harus diisi.";
    if (empty($uraian)) $errors[] = "Uraian harus diisi.";

    if (empty($errors)) {
        $stmt = $conn->prepare("INSERT INTO pendapatan (no, Uraian, REALISASI_tahunSebelum, ANGGARAN_tahun, REALISASI_tahun, ACH, GROWTH, ANALISIS_VERTICAL) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssddddds", $no, $uraian, $realisasi_2024, $anggaran_2025, $realisasi_2025, $ach_64, $growth, $analisis_vertical);
        if ($stmt->execute()) {
            header("Location: pendapatan.php");
            exit;
        } else {
            $errors[] = "Gagal menyimpan data: " . $stmt->error;
        }
        $stmt->close();
    }
}
?>

<?php include 'header.php'; ?>

<main class="container mt-4">
    <h2>Tambah Data Pendapatan</h2>
    <?php if ($errors): ?>
        <div class="alert alert-danger">
            <ul>
                <?php foreach($errors as $error): ?>
                    <li><?= htmlspecialchars($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>
    <form method="POST" action="tambah_pendapatan.php">
        <div class="mb-3">
            <label for="no" class="form-label">NO</label>
            <input type="text" class="form-control" id="no" name="no" value="<?= htmlspecialchars($_POST['no'] ?? '') ?>" required>
        </div>
        <div class="mb-3">
            <label for="uraian" class="form-label">U R A I A N</label>
            <input type="text" class="form-control" id="uraian" name="uraian" value="<?= htmlspecialchars($_POST['uraian'] ?? '') ?>" required>
        </div>
        <div class="mb-3">
            <label for="realisasi_2024" class="form-label">APRIL 2024 REALISASI</label>
            <input type="number" step="0.01" class="form-control" id="realisasi_2024" name="realisasi_2024" value="<?= htmlspecialchars($_POST['realisasi_2024'] ?? 0) ?>">
        </div>
        <div class="mb-3">
            <label for="anggaran_2025" class="form-label">APRIL 2025 ANGGARAN</label>
            <input type="number" step="0.01" class="form-control" id="anggaran_2025" name="anggaran_2025" value="<?= htmlspecialchars($_POST['anggaran_2025'] ?? 0) ?>">
        </div>
        <div class="mb-3">
            <label for="realisasi_2025" class="form-label">2025 REALISASI</label>
            <input type="number" step="0.01" class="form-control" id="realisasi_2025" name="realisasi_2025" value="<?= htmlspecialchars($_POST['realisasi_2025'] ?? 0) ?>">
        </div>
        <div class="mb-3">
            <label for="ach_64" class="form-label">% Ach (6:4)</label>
            <input type="number" step="0.01" class="form-control" id="ach_64" name="ach_64" value="<?= htmlspecialchars($_POST['ach_64'] ?? 0) ?>">
        </div>
        <div class="mb-3">
            <label for="growth" class="form-label">% Gro (6:3)</label>
            <input type="number" step="0.01" class="form-control" id="growth" name="growth" value="<?= htmlspecialchars($_POST['growth'] ?? 0) ?>">
        </div>
        <div class="mb-3">
            <label for="analisis_vertical" class="form-label">ANALISIS VERTICAL</label>
            <textarea class="form-control" id="analisis_vertical" name="analisis_vertical" rows="3"><?= htmlspecialchars($_POST['analisis_vertical'] ?? '') ?></textarea>
        </div>
        <button type="submit" class="btn btn-success">Simpan</button>
        <a href="pendapatan.php" class="btn btn-secondary">Batal</a>
    </form>
</main>

<?php include 'footer.php'; ?>
