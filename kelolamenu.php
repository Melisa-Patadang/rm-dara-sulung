<main class="content">
    <h2>Pilihan menu</h2>
    <a href="<?= base_url('kelolamenu/tambahmenu'); ?>" class="btn-csv">Tambah</a>

<table class="tabel-penjualan">
    <thead>
        <tr>
            <th>No</th>
            <th>Nama Menu</th>
            <th>Harga</th>
            <th>Kategori</th>
            <th>Gambar</th>
            <th>Action</th>
        </tr>
    </thead>

    <tbody>
        <?php 
        $no = $start + 1;  
        foreach($menu as $p): ?>
        <tr>
            <td><?= $no++; ?></td>
            <td><?= $p['nama_menu']; ?></td>
            <td><?= number_format($p['harga'],0,',','.'); ?></td>
            <td><?= $p['kategori']; ?></td>
            <td>
                <img src="<?= base_url('uploads/menu/'.$p['gambar']); ?>" style="width:60px; height:60px; object-fit:cover; border-radius:5px;">
            </td>
            <td>
                <a href="<?= base_url('kelolamenu/edit/'.$p['id_menu']); ?>" class="btn-edit">Edit</a>
                <a href="<?= base_url('kelolamenu/delete/'.$p['id_menu']); ?>" class="btn-del" onclick="return confirm('Hapus menu ini?');">Delete</a>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<div id="pagination-final-target">
 <?php echo $pagination; ?>
</div>


</main>
