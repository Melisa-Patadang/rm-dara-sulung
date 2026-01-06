<div style="width:1000px; margin: 40px auto; background: #e0e0e0; padding: 30px; border-radius: 5px; height:600px     ;">
    <h2 style="text-align:center; margin-top:0;">
        <?= isset($menu) ? 'Edit Menu' : 'Tambah Menu'; ?>
    </h2>

    <form action="<?= isset($menu) 
        ? base_url('kelolamenu/update') 
        : base_url('kelolamenu/store'); ?>" 
        method="post" enctype="multipart/form-data">

        <?php if(isset($menu)): ?>
            <input type="hidden" name="id_menu" value="<?= $menu['id_menu']; ?>">
        <?php endif; ?>

        <label>Nama menu</label>
        <input type="text" name="nama_menu" required
            value="<?= isset($menu) ? $menu['nama_menu'] : ''; ?>"
            style="width:100%; padding:10px; margin-bottom:15px; border:1px solid #ccc; border-radius:3px;">

        <label>Harga</label>
        <input type="number" name="harga" required
            value="<?= isset($menu) ? $menu['harga'] : ''; ?>"
            style="width:100%; padding:10px; margin-bottom:15px; border:1px solid #ccc; border-radius:3px;">

        <label>Kategori</label>
        <input type="text" name="kategori" required
            value="<?= isset($menu) ? $menu['kategori'] : ''; ?>"
            style="width:100%; padding:10px; margin-bottom:15px; border:1px solid #ccc; border-radius:3px;">

        <label>Gambar</label>
        <input type="file" name="gambar"
               style="width:100%; padding:10px; margin-bottom:20px; border:1px solid #ccc; border-radius:3px;">

        <?php if(isset($menu) && $menu['gambar']): ?>
            <img src="<?= base_url('uploads/menu/'.$menu['gambar']); ?>" 
                 style="width:120px; margin-top:10px; border-radius:5px;">
        <?php endif; ?>

        <button type="submit"
            style="background:#d32f2f; color:white; border:none; padding:10px 20px; float:right; cursor:pointer;">
            <?= isset($menu) ? 'Update' : 'Tambah'; ?>
        </button>

        <div style="clear:both;"></div>
    </form>
</div>
