<?php 
$CI =& get_instance(); 
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Dara Sulung</title>

    <link rel="stylesheet" type="text/css" href="<?php echo base_url('assets/css/loginadmin.css'); ?>">
    <link href="https://fonts.googleapis.com/css?family=Montserrat" rel="stylesheet">
</head>

<body>
    <div class="container">
        <div class="wrapper">

            <img src="<?= base_url('assets/picture/logodarasulung.jpeg'); ?>" class="logo" style="width:150px; height:auto;">
<div id="logoutToast" style="display:none; padding:10px 12px; border-radius:10px; background:#e8fff0; border:1px solid #b7f5cc; margin-bottom:12px;">
  <span id="logoutToastText"></span>        
</div>

<script>
  (function () {
    const msg = sessionStorage.getItem('logout_success');
    if (!msg) return;

    sessionStorage.removeItem('logout_success');

    const box = document.getElementById('logoutToast');
    const text = document.getElementById('logoutToastText');
    if (!box || !text) return;

    text.textContent = msg;
    box.style.display = 'block';

    // auto hilang (opsional)
    setTimeout(() => box.style.display = 'none', 3000);
  })();
</script>

            <form class="login-form" method="post" action="<?= base_url('login/proses'); ?>">

                <label>Username</lsabel>
                <input type="text" name="username">

                <label>Password</label>
                <input type="password" name="password">

                <label>Role</label>
                <select name="role">
                    <option value="">-- Pilih Role --</option>
                    <?php foreach ($roles as $role): ?>
                        <option value="<?= $role ?>"><?= ucfirst($role) ?></option>
                    <?php endforeach; ?>
                </select>
                <button class="login-button" type="submit">LOGIN</button>
            </form>
        </div>
    </div>

    <script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>

    <?php if ($CI->session->flashdata("pesan_sukses")): ?>
    <script>
        swal("Sukses!", "<?= $CI->session->flashdata('pesan_sukses'); ?>", "success");
    </script>
    <?php endif; ?>

    <?php if ($CI->session->flashdata("pesan_gagal")): ?>
    <script>
        swal("Gagal!", "<?= $CI->session->flashdata('pesan_gagal'); ?>", "error");
    </script>
    <?php endif; ?>

</body>
</html>
