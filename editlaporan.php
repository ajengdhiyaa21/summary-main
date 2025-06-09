<?php
include 'koneksi.php';

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

$id = $_GET['id'] ?? null;
if (!$id) {
    header("Location: laporan.php");
    exit;
}

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

    if (empty($uraian)) {
        $errors[] = "Uraian harus diisi.";
    }

    if (empty($errors)) {
        // Regenerate nomor on edit to keep numbering consistent
        $nomor = null;
        if ($parent_id === null) {
            // Top-level numbering
            $sql = "SELECT nomor FROM laporan WHERE kategori = ? AND (parent_id IS NULL OR parent_id = 0) ORDER BY CAST(nomor AS UNSIGNED) DESC LIMIT 1";
            $stmtNum = $conn->prepare($sql);
            $stmtNum->bind_param("s", $kategori);
            $stmtNum->execute();
            $resultNum = $stmtNum->get_result();
            if ($rowNum = $resultNum->fetch_assoc()) {
                $maxNum = intval($rowNum['nomor']);
                $nomor = (string)($maxNum + 1);
            } else {
                $nomor = '1';
            }
            $stmtNum->close();
        } else {
            // Child numbering
            $sql = "SELECT nomor FROM laporan WHERE parent_id = ? ORDER BY nomor DESC LIMIT 1";
            $stmtNum = $conn->prepare($sql);
            $stmtNum->bind_param("i", $parent_id);
            $stmtNum->execute();
            $resultNum = $stmtNum->get_result();
            if ($rowNum = $resultNum->fetch_assoc()) {
                $lastNomor = $rowNum['nomor'];
                if (preg_match('/([a-z])$/i', $lastNomor, $matches)) {
                    $lastChar = strtolower($matches[1]);
                    $nextChar = chr(ord($lastChar) + 1);
                    $base = rtrim($lastNomor, 'abcdefghijklmnopqrstuvwxyz');
                    $nomor = $base . $nextChar;
                } else {
                    // First child
                    $sqlParent = "SELECT nomor FROM laporan WHERE id = ?";
                    $stmtParent = $conn->prepare($sqlParent);
                    $stmtParent->bind_param("i", $parent_id);
                    $stmtParent->execute();
                    $resultParent = $stmtParent->get_result();
                    $rowParent = $resultParent->fetch_assoc();
                    $parentNomor = $rowParent['nomor'] ?? '';
                    $stmtParent->close();
                    $nomor = $parentNomor . 'a';
                }
            } else {
                // No children yet
                $sqlParent = "SELECT nomor FROM laporan WHERE id = ?";
                $stmtParent = $conn->prepare($sqlParent);
                $stmtParent->bind_param("i", $parent_id);
                $stmtParent->execute();
                $resultParent = $stmtParent->get_result();
                $rowParent = $resultParent->fetch_assoc();
                $parentNomor = $rowParent['nomor'] ?? '';
                $stmtParent->close();
                $nomor = $parentNomor . 'a';
            }
            $stmtNum->close();
        }

        $stmt = $conn->prepare("UPDATE laporan SET kategori=?, Uraian=?, parent_id=?, nomor=?, REALISASI_TAHUN_LALU=?, ANGGARAN_TAHUN_INI=?, REALISASI_TAHUN_INI=?, ANGGARAN_TAHUN_2025=? WHERE id=?");
        $stmt->bind_param("sssisddddi", $kategori, $uraian, $parent_id, $nomor, $realisasi_tahun_lalu, $anggaran_tahun_ini, $realisasi_tahun_ini, $anggaran_tahun_2025, $id);
        if ($stmt->execute()) {
            header("Location: laporan.php?kategori=" . urlencode($kategori));
            exit;
        } else {
            $errors[] = "Gagal memperbarui data: " . $stmt->error;
        }
        $stmt->close();
    }
} else {
    $stmt = $conn->prepare("SELECT * FROM laporan WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows === 0) {
        header("Location: laporan.php");
        exit;
    }
    $row = $result->fetch_assoc();
    $kategori = $row['kategori'];
    $uraian = $row['Uraian'];
    $parent_id = $row['parent_id'] ?? null;
    $realisasi_tahun_lalu = $row['REALISASI_TAHUN_LALU'];
    $anggaran_tahun_ini = $row['ANGGARAN_TAHUN_INI'];
    $realisasi_tahun_ini = $row['REALISASI_TAHUN_INI'];
    $anggaran_tahun_2025 = $row['ANGGARAN_TAHUN_2025'];
    $stmt->close();
}
?>

<?php include 'header.php'; ?>

<main id="main" class="main">
    <div class="pagetitle">
    <h1>Edit Data Laporan - <?= htmlspecialchars($categories[$kategori]) ?></h1>
    <?php if ($errors): ?>
        <div class="alert alert-danger">
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?= htmlspecialchars($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>
     <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="laporan.php">Laporan</a></li>
                <li class="breadcrumb-item active">Edit Data</li>
            </ol>
    </nav>
    </div>
    <div class="card p-3">
    <form method="POST" action="editlaporan.php?id=<?= $id ?>">
        <div class="mb-3">
            <label for="kategori" class="form-label">Kategori</label>
            <select class="form-select" id="kategori" name="kategori" required>
                <?php foreach ($categories as $key => $label): ?>
                    <option value="<?= htmlspecialchars($key) ?>" <?= ($key === $kategori) ? 'selected' : '' ?>><?= htmlspecialchars($label) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <!-- Removed Parent Uraian selection as per user request -->
        <div class="mb-3">
            <label for="uraian" class="form-label">U R A I A N</label>
            <input type="text" class="form-control" id="uraian" name="uraian" value="<?= htmlspecialchars($uraian) ?>" required>
        </div>
        <div class="mb-3">
            <label for="realisasi_tahun_lalu" class="form-label">REALISASI TAHUN LALU</label>
            <input type="number" step="0.01" class="form-control" id="realisasi_tahun_lalu" name="realisasi_tahun_lalu" value="<?= htmlspecialchars($realisasi_tahun_lalu) ?>">
        </div>
        <div class="mb-3">
            <label for="anggaran_tahun_ini" class="form-label">ANGGARAN TAHUN INI</label>
            <input type="number" step="0.01" class="form-control" id="anggaran_tahun_ini" name="anggaran_tahun_ini" value="<?= htmlspecialchars($anggaran_tahun_ini) ?>">
        </div>
        <div class="mb-3">
            <label for="realisasi_tahun_ini" class="form-label">REALISASI TAHUN INI</label>
            <input type="number" step="0.01" class="form-control" id="realisasi_tahun_ini" name="realisasi_tahun_ini" value="<?= htmlspecialchars($realisasi_tahun_ini) ?>">
        </div>
        <div class="mb-3">
            <label for="anggaran_tahun_2025" class="form-label">ANGGARAN TAHUN 2025</label>
            <input type="number" step="0.01" class="form-control" id="anggaran_tahun_2025" name="anggaran_tahun_2025" value="<?= htmlspecialchars($anggaran_tahun_2025) ?>">
        </div>
        <button type="submit" class="btn btn-success">Simpan Perubahan</button>
        <a href="laporan.php?kategori=<?= urlencode($kategori) ?>" class="btn btn-secondary">Batal</a>
    </form>
                </div>
</main>
</create_file><script src="assets/js/main.js"></script>
<?php include 'footer.php'; ?>
