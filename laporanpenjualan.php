<main class="content">

    <h2 class="section-title">Pendapatan</h2>

    <div class="laporan-card">
        <div class="infolaporan-card">
            <h3 class="laporan-harga">
                Rp. <?= number_format($total_pendapatan_harian ?? 0, 0, ',', '.'); ?>
            </h3>
        </div>

        <div class="laporan-tanggal">
            <?= !empty($tanggal_hari_ini) ? date('d / m / Y', strtotime($tanggal_hari_ini)) : date('d / m / Y'); ?>
        </div>
    </div>

    <h2 class="section-title">Statistik</h2>

    <div class="section-header">
        <form method="post" action="<?= base_url('laporanpenjualan/index'); ?>" class="laporan-filter">
            <label for="periode">Pilih Periode:</label>

            <select name="periode" id="periode">
                <option value="harian"   <?= (($periode_selected ?? 'harian') === 'harian') ? 'selected' : ''; ?>>Harian</option>
                <option value="mingguan" <?= (($periode_selected ?? 'harian') === 'mingguan') ? 'selected' : ''; ?>>Mingguan</option>
                <option value="bulanan"  <?= (($periode_selected ?? 'harian') === 'bulanan') ? 'selected' : ''; ?>>Bulanan</option>
            </select>

            <input type="date" name="tanggal_awal" value="<?= htmlspecialchars($tanggal_awal ?? '', ENT_QUOTES); ?>">
            <input type="date" name="tanggal_akhir" value="<?= htmlspecialchars($tanggal_akhir ?? '', ENT_QUOTES); ?>">

            <button type="submit">Tampilkan</button>
        </form>
    </div>

    <canvas id="chartPendapatan"></canvas>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
    var ctx = document.getElementById('chartPendapatan').getContext('2d');
    var chart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: <?= json_encode($labels ?? []); ?>,
            datasets: [{
                label: 'Pendapatan',
                data: <?= json_encode($values ?? []); ?>,
                borderColor: 'blue',
                fill: false
            }]
        }
    });
    </script>

    <h2 class="section-title">Daftar</h2>

    <table class="tabel-penjualan">
        <thead>
            <tr>
                <th>No</th>
                <th>Tanggal</th>
                <th>Total Transaksi</th>
            </tr>
        </thead>

        <tbody>
        <?php if (!empty($penjualan_harian)): ?>
            <?php $no = ($start ?? 0) + 1; ?>
            <?php foreach ($penjualan_harian as $row): ?>
                <tr>
                    <td><?= $no++; ?></td>
                    <td>
                        <?php
                        if (!empty($row['tanggal'])) {
                            echo date('d/m/Y', strtotime($row['tanggal']));
                        } elseif (!empty($row['tanggal_transaksi'])) {
                            echo date('d/m/Y', strtotime($row['tanggal_transaksi']));
                        } elseif (!empty($row['minggu'])) {
                            echo 'Minggu ' . $row['minggu'];
                        } elseif (!empty($row['bulan'])) {
                            echo date('F Y', strtotime($row['bulan'] . '-01'));
                        } else {
                            echo '-';
                        }
                        ?>
                    </td>
                    <td>
                        Rp <?= number_format(
                            $row['total_transaksi']
                            ?? $row['total_pendapatan']
                            ?? 0,
                            0, ',', '.'
                        ); ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="3" style="text-align:center;">Tidak ada data transaksi</td>
            </tr>
        <?php endif; ?>
        </tbody>
    </table>

    <?php
    $params = http_build_query([
        'periode' => ($periode_selected ?? 'harian'),
        'tanggal_awal' => ($tanggal_awal ?? ''),
        'tanggal_akhir' => ($tanggal_akhir ?? ''),
    ]);
    ?>

    <div class="download-wrapper">
        <a href="<?= base_url('laporanpenjualan/export_pdf?'.$params); ?>" class="btn-pdf">
            Unduh PDF
        </a>

        <a href="<?= base_url('laporanpenjualan/export_csv?'.$params); ?>" class="btn-csv">
            Unduh CSV
        </a>
    </div>

    <div id="pagination-final-target">
        <?= $pagination ?? ''; ?>
    </div>

</main>
