<main class="kasir-content">
  <div class="rw-page">
    <div class="rw-wrap">

    <h2 class="rw-title">Riwayat</h2>

    <?php if($this->session->flashdata('pesan_gagal')): ?>
      <div class="rw-alert rw-alert--danger"><?= $this->session->flashdata('pesan_gagal'); ?></div>
    <?php endif; ?>

    <?php if($this->session->flashdata('pesan_berhasil')): ?>
      <div class="rw-alert rw-alert--success"><?= $this->session->flashdata('pesan_berhasil'); ?></div>
    <?php endif; ?>

      <div class="kasir-toolbar">
        <form class="kasir-search" method="get" action="<?= site_url('kasir/riwayat'); ?>">
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

    <div class="rw-table-box">
      <table class="rw-table">
        <thead>
          <tr>
            <th style="width:70px;">ID</th>
            <th>Tanggal</th>
            <th>Item</th>

            <th style="width:140px;">Total</th>
            <th style="width:120px;">Status</th>
            <th style="width:120px;">Action</th>
          </tr>
        </thead>
        <tbody>
          <?php if(empty($diproses)): ?>
            <tr>
              <td colspan="6" class="rw-empty">Tidak ada pesanan.</td>

            </tr>
          <?php else: ?>
            <?php foreach($diproses as $p): ?>
              <tr>
                <td><?= (int)$p->id_pesanan; ?></td>
                <td><?= date('d/m/Y H:i', strtotime($p->tanggal_pesan)); ?></td>
                <td>
                  <?php
                    $pid = (int)$p->id_pesanan;
                    $items = $detail_map[$pid] ?? [];
                    if (empty($items)) {
                      echo '-';
                    } else {
                      foreach ($items as $it) {
                        echo htmlspecialchars($it['nama_menu']).' x'.$it['jumlah'].'<br>';
                      }
                    }
                  ?>
                </td>

                <td>Rp<?= number_format($p->total_harga,0,',','.'); ?></td>
               <?php
  $st = strtolower(trim((string)$p->status_pesanan));
  $st = preg_replace('/\s+/', '_', $st); // jaga-jaga kalau ada spasi
?>
<td>
  <span class="rw-badge rw-badge--<?= htmlspecialchars($st); ?>">
    <?= htmlspecialchars($p->status_pesanan); ?>
  </span>
</td>

                <td>
  <?php if ($p->status_pesanan === 'siap_ambil'): ?>
    <a class="rw-action"
       href="<?= site_url('kasir/riwayat/selesai/'.$p->id_pesanan); ?>"
       onclick="return confirm('Yakin pesanan ini sudah diambil dan SELESAI?');">
      selesai
    </a>
  <?php else: ?>
    <span class="rw-action rw-action--disabled">selesai</span>
  <?php endif; ?>
</td>

              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
      

    </div>
    <div class="pagination-kasir-wrapper">
  <?= $pagination ?? ''; ?>
</div>

  </div>
  </div>
  
</main>

<div id="toastNotif" style="display:none; position:fixed; right:20px; top:20px; z-index:9999;
  background:#111; color:#fff; padding:12px 14px; border-radius:10px; box-shadow:0 8px 20px rgba(0,0,0,.25);
  font-size:14px;">
  <span id="toastText">Notifikasi</span>
</div>

<script>
(function () {
  let unlocked = false;

  function beep() {
    try {
      const AC = window.AudioContext || window.webkitAudioContext;
      const ctx = new AC();
      const o = ctx.createOscillator();
      const g = ctx.createGain();
      o.type = "sine";
      o.frequency.value = 660;
      g.gain.value = 0.12;
      o.connect(g); g.connect(ctx.destination);
      o.start();
      setTimeout(() => { o.stop(); ctx.close(); }, 180);
    } catch(e) {}
  }

  function unlockOnce() {
    if (unlocked) return;
    unlocked = true;
    beep();
    window.removeEventListener("click", unlockOnce);
    window.removeEventListener("keydown", unlockOnce);
  }
  window.addEventListener("click", unlockOnce);
  window.addEventListener("keydown", unlockOnce);

  function showToast(msg, ms) {
    const el = document.getElementById("toastNotif");
    const tx = document.getElementById("toastText");
    if (!el || !tx) return;
    tx.textContent = msg;
    el.style.display = "block";
    clearTimeout(showToast._t);
    showToast._t = setTimeout(() => { el.style.display = "none"; }, ms);
  }

  async function checkKasirReady() {
    try {
      const res = await fetch("<?= site_url('kasir/notif/count_ready'); ?>", {cache:"no-store"});
      const data = await res.json();
      const n = parseInt(data.count || 0, 10);
      if (n > 0) {
        beep();
        showToast("Ada pesanan siap ambil: " + n, 2000);
      }
    } catch(e) {}
  }

  setInterval(checkKasirReady, 3000);
})();
</script>
    
  