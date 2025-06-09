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
$bulan = $_GET['bulan'] ?? '';
$tahun = $_GET['tahun'] ?? '';
$search = $_GET['search'] ?? '';

// Resequence numbering before fetching data
resequenceNumbering($conn, $kategori);

$whereClauses = [];
$params = [];
$paramTypes = '';

if ($kategori !== 'all') {
    $whereClauses[] = 'kategori = ?';
    $params[] = $kategori;
    $paramTypes .= 's';
}

if ($bulan !== '') {
    $whereClauses[] = 'MONTH(input_date) = ?';
    $params[] = $bulan;
    $paramTypes .= 'i';
}

if ($tahun !== '') {
    $whereClauses[] = 'YEAR(input_date) = ?';
    $params[] = $tahun;
    $paramTypes .= 'i';
}

if ($search !== '') {
    $whereClauses[] = 'Uraian LIKE ?';
    $params[] = '%' . $search . '%';
    $paramTypes .= 's';
}

$whereSql = '';
if (count($whereClauses) > 0) {
    $whereSql = 'WHERE ' . implode(' AND ', $whereClauses);
}

// Calculate total REALISASI_TAHUN_INI for filtered data
$stmtTotal = $conn->prepare("SELECT SUM(REALISASI_TAHUN_INI) as total_realisasi FROM laporan $whereSql");
if ($paramTypes) {
    $stmtTotal->bind_param($paramTypes, ...$params);
}
$stmtTotal->execute();
$resultTotal = $stmtTotal->get_result();
$rowTotal = $resultTotal->fetch_assoc();
$totalRealisasi = $rowTotal['total_realisasi'] ?? 0;
$stmtTotal->close();

/* Removed buildTree function and parent_id usage */

function buildTree(array $elements, $parentId = null) {
    $branch = [];
    foreach ($elements as $element) {
        if ($element['parent_id'] == $parentId) {
            $children = buildTree($elements, $element['id']);
            if ($children) {
                $element['children'] = $children;
            } else {
                $element['children'] = [];
            }
            $branch[] = $element;
        }
    }
    return $branch;
}

