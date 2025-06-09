<?php
$pageTitle = "Laporan Keuangan";
include 'header.php';
$conn = new mysqli("localhost", "root", "", "kai");
if ($conn->connect_error) die("Koneksi gagal: " . $conn->connect_error);

$bulan = isset($_GET['bulan']) ? (int)$_GET['bulan'] : date('n');
$tahun = isset($_GET['tahun']) ? (int)$_GET['tahun'] : date('Y');

function getDataKategori($conn, $kategori, $bulan, $tahun) {
    $sql = "SELECT uraian, SUM(COALESCE(realisasi,0)) AS total_realisasi 
            FROM laporan_keuangan l 
            LEFT JOIN laporan_nilai n ON l.id = n.laporan_id AND n.bulan = ? AND n.tahun = ?
            WHERE kategori = ? 
            GROUP BY uraian 
            ORDER BY uraian";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iis", $bulan, $tahun, $kategori);
    $stmt->execute();
    $result = $stmt->get_result();
    $labels = [];
    $values = [];
    while ($row = $result->fetch_assoc()) {
        $labels[] = $row['uraian'];
        $values[] = (float)$row['total_realisasi'];
    }
    $stmt->close();
    return [$labels, $values];
}

// New function to get preview data from laporan_preview view
function getLaporanPreview($conn) {
    $sql = "SELECT kategori, total_realisasi_tahun_lalu, total_anggaran_tahun_ini, total_realisasi_tahun_ini, total_anggaran_tahun_2025 FROM laporan_preview";
    $result = $conn->query($sql);
    $previewData = [];
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $previewData[$row['kategori']] = $row;
        }
        $result->free();
    }
    return $previewData;
}

$laporanPreview = getLaporanPreview($conn);


// Fungsi baru untuk mendapatkan data realisasi dan anggaran per uraian
function getDataRealisasiAnggaran($conn, $kategori, $bulan, $tahun, $uraianFilter = null) {
    $sql = "SELECT uraian, 
                   SUM(COALESCE(realisasi,0)) AS total_realisasi, 
                   SUM(COALESCE(anggaran,0)) AS total_anggaran
            FROM laporan_keuangan l 
            LEFT JOIN laporan_nilai n ON l.id = n.laporan_id AND n.bulan = ? AND n.tahun = ?
            WHERE kategori = ? ";
    if ($uraianFilter !== null) {
        $sql .= " AND uraian LIKE ? ";
    }
    $sql .= " GROUP BY uraian ORDER BY uraian";

    $stmt = $conn->prepare($sql);
    if ($uraianFilter !== null) {
        $likeFilter = "%$uraianFilter%";
        $stmt->bind_param("iiss", $bulan, $tahun, $kategori, $likeFilter);
    } else {
        $stmt->bind_param("iis", $bulan, $tahun, $kategori);
    }
    $stmt->execute();
    $result = $stmt->get_result();

    $labels = [];
    $realisasi = [];
    $anggaran = [];
    while ($row = $result->fetch_assoc()) {
        $labels[] = $row['uraian'];
        $realisasi[] = (float)$row['total_realisasi'];
        $anggaran[] = (float)$row['total_anggaran'];
    }
    $stmt->close();

    return [$labels, $realisasi, $anggaran];
}

// New code to display laporan preview on dashboard
?>

<div class="card" style="padding: 20px; margin-top: 20px;">
    <h5 class="card-title">Preview Laporan Keuangan</h5>
    <canvas id="laporanPreviewChart" height="200"></canvas>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const ctxPreview = document.getElementById('laporanPreviewChart').getContext('2d');
    const labelsPreview = <?= json_encode(array_keys($laporanPreview)) ?>;
    const dataRealisasiPreview = <?= json_encode(array_map(function($item) { return (float)$item['total_realisasi_tahun_ini']; }, $laporanPreview)) ?>;
    const dataAnggaranPreview = <?= json_encode(array_map(function($item) { return (float)$item['total_anggaran_tahun_ini']; }, $laporanPreview)) ?>;
    const dataAnggaran2025Preview = <?= json_encode(array_map(function($item) { return (float)$item['total_anggaran_tahun_2025']; }, $laporanPreview)) ?>;

    const laporanPreviewChart = new Chart(ctxPreview, {
        type: 'bar',
        data: {
            labels: labelsPreview,
            datasets: [
                {
                    label: 'Realisasi Tahun Ini',
                    data: dataRealisasiPreview,
                    backgroundColor: '#4caf50'
                },
                {
                    label: 'Anggaran Tahun Ini',
                    data: dataAnggaranPreview,
                    backgroundColor: '#ff9800'
                },
                {
                    label: 'Anggaran Tahun 2025',
                    data: dataAnggaran2025Preview,
                    backgroundColor: '#2196f3'
                }
            ]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { position: 'top' },
                title: {
                    display: true,
                    text: 'Preview Laporan Keuangan per Kategori'
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return value.toLocaleString();
                        }
                    }
                }
            }
        }
    });
