<?php
include 'koneksi.php';
include 'numbering_service.php';

// Inisialisasi variabel filter dari query string dengan nilai default
$kategori = isset($_GET['kategori']) ? $_GET['kategori'] : '';
$bulan = isset($_GET['bulan']) ? $_GET['bulan'] : '';
$tahun = isset($_GET['tahun']) ? (int)$_GET['tahun'] : date('Y');
$search = isset($_GET['search']) ? $_GET['search'] : '';
$totalRealisasi = 0;

// Inisialisasi array kategori default
$categories = [
    'all' => 'Semua Kategori',
    'pendapatan' => 'Pendapatan',
    'beban' => 'Beban',
    'laba rugi usaha' => 'Laba Rugi Usaha',
    // Tambahkan kategori lain sesuai kebutuhan
];

$whereClauses = ["tahun = ?"];
$params = [$tahun];
$paramTypes = "i";

// Tambahkan filter kategori jika ada dan bukan 'all'
if ($kategori !== '' && $kategori !== 'all') {
    $whereClauses[] = "kategori = ?";
    $params[] = $kategori;
    $paramTypes .= "s";
}

// Tambahkan filter bulan jika ada
if ($bulan !== '') {
    $whereClauses[] = "bulan = ?";
    $params[] = (int)$bulan;
    $paramTypes .= "i";
}

// Tambahkan filter pencarian pada kolom Uraian jika ada
if ($search !== '') {
    $whereClauses[] = "Uraian LIKE ?";
    $params[] = "%" . $search . "%";
    $paramTypes .= "s";
}

// Ambil data laporan tahun ini
$sql = "SELECT * FROM laporan WHERE " . implode(" AND ", $whereClauses) . " ORDER BY id ASC";
$stmt = $conn->prepare($sql);
$stmt->bind_param($paramTypes, ...$params);
$stmt->execute();
$result = $stmt->get_result();

$laporanData = [];
while ($row = $result->fetch_assoc()) {
    $laporanData[$row['kategori'] . '||' . $row['Uraian'] . '||' . $row['bulan']] = $row;
}
$stmt->close();

// Hitung total REALISASI_TAHUN_INI untuk Analisis Vertical
$totalRealisasi = 0;
foreach ($laporanData as $data) {
    $totalRealisasi += $data['REALISASI_TAHUN_INI'] ?? 0;
}

function getTotalByKategori($conn, $kategori, $column, $bulan = null, $tahun = null) {
    $sql = "SELECT SUM($column) as total FROM laporan WHERE kategori = ?";
    $types = "s";
    $params = [$kategori];

    if ($bulan !== null) {
        $sql .= " AND bulan = ?";
        $types .= "i";
        $params[] = $bulan;
    }
    if ($tahun !== null) {
        $sql .= " AND tahun = ?";
        $types .= "i";
        $params[] = $tahun;
    }

    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();
    return $row['total'] ?? 0;
}

// Asumsikan variabel $whereClauses, $params, $nextYear sudah didefinisikan sebelumnya
$whereClausesNextYear = $whereClauses ?? [];
$paramsNextYear = $params ?? [];
$nextYear = (int)$tahun + 1;

// Hapus filter bulan dari klausa
foreach (array_values($whereClausesNextYear) as $i => $clause) {
    if (strpos($clause, 'bulan = ?') !== false) {
        $keys = array_keys($whereClausesNextYear);
        $index = $keys[$i];
        unset($whereClausesNextYear[$index]);

        $paramIndexToRemove = 0;
        foreach ($whereClauses as $j => $c) {
            if ($c === 'bulan = ?') {
                break;
            }
            $paramIndexToRemove++;
        }
        array_splice($paramsNextYear, $paramIndexToRemove, 1);
        break;
    }
}

// Ubah filter tahun ke tahun berikutnya
foreach ($whereClausesNextYear as $i => $clause) {
    if (strpos($clause, 'tahun = ?') !== false) {
        $paramIndex = 0;
        foreach ($whereClausesNextYear as $j => $c) {
            if ($c === 'tahun = ?') {
                break;
            }
            $paramIndex++;
        }
        $paramsNextYear[$paramIndex] = $nextYear;
        break;
    }
}

// Hitung ulang jenis parameter untuk bind_param
$paramTypesNextYear = '';
foreach ($paramsNextYear as $param) {
    $paramTypesNextYear .= is_int($param) ? 'i' : 's';
}