// Fetch laporan data with filters
$sql = "SELECT * FROM laporan $whereSql ORDER BY kategori, parent_id ASC, id ASC";
$stmt = $conn->prepare($sql);
if ($paramTypes) {
    $stmt->bind_param($paramTypes, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

$laporanData = [];
while ($row = $result->fetch_assoc()) {
    $laporanData[] = $row;
}

$tree = buildTree($laporanData);
?>

<?php include 'header.php'; ?>

    <main id="main" class="main">
        <div class="pagetitle">
    <h1>Laporan Keuangan</h1>
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
            <li class="breadcrumb-item active">Laporan Keuangan</li>
        </ol>
    </nav>
</div>
<div class="card p-3" style="padding: 30px; margin-bottom: 20px;">
    <h5>Laporan - <?= htmlspecialchars($kategori === 'all' ? 'Semua Kategori' : $categories[$kategori]) ?></h5>
    <form method="GET" class="mb-3 row g-2 align-items-center">
        <div class="col-auto">
            <label class="input-group-text" for="kategoriSelect">Pilih Kategori</label>
            <select class="form-select" id="kategoriSelect" name="kategori" onchange="this.form.submit()">
                <option value="all" <?= ($kategori === 'all') ? 'selected' : '' ?>>Tampilkan Semua</option>
                <?php foreach ($categories as $key => $label): ?>
                    <option value="<?= htmlspecialchars($key) ?>" <?= ($key === $kategori) ? 'selected' : '' ?>><?= htmlspecialchars($label) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-auto">
            <label class="input-group-text" for="bulanSelect">Bulan</label>
            <select class="form-select" id="bulanSelect" name="bulan" onchange="this.form.submit()">
                <option value="">Semua</option>
                <?php for ($m=1; $m<=12; $m++): ?>
                    <option value="<?= $m ?>" <?= ($bulan == $m) ? 'selected' : '' ?>><?= date('F', mktime(0,0,0,$m,1)) ?></option>
                <?php endfor; ?>
            </select>
        </div>
        <div class="col-auto">
            <label class="input-group-text" for="tahunSelect">Tahun</label>
            <select class="form-select" id="tahunSelect" name="tahun" onchange="this.form.submit()">
                <option value="">Semua</option>
                <?php
                $currentYear = date('Y');
                for ($y = $currentYear - 5; $y <= $currentYear + 1; $y++): ?>
                    <option value="<?= $y ?>" <?= ($tahun == $y) ? 'selected' : '' ?>><?= $y ?></option>
                <?php endfor; ?>
            </select>
        </div>
        <div class="col-auto">
            <input type="text" class="form-control" name="search" placeholder="Cari Uraian..." value="<?= htmlspecialchars($search) ?>" onkeydown="if(event.key === 'Enter'){ this.form.submit(); }">
        </div>
        <div class="col-auto">
            <button type="submit" class="btn btn-primary">Filter</button>
        </div>
    </form>
    <div class="col-md">
    <?php if ($kategori !== 'all'): ?>
        <a href="tambahlaporan.php?kategori=<?= urlencode($kategori) ?>" class="btn btn-success my-3">+ Tambah Data</a>
    <?php endif; ?>
    <a href="export_laporan.php?<?= http_build_query(['kategori' => $kategori, 'bulan' => $bulan, 'tahun' => $tahun, 'search' => $search]) ?>" class="btn btn-info my-3 ms-2">Download XLS</a>
    <button onclick="printTable()" class="btn btn-secondary my-3 ms-2">Cetak</button>
                </div>

<script>
function printTable() {
    const originalContents = document.body.innerHTML;
    const tableContents = document.querySelector('table').outerHTML;
    document.body.innerHTML = '<html><head><title>Cetak Laporan</title><style>table {width: 100%; border-collapse: collapse;} th, td {border: 1px solid #000; padding: 8px; text-align: left;} th {background-color: #f2f2f2;}</style></head><body>' + tableContents + '</body></html>';
    window.print();
    document.body.innerHTML = originalContents;
    location.reload();
}
</script>
    <table class="table table-bordered table-striped table-hover">
        <thead class="table-dark">
            <tr>
                <th>Nomor</th>
                <th>U R A I A N</th>
                <th>REALISASI TAHUN LALU</th>
                <th>ANGGARAN TAHUN INI</th>
                <th>REALISASI TAHUN INI</th>
                <th>ANGGARAN TAHUN 2025</th>
                <th>% Ach (1)</th>
                <th>% Gro</th>
                <th>% Ach (2)</th>
                <th>ANALISIS VERTICAL</th>
                <th style="width: 120px;">Aksi</th>
            </tr>
        </thead>
        <tbody>
    <?php 
    $totalRealisasiTahunLalu = 0;
    $totalAnggaranTahunIni = 0;
    $totalRealisasiTahunIni = 0;
    $totalAnggaranTahun2025 = 0;
    $totalAnalisisVertical = 0;

    function renderTree($tree, $level = 0, &$totals) {
        foreach ($tree as $node) {
            $indent = str_repeat('&nbsp;&nbsp;&nbsp;&nbsp;', $level);
            $numbering = $node['nomor'] ?? '';

            $ach1 = ($node['ANGGARAN_TAHUN_INI'] != 0) ? ($node['REALISASI_TAHUN_INI'] / $node['ANGGARAN_TAHUN_INI']) * 100 : 0;
            $gro = ($node['REALISASI_TAHUN_LALU'] != 0) ? (($node['REALISASI_TAHUN_INI'] - $node['REALISASI_TAHUN_LALU']) / $node['REALISASI_TAHUN_LALU']) * 100 : 0;
            $ach2 = ($node['ANGGARAN_TAHUN_2025'] != 0) ? ($node['REALISASI_TAHUN_INI'] / $node['ANGGARAN_TAHUN_2025']) * 100 : 0;

            // Perhitungan Ach 1, Ach 2, dan Analisis Vertical yang diminta
            $ach1_calc = $ach1;
            $ach2_calc = $ach2;
            $analisisVertical = ($totals['totalRealisasi'] != 0) ? ($node['REALISASI_TAHUN_INI'] / $totals['totalRealisasi']) * 100 : 0;

            $totals['totalRealisasiTahunLalu'] += $node['REALISASI_TAHUN_LALU'];
            $totals['totalAnggaranTahunIni'] += $node['ANGGARAN_TAHUN_INI'];
            $totals['totalRealisasiTahunIni'] += $node['REALISASI_TAHUN_INI'];
            $totals['totalAnggaranTahun2025'] += $node['ANGGARAN_TAHUN_2025'];
            $totals['totalAnalisisVertical'] += $analisisVertical;
    ?>
            <tr>
                <td><?= htmlspecialchars($numbering) ?></td>
                <td><?= $indent . htmlspecialchars($node['Uraian']) ?></td>
                <td><?= number_format($node['REALISASI_TAHUN_LALU'], 2, ',', '.') ?></td>
                <td><?= number_format($node['ANGGARAN_TAHUN_INI'], 2, ',', '.') ?></td>
                <td><?= number_format($node['REALISASI_TAHUN_INI'], 2, ',', '.') ?></td>
                <td><?= number_format($node['ANGGARAN_TAHUN_2025'], 2, ',', '.') ?></td>
                <td><?= number_format($ach1, 2, ',', '.') ?>%</td>
                <td><?= number_format($gro, 2, ',', '.') ?>%</td>
                <td><?= number_format($ach2, 2, ',', '.') ?>%</td>
                <td><?= number_format($analisisVertical, 2, ',', '.') ?>%</td>
                <td>
                    <a href="editlaporan.php?id=<?= $node['id'] ?>" class="btn btn-sm btn-warning me-1">Edit</a>
                    <a href="hapuslaporan.php?id=<?= $node['id'] ?>&kategori=<?= urlencode($node['kategori']) ?>" class="btn btn-sm btn-danger me-1" onclick="return confirm('Yakin ingin menghapus data ini?');">Hapus</a>
                    <a href="tambahlaporan.php?kategori=<?= urlencode($node['kategori']) ?>&parent_id=<?= $node['id'] ?>" class="btn btn-sm btn-success" title="Tambah Sub Uraian"><i class="bi bi-plus-lg"></i></a>
                </td>
            </tr>
    <?php
            if (!empty($node['children'])) {
                renderTree($node['children'], $level + 1, $totals);
            }
        }
    }

    if (!empty($tree)):
        $totals = [
            'totalRealisasiTahunLalu' => 0,
            'totalAnggaranTahunIni' => 0,
            'totalRealisasiTahunIni' => 0,
            'totalAnggaranTahun2025' => 0,
            'totalAnalisisVertical' => 0,
            'totalRealisasi' => $totalRealisasi
        ];

        // Fungsi untuk menghitung total berdasarkan kategori dan kolom
        function getTotalByKategori($conn, $kategori, $column) {
            $sql = "SELECT SUM($column) as total FROM laporan WHERE kategori = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("s", $kategori);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            $stmt->close();
            return $row['total'] ?? 0;
        }

        // Hitung nilai khusus untuk kategori 'laba rugi usaha'
        if ($kategori === 'all' || $kategori === 'laba rugi usaha') {
            $pendapatanRealisasiLalu = getTotalByKategori($conn, 'pendapatan', 'REALISASI_TAHUN_LALU');
            $bebanRealisasiLalu = getTotalByKategori($conn, 'beban', 'REALISASI_TAHUN_LALU');
            $pendapatanRealisasiIni = getTotalByKategori($conn, 'pendapatan', 'REALISASI_TAHUN_INI');
            $bebanRealisasiIni = getTotalByKategori($conn, 'beban', 'REALISASI_TAHUN_INI');
            $pendapatanAnggaranIni = getTotalByKategori($conn, 'pendapatan', 'ANGGARAN_TAHUN_INI');
            $bebanAnggaranIni = getTotalByKategori($conn, 'beban', 'ANGGARAN_TAHUN_INI');

            $totalsLabaRugiUsaha = [
                'totalRealisasiTahunLalu' => $pendapatanRealisasiLalu - $bebanRealisasiLalu,
                'totalRealisasiTahunIni' => $pendapatanRealisasiIni - $bebanRealisasiIni,
                'totalAnggaranTahunIni' => $pendapatanAnggaranIni - $bebanAnggaranIni,
                'totalAnggaranTahun2025' => 0, // Jika ada, bisa ditambahkan
                'totalAnalisisVertical' => 0, // Jika ada, bisa ditambahkan
            ];
        }

        // Hitung nilai khusus untuk kategori 'laba rugi sebelum pajak penghasilan'
        if ($kategori === 'all' || $kategori === 'laba rugi sebelum pajak penghasilan') {
            $labaRugiUsahaRealisasiLalu = getTotalByKategori($conn, 'laba rugi usaha', 'REALISASI_TAHUN_LALU');
            $pendapatanBebanLainLainRealisasiLalu = getTotalByKategori($conn, 'pendapatan beban lain lain', 'REALISASI_TAHUN_LALU');
            $labaRugiUsahaRealisasiIni = getTotalByKategori($conn, 'laba rugi usaha', 'REALISASI_TAHUN_INI');
            $pendapatanBebanLainLainRealisasiIni = getTotalByKategori($conn, 'pendapatan beban lain lain', 'REALISASI_TAHUN_INI');
            $labaRugiUsahaAnggaranIni = getTotalByKategori($conn, 'laba rugi usaha', 'ANGGARAN_TAHUN_INI');
            $pendapatanBebanLainLainAnggaranIni = getTotalByKategori($conn, 'pendapatan beban lain lain', 'ANGGARAN_TAHUN_INI');

            $totalsLabaRugiSebelumPajak = [
                'totalRealisasiTahunLalu' => $labaRugiUsahaRealisasiLalu - $pendapatanBebanLainLainRealisasiLalu,
                'totalRealisasiTahunIni' => $labaRugiUsahaRealisasiIni - $pendapatanBebanLainLainRealisasiIni,
                'totalAnggaranTahunIni' => $labaRugiUsahaAnggaranIni - $pendapatanBebanLainLainAnggaranIni,
                'totalAnggaranTahun2025' => 0, // Jika ada, bisa ditambahkan
                'totalAnalisisVertical' => 0, // Jika ada, bisa ditambahkan
            ];
        }

        $currentKategori = null;
        foreach ($tree as $node):
                if ($kategori === 'all' && $currentKategori !== $node['kategori']):
                    if ($currentKategori !== null):
?>
                    <tr class="table-secondary fw-bold">
                        <td>Jumlah <?= htmlspecialchars($categories[$currentKategori]) ?></td>
                        <td>
                            <?php
                            // Override totalRealisasiTahunLalu untuk kategori khusus
                            if ($currentKategori === 'laba rugi usaha' && isset($totalsLabaRugiUsaha)) {
                                echo number_format($totalsLabaRugiUsaha['totalRealisasiTahunLalu'], 2, ',', '.');
                            } else if ($currentKategori === 'laba rugi sebelum pajak penghasilan' && isset($totalsLabaRugiSebelumPajak)) {
                                echo number_format($totalsLabaRugiSebelumPajak['totalRealisasiTahunLalu'], 2, ',', '.');
                            } else {
                                echo number_format($totals['totalRealisasiTahunLalu'], 2, ',', '.');
                            }
                            ?>
                        </td>
                        <td><?= number_format($totals['totalAnggaranTahunIni'], 2, ',', '.') ?></td>
                        <td>
                            <?php
                            // Override totalRealisasiTahunIni untuk kategori khusus
                            if ($currentKategori === 'laba rugi usaha' && isset($totalsLabaRugiUsaha)) {
                                echo number_format($totalsLabaRugiUsaha['totalRealisasiTahunIni'], 2, ',', '.');
                            } else if ($currentKategori === 'laba rugi sebelum pajak penghasilan' && isset($totalsLabaRugiSebelumPajak)) {
                                echo number_format($totalsLabaRugiSebelumPajak['totalRealisasiTahunIni'], 2, ',', '.');
                            } else {
                                echo number_format($totals['totalRealisasiTahunIni'], 2, ',', '.');
                            }
                            ?>
                        </td>
                        <td>
                            <?php
                            // Override totalAnggaranTahunIni untuk kategori khusus
                            if ($currentKategori === 'laba rugi usaha' && isset($totalsLabaRugiUsaha)) {
                                echo number_format($totalsLabaRugiUsaha['totalAnggaranTahunIni'], 2, ',', '.');
                            } else if ($currentKategori === 'laba rugi sebelum pajak penghasilan' && isset($totalsLabaRugiSebelumPajak)) {
                                echo number_format($totalsLabaRugiSebelumPajak['totalAnggaranTahunIni'], 2, ',', '.');
                            } else {
                                echo number_format($totals['totalAnggaranTahun2025'], 2, ',', '.');
                            }
                            ?>
                        </td>
                        <td><?= number_format($totals['totalAnggaranTahun2025'], 2, ',', '.') ?></td>
                        <?php
                            // Hitung Ach 1, Gro, Ach 2, dan Analisis Vertical untuk baris jumlah di semua kategori
                            $ach1_jumlah = ($totals['totalAnggaranTahunIni'] != 0) ? ($totals['totalRealisasiTahunIni'] / $totals['totalAnggaranTahunIni']) * 100 : 0;
                            $gro_jumlah = ($totals['totalRealisasiTahunLalu'] != 0) ? (($totals['totalRealisasiTahunIni'] - $totals['totalRealisasiTahunLalu']) / $totals['totalRealisasiTahunLalu']) * 100 : 0;
                            $ach2_jumlah = ($totals['totalAnggaranTahun2025'] != 0) ? ($totals['totalRealisasiTahunIni'] / $totals['totalAnggaranTahun2025']) * 100 : 0;
                            $analisisVertical_jumlah = ($totals['totalRealisasi'] != 0) ? ($totals['totalRealisasiTahunIni'] / $totals['totalRealisasi']) * 100 : 0;
                        ?>
                        <td><?= number_format($ach1_jumlah, 2, ',', '.') ?>%</td>
                        <td><?= number_format($gro_jumlah, 2, ',', '.') ?>%</td>
                        <td><?= number_format($ach2_jumlah, 2, ',', '.') ?>%</td>
                        <td><?= number_format($analisisVertical_jumlah, 2, ',', '.') ?>%</td>
                        <td></td>
                    </tr>
<?php
                    endif;
                $totals = [
                    'totalRealisasiTahunLalu' => 0,
                    'totalAnggaranTahunIni' => 0,
                    'totalRealisasiTahunIni' => 0,
                    'totalAnggaranTahun2025' => 0,
                    'totalAnalisisVertical' => 0,
                    'totalRealisasi' => $totalRealisasi
                ];
                $currentKategori = $node['kategori'];
    ?>
                <tr class="table-primary fw-bold">
                    <td colspan="10"><?= htmlspecialchars($categories[$currentKategori]) ?></td>
                </tr>
    <?php
            elseif ($currentKategori === null):
                $currentKategori = $node['kategori'];
    ?>
                <tr class="table-primary fw-bold">
                    <td colspan="10"><?= htmlspecialchars($categories[$currentKategori]) ?></td>
                </tr>
    <?php
            endif;
            renderTree([$node], 0, $totals);
        endforeach;
    ?>
        <tr class="table-secondary fw-bold">
            <td>Jumlah <?= htmlspecialchars($categories[$currentKategori]) ?></td>
            <td><?= number_format($totals['totalRealisasiTahunLalu'], 2, ',', '.') ?></td>
            <td><?= number_format($totals['totalAnggaranTahunIni'], 2, ',', '.') ?></td>
            <td><?= number_format($totals['totalRealisasiTahunIni'], 2, ',', '.') ?></td>
            <td><?= number_format($totals['totalAnggaranTahun2025'], 2, ',', '.') ?></td>
            <td></td>
            <td></td>
            <td></td>
            <td><?= number_format($totals['totalAnalisisVertical'], 2, ',', '.') ?>%</td>
            <td></td>
        </tr>
    <?php else: ?>
        <tr><td colspan="10" class="text-center">Tidak ada data.</td></tr>
    <?php endif; ?>
</tbody>

    </table>
</div>
</main>
<?php include 'footer.php'; ?>
<script src="assets/js/sidebar-accordion.js"></script>
<script src="assets/js/main.js"></script>
