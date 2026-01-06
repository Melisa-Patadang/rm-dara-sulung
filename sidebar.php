<aside class="sidebar" id="sidebar">
            <div class="sidebar-header" id="sidebar-toggle">
                <img src="<?= base_url('assets/picture/logodarasulung.jpeg'); ?>" class="logo">
                <div class="admin-info">
                    <p class="role">Admin</p>
                    <p class="name">Dara Sulung</p>
                </div>
            </div>
            
            <nav class="sidebar-nav">
                <ul>
                    <li>
                        <a href="<?php echo base_url('Dashboardadmin') ?>">
                            <i class="icon bi bi-ui-checks-grid"></i>
                            <span>Dashboard</span>
                        </a>
                    </li>
                    <li>
                        <a href="<?php echo base_url("kelolamenu") ?>">
                            <i class="icon bi bi-pencil-square"></i>
                            <span>Kelola Menu</span>
                        </a>
                    </li>

                    <li>
                        <a href="<?php  echo base_url("laporanpenjualan") ?>">
                            <i class="icon bi bi-graph-up"></i>
                            <span>Laporan</span>
                        </a>
                    </li>

                </ul>
            </nav>
        </aside>