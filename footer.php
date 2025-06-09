</div> <!-- end of #content atau #mainContent -->

<footer class="footer py-4 bg-light text-center">
  <div class="container">
    <p class="mb-1">© Copyright <strong>Laporan Keuangan.</strong> All Rights Reserved</p>
    <p>Designed by <a href="#">KAI</a></p>
  </div>
</footer>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {
  function createBarChart(ctx, labels, data, label, color) {
      return new Chart(ctx, {
          type: 'bar',
          data: {
              labels: labels,
              datasets: [{
                  label: label,
                  data: data,
                  backgroundColor: color,
                  borderRadius: 4,
                  barPercentage: 0.6,
              }]
          },
          options: {
              responsive: true,
              plugins: {
                  legend: { display: false },
                  tooltip: { enabled: true }
              },
              scales: {
                  y: { beginAtZero: true }
              }
          }
      });
  }

  <?php if (isset($labelsPendapatan)): ?>
  createBarChart(document.getElementById('chartPendapatan'), <?= json_encode($labelsPendapatan) ?>, <?= json_encode($valuesPendapatan) ?>, 'Pendapatan', '#198754');
  <?php endif; ?>
  <?php if (isset($labelsBeban)): ?>
  createBarChart(document.getElementById('chartBeban'), <?= json_encode($labelsBeban) ?>, <?= json_encode($valuesBeban) ?>, 'Beban', '#dc3545');
  <?php endif; ?>
  <?php if (isset($labelsPendapatanPenumpang)): ?>
  createBarChart(document.getElementById('chartPendapatanPenumpang'), <?= json_encode($labelsPendapatanPenumpang) ?>, <?= json_encode($valuesPendapatanPenumpang) ?>, 'Pendapatan Penumpang', '#0d6efd');
  <?php endif; ?>
  <?php if (isset($labelsPendapatanBarang)): ?>
  createBarChart(document.getElementById('chartPendapatanBarang'), <?= json_encode($labelsPendapatanBarang) ?>, <?= json_encode($valuesPendapatanBarang) ?>, 'Pendapatan Barang', '#fd7e14');
  <?php endif; ?>
  <?php if (isset($labelsPendapatanAsset)): ?>
  createBarChart(document.getElementById('chartPendapatanAsset'), <?= json_encode($labelsPendapatanAsset) ?>, <?= json_encode($valuesPendapatanAsset) ?>, 'Pendapatan Asset', '#6f42c1');
  <?php endif; ?>
});
</script>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
