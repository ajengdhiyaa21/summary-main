<?php
// Alternative XLSX export without Composer or external libraries
// Using simple XML Spreadsheet format compatible with Excel

include 'koneksi.php';

$kategori = $_GET['kategori'] ?? 'pendapatan';
$bulan = $_GET['bulan'] ?? '';
$tahun = $_GET['tahun'] ?? '';
$search = $_GET['search'] ?? '';

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

$sql = "SELECT * FROM laporan $whereSql ORDER BY kategori, id ASC";
$stmt = $conn->prepare($sql);
if ($paramTypes) {
    $stmt->bind_param($paramTypes, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

$filename = 'laporan_keuangan_' . date('Ymd_His') . '.xls';

header("Content-Type: application/vnd.ms-excel; charset=utf-8");
header("Content-Disposition: attachment; filename=\"$filename\"");
header("Cache-Control: max-age=0");

echo "<?xml version=\"1.0\"?>\n";
echo "<?mso-application progid=\"Excel.Sheet\"?>\n";
echo "<Workbook xmlns=\"urn:schemas-microsoft-com:office:spreadsheet\"\n";
echo " xmlns:o=\"urn:schemas-microsoft-com:office:office\"\n";
echo " xmlns:x=\"urn:schemas-microsoft-com:office:excel\"\n";
echo " xmlns:ss=\"urn:schemas-microsoft-com:office:spreadsheet\"\n";
echo " xmlns:html=\"http://www.w3.org/TR/REC-html40\">\n";
echo " <Worksheet ss:Name=\"Laporan Keuangan\">\n";
echo "  <Table>\n";

// Header row
$headers = [
    'Uraian', 'Realisasi Tahun Lalu', 'Anggaran Tahun Ini', 'Realisasi Tahun Ini',
    'Anggaran Tahun 2025', '% Ach (1)', '% Gro', '% Ach (2)', 'Analisis Vertical'
];

echo "   <Row>\n";
foreach ($headers as $header) {
    echo "    <Cell><Data ss:Type=\"String\">" . htmlspecialchars($header) . "</Data></Cell>\n";
}
echo "   </Row>\n";

// Data rows
while ($row = $result->fetch_assoc()) {
    $ach1 = ($row['ANGGARAN_TAHUN_INI'] != 0) ? ($row['REALISASI_TAHUN_INI'] / $row['ANGGARAN_TAHUN_INI']) * 100 : 0;
    $gro = ($row['REALISASI_TAHUN_LALU'] != 0) ? (($row['REALISASI_TAHUN_INI'] - $row['REALISASI_TAHUN_LALU']) / $row['REALISASI_TAHUN_LALU']) * 100 : 0;
    $ach2 = ($row['ANGGARAN_TAHUN_2025'] != 0) ? ($row['REALISASI_TAHUN_INI'] / $row['ANGGARAN_TAHUN_2025']) * 100 : 0;
    $analisisVertical = 0; // Could calculate if needed

    echo "   <Row>\n";
    echo "    <Cell><Data ss:Type=\"String\">" . htmlspecialchars($row['Uraian']) . "</Data></Cell>\n";
    echo "    <Cell><Data ss:Type=\"Number\">" . $row['REALISASI_TAHUN_LALU'] . "</Data></Cell>\n";
    echo "    <Cell><Data ss:Type=\"Number\">" . $row['ANGGARAN_TAHUN_INI'] . "</Data></Cell>\n";
    echo "    <Cell><Data ss:Type=\"Number\">" . $row['REALISASI_TAHUN_INI'] . "</Data></Cell>\n";
    echo "    <Cell><Data ss:Type=\"Number\">" . $row['ANGGARAN_TAHUN_2025'] . "</Data></Cell>\n";
    echo "    <Cell><Data ss:Type=\"String\">" . round($ach1, 2) . "%</Data></Cell>\n";
    echo "    <Cell><Data ss:Type=\"String\">" . round($gro, 2) . "%</Data></Cell>\n";
    echo "    <Cell><Data ss:Type=\"String\">" . round($ach2, 2) . "%</Data></Cell>\n";
    echo "    <Cell><Data ss:Type=\"String\">" . $analisisVertical . "</Data></Cell>\n";
    echo "   </Row>\n";
}

echo "  </Table>\n";
echo " </Worksheet>\n";
echo "</Workbook>\n";

exit;
?>
