<aside class="sidebar" id="sidebar">
            <div class="sidebar-header" id="sidebar-toggle">
                <img src="<?= base_url('assets/picture/logodarasulung.jpeg'); ?>" class="logo">
                <div class="admin-info">
                    <p class="role">Kasir</p>
                    <p class="name">Dara Sulung</p>
                </div>
            </div>
            
            <nav class="sidebar-nav">
                <ul>
                    <li>
                        <a href="<?php echo base_url('kasir/dashboardkasir') ?>">
                            <i class="icon bi bi-ui-checks-grid"></i>
                            <span>Dashboard</span>
                        </a>
                    </li>
                    <li>
                        <a href="<?php echo base_url("kasir/pemesanan") ?>">
                            <i class="icon bi bi-pencil-square"></i>
                            <span>Pemesanan</span>
                        </a>
                    </li>

                    <li>
                        <a href="<?php  echo base_url("kasir/riwayat") ?>">
                            <i class="icon bi bi-graph-up"></i>
                            <span>Riwayat</span>
                        </a>
                    </li>

                </ul>
            </nav>
        </aside>