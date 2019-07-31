<!DOCTYPE html>
<html lang="{{ config('app.locale') }}">

<head>
  @yield('header')
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>{{ config('app.name', 'Toko Insan') }}</title>
  <meta name="csrf-token" content="{{csrf_token()}}">
  <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
 
  
  <link rel="stylesheet" href="{{ asset('public/adminLTE/bootstrap/css/bootstrap.min.css') }}">
  <link rel="stylesheet" href="{{ asset('public/font-awesome/css/font-awesome.min.css') }}">
  <link rel="stylesheet" href="{{ asset('public/adminLTE/dist/css/AdminLTE.min.css') }}">
  <link rel="stylesheet" href="{{ asset('public/adminLTE/dist/css/skins/skin-blue.min.css') }}">
  <link rel="stylesheet" href="{{ asset('public/adminLTE/plugins/datatables/dataTables.bootstrap.css') }}">
  <link rel="stylesheet" href="{{ asset('public/adminLTE/plugins/datepicker/datepicker3.css') }}">
  
</head>
<body class="hold-transition skin-blue sidebar-mini">
<div class="wrapper">

   <!-- Header -->
  <header class="main-header">

    <a href="#" class="logo">
      <span class="logo-mini"><b>TI</b></span>
     <span class="logo-lg"><b>Toko</b>Insan</span>
    </a>


    <nav class="navbar navbar-static-top" role="navigation">
      <a href="#" class="sidebar-toggle" data-toggle="offcanvas" role="button">
        <span class="sr-only">Toggle navigation</span>
      </a>
      
      <div class="navbar-custom-menu">
        <ul class="nav navbar-nav">
         
          <li class="dropdown user user-menu">
            <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                <img src="{{ asset('public/images/'.Auth::user()->foto) }}" class="user-image" alt="User Image">
                <span class="hidden-xs">{{ Auth::user()->name }}</span>
            </a>
            <ul class="dropdown-menu">
                <li class="user-header">
                    <img src="{{ asset('public/images/'.Auth::user()->foto) }}" class="img-circle" alt="User Image">

                    <p>
                      {{ Auth::user()->name }}
                    </p>
                </li>
                <li class="user-footer">
                    <div class="pull-left">
                        <a class="btn btn-default btn-flat" href="{{ route('user.profil') }}">Edit Profil</a>
                    </div>
                    <div class="pull-right">
                        <a class="btn btn-default btn-flat" href="{{ url('/logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();"> Logout
                        </a>
                        <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                            {{ csrf_field() }}
                        </form>
                    </div>
                </li>

            </ul>
          </li>
        </ul>
      </div>
    </nav>
  </header>
  <!-- End Header -->


  <!-- Sidebar -->
  <aside class="main-sidebar">

    <section class="sidebar">
      <ul class="sidebar-menu">
        <li class="header">MENU NAVIGASI</li>

        <li><a href="{{ route('home') }}"><i class="fa fa-dashboard"></i> <span>Dashboard</span></a></li>

      @if( Auth::user()->level == 1 )
        <li><a href="{{ route('member.index') }}"><i class="fa fa-credit-card"></i> <span>Member</span></a></li>
        <li><a href="{{ route('supplier.index') }}"><i class="fa fa-truck"></i> <span>Supplier</span></a></li>
        <li><a href="{{ route('penjualan.index') }}"><i class="fa fa-upload"></i> <span>Penjualan</span></a></li>
        <li><a href="{{ route('laporan.index') }}"><i class="fa fa-file-pdf-o"></i> <span>Laporan</span></a></li>
        <li><a href="{{ route('setting.index') }}"><i class="fa fa-gears"></i> <span>Setting</span></a></li>
        <li><a href="{{ route('user.index') }}""><i class="fa fa-user"></i> <span>User</span></a></li>       
        <!-- menu approve po  -->
        <li><a href="{{ route('pembelian.admin') }}"><i class="fa fa-file-pdf-o"></i> <span>Pembelian</span></a></li>
        <!-- menu jurnal umum -->
        @elseif( Auth::user()->level == 2 )
      <li><a href="{{ route('transaksi.menu') }}"><i class="fa fa-shopping-cart"></i> <span>Pilih Transaksi</span></a></li>
      <li><a href="{{ route('transaksi.new') }}"><i class="fa fa-cart-plus"></i> <span>Transaksi Baru</span></a></li>
      <li><a href="{{ route('kasa.index') }}"><i class="fa fa-cart-plus"></i> <span>Cash Count</span></a></li>
      <li><a href="{{ route('pengeluaran.index') }}"><i class="fa fa-money"></i> <span>Pengeluaran</span></a></li>
      <li><a href="{{ route('musawamahdetail.index') }}"><i class="fa fa-money"></i> <span>Setor Angsuran</span></a></li>
      <li><a href=""><i class="fa fa-cart-plus"></i> <span>Laporan Penjualan</span></a></li>
      <!-- menu jurnal umum -->
      <li><a href="{{ route('jurnal_umum_admin.index') }}"><i class="fa fa-file-pdf-o"></i> <span>Jurnal</span></a></li>
      
        @elseif( Auth::user()->level == 3 )
        <li><a href="{{ route('kategori.index') }}"><i class="fa fa-cube"></i> <span>Kategori</span></a></li>
        <li><a href="{{ route('supplier.index') }}"><i class="fa fa-truck"></i> <span>Supplier</span></a></li>
        <li><a href="{{ route('pembelian.index') }}"><i class="fa fa-download"></i> <span>PO</span></a></li>
        <!-- menu jurnal umum -->
        <li><a href="{{ route('jurnal_umum_po.index') }}"><i class="fa fa-file-pdf-o"></i><span>Jurnal</span></a></li>
              
        @elseif( Auth::user()->level == 4 )
        <li><a href="{{ route('kirim_barang.index') }}"><i class="fa fa-cubes"></i> <span>Surat Jalan</span></a></li>
        <!-- menu baru di gudang -->
        <li class="treeview">
          <a href="#">
            <i class="fa fa-cubes"></i>
            <span>Terima Barang</span>
            <span class="pull-right-container">
              <i class="fa fa-angle-left pull-right"></i>
            </span>
          </a>
          <ul class="treeview-menu">
          <li><a href="{{ route('terima.index') }}"><i class="fa fa-cubes"></i> <span>Terima Barang PO</span></a></li>
          <li><a href="{{ route('retur.index') }}"><i class="fa fa-cubes"></i> <span>Terima Barang Retur</span></a></li>            
          </ul>
        <!-- tambah menu stock barang -->
          <li><a href="{{ route('stock.index') }}"><i class="fa fa-cubes"></i> <span>Sotck Gudang</span></a></li>
        @elseif( Auth::user()->level == 5 )
        <li><a href="{{ route('kirim_barang_toko.index') }}"><i class="fa fa-cubes"></i> <span>Surat Jalan</span></a></li>
        <li><a href="{{ route('terimaToko.index') }}"><i class="fa fa-cubes"></i> <span>Terima Barang</span></a></li> 
                      
        @else( Auth::user()->level == 6 )
        <li><a href="{{ route('produk.index') }}"><i class="fa fa-cubes"></i> <span>Produk</span></a></li>
        <li><a href=""><i class="fa fa-cart-plus"></i> <span>Laporan Penjualan</span></a></li>
        <!-- menu jurnal umum -->
        <li><a href="{{ route('jurnal_umum_kp.index') }}"><i class="fa fa-file-pdf-o"></i> <span>Jurnal</span></a></li>
        @endif
      </ul>
    </section>
  </aside>
  <!-- End Sidebar -->

  <!-- Content  -->
  <div class="content-wrapper">
  <section class="content-header">
      <h1>
        @yield('title')
      </h1>
      <ol class="breadcrumb">
        @section('breadcrumb')
        <li><a href="#"><i class="fa fa-home"></i>Home</a></li>
        @show
      </ol>
    </section>

    <section class="content">
        @yield('content')
    </section>
  </div>
  <!-- End Content -->

  <!-- Footer -->
  <footer class="main-footer">
  
    <div class="pull-right hidden-xs">
      Aplikasi POS 
    </div>
    <strong>Copyright &copy; 2019 <a href="#">Toko Insan</a>.</strong> All rights reserved.
  </footer>
  <!-- End Footer -->
 
<script src="{{ asset('public/adminLTE/plugins/jQuery/jquery-2.2.3.min.js') }}"></script>
<script src="{{ asset('public/adminLTE/bootstrap/js/bootstrap.min.js') }}"></script>
<script src="{{ asset('public/adminLTE/dist/js/app.min.js') }}"></script>

<script src="{{ asset('public/adminLTE/plugins/chartjs/Chart.min.js') }}"></script>
<script src="{{ asset('public/adminLTE/plugins/datatables/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('public/adminLTE/plugins/datatables/dataTables.bootstrap.min.js') }}"></script>
<script src="{{ asset('public/adminLTE/plugins/datepicker/bootstrap-datepicker.js') }}"></script>
<script src="{{ asset('public/js/validator.min.js') }}"></script>
<script src="//cdnjs.cloudflare.com/ajax/libs/x-editable/1.5.0/bootstrap3-editable/js/bootstrap-editable.min.js"></script>
 
<script>
$(document).ready(function(){
  $('#tables').DataTable()
});
</script>
@yield('script')

</body>
</html>