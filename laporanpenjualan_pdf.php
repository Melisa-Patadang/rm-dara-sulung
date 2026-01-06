<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Laporan Penjualan</title>
    <style>
        body { 
            font-family: DejaVu Sans, Arial, sans-serif; 
            font-size: 12px; 
            color: #111; 
        }
        .header { 
            margin-bottom: 12px; 
        }
        .title { 
            font-size: 18px; 
            font-weight: bold; 
            margin: 0; 
        }
        .meta { margin: 4px 0 0 0; 
            font-size: 11px; 
        }
        .badge { 
            display: inline-block; 
            padding: 3px 8px; 
            border: 1px solid #333; 
            border-radius: 12px; 
            font-size: 11px; 
        }
        table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-top: 10px; }
        th, td { border: 1px solid #333; 
            padding: 8px; 
        }
        th { 
            background: #efefef; 
            text-align: left; 
        }
        td.num { 
            text-align: right; 
        }
        .footer { 
            margin-top: 12px;
         font-size: 11px; 
        }
        .total { 
            margin-top: 8px; 
            padding: 10px; 
            border: 1px solid #333; 
        }
        .total strong { 
            font-size: 13px; 
        }
    </style>
</head>
<body>

<?php
    $periode_label = 'Harian';
    if (($periode ?? '') === 'mingguan') $periode_label = 'Mingguan';
    if (($periode ?? '') === 'bulanan')  $periode_label = 'Bulanan';

    $ta = !empty($tanggal_awal) ? date('d/m/Y', strtotime($tanggal_awal)) : '-';
    $tk = !empty($tanggal_akhir) ? date('d/m/Y', strtotime($tanggal_akhir)) : '-';
?>

<div class="header">
    <p class="title">Laporan Penjualan</p>
    <p class="meta">
        <span class="badge">Periode: <?= htmlspecialchars($periode_label); ?></span>
        &nbsp;&nbsp;Tanggal Awal: <?= htmlspecialchars($ta); ?>
        &nbsp;&nbsp;Tanggal Akhir: <?= htmlspecialchars($tk); ?>
    </p>
    <p class="meta">Dicetak pada: <?= htmlspecialchars($printed_at ?? date('d/m/Y H:i')); ?></p>
</div>

<table>
    <thead>
        <tr>
            <th style="width:50px;">No</th>
            <th>Tanggal/Periode</th>
            <th style="width:180px;">Total Transaksi</th>
        </tr>
    </thead>
    <tbody>
        <?php if (!empty($rows)): ?>
            <?php $no = 1; foreach ($rows as $r): ?>
                <tr>
                    <td><?= (int)$no++; ?></td>
                    <td><?= htmlspecialchars($r['label_tanggal'] ?? '-'); ?></td>
                    <td class="num">Rp <?= number_format((float)($r['total'] ?? 0), 0, ',', '.'); ?></td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="3" style="text-align:center;">Tidak ada data</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>

<div class="total">
    <strong>Total Penjualan: Rp <?= number_format((float)($total_penjualan ?? 0), 0, ',', '.'); ?></strong>
</div>

<div class="footer">
    <div>Catatan: Nilai total dihitung dari akumulasi data pada periode yang dipilih.</div>
</div>

</body>
</html>
