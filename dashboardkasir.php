<main class="kasir-content">
    <h2 class="kasir-title">Dashboard Kasir</h2>

    <div class="kasir-kpi-row">
        <div class="kasir-kpi-card">
<div class="kasir-kpi-label">Penjualan Hari Ini</div>
<div class="kasir-kpi-value"><?= (int)($penjualan_hari_ini ?? 0); ?></div>
<div style="font-size:12px; opacity:.75; margin-top:6px;">
  Pendapatan hari ini: Rp <?= number_format((int)($pendapatan_hari_ini ?? 0), 0, ',', '.'); ?>
</div>

        </div>

        <div class="kasir-chart-card">
            <div class="kasir-chart-title">Pendapatan (6 Bulan Terakhir)</div>
            <div class="kasir-chart-wrap">
                <canvas id="incomeChart"></canvas>
            </div>
        </div>
    </div>
    <div class="kasir-toolbar">
        <form class="kasir-search" method="get" action="<?= site_url('kasir/dashboardkasir'); ?>">
            <div class="kasir-search-box">
                <span class="kasir-search-icon"><i class="bi bi-search"></i></span>
                <input
                    type="text"
                    name="q"
                    placeholder="Cari id transaksi / id pesanan / nama..."
                    value="<?= $q ?? ''; ?>"
                >
            </div>

            <button type="submit" class="kasir-btn-cari">cari</button>

            <button
                type="submit"
                name="sort"
                value="<?= ($sort ?? 'desc') === 'desc' ? 'asc' : 'desc'; ?>"
                class="kasir-btn-sort"
                title="Urutkan"
            >
                <?= ($sort ?? 'desc') === 'desc' ? '↓↑' : '↑↓'; ?>
            </button>
        </form>
    </div>
    <div class="kasir-table-wrap">
        <table class="kasir-table">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Kode</th>
                    <th>ID Pesanan</th>
                    <th>Total</th>
                    <th>Tanggal</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($transaksi)): ?>
                    <?php
                       
                        $page = (int)($this->input->get('page') ?? 1);
                        if ($page < 1) $page = 1;
                        $no = ($page - 1) * 10 + 1;
                    ?>
                    <?php foreach ($transaksi as $t): ?>
                        <tr>
                            <td><?= $no++; ?></td>
                            <td><?= $t['kode']; ?></td>
                            <td><?= $t['id_pesanan']; ?></td>
                            <td><?= number_format($t['total_transaksi'], 0, ',', '.'); ?></td>
                            <td><?= $t['tanggal_transaksi']; ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" style="text-align:center; padding:18px;">
                            Belum ada data transaksi.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

    </div>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const incomeLabels = <?= json_encode($chart_labels ?? []); ?>;
        const incomeValues = <?= json_encode($chart_values ?? []); ?>;

        const incomeCtx = document.getElementById('incomeChart');

        if (incomeCtx && incomeLabels.length && incomeValues.length) {
            new Chart(incomeCtx, {
                type: 'bar',
                data: {
                    labels: incomeLabels,
                    datasets: [{
                        label: 'Pendapatan',
                        data: incomeValues,
                        backgroundColor: 'rgba(226, 133, 46, 0.35)',
                        borderColor: '#E2852E',
                        borderWidth: 2,
                        borderRadius: 8
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: { y: { beginAtZero: true } }
                }
            });
        }
    </script>
</main>
