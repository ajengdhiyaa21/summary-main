<?php
$pageTitle = "Data Investasi";
include 'header.php';

// Koneksi database
$conn = new mysqli("localhost", "root", "", "kai");
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

$filter_bulan = isset($_GET['bulan']) ? (int)$_GET['bulan'] : 0;
$filter_tahun = isset($_GET['tahun']) ? (int)$_GET['tahun'] : 0;
$search_query = isset($_GET['search']) ? trim($_GET['search']) : '';

// Buat query dasar
$sql = "SELECT * FROM investasi";

// Tambahkan kondisi filter jika ada
$conditions = [];
$params = [];
$param_types = '';

if ($filter_bulan > 0) {
    $conditions[] = "MONTH(input_date) = ?";
    $params[] = $filter_bulan;
    $param_types .= 'i';
}

if ($filter_tahun > 0) {
    $conditions[] = "YEAR(input_date) = ?";
    $params[] = $filter_tahun;
    $param_types .= 'i';
}

if (!empty($search_query)) {
    $conditions[] = "(uraian LIKE ? OR wbs LIKE ? OR lokasi_pengadaan LIKE ?)";
    $search_param = '%' . $search_query . '%';
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
    $param_types .= 'sss';
}

if (count($conditions) > 0) {
    $sql .= " WHERE " . implode(" AND ", $conditions);
}

$sql .= " ORDER BY id ASC";

