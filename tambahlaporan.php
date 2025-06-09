<?php
include 'koneksi.php';
include 'numbering_service.php';

$categories = [
    'pendapatan' => 'Pendapatan',
    'beban' => 'Beban',
    'laba rugi usaha' => 'Laba Rugi Usaha',
    'pendapatan beban lain lain' => 'Pendapatan Beban Lain Lain',
    'laba rugi sebelum pajak penghasilan' => 'Laba Rugi Sebelum Pajak Penghasilan',
    'pajak penghasilan' => 'Pajak Penghasilan',
    'laba rugi bersih tahun berjalan' => 'Laba Rugi Bersih Tahun Berjalan',
    'kepentingan non pengendali' => 'Kepentingan Non Pengendali',
    'laba yang dapat diatribusikan kepada pemilik entitas induk' => 'Laba yang Dapat Diatribusikan kepada Pemilik Entitas Induk'
];

$kategori = $_GET['kategori'] ?? 'pendapatan';
$parent_id = $_GET['parent_id'] ?? null;

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $kategori = $_POST['kategori'] ?? 'pendapatan';
    $uraian = $_POST['uraian'] ?? '';
    $parent_id = $_POST['parent_id'] ?? null;
    if ($parent_id === '' || $parent_id === '0') {
        $parent_id = null;
    }
    $realisasi_tahun_lalu = $_POST['realisasi_tahun_lalu'] ?? 0;
    $anggaran_tahun_ini = $_POST['anggaran_tahun_ini'] ?? 0;
    $realisasi_tahun_ini = $_POST['realisasi_tahun_ini'] ?? 0;
    $anggaran_tahun_2025 = $_POST['anggaran_tahun_2025'] ?? 0;
    $analisis_vertical = 0;

    if (empty($uraian)) {
        $errors[] = "Uraian harus diisi.";
    }

    if (empty($errors)) {
        $nomor = null;
        if ($parent_id === null) {
            $nomor = getNextTopLevelNumbering($conn, $kategori);
        } else {
            $nomor = getNextChildNumbering($conn, $parent_id);
        }
        $stmt = $conn->prepare("INSERT INTO laporan (kategori, Uraian, parent_id, nomor, REALISASI_TAHUN_LALU, ANGGARAN_TAHUN_INI, REALISASI_TAHUN_INI, ANGGARAN_TAHUN_2025, ANALISIS_VERTICAL) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssiddddd", $kategori, $uraian, $parent_id, $nomor, $realisasi_tahun_lalu, $anggaran_tahun_ini, $realisasi_tahun_ini, $anggaran_tahun_2025, $analisis_vertical);
        if ($stmt->execute()) {
            header("Location: laporan.php?kategori=" . urlencode($kategori));
            exit;
        } else {
            $errors[] = "Gagal menyimpan data: " . $stmt->error;
        }
        $stmt->close();
    }
} else {
    $prefix = null;
    if ($parent_id === null) {
        $prefix = getNextTopLevelNumbering($conn, $kategori);
    } else {
        $prefix = getNextChildNumbering($conn, $parent_id);
    }
    $uraian = '';
}


?>

<?php include 'header.php'; ?>

<main id="main " class="main" style="margin-left: 300px; padding: 20px;">
    <div class="pagetitle" style="margin-top: 60px;">
    <h1>Tambah Data Laporan - <?= htmlspecialchars($categories[$kategori]) ?></h1>
    <?php if ($errors): ?>
        <div class="alert alert-danger">
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?= htmlspecialchars($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>    
    <?php endif; ?><nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="laporan.php">Laporan</a></li>
                <li class="breadcrumb-item active">Tambah Data</li>
            </ol>
    </nav></div>
    <div class="card p-3">
    <form method="POST" action="tambahlaporan.php" style="padding 20px;">
        <input type="hidden" name="kategori" value="<?= htmlspecialchars($kategori) ?>">
        <input type="hidden" name="parent_id" value="<?= htmlspecialchars($parent_id) ?>">
        <div class="mb-3">
            <label for="uraian" class="form-label">U R A I A N</label>
            <input type="text" class="form-control" id="uraian" name="uraian" value="<?= htmlspecialchars($uraian) ?>" required>
        </div>
        <div class="mb-3">
            <label for="realisasi_tahun_lalu" class="form-label">REALISASI TAHUN LALU</label>
            <input type="number" step="0.01" class="form-control" id="realisasi_tahun_lalu" name="realisasi_tahun_lalu" value="0">
        </div>
        <div class="mb-3">
            <label for="anggaran_tahun_ini" class="form-label">ANGGARAN TAHUN INI</label>
            <input type="number" step="0.01" class="form-control" id="anggaran_tahun_ini" name="anggaran_tahun_ini" value="0">
        </div>
        <div class="mb-3">
            <label for="realisasi_tahun_ini" class="form-label">REALISASI TAHUN INI</label>
            <input type="number" step="0.01" class="form-control" id="realisasi_tahun_ini" name="realisasi_tahun_ini" value="0">
        </div>
        <div class="mb-3">
            <label for="anggaran_tahun_2025" class="form-label">ANGGARAN TAHUN 2025</label>
            <input type="number" step="0.01" class="form-control" id="anggaran_tahun_2025" name="anggaran_tahun_2025" value="0">
        </div>
        <button type="submit" class="btn btn-success">Simpan</button>
        <a href="laporan.php?kategori=<?= urlencode($kategori) ?>" class="btn btn-secondary">Batal</a>
    </form>
                </div>
</main>
<script src="assets/js/main.js"></script>
<?php include 'footer.php'; ?>