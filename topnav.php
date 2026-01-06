<div class="main-content-area" id="main-content">

  <header class="top-navbar">
    <div class="user-controls">
      <div class="notif-wrap" id="notifWrap">
        <i class="icon bi bi-bell-fill" id="btnNotif"></i>
          <span class="notif-badge" id="notifBadge" style="display:none;">0</span>

      <div class="notif-dropdown" id="notifDropdown" style="display:none;">
        <div class="notif-head">Notifikasi</div>
        <div class="notif-list" id="notifList">
          <div class="notif-empty">Belum ada notifikasi</div>
        </div>
      </div>
    </div>
      <i class="icon bi bi-person-circle"></i>
      <form id="logoutForm" action="<?= site_url('logout/logout') ?>" method="post" style="margin:0;">
        <button type="submit" class="logout-inline">Logout</button>
      </form>

    </div>
  </header>