// Eksekusi query untuk tahun berikutnya
$sqlNextYear = "SELECT * FROM laporan " . (count($whereClausesNextYear) ? 'WHERE ' . implode(' AND ', $whereClausesNextYear) : '') . " ORDER BY id ASC";
$stmtNextYear = $conn->prepare($sqlNextYear);
if ($paramTypesNextYear) {
    error_log("paramTypesNextYear: $paramTypesNextYear");
    error_log("paramsNextYear count: " . count($paramsNextYear));
    $stmtNextYear->bind_param($paramTypesNextYear, ...$paramsNextYear);
}
$stmtNextYear->execute();
$resultNextYear = $stmtNextYear->get_result();

$laporanDataNextYear = [];
while ($row = $resultNextYear->fetch_assoc()) {
    $laporanDataNextYear[$row['kategori'] . '||' . $row['Uraian'] . '||' . $row['bulan']] = $row;
}
$stmtNextYear->close();

foreach ($laporanData as $key => &$row) {
    if (isset($laporanDataNextYear[$key])) {
        $row['REALISASI_TAHUN_INI'] = $laporanDataNextYear[$key]['REALISASI_TAHUN_LALU'];
    }
}
unset($row);

$tree = buildTree(array_values($laporanData));

/* Build tree with protection against infinite recursion */
function buildTree(array $elements, $parentId = null, $visited = [], $depth = 0, $maxDepth = 100) {
    $branch = [];
    if ($depth > $maxDepth) {
        return $branch; // prevent infinite recursion
    }
    foreach ($elements as $element) {
        if ($element['parent_id'] == $parentId) {
            if (in_array($element['id'], $visited)) {
                continue; // cycle detected prevent infinite loop
            }
            $newVisited = $visited;
            $newVisited[] = $element['id'];
            $children = buildTree($elements, $element['id'], $newVisited, $depth + 1, $maxDepth);
            if ($children) {
                usort($children, function($a, $b) {
                    return strcmp($a['nomor'], $b['nomor']);
                });
                $element['children'] = $children;
            } else {
                $element['children'] = [];
            }
            $branch[] = $element;
        }
    }
    return $branch;
}

// Fungsi agregasi rekursif untuk menjumlahkan nilai anak ke parent
function aggregateChildrenValues(array &$elements, $parentId = null) {
    $sumRealisasi = 0;
    $sumAnggaran = 0;
    foreach ($elements as &$element) {
        if ($element['parent_id'] == $parentId) {
            // Rekursif agregasi anak
            $childSums = aggregateChildrenValues($elements, $element['id']);
            // Tambahkan nilai anak ke parent
            $element['REALISASI_TAHUN_INI'] += $childSums['realisasi'];
            $element['ANGGARAN_TAHUN_INI'] += $childSums['anggaran'];
            $sumRealisasi += $element['REALISASI_TAHUN_INI'];
            $sumAnggaran += $element['ANGGARAN_TAHUN_INI'];
        }
    }
    return ['realisasi' => $sumRealisasi, 'anggaran' => $sumAnggaran];
}

