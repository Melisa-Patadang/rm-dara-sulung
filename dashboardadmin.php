<main class="content">
    <h2 class="section-title">Dashboard</h2>

    <div class="dashboard-grid">
        <div class="menu-list">
            <?php foreach ($menu as $m): ?>
                <div class="menu-card">
                    <img src="<?= base_url('uploads/menu/'.$m['gambar']); ?>" class="menu-img">

                    <div class="menu-info">
                        <h3 class="menu-title"><?= $m['nama_menu']; ?></h3>

                        <p class="menu-price">
                            Rp <?= number_format($m['harga'], 0, ',', '.'); ?>
                        </p>

                        <p class="menu-desc">
                            <?= $m['nama_menu']; ?> || <?= $m['kategori']; ?>
                        </p>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <div class="chart-card">
            <div class="chart-title">Penjualan 6 Bulan Terakhir</div>
            <div class="chart-wrap">
                <canvas id="salesChart"></canvas>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const labels = <?= json_encode($chart_labels ?? []); ?>;
        const values = <?= json_encode($chart_values ?? []); ?>;

        const ctx = document.getElementById('salesChart');

        if (labels.length && values.length) {
            new Chart(ctx, {
          type: 'bar',
          data: {
            labels: labels,
            datasets: [{
              label: 'Total Penjualan',
              data: values,

    
              backgroundColor: 'rgba(226, 133, 46, 0.35)', 
              borderColor: '#E2852E',                    
              borderWidth: 2,
              borderRadius: 8
            }]
          },
          options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
              legend: { display: false }
            },
            scales: {
              y: { beginAtZero: true }
            }
          }
        });

        }
    </script>

    <h2 style="text-align:center;">Tabel Penjualan</h2>

    <table class="tabel-penjualan">
        <thead>
            <tr>
                <th>No</th>
                <th>Periode Laporan</th>
                <th>Total Penjualan</th>
                <th>Tanggal Cetak</th>
            </tr>
        </thead>
        <tbody>
            <?php $no = 1; foreach($penjualan as $p): ?>
                <tr>
                    <td><?= $no++; ?></td>
                    <td><?= $p['periode_laporan']; ?></td>
                    <td><?= number_format($p['total_penjualan'],0,',','.'); ?></td>
                    <td><?= $p['tanggal_cetak']; ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</main>