</script>

<?php

list($labelsPendapatan, $valuesPendapatan) = getDataKategori($conn, 'pendapatan', $bulan, $tahun);
list($labelsBeban, $valuesBeban) = getDataKategori($conn, 'beban', $bulan, $tahun);

// Data untuk card Pendapatan dan Beban dengan realisasi dan anggaran
list($labelsPendapatanDetail, $realisasiPendapatan, $anggaranPendapatan) = getDataRealisasiAnggaran($conn, 'pendapatan', $bulan, $tahun);
list($labelsBebanDetail, $realisasiBeban, $anggaranBeban) = getDataRealisasiAnggaran($conn, 'beban', $bulan, $tahun);

// Data untuk Kompensasi Pemerintahan (uraian mengandung 'Kompensasi Pemerintahan') dan Perawatan Sarana dan Prasarana (uraian mengandung 'Perawatan Sarana dan Prasarana')
list($labelsKompensasi, $realisasiKompensasi, $anggaranKompensasi) = getDataRealisasiAnggaran($conn, 'pendapatan', $bulan, $tahun, 'Kompensasi Pemerintahan');
list($labelsPerawatan, $realisasiPerawatan, $anggaranPerawatan) = getDataRealisasiAnggaran($conn, 'beban', $bulan, $tahun, 'Perawatan Sarana dan Prasarana');

// Prepare data for Laba Rugi line chart (metrics data for selected year)
$labaRugiLabels = ['Realisasi', 'Anggaran', 'Anggaran Tahun', '% Ach', '% Growth', '% Ach (Lalu)', 'Analisis Vertical'];

$metrics = ['realisasi', 'anggaran', 'anggaran_tahun', 'ach', 'growth', 'ach_lalu', 'analisis_vertical'];

$pendapatanSums = [];
$bebanSums = [];

foreach ($metrics as $metric) {
    // Sum for pendapatan
    $sqlPendapatan = "SELECT SUM(COALESCE(n.$metric,0)) AS total FROM laporan_keuangan l LEFT JOIN laporan_nilai n ON l.id = n.laporan_id WHERE l.kategori = 'pendapatan' AND n.tahun = ?";
    $stmtPendapatan = $conn->prepare($sqlPendapatan);
    $stmtPendapatan->bind_param("i", $tahun);
    $stmtPendapatan->execute();
    $resultPendapatan = $stmtPendapatan->get_result();
    $rowPendapatan = $resultPendapatan->fetch_assoc();
    $pendapatanSums[$metric] = (float)$rowPendapatan['total'];
    $stmtPendapatan->close();

    // Sum for beban
    $sqlBeban = "SELECT SUM(COALESCE(n.$metric,0)) AS total FROM laporan_keuangan l LEFT JOIN laporan_nilai n ON l.id = n.laporan_id WHERE l.kategori = 'beban' AND n.tahun = ?";
    $stmtBeban = $conn->prepare($sqlBeban);
    $stmtBeban->bind_param("i", $tahun);
    $stmtBeban->execute();
    $resultBeban = $stmtBeban->get_result();
    $rowBeban = $resultBeban->fetch_assoc();
    $bebanSums[$metric] = (float)$rowBeban['total'];
    $stmtBeban->close();

    // Calculate difference pendapatan - beban
    $diff = $pendapatanSums[$metric] - $bebanSums[$metric];
    if ($diff >= 0) {
        $labaValues[] = $diff;
        $rugiValues[] = 0;
    } else {
        $labaValues[] = 0;
        $rugiValues[] = abs($diff);
    }
}