if (is_numeric($tahun)) {
    // Definisikan $whereSql sebelum digunakan
    $whereSql = 'WHERE ' . implode(' AND ', $whereClauses);

    // Fetch data for year Y (filter year)
    $sql = "SELECT * FROM laporan $whereSql ORDER BY id ASC";
    $stmt = $conn->prepare($sql);
    if ($paramTypes) {
        $stmt->bind_param($paramTypes, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();

    $laporanData = [];
    while ($row = $result->fetch_assoc()) {
        $laporanData[$row['kategori'] . '||' . $row['Uraian'] . '||' . $row['bulan']] = $row;
    }
    error_log("Data tahun $tahun count: " . count($laporanData));
    error_log("Data tahun $tahun keys: " . implode(", ", array_keys($laporanData)));

    // Restore mapping REALISASI_TAHUN_LALU from next year data to REALISASI_TAHUN_INI in current year data
    $nextYear = (int)$tahun + 1;
    $whereClausesNextYear = $whereClauses;
    $paramsNextYear = $params;
    $paramTypesNextYear = $paramTypes;

    // Remove month filter for next year data to allow mapping across all months
   // Remove month filter for next year data to allow mapping across all months
foreach (array_values($whereClausesNextYear) as $i => $clause) {
    if (strpos($clause, 'bulan = ?') !== false) {
        $keys = array_keys($whereClausesNextYear);
        $index = $keys[$i];
        unset($whereClausesNextYear[$index]);

        // Temukan index parameter bulan dan hapus dari $paramsNextYear
        $paramIndexToRemove = 0;
        foreach ($whereClauses as $j => $c) {
            if ($c === 'bulan = ?') {
                break;
            }
            $paramIndexToRemove++;
        }
        array_splice($paramsNextYear, $paramIndexToRemove, 1);

        // Update paramTypesNextYear
        $paramTypesNextYear = '';
        foreach ($paramsNextYear as $param) {
            $paramTypesNextYear .= is_int($param) ? 'i' : 's';
        }
        break;
    }
}

// ✅ Tambahan kode untuk mengganti tahun = ? ke next year
foreach ($whereClausesNextYear as $i => $clause) {
    if (strpos($clause, 'tahun = ?') !== false) {
        $paramsNextYear[$i] = $nextYear;
        break;
    }
}


    // Replace year filter with next year
    // Find the correct index in $paramsNextYear corresponding to the year filter
    $paramIndex = 0;


    $whereSqlNextYear = '';
    if (count($whereClausesNextYear) > 0) {
        $whereSqlNextYear = 'WHERE ' . implode(' AND ', $whereClausesNextYear);
    }

    $sqlNextYear = "SELECT * FROM laporan $whereSqlNextYear ORDER BY id ASC";
    $stmtNextYear = $conn->prepare($sqlNextYear);
    if ($paramTypesNextYear) {
        error_log("paramTypesNextYear: " . $paramTypesNextYear);
        error_log("paramsNextYear count: " . count($paramsNextYear));
        $stmtNextYear->bind_param($paramTypesNextYear, ...$paramsNextYear);
    }
    $stmtNextYear->execute();
    $resultNextYear = $stmtNextYear->get_result();

    $laporanDataNextYear = [];
    while ($row = $resultNextYear->fetch_assoc()) {
        $laporanDataNextYear[$row['kategori'] . '||' . $row['Uraian'] . '||' . $row['bulan']] = $row;
    }
    error_log("Data tahun " . ($tahun + 1) . " count: " . count($laporanDataNextYear));
    error_log("Data tahun " . ($tahun + 1) . " keys: " . implode(", ", array_keys($laporanDataNextYear)));

    foreach ($laporanData as $key => &$row) {
        if (isset($laporanDataNextYear[$key])) {
            // Only replace REALISASI_TAHUN_INI with next year's REALISASI_TAHUN_LALU
            $row['REALISASI_TAHUN_INI'] = $laporanDataNextYear[$key]['REALISASI_TAHUN_LALU'];
        }
    }
    unset($row);

    // Tambahkan data laba rugi usaha secara dinamis ke laporanData jika kategori adalah 'all' atau 'laba rugi usaha'
    if ($kategori === 'all' || $kategori === 'laba rugi usaha') {
        $pendapatanRealisasiLalu = getTotalByKategori($conn, 'pendapatan', 'REALISASI_TAHUN_LALU', $bulan, $tahun);
        $bebanRealisasiLalu = getTotalByKategori($conn, 'beban', 'REALISASI_TAHUN_LALU', $bulan, $tahun);
        $pendapatanRealisasiIni = getTotalByKategori($conn, 'pendapatan', 'REALISASI_TAHUN_INI', $bulan, $tahun);
        $bebanRealisasiIni = getTotalByKategori($conn, 'beban', 'REALISASI_TAHUN_INI', $bulan, $tahun);
        $pendapatanAnggaranIni = getTotalByKategori($conn, 'pendapatan', 'ANGGARAN_TAHUN_INI', $bulan, $tahun);
        $bebanAnggaranIni = getTotalByKategori($conn, 'beban', 'ANGGARAN_TAHUN_INI', $bulan, $tahun);
        $pendapatanAnggaranPerTahun = getTotalByKategori($conn, 'pendapatan', 'ANGGARAN_PER_TAHUN', $bulan, $tahun);
        $bebanAnggaranPerTahun = getTotalByKategori($conn, 'beban', 'ANGGARAN_PER_TAHUN', $bulan, $tahun);

        $labaRugiUsahaData = [
            'id' => 0,
            'kategori' => 'laba rugi usaha',
            'Uraian' => 'Laba Rugi Usaha',
            'parent_id' => null,
            'nomor' => '',
            'REALISASI_TAHUN_LALU' => $pendapatanRealisasiLalu - $bebanRealisasiLalu,
            'REALISASI_TAHUN_INI' => $pendapatanRealisasiIni - $bebanRealisasiIni,
            'ANGGARAN_TAHUN_INI' => $pendapatanAnggaranIni - $bebanAnggaranIni,
            'ANGGARAN_PER_TAHUN' => $pendapatanAnggaranPerTahun - $bebanAnggaranPerTahun,
        ];

        $laporanData['laba rugi usaha||Laba Rugi Usaha||'] = $labaRugiUsahaData;
    }

    $tree = buildTree(array_values($laporanData));
} else {
    // Fetch laporan data with filters (no year filter)
    $sql = "SELECT * FROM laporan $whereSql ORDER BY id ASC";
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

    // Tambahkan data laba rugi usaha secara dinamis ke laporanData jika kategori adalah 'all' atau 'laba rugi usaha'
    if ($kategori === 'all' || $kategori === 'laba rugi usaha') {
        $pendapatanRealisasiLalu = getTotalByKategori($conn, 'pendapatan', 'REALISASI_TAHUN_LALU', $bulan, $tahun);
        $bebanRealisasiLalu = getTotalByKategori($conn, 'beban', 'REALISASI_TAHUN_LALU', $bulan, $tahun);
        $pendapatanRealisasiIni = getTotalByKategori($conn, 'pendapatan', 'REALISASI_TAHUN_INI', $bulan, $tahun);
        $bebanRealisasiIni = getTotalByKategori($conn, 'beban', 'REALISASI_TAHUN_INI', $bulan, $tahun);
        $pendapatanAnggaranIni = getTotalByKategori($conn, 'pendapatan', 'ANGGARAN_TAHUN_INI', $bulan, $tahun);
        $bebanAnggaranIni = getTotalByKategori($conn, 'beban', 'ANGGARAN_TAHUN_INI', $bulan, $tahun);
        $pendapatanAnggaranPerTahun = getTotalByKategori($conn, 'pendapatan', 'ANGGARAN_PER_TAHUN', $bulan, $tahun);
        $bebanAnggaranPerTahun = getTotalByKategori($conn, 'beban', 'ANGGARAN_PER_TAHUN', $bulan, $tahun);

        $labaRugiUsahaData = [
            'id' => 0,
            'kategori' => 'laba rugi usaha',
            'Uraian' => 'Laba Rugi Usaha',
            'parent_id' => null,
            'nomor' => '',
            'REALISASI_TAHUN_LALU' => $pendapatanRealisasiLalu - $bebanRealisasiLalu,
            'REALISASI_TAHUN_INI' => $pendapatanRealisasiIni - $bebanRealisasiIni,
            'ANGGARAN_TAHUN_INI' => $pendapatanAnggaranIni - $bebanAnggaranIni,
            'ANGGARAN_PER_TAHUN' => $pendapatanAnggaranPerTahun - $bebanAnggaranPerTahun,
        ];

        $laporanData[] = $labaRugiUsahaData;
    }

    $tree = buildTree($laporanData); // Build hierarchical tree structure from flat list
}
?>

<?php include 'header.php'; ?>

<?php
// Prepare chart data for pendapatan and beban
$labelsPendapatan = [];
$valuesPendapatan = [];
$labelsBeban = [];
$valuesBeban = [];

foreach ($laporanData as $data) {
    if ($data['kategori'] === 'pendapatan') {
        $labelsPendapatan[] = $data['Uraian'];
        $valuesPendapatan[] = $data['REALISASI_TAHUN_INI'];
    } elseif ($data['kategori'] === 'beban') {
        $labelsBeban[] = $data['Uraian'];
        $valuesBeban[] = $data['REALISASI_TAHUN_INI'];
    }
}
?>

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
        <h5>Laporan - <?= htmlspecialchars($kategori === '' ? 'Pilih Kategori' : ($kategori === 'all' ? 'Semua Kategori' : ($categories[$kategori] ?? ''))) ?></h5>
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

        <!-- <div id="chartsContainer">
            <div id="pendapatanChart" class="chart"></div>
            <div id="bebanChart" class="chart"></div>
        </div>

        <script src="https://cdn.jsdelivr.net/npm/echarts@5.4.2/dist/echarts.min.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                var pendapatanChart = echarts.init(document.getElementById('pendapatanChart'));
                var bebanChart = echarts.init(document.getElementById('bebanChart'));

                var pendapatanOption = {
                    title: {
                        text: 'Pendapatan',
                        left: 'center'
                    },
                    tooltip: {},
                    xAxis: {
                        type: 'category',
                        data: <?= json_encode($labelsPendapatan) ?>
                    },
                    yAxis: {
                        type: 'value'
                    },
                    series: [{
                        data: <?= json_encode($valuesPendapatan) ?>,
                        type: 'bar'
                    }]
                };

                var bebanOption = {
                    title: {
                        text: 'Beban',
                        left: 'center'
                    },
                    tooltip: {},
                    xAxis: {
                        type: 'category',
                        data: <?= json_encode($labelsBeban) ?>
                    },
                    yAxis: {
                        type: 'value'
                    },
                    series: [{
                        data: <?= json_encode($valuesBeban) ?>,
                        type: 'bar'
                    }]
                };

                pendapatanChart.setOption(pendapatanOption);
                bebanChart.setOption(bebanOption);

                window.addEventListener('resize', function() {
                    pendapatanChart.resize();
                    bebanChart.resize();
                });
            });
        </script> -->

        <table class="table table-bordered table-striped table-hover">
            <thead class="table-dark">
                <tr>
                    <th>Nomor</th>
                    <th>U R A I A N</th>
                    <th>REALISASI TAHUN LALU</th>
                    <th>ANGGARAN TAHUN INI</th>
                    <th>REALISASI TAHUN INI</th>
                    <th>ANGGARAN PER TAHUN</th>
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
$totalAnggaranPerTahun = 0;
$totalAnalisisVertical = 0;

        function renderTree($tree, $level = 0, &$totals) {
            foreach ($tree as $node) {
                $indent = str_repeat('&nbsp;&nbsp;&nbsp;&nbsp;', $level);
                $numbering = $node['nomor'] ?? '';

                // Format numbering: parent numeric, child with letter suffixes
                if (isset($node['parent_id']) && $node['parent_id'] != null && $node['parent_id'] != 0) {
                    if (preg_match('/^(\d+)([A-Z]+)$/i', $numbering, $matches)) {
                        $parentNum = $matches[1];
                        $letterSuffix = strtolower($matches[2]);
                        $numbering = $parentNum . $letterSuffix;
                    }
                } else {
                    if (preg_match('/^\d+/', $numbering, $matches)) {
                        $numbering = $matches[0];
                    }
                }

                $ach1 = ($node['ANGGARAN_TAHUN_INI'] != 0) ? ($node['REALISASI_TAHUN_INI'] / $node['ANGGARAN_TAHUN_INI']) * 100 : 0;
                $gro = ($node['REALISASI_TAHUN_LALU'] != 0) ? (($node['REALISASI_TAHUN_INI'] - $node['REALISASI_TAHUN_LALU']) / $node['REALISASI_TAHUN_LALU']) * 100 : 0;
                $ach2 = ((isset($node['ANGGARAN_PER_TAHUN']) && $node['ANGGARAN_PER_TAHUN'] != 0) ? ($node['REALISASI_TAHUN_INI'] / $node['ANGGARAN_PER_TAHUN']) * 100 : 0);
                

                 // Perhitungan Ach 1, Ach 2, dan Analisis Vertical yang diminta
                $ach1_calc = $ach1;
                $ach2_calc = $ach2;
                $analisisVertical = ($totals['totalRealisasi'] != 0) ? ($node['REALISASI_TAHUN_INI'] / $totals['totalRealisasi']) * 100 : 0;

                $totals['totalRealisasiTahunLalu'] += $node['REALISASI_TAHUN_LALU'];
        $totals['totalAnggaranTahunIni'] += $node['ANGGARAN_TAHUN_INI'];
        $totals['totalRealisasiTahunIni'] += $node['REALISASI_TAHUN_INI'];
        $totals['totalAnggaranPerTahun'] += (isset($node['ANGGARAN_PER_TAHUN']) ? $node['ANGGARAN_PER_TAHUN'] : 0);
        $totals['totalAnalisisVertical'] += $analisisVertical;
        ?>
            <tr>
                <td><?= htmlspecialchars($numbering) ?></td>
                <td><?= $indent . htmlspecialchars($node['Uraian']) ?></td>
                <td><?= number_format($node['REALISASI_TAHUN_LALU'], 2, ',', '.') ?></td>
                <td><?= number_format($node['ANGGARAN_TAHUN_INI'], 2, ',', '.') ?></td>
                <td><?= number_format($node['REALISASI_TAHUN_INI'], 2, ',', '.') ?></td>
                <td>
                    <?php 
                    if (isset($node['ANGGARAN_PER_TAHUN'])) {
                        echo number_format($node['ANGGARAN_PER_TAHUN'], 2, ',', '.');
                    } else {
                        echo 0;
                    }
                    ?>
                </td>
                <td><?= number_format($ach1, 2, ',', '.') ?>%</td>
                <td><?= number_format($gro, 2, ',', '.') ?>%</td>
                <td><?= number_format($ach2, 2, ',', '.') ?>%</td>
                <td><?= number_format($analisisVertical, 2, ',', '.') ?>%</td>
                <td style="display: flex; justify-content: flex-end; gap: 6px; align-items: center;">
                    <a href="editlaporan.php?id=<?= $node['id'] ?>" class="btn btn-sm btn-outline-warning p-1 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;" title="Edit"><i class="bi bi-pencil"></i></a>
                    <form method="POST" action="hapuslaporan.php" style="margin: 0;" onsubmit="return confirm('Yakin ingin menghapus data ini?');">
                        <input type="hidden" name="id" value="<?= htmlspecialchars($node['id']) ?>">
                        <input type="hidden" name="kategori" value="<?= htmlspecialchars($node['kategori']) ?>">
                        <button type="submit" class="btn btn-sm btn-outline-danger p-1 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;" title="Hapus"><i class="bi bi-trash"></i></button>
                    </form>
                    <a href="tambahlaporan.php?kategori=<?= urlencode($node['kategori']) ?>&parent_id=<?= $node['id'] ?>" class="btn btn-sm btn-outline-success p-1 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;" title="Tambah Sub Uraian"><i class="bi bi-plus-lg"></i></a>
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
        'totalAnggaranPerTahun' => 0,
        'totalAnalisisVertical' => 0,
        'totalRealisasi' => $totalRealisasi
    ];

    if ($kategori === 'all' || $kategori === 'laba rugi usaha') {
        $pendapatanRealisasiLalu = getTotalByKategori($conn, 'pendapatan', 'REALISASI_TAHUN_LALU');
        $bebanRealisasiLalu = getTotalByKategori($conn, 'beban', 'REALISASI_TAHUN_LALU');
        $pendapatanRealisasiIni = getTotalByKategori($conn, 'pendapatan', 'REALISASI_TAHUN_INI');
        $bebanRealisasiIni = getTotalByKategori($conn, 'beban', 'REALISASI_TAHUN_INI');
        $pendapatanAnggaranIni = getTotalByKategori($conn, 'pendapatan', 'ANGGARAN_TAHUN_INI');
        $bebanAnggaranIni = getTotalByKategori($conn, 'beban', 'ANGGARAN_TAHUN_INI');
        error_log("DEBUG: getTotalByKategori pendapatan ANGGARAN_PER_TAHUN with bulan=$bulan tahun=$tahun");
        $pendapatanAnggaranPerTahun = getTotalByKategori($conn, 'pendapatan', 'ANGGARAN_PER_TAHUN', $bulan, $tahun);
        error_log("DEBUG: pendapatanAnggaranPerTahun = $pendapatanAnggaranPerTahun");

        error_log("DEBUG: getTotalByKategori beban ANGGARAN_PER_TAHUN with bulan=$bulan tahun=$tahun");
        $bebanAnggaranPerTahun = getTotalByKategori($conn, 'beban', 'ANGGARAN_PER_TAHUN', $bulan, $tahun);
        error_log("DEBUG: bebanAnggaranPerTahun = $bebanAnggaranPerTahun");

        $totalsLabaRugiUsaha = [
            'totalRealisasiTahunLalu' => $pendapatanRealisasiLalu + $bebanRealisasiLalu,
            'totalRealisasiTahunIni' => $pendapatanRealisasiIni + $bebanRealisasiIni,
            'totalAnggaranTahunIni' => $pendapatanAnggaranIni + $bebanAnggaranIni,
            'totalAnggaranPerTahun' => $pendapatanAnggaranPerTahun + $bebanAnggaranPerTahun,
            'totalAnalisisVertical' => 0,
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
                if ($currentKategori === 'laba rugi usaha' && isset($totalsLabaRugiUsaha)) {
                    echo number_format($totalsLabaRugiUsaha['totalRealisasiTahunLalu'], 2, ',', '.');
                } else {
                    echo number_format($totals['totalRealisasiTahunLalu'], 2, ',', '.');
                }
                ?>
            </td>
            <td><?= number_format($totals['totalAnggaranTahunIni'], 2, ',', '.') ?></td>
            <td>
                <?php
                if ($currentKategori === 'laba rugi usaha' && isset($totalsLabaRugiUsaha)) {
                    echo number_format($totalsLabaRugiUsaha['totalRealisasiTahunIni'], 2, ',', '.');
                } else {
                    echo number_format($totals['totalRealisasiTahunIni'], 2, ',', '.');
                }
                ?>
            </td>
            <td>
                <?php
                if ($currentKategori === 'laba rugi usaha' && isset($totalsLabaRugiUsaha)) {
                    echo number_format($totalsLabaRugiUsaha['totalAnggaranTahunIni'], 2, ',', '.');
                } else {
                    echo number_format(isset($totals['totalAnggaranPerTahun']) ? $totals['totalAnggaranPerTahun'] : 0, 2, ',', '.');
                }
                ?>
            </td>
            <td><?= number_format(isset($totals['totalAnggaranPerTahun']) ? $totals['totalAnggaranPerTahun'] : 0, 2, ',', '.') ?></td>
            <td><?= number_format(($totals['totalAnggaranTahunIni'] != 0) ? ($totals['totalRealisasiTahunIni'] / $totals['totalAnggaranTahunIni']) * 100 : 0, 2, ',', '.') ?>%</td>
            <td><?= number_format(($totals['totalRealisasiTahunLalu'] != 0) ? (($totals['totalRealisasiTahunIni'] - $totals['totalRealisasiTahunLalu']) / $totals['totalRealisasiTahunLalu']) * 100 : 0, 2, ',', '.') ?>%</td>
            <td><?= number_format((isset($totals['totalAnggaranPerTahun']) && $totals['totalAnggaranPerTahun'] != 0) ? ($totals['totalRealisasiTahunIni'] / $totals['totalAnggaranPerTahun']) * 100 : 0, 2, ',', '.') ?>%</td>
            <td><?= number_format(($totals['totalRealisasi'] != 0) ? ($totals['totalRealisasiTahunIni'] / $totals['totalRealisasi']) * 100 : 0, 2, ',', '.') ?>%</td>
            <td></td>
        </tr>
<?php
            endif;
    $totals = [
        'totalRealisasiTahunLalu' => 0,
        'totalAnggaranTahunIni' => 0,
        'totalRealisasiTahunIni' => 0,
        'totalAnggaranPerTahun' => 0,
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
        <td></td>
        <td>Jumlah <?= htmlspecialchars($categories[$currentKategori]) ?></td>
        <td><?= number_format($totals['totalRealisasiTahunLalu'], 2, ',', '.') ?></td>
        <td><?= number_format($totals['totalAnggaranTahunIni'], 2, ',', '.') ?></td>
        <td><?= number_format($totals['totalRealisasiTahunIni'], 2, ',', '.') ?></td>
            <td><?= number_format(isset($totals['totalAnggaranPerTahun']) ? $totals['totalAnggaranPerTahun'] : 0, 2, ',', '.') ?></td>
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
<script>
document.addEventListener('DOMContentLoaded', function() {
    function adjustCardSize() {
        var card = document.querySelector('.card.p-3');
        var table = card.querySelector('table');
        if (card && table) {
            console.log('Table height:', table.offsetHeight);
            card.style.height = (table.offsetHeight + 150) + 'px';
            card.style.width = (table.offsetWidth + 50) + 'px';
            console.log('Card height set to:', card.style.height);
            console.log('Card width set to:', card.style.width);
        }
    }
    adjustCardSize();
    window.addEventListener('resize', adjustCardSize);
});
</script>
<?php include 'footer.php'; ?>
<script src="assets/js/sidebar-accordion.js"></script>
<script src="assets/js/main.js"></script>
