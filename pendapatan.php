<?php

include 'koneksi.php';

$sql = "SELECT *, 
        CASE WHEN ANGGARAN_tahun != 0 THEN (REALISASI_tahun / ANGGARAN_tahun) * 100 ELSE 0 END AS ach_65
        FROM pendapatan ORDER BY id ASC";
$result = $conn->query($sql);
?>

<?php include 'header.php'; ?>

<main class="container mt-4">
    <h2>Data Pendapatan</h2>
    <a href="tambah_pendapatan.php" class="btn btn-primary mb-3">Tambah Data</a>
    <table class="table table-bordered table-striped">
        <thead class="table-dark">
            <tr>
                <th>NO</th>
                <th>U R A I A N</th>
                <th>APRIL 2024 REALISASI</th>
                <th>APRIL 2025 ANGGARAN</th>
                <th>2025 REALISASI</th>
                <th>% Ach (6:4)</th>
                <th>% Gro (6:3)</th>
                <th>% Ach (6:5)</th>
                <th>ANALISIS VERTICAL</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($result->num_rows > 0): ?>
                <?php while($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row['no']) ?></td>
                    <td><?= htmlspecialchars($row['Uraian']) ?></td>
                    <td><?= number_format($row['REALISASI_tahunSebelum'], 2, ',', '.') ?></td>
                    <td><?= number_format($row['ANGGARAN_tahun'], 2, ',', '.') ?></td>
                    <td><?= number_format($row['REALISASI_tahun'], 2, ',', '.') ?></td>
                    <td><?= number_format($row['ACH'], 2, ',', '.') ?>%</td>
                    <td><?= number_format($row['GROWTH'], 2, ',', '.') ?>%</td>
                    <td><?= number_format($row['ach_65'], 2, ',', '.') ?>%</td>
                    <td><?= nl2br(htmlspecialchars($row['ANALISIS_VERTICAL'])) ?></td>
                    <td>
                        <a href="edit_pendapatan.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-warning">Edit</a>
                        <a href="hapus_pendapatan.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Yakin ingin menghapus data ini?');">Hapus</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="10" class="text-center">Tidak ada data.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</main>

<?php include 'footer.php'; ?>
