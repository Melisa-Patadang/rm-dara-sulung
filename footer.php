</div> 
</div> 

<script>
  document.addEventListener('DOMContentLoaded', function() {
    const sidebar = document.getElementById('sidebar');
    const toggleButton = document.getElementById('sidebar-toggle'); 
    
    if (sidebar && toggleButton) {
      toggleButton.addEventListener('click', function() {
        sidebar.classList.toggle('collapsed');
      });
    }
  });
</script>

<script>
(function () {
  const form = document.getElementById('logoutForm');
  if (!form) return;

  form.addEventListener('submit', function (e) {
    const yakin = confirm('Yakin ingin logout?');

    if (!yakin) {
      e.preventDefault();
      return;
    }
    sessionStorage.setItem('logout_success', 'Anda berhasil logout.');
  });
})();
</script>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>

<script>
$(function () {
  const COUNT_URL = "<?= site_url('notif/count'); ?>";
  const LIST_URL  = "<?= site_url('notif/list'); ?>";
  const SEEN_URL  = "<?= site_url('notif/seen'); ?>";

  const $badge = $("#notifBadge");
  const $drop  = $("#notifDropdown");
  const $list  = $("#notifList");

  let badgeTimer = null;
  let seenTimer  = null;

  function escapeHtml(s){
    return String(s).replace(/[&<>"']/g, m => ({
      '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'
    }[m]));
  }

  function markSeenAndHide(){
    clearTimeout(seenTimer);
    seenTimer = setTimeout(function(){
      $.get(SEEN_URL, function(){
        $badge.hide();
      });
    }, 0);
  }

  function loadCount(){
    $.getJSON(COUNT_URL, function(res){
      const c = parseInt(res.count || 0, 10);

      if (c > 0) {
        $badge.text(c > 99 ? "99+" : c).show();

        clearTimeout(badgeTimer);
        badgeTimer = setTimeout(function(){
       
          markSeenAndHide();
        }, 2000);

      } else {
        $badge.hide();
        clearTimeout(badgeTimer);
      }
    });
  }

  function loadList(callback){
    $.getJSON(LIST_URL, function(res){
      const items = (res.items || []);
      if(items.length === 0){
        $list.html('<div class="notif-empty">Tidak ada transaksi</div>');
        if(callback) callback(false);
        return;
      }

      let html = `<div class="notif-header">Transaksi masuk (${items.length})</div>`;
      let hasNew = false;

      items.forEach(function(it){
        if(it.is_new) hasNew = true;
        html += `
          <div class="notif-item" style="${it.is_new ? 'font-weight:700;' : ''}">
            <div class="t">${escapeHtml(it.title)}</div>
            <div class="d">${escapeHtml(it.desc)}</div>
            <div class="d" style="margin-top:6px;">${escapeHtml(it.time)}</div>
          </div>
        `;
      });

      $list.html(html);
      if(callback) callback(hasNew);
    });
  }

  $("#btnNotif").on("click", function(e){
    e.stopPropagation();

    const isOpen = $drop.is(":visible");
    if(isOpen){
      $drop.hide();
      return;
    }

    $drop.show();

    loadList(function(hasNew){
      if(hasNew){
        markSeenAndHide();
      }
    });
  });

  $(document).on("click", function(){
    $drop.hide();
  });

  loadCount();
  setInterval(loadCount, 3000);
});
</script>

</body>
</html>
    