function totalValue($values) {
    return array_sum($values);
}

$tahunMulai = 2020;
$tahunSekarang = date('Y');

$namaBulan = [
    1=>'Januari', 2=>'Februari', 3=>'Maret', 4=>'April', 5=>'Mei', 6=>'Juni',
    7=>'Juli', 8=>'Agustus', 9=>'September', 10=>'Oktober', 11=>'November', 12=>'Desember'
];
?>

<main id="main" class="main">

    <div class="pagetitle">
        <h1>Dashboard Keuangan</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                <li class="breadcrumb-item active">Dashboard</li>
            </ol>
        </nav>
    </div>

    <form method="GET" class="filter-form row g-2" style="margin-top: 20px; margin-bottom: 25px;">
        <div class="col-md-3">
            <label for="bulan" class="form-label">Bulan:</label>
            <select name="bulan" id="bulan" class="form-select" required>
                <?php foreach($namaBulan as $blnNum => $blnNama): ?>
                    <option value="<?= $blnNum ?>" <?= ($blnNum == $bulan) ? 'selected' : '' ?>><?= $blnNama ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-3">
            <label for="tahun" class="form-label">Tahun:</label>
            <select name="tahun" id="tahun" class="form-select" required>
                <?php for($t=$tahunMulai; $t<=$tahunSekarang; $t++): ?>
                    <option value="<?= $t ?>" <?= ($t == $tahun) ? 'selected' : '' ?>><?= $t ?></option>
                <?php endfor; ?>
            </select>
        </div>
        <div class="col-md-3 align-self-end">
            <button type="submit" class="btn btn-success">Tampilkan</button>
        </div>
    </form>

    <div class="dashboard-grid" style="display: grid; grid-template-columns: 1fr; gap: 12px 20px; margin-top: 20px;">
        <div class="card" style="padding: 20px;">
            <h5 class="card-title">Laba Rugi</h5>
            <canvas id="chartLabaRugi" height="200" style="width: 100%; height: 300px;"></canvas>
        </div>
    </div>

    <div class="dashboard-grid" style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 12px 20px; margin-top: 20px;">
        <div class="card" style="padding: 20px;">
            <h5 class="card-title">Pendapatan</h5>
            <canvas id="chartPendapatan" height="200"></canvas>
        </div>
        <div class="card" style="padding: 20px;">
            <h5 class="card-title">Beban</h5>
            <canvas id="chartBeban" height="200"></canvas>
        </div>
    </div>

    <div class="dashboard-grid" style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 12px 20px; margin-top: 20px;">
        <div class="card" style="padding: 20px;">
            <h5 class="card-title">Kompensasi Pemerintahan</h5>
            <canvas id="chartKompensasi" height="200"></canvas>
        </div>
        <div class="card" style="padding: 20px;">
            <h5 class="card-title">Perawatan Sarana dan Prasarana</h5>
            <canvas id="chartPerawatan" height="200"></canvas>
        </div>
    </div>
</main>

<?php include 'footer.php'; ?>
<a href="#" class="back-to-top d-flex align-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>


<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="assets/js/main.js"></script>
<script>
function createBarChart(ctx, labels, dataRealisasi, dataAnggaran, labelRealisasi, labelAnggaran, colorRealisasi, colorAnggaran, totalRealisasi = null, totalAnggaran = null) {
    return new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [
                {
                    label: labelRealisasi,
                    data: dataRealisasi,
                    backgroundColor: colorRealisasi,
                    borderRadius: 4,
                    barPercentage: 0.4,
                },
                {
                    label: labelAnggaran,
                    data: dataAnggaran,
                    backgroundColor: colorAnggaran,
                    borderRadius: 4,
                    barPercentage: 0.4,
                }
            ]
        },
        options: {
            responsive: true,
            plugins: {
                tooltip: { enabled: true },
                subtitle: {
                    display: (totalRealisasi !== null && totalAnggaran !== null),
                    text: totalRealisasi !== null && totalAnggaran !== null ? 
                        `Total Realisasi: ${totalRealisasi.toLocaleString()} | Total Anggaran: ${totalAnggaran.toLocaleString()}` : '',
                    font: {
                        size: 14,
                        weight: 'bold'
                    },
                    padding: {
                        bottom: 10
                    }
                }
            },
            scales: {
                y: { beginAtZero: true }
            }
        }
    });
}

