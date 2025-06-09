<?php
include 'koneksi.php';

$conn = open_connection();

$id = $_GET['id'] ?? null;
if (!$id) {
    header("Location: pendapatan.php");
    exit;
}

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

    if (empty($no)) $errors[] = "NO harus diisi.";
    if (empty($uraian)) $errors[] = "Uraian harus diisi.";

    if (empty($errors)) {
        $stmt = $conn->prepare("UPDATE pendapatan SET no=?, Uraian=?, REALISASI_tahunSebelum=?, ANGGARAN_tahun=?, REALISASI_tahun=?, ACH=?, GROWTH=?, ANALISIS_VERTICAL=? WHERE id=?");
        $stmt->bind_param("ssdddddsi", $no, $uraian, $realisasi_2024, $anggaran_2025, $realisasi_2025, $ach_64, $growth, $analisis_vertical, $id);
        if ($stmt->execute()) {
            header("Location: pendapatan.php");
            exit;
        } else {
            $errors[] = "Gagal memperbarui data: " . $stmt->error;
        }
        $stmt->close();
    }
} else {
    $stmt = $conn->prepare("SELECT * FROM pendapatan WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows === 0) {
        header("Location: pendapatan.php");
        exit;
    }
    $row = $result->fetch_assoc();
    $no = $row['no'];
    $uraian = $row['Uraian'];
    $realisasi_2024 = $row['REALISASI_tahunSebelum'];
    $anggaran_2025 = $row['ANGGARAN_tahun'];
    $realisasi_2025 = $row['REALISASI_tahun'];
    $ach_64 = $row['ACH'];
    $growth = $row['GROWTH'];
    $analisis_vertical = $row['ANALISIS_VERTICAL'];
    $stmt->close();
}
?>

<?php include 'header.php'; ?>

<main class="container mt-4">
    <h2>Edit Data Pendapatan</h2>
    <?php if ($errors): ?>
        <div class="alert alert-danger">
            <ul>
                <?php foreach($errors as $error): ?>
                    <li><?= htmlspecialchars($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>
    <form method="POST" action="edit_pendapatan.php?id=<?= $id ?>">
        <div class="mb-3">
            <label for="no" class="form-label">NO</label>
            <input type="text" class="form-control" id="no" name="no" value="<?= htmlspecialchars($no) ?>" required>
        </div>
        <div class="mb-3">
            <label for="uraian" class="form-label">U R A I A N</label>
            <input type="text" class="form-control" id="uraian" name="uraian" value="<?= htmlspecialchars($uraian) ?>" required>
        </div>
        <div class="mb-3">
            <label for="realisasi_2024" class="form-label">APRIL 2024 REALISASI</label>
            <input type="number" step="0.01" class="form-control" id="realisasi_2024" name="realisasi_2024" value="<?= htmlspecialchars($realisasi_2024) ?>">
        </div>
        <div class="mb-3">
            <label for="anggaran_2025" class="form-label">APRIL 2025 ANGGARAN</label>
            <input type="number" step="0.01" class="form-control" id="anggaran_2025" name="anggaran_2025" value="<?= htmlspecialchars($anggaran_2025) ?>">
        </div>
        <div class="mb-3">
            <label for="realisasi_2025" class="form-label">2025 REALISASI</label>
            <input type="number" step="0.01" class="form-control" id="realisasi_2025" name="realisasi_2025" value="<?= htmlspecialchars($realisasi_2025) ?>">
        </div>
        <div class="mb-3">
            <label for="ach_64" class="form-label">% Ach (6:4)</label>
            <input type="number" step="0.01" class="form-control" id="ach_64" name="ach_64" value="<?= htmlspecialchars($ach_64) ?>">
        </div>
        <div class="mb-3">
            <label for="growth" class="form-label">% Gro (6:3)</label>
            <input type="number" step="0.01" class="form-control" id="growth" name="growth" value="<?= htmlspecialchars($growth) ?>">
        </div>
        <div class="mb-3">
            <label for="analisis_vertical" class="form-label">ANALISIS VERTICAL</label>
            <textarea class="form-control" id="analisis_vertical" name="analisis_vertical" rows="3"><?= htmlspecialchars($analisis_vertical) ?></textarea>
        </div>
        <button type="submit" class="btn btn-success">Simpan Perubahan</button>
        <a href="pendapatan.php" class="btn btn-secondary">Batal</a>
    </form>
</main>

<?php include 'footer.php'; ?>
