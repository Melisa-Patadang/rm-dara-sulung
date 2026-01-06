<div class="order-wrapper">
  <div class="order-card">
    <h2 class="order-title">Pemesanan</h2>

    <?php if($this->session->flashdata('pesan_gagal')): ?>
      <div class="alert alert-danger"><?= $this->session->flashdata('pesan_gagal'); ?></div>
    <?php endif; ?>

    <?php if($this->session->flashdata('pesan_berhasil')): ?>
      <div class="alert alert-success"><?= $this->session->flashdata('pesan_berhasil'); ?></div>
    <?php endif; ?>

    <form action="<?= site_url('kasir/pemesanan/store'); ?>" method="post" class="order-form" id="orderForm">
      <div id="itemsContainer">
        <div class="item-row">
          <select name="id_menu[]" class="order-input menu-select" required>
            <option value="">-- Pilih Menu --</option>
            <?php foreach($menus as $m): ?>
              <option value="<?= (int)$m->id_menu; ?>" data-harga="<?= (float)$m->harga; ?>">
                <?= htmlspecialchars($m->nama_menu); ?> - Rp<?= number_format($m->harga,0,',','.'); ?>
              </option>
            <?php endforeach; ?>
          </select>

          <input type="number" name="jumlah[]" class="order-input qty-input" min="1" value="1" required>

          <button type="button" class="btn-remove" title="Hapus item">×</button>
        </div>

      </div>

      <div class="row-actions">
        <button type="button" class="btn-add" id="btnAddItem">+ Tambah Item</button>
      </div>

      <!-- TOTAL -->
      <div class="total-box">
        <div class="total-label">Total</div>
        <div class="total-value" id="totalValue">Rp0</div>
      </div>

      <button type="submit" class="order-btn">SIMPAN PESANAN</button>
    </form>
  </div>
</div>

<script>
(function(){
  const itemsContainer = document.getElementById('itemsContainer');
  const btnAddItem = document.getElementById('btnAddItem');
  const totalValue = document.getElementById('totalValue');

  function formatRupiah(num){
    num = Math.round(num || 0);
    return 'Rp' + num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
  }

  function hitungTotal(){
    let total = 0;
    const rows = itemsContainer.querySelectorAll('.item-row');
    rows.forEach(row => {
      const select = row.querySelector('.menu-select');
      const qtyInp = row.querySelector('.qty-input');

      const opt = select.options[select.selectedIndex];
      const harga = opt && opt.dataset && opt.dataset.harga ? parseFloat(opt.dataset.harga) : 0;
      const qty = qtyInp && qtyInp.value ? parseInt(qtyInp.value, 10) : 0;

      if (harga > 0 && qty > 0) total += harga * qty;
    });
    totalValue.textContent = formatRupiah(total);
  }

  function bindRowEvents(row){
    row.querySelector('.menu-select').addEventListener('change', hitungTotal);
    row.querySelector('.qty-input').addEventListener('input', hitungTotal);

    row.querySelector('.btn-remove').addEventListener('click', function(){
      const allRows = itemsContainer.querySelectorAll('.item-row');
      if (allRows.length <= 1) {
        row.querySelector('.menu-select').value = '';
        row.querySelector('.qty-input').value = 1;
      } else {
        row.remove();
      }
      hitungTotal();
    });
  }
  bindRowEvents(itemsContainer.querySelector('.item-row'));
  hitungTotal();

  btnAddItem.addEventListener('click', function(){
    const firstRow = itemsContainer.querySelector('.item-row');
    const newRow = firstRow.cloneNode(true);
    newRow.querySelector('.menu-select').value = '';
    newRow.querySelector('.qty-input').value = 1;

    itemsContainer.appendChild(newRow);
    bindRowEvents(newRow);
    hitungTotal();
  });
})();
</script>