function createLineChart(ctx, labels, data, label, color) {
    return new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: label,
                data: data,
                borderColor: color,
                backgroundColor: color,
                fill: false,
                tension: 0.1,
                pointRadius: 5,
                pointHoverRadius: 7,
                borderWidth: 3
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { display: true },
                tooltip: { enabled: true }
            },
            scales: {
                x: {
                    display: true,
                    title: {
                        display: true,
                        text: 'Keterangan'
                    }
                },
                y: {
                    beginAtZero: true,
                    display: true,
                    title: {
                        display: true,
                        text: 'Nominal (Rupiah)'
                    }
                }
            }
        }
    });
}

const labaData = <?= json_encode($labaValues) ?>;
const rugiData = <?= json_encode($rugiValues) ?>;
const labaRugiLabels = <?= json_encode($labaRugiLabels) ?>;

const ctx = document.getElementById('chartLabaRugi').getContext('2d');
const labaRugiChart = new Chart(ctx, {
    type: 'line',
    data: {
        labels: labaRugiLabels,
        datasets: [
            {
                label: 'Laba',
                data: labaData,
                borderColor: 'green',
                backgroundColor: 'green',
                fill: false,
                tension: 0.1,
                pointRadius: 5,
                pointHoverRadius: 7,
                borderWidth: 3
            },
            {
                label: 'Rugi',
                data: rugiData,
                borderColor: 'darkred',
                backgroundColor: 'darkred',
                fill: false,
                tension: 0.1,
                pointRadius: 5,
                pointHoverRadius: 7,
                borderWidth: 3
            }
        ]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { display: true },
            tooltip: { enabled: true }
        },
        scales: {
            x: {
                display: true,
                title: {
                    display: true,
                    text: 'Keterangan'
                }
            },
            y: {
                beginAtZero: true,
                display: true,
                title: {
                    display: true,
                    text: 'Nominal (Rupiah)'
                }
            }
        }
    }
});

createBarChart(
    document.getElementById('chartPendapatan'),
    <?= json_encode($labelsPendapatanDetail) ?>,
    <?= json_encode($realisasiPendapatan) ?>,
    <?= json_encode($anggaranPendapatan) ?>,
    'Realisasi',
    'Anggaran',
    '#006400',  // hijau
    '#cc5500',  // oren tua
    <?= json_encode(array_sum($realisasiPendapatan)) ?>,
    <?= json_encode(array_sum($anggaranPendapatan)) ?>
);

createBarChart(
    document.getElementById('chartBeban'),
    <?= json_encode($labelsBebanDetail) ?>,
    <?= json_encode($realisasiBeban) ?>,
    <?= json_encode($anggaranBeban) ?>,
    'Realisasi',
    'Anggaran',
    '#006400',  // hijau
    '#cc5500',  // oren tua
    <?= json_encode(array_sum($realisasiBeban)) ?>,
    <?= json_encode(array_sum($anggaranBeban)) ?>
);

createBarChart(
    document.getElementById('chartKompensasi'),
    <?= json_encode($labelsKompensasi) ?>,
    <?= json_encode($realisasiKompensasi) ?>,
    <?= json_encode($anggaranKompensasi) ?>,
    'Realisasi',
    'Anggaran',
    '#006400',  // hijau
    '#cc5500',  // oren tua
    <?= json_encode(array_sum($realisasiKompensasi)) ?>,
    <?= json_encode(array_sum($anggaranKompensasi)) ?>
);

createBarChart(
    document.getElementById('chartPerawatan'),
    <?= json_encode($labelsPerawatan) ?>,
    <?= json_encode($realisasiPerawatan) ?>,
    <?= json_encode($anggaranPerawatan) ?>,
    'Realisasi',
    'Anggaran',
    '#006400',  // hijau
    '#cc5500',  // oren tua
    <?= json_encode(array_sum($realisasiPerawatan)) ?>,
    <?= json_encode(array_sum($anggaranPerawatan)) ?>
);
</script>
