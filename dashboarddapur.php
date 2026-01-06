<main class="dapur-content">
  <h2 class="dapur-title">Dashboard dapur</h2>
  
<div class="dapur-page">
  <div class="dapur-kpi-row">
    <div class="dapur-kpi-card">
      <div class="dapur-kpi-label">Pesanan diproses</div>
      <div class="dapur-kpi-value"><?= (int)($count_diproses ?? 0); ?></div>
      <div class="dapur-kpi-sub">Antrian aktif sekarang</div>
    </div>

    <div class="dapur-kpi-card">
      <div class="dapur-kpi-label">Pesanan siap ambil</div>
      <div class="dapur-kpi-value"><?= (int)($count_siap_ambil ?? 0); ?></div>
      <div class="dapur-kpi-sub">Sudah siap diserahkan</div>
    </div>
  </div>
</div>

  

  <div class="kasir-table-wrap">
    <table class="kasir-table">
      <thead>
        <tr>
          <th>No</th>
          <th>ID Pesanan</th>
          <th>Item</th>
          <th>Jumlah</th>
          <th>Status</th>
          <th>Aksi</th>
        </tr>
      </thead>

      <tbody>
        <?php if (!empty($pesanan)): ?>
          <?php $no = 1; ?>
          <?php foreach ($pesanan as $p): ?>
            <tr>
              <td><?= $no++; ?></td>

              <td><?= (int)$p['id_pesanan']; ?></td>

              <td style="text-align:left;">
                <?php if (!empty($p['items'])): ?>
                  <?php foreach ($p['items'] as $it): ?>
                    <div><?= htmlspecialchars($it['nama_menu']); ?></div>
                  <?php endforeach; ?>
                <?php else: ?>
                  <div>-</div>
                <?php endif; ?>
              </td>

              <td>
                <?php if (!empty($p['items'])): ?>
                  <?php foreach ($p['items'] as $it): ?>
                    <div><?= (int)$it['jumlah']; ?></div>
                  <?php endforeach; ?>
                <?php else: ?>
                  <div>-</div>
                <?php endif; ?>
              </td>

              <td>
                <span class="dapur-badge dapur-badge--<?= htmlspecialchars($p['status']); ?>">
                  <?= htmlspecialchars($p['status']); ?>
                </span>
              </td>

              <td>
                <?php if ($p['status'] === 'diproses'): ?>
                  <a class="dapur-btn dapur-btn--confirm"
                     href="<?= site_url('dapur/dashboarddapur/siap_ambil/'.(int)$p['id_pesanan']); ?>"
                     onclick="return confirm('Ubah status pesanan jadi SIAP AMBIL?');">
                    siap ambil
                  </a>
                <?php else: ?>
                  <span class="dapur-btn dapur-btn--disabled">siap ambil</span>
                <?php endif; ?>

                <?php if ($p['status'] === 'siap_ambil'): ?>
                  <a class="dapur-btn dapur-btn--selesai"
                     href="<?= site_url('dapur/dashboarddapur/selesai/'.(int)$p['id_pesanan']); ?>"
                     onclick="return confirm('Hapus dari tabel dapur?');">
                    selesai
                  </a>
                <?php else: ?>
                  <span class="dapur-btn dapur-btn--disabled">selesai</span>
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php else: ?>
          <tr>
            <td colspan="6" style="text-align:center; padding:18px;">
              Belum ada pesanan.
            </td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</main>

<div id="toastNotif" style="display:none; position:fixed; right:20px; top:20px; z-index:9999;
  background:#111; color:#fff; padding:12px 14px; border-radius:10px; box-shadow:0 8px 20px rgba(0,0,0,.25);
  font-size:14px;">
  <span id="toastText">Notifikasi</span>
</div>

<script>
(function () {
  let ctx = null;
  let unlocked = false;

  function getCtx() {
    const AC = window.AudioContext || window.webkitAudioContext;
    if (!AC) return null;
    if (!ctx) ctx = new AC();
    return ctx;
  }

  async function unlockAudio() {
    const c = getCtx();
    if (!c) return false;
    try {
      if (c.state === "suspended") await c.resume();
      unlocked = true;
      beep();
      return true;
    } catch (e) {
      return false;
    }
  }

  function beep() {
    const c = getCtx();
    if (!c) return;

    try {
      const o = c.createOscillator();
      const g = c.createGain();

      o.type = "square";
      g.gain.value = 0.2;

      o.connect(g);
      g.connect(c.destination);

      o.frequency.setValueAtTime(900, c.currentTime);
      o.start();
      setTimeout(() => { o.stop(); }, 300);
    } catch (e) {}
  }

  function showToast(msg, ms) {
    const el = document.getElementById("toastNotif");
    const tx = document.getElementById("toastText");
    if (!el || !tx) return;

    tx.textContent = msg;
    el.style.display = "block";

    clearTimeout(showToast._t);
    showToast._t = setTimeout(() => { el.style.display = "none"; }, ms);
  }

  // tombol manual (paling aman)
  const btn = document.getElementById("btnSoundOn");
  if (btn) {
    btn.addEventListener("click", async () => {
      const ok = await unlockAudio();
      showToast(ok ? "Suara aktif" : "Gagal aktifkan suara", 1500);
    });
  }

  // fallback: kalau user klik/ketik di mana pun
  async function unlockOnce() {
    if (unlocked) return;
    await unlockAudio();
    window.removeEventListener("click", unlockOnce);
    window.removeEventListener("keydown", unlockOnce);
  }
  window.addEventListener("click", unlockOnce);
  window.addEventListener("keydown", unlockOnce);

  async function checkDapur() {
    try {
      const res = await fetch("<?= site_url('dapur/notif/count'); ?>", { cache:"no-store" });
      const data = await res.json();
      const n = parseInt(data.count || 0, 10);

      if (n > 0) {
        showToast("Pesanan baru masuk: " + n, 3000);
        if (unlocked) beep();
      }
    } catch(e) {}
  }

  setInterval(checkDapur, 3000);
})();
</script>
