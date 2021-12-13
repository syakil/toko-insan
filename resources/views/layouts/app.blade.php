<!DOCTYPE html>
<html lang="{{ config('app.locale') }}">

<head>
  @yield('header')
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>{{ config('app.name', 'Toko Insan') }}</title>
  <meta name="csrf-token" content="{{csrf_token()}}">
  <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
 
  <link rel="shortcut icon" href="#" />
  <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.8.3/jquery.min.js"></script>
  <link rel="stylesheet" href="{{ asset('public/adminLTE/bootstrap/css/bootstrap.min.css') }}">
  <link rel="stylesheet" href="{{ asset('public/font-awesome/css/font-awesome.min.css') }}">
  <link rel="stylesheet" href="{{ asset('public/adminLTE/dist/css/AdminLTE.min.css') }}">
  <link rel="stylesheet" href="{{ asset('public/adminLTE/dist/css/skins/skin-blue.min.css') }}">
  <link rel="stylesheet" href="{{ asset('public/adminLTE/plugins/datatables/dataTables.bootstrap.css') }}">
  <link rel="stylesheet" href="{{ asset('public/adminLTE/plugins/datepicker/datepicker3.css') }}">
  <script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
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
        <li><a href="{{ route('restruktur.index') }}"><i class="fa fa-users"></i> <span>Restrukturisasi</span></a></li> 

        <li><a href="{{ route('reset_pin.index') }}"><i class="fa fa-credit-card"></i> <span>Reset PIN</span></a></li>   
        <li class="treeview">
          <a href="#">
            <i class="fa fa-shopping-cart"></i>
            <span>Penjualan</span>
            <span class="pull-right-container">
              <i class="fa fa-angle-left pull-right"></i>
            </span>
          </a>
          <ul class="treeview-menu">
            <li><a href="{{ route('penjualan.index') }}"><i class="fa fa-tag"></i> <span>Struk Penjualan</span></a></li>
            <li><a href="{{ route('penjualan.detail') }}"><i class="fa fa-tags"></i> <span>Detail Struk Penjualan</span></a></li>
          </ul>
        </li>
          
        <li class="treeview">
          <a href="#">
            <i class="fa fa-file"></i>
            <span>Write Off</span>
            <span class="pull-right-container">
              <i class="fa fa-angle-left pull-right"></i>
            </span>
          </a>
          <ul class="treeview-menu">
            <li><a href="{{ route('write_off.index_admin') }}"><i class="fa fa-file-pdf-o"></i> <span>Terima Write Off</span></a></li>
            <li><a href="{{ route('write_off.index_approve') }}"><i class="fa fa-file-pdf-o"></i> <span>Approval Write Off</span></a></li>
            <li><a href="{{ route('write_off.index_report') }}"><i class="fa fa-file-pdf-o"></i> <span>Report Write Off</span></a></li>
          </ul>
        </li>
          
        <li><a href="{{ route('laporan.index') }}"><i class="fa fa-file-pdf-o"></i> <span>Laporan</span></a></li>
        <li><a href="{{ route('setting.index') }}"><i class="fa fa-gears"></i> <span>Setting</span></a></li>
          
        <li class="treeview">
          <a href="#">
            <i class="fa fa-file"></i>
            <span>Report</span>
            <span class="pull-right-container">
              <i class="fa fa-angle-left pull-right"></i>
            </span>
          </a>
          <ul class="treeview-menu">
            <!-- report pembelian -->
            <li><a href="{{ route('report_pembelian.index') }}"><i class="fa fa-cubes"></i> <span>Report Pembelian</span></a></li>
          <li><a href="{{ route('report_kirim.index') }}"><i class="fa fa-cubes"></i> <span>Report Kirim Barang</span></a></li>  
          <li><a href="{{ route('report_so.index') }}"><i class="fa fa-cubes"></i> <span>Report Stok Opname</span></a></li>  
          </ul>
        </li>          
        <li><a href="{{ route('pembelian.admin') }}"><i class="fa fa-file-pdf-o"></i> <span>Pembelian</span></a></li>
        <li><a href=" {{ route('muswamah.index') }} "><i class="fa fa-dollar"></i> <span>Laporan Muswamah</span></a></li>
        <!-- menu approve -->
        <li><a href="{{ route('approve_admin.index') }}"><i class="fa fa-cubes"></i> <span>Approval Stock</span></a></li>
       
      @elseif( Auth::user()->level == 2 )

        <li><a href="{{ route('transaksi.menu') }}"><i class="fa fa-shopping-cart"></i> <span>Pilih Transaksi</span></a></li>
        <li><a href="{{ route('transaksi.new') }}"><i class="fa fa-cart-plus"></i> <span>Transaksi Baru</span></a></li>
        <li><a href="{{ route('kasa.index') }}"><i class="fa fa-cart-plus"></i> <span>Cash Count</span></a></li>
        <li><a href="{{ route('pengeluaran.index') }}"><i class="fa fa-money"></i> <span>Pengeluaran</span></a></li>
        <li><a href="{{ route('musawamahdetail.index') }}"><i class="fa fa-money"></i> <span>Setor Angsuran</span></a></li>
        <li><a href=""><i class="fa fa-cart-plus"></i> <span>Laporan Penjualan</span></a></li>
        <!-- menu jurnal umum -->

        <li><a href="{{ route('cek_harga.index') }}"><i class="fa fa-dollar"></i> <span>Cek Harga</span></a></li>
        <li><a href="{{ route('jurnal_umum_admin.index') }}"><i class="fa fa-file-pdf-o"></i> <span>Jurnal</span></a></li>
        <!-- Saldo_simpanan -->
        <li><a href="{{ route('saldo_titipan.index') }}"><i class="fa fa-file-pdf-o"></i> <span>Saldo Titipan</span></a></li>
        <!-- Saldo_simpanan -->
        <li><a href="{{ route('angsuran.index') }}"><i class="fa fa-file-pdf-o"></i> <span>Angsuran</span></a></li>
        <!-- stok -->
    
      @elseif( Auth::user()->level == 3 )

        <!-- approve supplier -->
        <li><a href="{{ route('approve_supplier.index') }}"><i class="fa fa-cube"></i> <span>Approve Supplier</span></a></li>
        <!-- approve produk -->
        <li><a href="{{ route('approve_produk.index') }}"><i class="fa fa-cube"></i> <span>Approve Produk</span></a></li>
        <!-- permohhonan pembelian -->
        <!-- <li><a href="{{ route('permohonan_pembelian.index') }}"><i class="fa fa-file-pdf-o"></i><span>Permohonan Pembelian</span></a></li>  -->
        <!-- riwayat stok -->
        <li><a href="{{ route('riwayat_stok.index') }}"><i class="fa fa-file-pdf-o"></i><span>Stok</span></a></li>  
        <!-- koreksi_po -->
        <li><a href="{{ route('koreksi_pembelian.index') }}"><i class="fa fa-retweet"></i><span>Koreksi Pembelian</span></a></li>       
            
      @elseif( Auth::user()->level == 4 )
      
        <li class="treeview">
          <a href="#">
            <i class="fa fa-cubes"></i>
            <span>Surat Jalan</span>
            <span class="pull-right-container">
              <i class="fa fa-angle-left pull-right"></i>
            </span>
          </a>
          <ul class="treeview-menu">
            <li><a href="{{ route('kirim_barang.index') }}"><i class="fa fa-cubes"></i> <span>Kirim Toko</span></a></li>
            <li><a href="{{ route('kirim_antar_gudang.index') }}"><i class="fa fa-cubes"></i> <span>Kirim Barang Antar Gudang</span></a></li>
          </ul>
        </li>
        
        
        <li class="treeview">
          <a href="#">
            <i class="fa fa-cubes"></i>
            <span>Retur Barang</span>
            <span class="pull-right-container">
              <i class="fa fa-angle-left pull-right"></i>
            </span>
          </a>
          <ul class="treeview-menu">
            <li><a href="{{ route('retur_supplier.index') }}"><i class="fa fa-download"></i> <span>Retur Barang Rusak</span></a></li>
            <li><a href="{{ route('retur_tukar_barang.index') }}"><i class="fa fa-download"></i> <span>Retur Tukar Barang</span></a></li>
          </ul>
        </li>
        
        
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
            <li><a href="{{ route('terima_po.index') }}"><i class="fa fa-cubes"></i> <span>Terima Barang PO</span></a></li>
            <li><a href="{{ route('retur.index') }}"><i class="fa fa-cubes"></i> <span>Terima Retur Toko</span></a></li>     
            <li><a href="{{ route('terima_retur_tukar_barang.index') }}"><i class="fa fa-cubes"></i> <span>Terima Retur Tukar Barang</span></a></li>     
            <li><a href="{{ route('terima_antar_gudang.index') }}"><i class="fa fa-cubes"></i> <span>Terima Tranfer Antar Gudang</span></a></li>
          </ul>
        </li>
        <li><a href="{{ route('write_off.index') }}"><i class="fa fa-cubes"></i> <span>Write OFF</span></a></li>
        <li><a href="{{ route('stock_opname.index') }}"><i class="fa fa-cubes"></i> <span>Stock Opname</span></a></li>
        <li><a href="{{ route('stok_opname_parsial.index') }}"><i class="fa fa-cubes"></i> <span>Stock Opname Parsial</span></a></li>
        <!-- tambah menu stock barang -->
        <li><a href="{{ route('stock.index') }}"><i class="fa fa-cubes"></i> <span>Stock Gudang</span></a></li>      

      @elseif( Auth::user()->level == 5 )

        <li><a href="{{ route('koreksi_penjualan.index') }}"><i class="fa fa-cubes"></i> <span>Koreksi Penjualan</span></a></li>
        <li><a href="{{ route('kirim_barang_toko.index') }}"><i class="fa fa-cubes"></i> <span>Surat Jalan</span></a></li>
        <li><a href="{{ route('terimaToko.index') }}"><i class="fa fa-cubes"></i> <span>Terima Barang</span></a></li> 
        <!-- tambah menu stock barang -->
        <li><a href="{{ route('stockToko.index') }}"><i class="fa fa-cubes"></i> <span>Stock Toko</span></a></li>
        <li><a href="{{ route('kartu_stok_toko.index') }}"><i class="fa fa-cubes"></i> <span>Kartu Stok</span></a></li>
        <li><a href="{{ route('stok_opname_parsial_toko.index') }}"><i class="fa fa-cubes"></i> <span>Stock Opname Parsial</span></a></li>
        <li><a href="{{ route('stock_opname_toko.index') }}"><i class="fa fa-cubes"></i> <span>Stock Opname</span></a></li>
      
      @elseif( Auth::user()->level == 6 )

        <!-- pricing_kp -->
        <li><a href="{{ route('pricing_kp.index') }}"><i class="fa fa-dollar"></i><span>Pricing Invoice</span></a></li>
        <!-- pricing produk existitng -->
        <li><a href=" {{ route('pricing.index') }} "><i class="fa fa-dollar"></i><span>Pricing Produk Existing</span></a></li>
        <!-- pricing harga pasar -->
        <li><a href=" {{ route('pricing_kompetitor.index') }} "><i class="fa fa-dollar"></i><span>Pricing Harga Pasar</span></a></li>       
        
      @elseif( Auth::user()->level == 7 )

        <li><a href="{{ route('all_stok.index') }}"><i class="fa fa-cubes"></i> <span>Stok</span></a></li>

        <li><a href="{{ route('user.index') }}""><i class="fa fa-user"></i> <span>User</span></a></li> 
      
      @elseif( Auth::user()->level == 8 )

        <!-- supplier -->
        <li><a href="{{ route('supplier.index') }}"><i class="fa fa-truck"></i> <span>Supplier</span></a></li>
        <!-- produk -->
        <li><a href="{{ route('produk.index') }}"><i class="fa fa-cubes"></i> <span>Produk</span></a></li>
        <!-- kategori -->
        <li><a href="{{ route('kategori.index') }}"><i class="fa fa-cube"></i> <span>Kategori</span></a></li>
        <!-- pembuatan po -->
        <li><a href="{{ route('pembelian.index') }}"><i class="fa fa-download"></i> <span>Pesanan Pembelian</span></a></li>
        <!-- invoice -->
        <li><a href="{{ route('invoice.index') }}"><i class="fa fa-cubes"></i> <span>Invoice</span></a></li>
      
      @elseif( Auth::user()->level == 9 )

        <!-- menu jurnal umum -->
        <li><a href="{{ route('jurnal_umum_kp.index') }}"><i class="fa fa-file-pdf-o"></i> <span>Jurnal</span></a></li>
        <!-- menu approve -->
        <li><a href="{{ route('approve_kp.index') }}"><i class="fa fa-cubes"></i> <span>Approval Stock</span></a></li>
        <!-- menu approve -->
        <li><a href="{{ route('approve_stok_opname_parsial_gudang.index') }}"><i class="fa fa-cubes"></i> <span>Approval Stock Opname Parsial</span></a></li>
        <!-- menu approve -->
        <li><a href="{{ route('approve_pricing.index') }}"><i class="fa fa-cubes"></i> <span>Approval Pricing</span></a></li>
        <!-- selisih kirim -->
        <li><a href="{{ route('selisih_kirim_barang.index') }}"><i class="fa fa-cubes"></i> <span>Selisih Kirim Barang</span></a></li>
        <!-- report pembayaran -->
        <li><a href="{{ route('report_jatpo.index') }}"><i class="fa fa-file-pdf-o"></i><span>Pembayaran</span></a></li>
      
      @else
      
        <li class="treeview">
          <a href="#">
            <i class="fa fa-cubes"></i>
            <span>Approval Gudang</span>
            <span class="pull-right-container">
              <i class="fa fa-angle-left pull-right"></i>
            </span>
          </a>
          <ul class="treeview-menu">
            <!-- menu Approve Terima -->
            <li><a href="{{ route('approve_terima_po.index') }}"><i class="fa fa-file-pdf-o"></i> <span>Approve Terima PO</span></a></li>
            <!-- menu Approve Terima Retur -->
            <li><a href="{{ route('approve_terima_retur_toko.index') }}"><i class="fa fa-file-pdf-o"></i> <span>Approve Terima Retur</span></a></li>
            <!-- menu Approve Kirim -->
            <li><a href="{{ route('approve_kirim_barang.index') }}"><i class="fa fa-file-pdf-o"></i> <span>Approve Kirim Barang</span></a></li>
            <!-- menu Approve Retur -->
            <li><a href="{{ route('approve_retur_supplier.index') }}"><i class="fa fa-file-pdf-o"></i> <span>Approve Retur Supplier</span></a></li>
            <!-- menu Approve Retur -->
            <li><a href="{{ route('approve_retur_tukar_barang.index') }}"><i class="fa fa-file-pdf-o"></i> <span>Approve Retur Tukar Barang</span></a></li>
            <!-- menu Approve Terima Retur -->
            <li><a href="{{ route('approve_terima_retur_tukar_barang.index') }}"><i class="fa fa-file-pdf-o"></i> <span>Approve Terima Tukar Barang</span></a></li>
            <!-- menu approve -->
          </ul>
        </li>


        <li class="treeview">
          <a href="#">
            <i class="fa fa-cubes"></i>
            <span>Approval Toko</span>
            <span class="pull-right-container">
              <i class="fa fa-angle-left pull-right"></i>
            </span>
          </a>
          <ul class="treeview-menu">
            <!-- menu Approve Kirim -->
            <li><a href="{{ route('approve_kirim_barang_toko.index') }}"><i class="fa fa-file-pdf-o"></i> <span>Approve Kirim Barang</span></a></li>
            <!-- menu approve terima toko -->
            <li><a href="{{ route('approve_terima_toko.index') }}"><i class="fa fa-file-pdf-o"></i> <span>Approve Terima Barang</span></a></li>
            <!--  -->
            <li><a href="{{ route('approve_stok_opname_parsial_toko.index') }}"><i class="fa fa-cubes"></i> <span>Approval SO Parsial</span></a></li>
            <!-- so bulanan -->
            <li><a href="{{ route('approve_gudang.index') }}"><i class="fa fa-cubes"></i> <span>Approval SO Bulanan</span></a></li>
          </ul>
        </li>


        <li class="treeview">
          <a href="#">
            <i class="fa fa-cubes"></i>
            <span>Stok</span>
            <span class="pull-right-container">
              <i class="fa fa-angle-left pull-right"></i>
            </span>
          </a>
          <ul class="treeview-menu">
            <!-- Kartu Stok -->
            <li><a href="{{ route('stok_wo.index') }}"><i class="fa fa-file-pdf-o"></i> <span>Stok Write Off</span></a></li>
            <!-- Kartu Stok -->
            <li><a href="{{ route('kartu_stok.index') }}"><i class="fa fa-file-pdf-o"></i> <span>Kartu Stok</span></a></li>
          </ul>
        </li>
                  
      @endif

        <li><a href="{{ route('ganti_password.index') }}"><i class="fa fa-user"></i> <span>Ganti Password</span></a></li> 

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
      @php
        $tanggal = Session::get('tanggal');
      @endphp
        <li>{{$tanggal}}</li>
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
<script type="text/javascript">
  $(document).ready(function() {
      window.history.pushState(null, "", window.location.href);        
      window.onpopstate = function() {
          window.history.pushState(null, "", window.location.href);
      };
  });
</script>
@yield('script')

</body>
</html>
