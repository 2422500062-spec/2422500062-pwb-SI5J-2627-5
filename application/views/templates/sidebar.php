<!-- Main Sidebar Container -->
  <aside class="main-sidebar sidebar-dark-primary elevation-4">
    <!-- Brand Logo -->
    <a href="index3.html" class="brand-link">
      <img src="<?= base_url('assets/') ?>dist/img/AdminLTELogo.png" alt="AdminLTE Logo" class="brand-image img-circle elevation-3" style="opacity: .8">
      <span class="brand-text font-weight-light">SIP</span>
    </a>

    <!-- Sidebar -->
    <div class="sidebar">
      <!-- Sidebar user panel (optional) -->
      <div class="user-panel mt-3 pb-3 mb-3 d-flex">
        <div class="image">
          <img src="<?= base_url('assets/') ?>dist/img/WhatsApp Image 2026-05-06 at 22.09.16.jpeg" class="img-circle elevation-2" alt="User Image">
        </div>
        <div class="info">
          <a href="#" class="d-block">Afdila mirzan</a>
        </div>
      </div>

      <!-- SidebarSearch Form -->
      <div class="form-inline">
        <div class="input-group" data-widget="sidebar-search">
          <input class="form-control form-control-sidebar" type="search" placeholder="Search" aria-label="Search">
          <div class="input-group-append">
            <button class="btn btn-sidebar">
              <i class="fas fa-search fa-fw"></i>
            </button>
          </div>
        </div>
      </div>

      <!-- Sidebar Menu -->
      <nav class="mt-2">
        <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
          <!-- Add icons to the links using the .nav-icon class
               with font-awesome or any other icon font library -->
          <li class="nav-item menu-open">
            <a href="#" class="nav-link active">
              <i class="nav-icon fas fa-tachometer-alt"></i>
              <p>
                Master
                <i class="right fas fa-angle-left"></i>
              </p>
            </a>
          
<?php
$current_menu = $this->uri->segment(2);
$master_active = in_array($current_menu, ['kategori', 'produk']);
?>



    <ul class="nav nav-treeview">

        <li class="nav-item">
            <a href="<?= base_url('admin/kategori') ?>"
               class="nav-link <?= $current_menu === 'kategori' ? 'active' : '' ?>">
                <i class="far fa-circle nav-icon"></i>
                <p>Kategori</p>
            </a>
        </li>

        <li class="nav-item">
            <a href="<?= base_url('admin/produk') ?>"
               class="nav-link <?= $current_menu === 'produk' ? 'active' : '' ?>">
                <i class="far fa-circle nav-icon"></i>
                <p>Produk</p>
            </a>
        </li>

    </ul>
</li>

          <li class="nav-item">
            <a href="<?= base_url('admin/logout') ?>" class="nav-link">
              <i class="nav-icon fas fa-th"></i>
              <p>
                logout
              </p>
            </a>
          </li>
        </ul>
      </nav>
      <!-- /.sidebar-menu -->
    </div>
    <!-- /.sidebar -->
  </aside>