if (count($params) > 0) {
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($param_types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = $conn->query($sql);
}

?>

<main id="main" class="main">

<div class="pagetitle">
    <h1>Data Investasi</h1>
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
            <li class="breadcrumb-item active">Data Investasi</li>
        </ol>
    </nav>
</div>

<div class="card p-3" style="padding: 30px; margin-bottom: 20px;">
<div class="col-md">
        <a href="tambah_investasi.php" class="btn btn-success">+ Tambah Data Investasi</a>
    </div>
<form method="GET" class="row g-3 mb-3" action="investasi.php">
    
    <div class="col-md-3">
        <label for="bulan" class="form-label"></label>
        <select name="bulan" id="bulan" class="form-select">
            <option value="0">Semua Bulan</option>
            <?php
            for ($m=1; $m<=12; $m++) {
                $selected = ($m == $filter_bulan) ? 'selected' : '';
                echo "<option value='$m' $selected>" . date('F', mktime(0,0,0,$m,1)) . "</option>";
            }
            ?>
        </select>
    </div>
    <div class="col-md-3">
        <label for="tahun" class="form-label"></label>
        <select name="tahun" id="tahun" class="form-select">
            <option value="0">Semua Tahun</option>
            <?php
            $year_start = 2020;
            $year_end = date('Y');
            for ($y=$year_start; $y<=$year_end; $y++) {
                $selected = ($y == $filter_tahun) ? 'selected' : '';
                echo "<option value='$y' $selected>$y</option>";
            }
            ?>
        </select>
    </div>
    <div class="col-md-3">
        <label for="search" class="form-label"></label>
        <input type="text" name="search" id="search" class="form-control" value="<?= htmlspecialchars($search_query) ?>" placeholder="Cari Data">
    </div>
    <div class="col-md-3 align-self-end d-flex gap-2">
        <button type="submit" class="btn btn-primary">Filter</button>
        <a href="investasi.php" class="btn btn-secondary">Reset</a>
        
    </div>
</form>

<table class="table table-bordered table-striped align-middle text-center">
    <thead class="table-light">
        <tr>
            <th>NO</th>
            <th>URAIAN</th>
            <th>WBS</th>
            <th>LOKASI PENGADAAN</th>
            <th>VOLUME & SATUAN</th>
            <th>HARGA SATUAN (Rp)</th>
            <th>JUMLAH DANA (Rp)</th>
            <th>BUDGET TAHUN 2024 (Rp)</th>
            <th>TAMBAHAN DANA (Rp)</th>
            <th>TOTAL TAHUN 2024 (Rp)</th>
            <th>COMMITMENT</th>
            <th>ACTUAL</th>
            <th>CONSUMED BUDGET</th>
            <th>AVAILABLE BUDGET</th>
            <th>PROGRES SAAT INI</th>
            <th>TANGGAL KONTRAK</th>
            <th>NO KONTRAK</th>
            <th>NILAI KONTRAK (Rp)</th>
            <th>KET</th>
            <th>Aksi</th>
        </tr>
    </thead>
        <tbody>
        <?php
        $total_jumlah_dana = 0;
        $total_budget_tahun_2024 = 0;
        $total_tambahan_dana = 0;
        $total_total_tahun_2024 = 0;
        $total_commitment = 0;
        $total_actual = 0;
        $total_consumed_budget = 0;
        $total_available_budget = 0;
        $loopIndex = 1;
        while ($row = $result->fetch_assoc()):
            $calculated_jumlah_dana = floatval($row['volume_satuan']) * floatval($row['harga_satuan']);
            $calculated_total_tahun_2024 = floatval($row['budget_tahun_2024']) + floatval($row['tambahan_dana']);
            $calculated_consumed_budget = floatval($row['commitment']) + floatval($row['actual']);
            $calculated_available_budget = $calculated_total_tahun_2024 - $calculated_consumed_budget;

            $total_jumlah_dana += $calculated_jumlah_dana;
            $total_budget_tahun_2024 += floatval($row['budget_tahun_2024']);
            $total_tambahan_dana += floatval($row['tambahan_dana']);
            $total_total_tahun_2024 += $calculated_total_tahun_2024;
            $total_commitment += floatval($row['commitment']);
            $total_actual += floatval($row['actual']);
            $total_consumed_budget += $calculated_consumed_budget;
            $total_available_budget += $calculated_available_budget;
        ?>
        <tr>
            <td><?= htmlspecialchars($row['no']) ?></td>
            <td class="text-start"><?= htmlspecialchars($row['uraian']) ?></td>
            <td><?= htmlspecialchars($row['wbs']) ?></td>
            <td><?= htmlspecialchars($row['lokasi_pengadaan']) ?></td>
            <td><?= htmlspecialchars($row['volume_satuan']) ?></td>
            <td class="text-end"><?= number_format($row['harga_satuan'], 2) ?></td>
            <td class="text-end"><?= number_format($calculated_jumlah_dana, 2) ?></td>
            <td class="text-end"><?= number_format($row['budget_tahun_2024'], 2) ?></td>
            <td class="text-end"><?= number_format($row['tambahan_dana'], 2) ?></td>
            <td class="text-end"><?= number_format($calculated_total_tahun_2024, 2) ?></td>
            <td class="text-end"><?= number_format($row['commitment'], 2) ?></td>
            <td class="text-end"><?= number_format($row['actual'], 2) ?></td>
            <td class="text-end"><?= number_format($calculated_consumed_budget, 2) ?></td>
            <td class="text-end"><?= number_format($calculated_available_budget, 2) ?></td>
            <td class="text-start"><?= nl2br(htmlspecialchars($row['progres_saat_ini'])) ?></td>
            <td><?= htmlspecialchars($row['tanggal_kontrak']) ?></td>
            <td><?= htmlspecialchars($row['no_kontrak']) ?></td>
            <td class="text-end"><?= number_format($row['nilai_kontrak'], 2) ?></td>
            <td class="text-start"><?= nl2br(htmlspecialchars($row['ket'])) ?></td>
            <td>
                <a href="edit_investasi.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-warning mb-1">Edit</a>
                <a href="hapus_investasi.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-danger mb-1" onclick="return confirm('Yakin hapus data?')">Hapus</a>
            </td>
        </tr>
        <?php endwhile; ?>
        </tbody>
        <tfoot>
            <tr style="font-weight: bold; background-color: #f0f0f0;">
                <td colspan="6" class="text-end">Jumlah</td>
                <td class="text-end"><?= number_format($total_jumlah_dana, 2) ?></td>
                <td class="text-end"><?= number_format($total_budget_tahun_2024, 2) ?></td>
                <td class="text-end"><?= number_format($total_tambahan_dana, 2) ?></td>
                <td class="text-end"><?= number_format($total_total_tahun_2024, 2) ?></td>
                <td class="text-end"><?= number_format($total_commitment, 2) ?></td>
                <td class="text-end"><?= number_format($total_actual, 2) ?></td>
                <td class="text-end"><?= number_format($total_consumed_budget, 2) ?></td>
                <td class="text-end"><?= number_format($total_available_budget, 2) ?></td>
                <td colspan="6"></td>
            </tr>
        </tfoot>
</table>

</div>
 <a href="#" class="back-to-top d-flex align-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>

</main>
<script src="assets/js/main.js"></script>
<?php include 'footer.php'; ?>


