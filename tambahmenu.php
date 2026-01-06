<div style="width:1000px; margin: 40px auto; background: #e0e0e0; padding: 30px; border-radius: 5px; height:500px     ;">
    <h2 style="text-align:center; margin-top:0;">Tambah menu</h2>

    <form action="<?= base_url('kelolamenu/store'); ?>" method="post" enctype="multipart/form-data">

        <label>Nama menu</label>
        <input type="text" name="nama_menu" required
               style="width:100%; padding:10px; margin-bottom:15px; border:1px solid #ccc; border-radius:3px;">

        <label>Harga</label>
        <input type="number" name="harga" required
               style="width:100%; padding:10px; margin-bottom:15px; border:1px solid #ccc; border-radius:3px;">

        <label>Kategori</label>
        <input type="text" name="kategori" required
               style="width:100%; padding:10px; margin-bottom:15px; border:1px solid #ccc; border-radius:3px;">

        <label>Gambar</label>
        <input type="file" name="gambar"
               style="width:100%; padding:10px; margin-bottom:20px; border:1px solid #ccc; border-radius:3px;">

        <button type="submit"
                style="background:#d32f2f; color:white; border:none; padding:10px 20px; float:right; cursor:pointer;">
            Tambah
        </button>

        <div style="clear:both;"></div>
    </form>
</div>
