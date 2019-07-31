<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
Route::get('/home', 'HomeController@index')->name('home');
Route::get('/logout', 'Auth\LoginController@logout');
Auth::routes();

Route::group(['middleware' => ['web', 'cekuser:2']], function(){
      Route::get('user/profil', 'UserController@profil')->name('user.profil');
      Route::patch('user/{id}/change', 'UserController@changeProfil');
   
      Route::get('transaksi/menu', 'PenjualanDetailController@NewMenu')->name('transaksi.menu');
      Route::get('transaksi/baru', 'PenjualanDetailController@newSession')->name('transaksi.new');
      Route::get('transaksi/{id}/data', 'PenjualanDetailController@listData')->name('transaksi.data');
      Route::get('transaksi/cetaknota', 'PenjualanDetailController@printNota')->name('transaksi.cetak');
      Route::get('transaksi/notapdf', 'PenjualanDetailController@notaPDF')->name('transaksi.pdf');
      Route::post('transaksi/simpan', 'PenjualanDetailController@saveData');
      Route::get('transaksi/loadform/{diskon}/{total}/{diterima}', 'PenjualanDetailController@loadForm');
      Route::resource('transaksi', 'PenjualanDetailController');

      
      //harga member insan
      Route::get('memberinsan/menu', 'PenjualanDetailMemberInsanController@NewMenu')->name('memberinsan.menu');
      Route::get('memberinsan/{id}/baru', 'PenjualanDetailMemberInsanController@newSession')->name('memberinsan.new');
      Route::get('memberinsan/{id}/data', 'PenjualanDetailMemberInsanController@listData')->name('memberinsan.data');
      Route::get('memberinsan/cetaknota', 'PenjualanDetailMemberInsanController@printNota')->name('memberinsan.cetak');
      Route::get('memberinsan/notapdf', 'PenjualanDetailMemberInsanController@notaPDF')->name('memberinsan.pdf');
      Route::post('memberinsan/simpan', 'PenjualanDetailMemberInsanController@saveData');
      Route::get('memberinsan/loadform/{diskon}/{total}/{diterima}', 'PenjualanDetailMemberInsanController@loadForm');
      Route::resource('memberinsan', 'PenjualanDetailMemberInsanController');

      //harga member pbarik
      Route::get('memberpabrik/menu', 'PenjualanDetailMemberPabrikController@NewMenu')->name('memberpabrik.menu');
      Route::get('memberpabrik/{id}/baru', 'PenjualanDetailMemberPabrikController@newSession')->name('memberpabrik.new');
      Route::get('memberpabrik/{id}/data', 'PenjualanDetailMemberPabrikController@listData')->name('memberpabrik.data');
      Route::get('memberpabrik/cetaknota', 'PenjualanDetailMemberPabrikController@printNota')->name('memberpabrik.cetak');
      Route::get('memberpabrik/notapdf', 'PenjualanDetailMemberPabrikController@notaPDF')->name('memberpabrik.pdf');
      Route::post('memberpabrik/simpan', 'PenjualanDetailMemberPabrikController@saveData');
      Route::get('memberpabrik/loadform/{diskon}/{total}/{diterima}', 'PenjualanDetailMemberPabrikController@loadForm');
      Route::resource('memberpabrik', 'PenjualanDetailMemberPabrikController');

      //harga cash insan
      Route::get('cashinsan/menu', 'CashinsanControllerr@NewMenu')->name('cashinsan.menu');
      Route::get('cashinsan/baru', 'CashinsanController@newSession')->name('cashinsan.new');
      Route::get('cashinsan/{id}/data', 'CashinsanController@listData')->name('cashinsan.data');
      Route::get('cashinsan/cetaknota', 'CashinsanController@printNota')->name('cashinsan.cetak');
      Route::get('cashinsan/notapdf', 'CashinsanController@notaPDF')->name('cashinsan.pdf');
      Route::post('cashinsan/simpan', 'CashinsanController@saveData');
      Route::get('cashinsan/loadform/{diskon}/{total}/{diterima}', 'CashinsanController@loadForm');
      Route::resource('cashinsan', 'CashinsanController');

      //harga cash Pabrik
      
      Route::get('umum/baru', 'PenjualanDetailMemberPabrikController@newSession')->name('umum.new');
      Route::get('umum/{id}/data', 'PenjualanDetailMemberPabrikController@listData')->name('umum.data');
      Route::get('umum/cetaknota', 'PenjualanDetailMemberPabrikController@printNota')->name('umum.cetak');
      Route::get('umum/notapdf', 'PenjualanDetailMemberPabrikController@notaPDF')->name('umum.pdf');
      Route::post('umum/simpan', 'PenjualanDetailMemberPabrikController@saveData');
      Route::get('umum/loadform/{diskon}/{total}/{diterima}', 'PenjualanDetailMemberPabrikController@loadForm');
      Route::resource('umum', 'PenjualanDetailMemberPabrikController');

      //kasa
      Route::get('kasa/data', 'KasaController@listData')->name('kasa.data');
      Route::post('kasa/cetak', 'KasaController@printCard');
      Route::resource('kasa', 'KasaController');

      Route::get('pengeluaran/data', 'PengeluaranController@listData')->name('pengeluaran.data');
      Route::resource('pengeluaran', 'PengeluaranController');

      Route::get('musawamahdetail/data', 'MusawamahDetailController@listData')->name('musawamahdetail.data');
      Route::post('musawamahdetail/cetak', 'MusawamahDetailController@printCard');
      Route::resource('musawamahdetail', 'MusawamahDetailController');

      // controller menu jurnal di user admin
      Route::get('jurnal_umum_admin/index', 'JurnalUmumAdminController@index')->name('jurnal_umum_admin.index');
      Route::post('jurnal_umum_admin/create','JurnalUmumAdminController@create')->name('jurnal_umum_admin.create');
      Route::get('jurnal_umum_admin/destroy/{id}', 'JurnalUmumAdminController@destroy')->name('jurnal_umum_admin.destroy');
      Route::get('jurnal_umum_admin/approve', 'JurnalUmumAdminController@approve')->name('jurnal_umum_admin.approve');
      Route::post('jurnal_umum_admin/autocomplete', 'JurnalUmumAdminController@autocomplete')->name('jurnal_umum_admin.autocomplete');
      
   });
   
   Route::group(['middleware' => ['web', 'cekuser:1' ]], function(){
      Route::get('kategori/data', 'KategoriController@listData')->name('kategori.data');
      Route::resource('kategori', 'KategoriController');
      Route::get('produk/data', 'ProdukController@listData')->name('produk.data');
      Route::post('produk/hapus', 'ProdukController@deleteSelected');
      Route::post('produk/cetak', 'ProdukController@printBarcode');
      Route::resource('produk', 'ProdukController');
      Route::get('supplier/data', 'SupplierController@listData')->name('supplier.data');
      Route::resource('supplier', 'SupplierController');
      Route::get('member/data', 'MemberController@listData')->name('member.data');
      Route::post('member/cetak', 'MemberController@printCard');
      Route::resource('member', 'MemberController');
      Route::get('user/data', 'UserController@listData')->name('user.data');
      Route::resource('user', 'UserController');
   
      Route::get('penjualan/data', 'PenjualanController@listData')->name('penjualan.data');
      Route::get('penjualan/{id}/lihat', 'PenjualanController@show');
      Route::resource('penjualan', 'PenjualanController');
      Route::get('laporan', 'LaporanController@index')->name('laporan.index');
      Route::post('laporan', 'LaporanController@refresh')->name('laporan.refresh');
      Route::get('laporan/data/{awal}/{akhir}', 'LaporanController@listData')->name('laporan.data'); 
      Route::get('laporan/pdf/{awal}/{akhir}', 'LaporanController@exportPDF');
   
      Route::resource('setting', 'SettingController');

      // controller menu pembelian di user admin
      Route::get('pembelian_admin/index','PembelianAdminController@index')->name('pembelian.admin');
      Route::get('pembelian_admin/detail/{id}','PembelianAdminController@detail')->name('pembelian.admin_detail');
      Route::post('pembelian_admin/store_jurnal','PembelianAdminController@store_jurnal')->name('pembelian.update_jurnal');
      Route::get('pembelian_admin/jurnal/{id}','PembelianAdminController@jurnal')->name('pembelian.jurnal');
      Route::get('pembelian_admin/cetak/{id}','PembelianAdminController@cetak_po')->name('pembelian.cetak_po');
      Route::get('pembelian_admin/fpd/{id}','PembelianAdminController@cetak_fpd')->name('pembelian.cetak_fpd');

      // controller menu jurnal di user admin
      Route::get('jurnal_umum_admin/index', 'JurnalUmumAdminController@index')->name('jurnal_umum_admin.index');
      Route::post('jurnal_umum_admin/create','JurnalUmumAdminController@create')->name('jurnal_umum_admin.create');
      Route::get('jurnal_umum_admin/destroy/{id}', 'JurnalUmumAdminController@destroy')->name('jurnal_umum_admin.destroy');
      Route::get('jurnal_umum_admin/approve', 'JurnalUmumAdminController@approve')->name('jurnal_umum_admin.approve');
      Route::post('jurnal_umum_admin/autocomplete', 'JurnalUmumAdminController@autocomplete')->name('jurnal_umum_admin.autocomplete');
      

   });


   Route::group(['middleware' => ['web', 'cekuser:3' ]], function(){
   Route::get('kategori/data', 'KategoriController@listData')->name('kategori.data');
   Route::resource('kategori', 'KategoriController');

   Route::get('produk/data', 'ProdukController@listData')->name('produk.data');
   Route::post('produk/hapus', 'ProdukController@deleteSelected');
   Route::post('produk/cetak', 'ProdukController@printBarcode');
   Route::resource('produk', 'ProdukController');

   Route::get('pembelian/data', 'PembelianController@listData')->name('pembelian.data');
   Route::get('pembelian/{id}/tambah', 'PembelianController@create');
   Route::get('pembelian/{id}/lihat', 'PembelianController@show');
   Route::get('pembelian/{id}/poPDF', 'PembelianController@cetak');
   Route::resource('pembelian', 'PembelianController');   

   Route::get('pembelian_detail/{id}/data', 'PembelianDetailController@listData')->name('pembelian_detail.data');
   Route::get('pembelian_detail/loadform/{diskon}/{total}', 'PembelianDetailController@loadForm');
   Route::resource('pembelian_detail', 'PembelianDetailController');   

   Route::get('supplier/data', 'SupplierController@listData')->name('supplier.data');
   Route::resource('supplier', 'SupplierController');

   // controller menu jurnal di user admin
   Route::get('jurnal_umum_po/index', 'JurnalUmumPoController@index')->name('jurnal_umum_po.index');
   Route::post('jurnal_umum_po/create','JurnalUmumPoController@create')->name('jurnal_umum_po.create');
   Route::get('jurnal_umum_po/destroy/{id}', 'JurnalUmumPoController@destroy')->name('jurnal_umum_po.destroy');
   Route::get('jurnal_umum_po/approve', 'JurnalUmumPoController@approve')->name('jurnal_umum_po.approve');
   Route::post('jurnal_umum_po/autocomplete', 'JurnalUmumPoController@autocomplete')->name('jurnal_umum_po.autocomplete');
});

Route::group(['middleware' => ['web', 'cekuser:4' ]], function(){
   // merubah resource TranferController menjadi ReturGudang Controller untuk controller terima barang retur
   Route::get('retur/gudang', 'ReturGudangController@index')->name('retur.index');
   Route::get('retur/detail/{id}', 'ReturGudangController@show')->name('retur.detail');   
   Route::post('retur/create', 'ReturGudangController@update_status')->name('retur.update_status');
   Route::post('retur/create_stok', 'ReturGudangController@input_stok')->name('retur.input_stok');
   Route::resource('retur', 'ReturGudangController');
  // sotck gudang
   Route::get('stock/index','StockController@index')->name('stock.index');
   Route::get('stock/detail/{id}','StockController@detail')->name('stock.detail');
   Route::resource('stock', 'StockController');
   // stock gudang
   Route::resource('stock', 'StockController');
   // terima barang dari PO
   Route::get('terima/index', 'TerimaController@index')->name('terima.index');
   Route::get('terima/detail/{id}', 'TerimaController@detail')->name('terima.detail');
   Route::post('terima/create', 'TerimaController@update_status')->name('terima.update_status');
   Route::post('terima/create_stok', 'TerimaController@input_stok')->name('terima.input_stok');
   Route::resource('terima', 'TerimaController');
   
   // ----//
   
   Route::get('transfer/gudang', 'TransferController@gudang')->name('kirim.index');
   Route::get('transfer/detail/{id}', 'TransferController@detail');
   Route::get('/transfer/gudang/{id}', 'TransferController@print_gudang');
   
   

   Route::get('kirim_barang/data', 'KirimBarangController@listData')->name('kirim_barang.data');
   Route::get('kirim_barang/{id}/tambah', 'KirimBarangController@create');
   Route::get('kirim_barang/{id}/lihat', 'KirimBarangController@show');
   Route::get('kirim_barang/{id}/poPDF', 'KirimBarangController@cetak');
   Route::resource('kirim_barang', 'KirimBarangController');   

   Route::get('kirim_barang_detail/{id}/data', 'KirimBarangDetailController@listData')->name('barang_detail.data');
   Route::get('kirim_barang_detail/loadform/{diskon}/{total}', 'KirimBarangDetailController@loadForm');
   Route::resource('kirim_barang_detail', 'KirimBarangDetailController');  
   



});

Route::group(['middleware' => ['web', 'cekuser:5' ]], function(){
   Route::get('terima_toko/index', 'TerimaTokoController@index')->name('terimaToko.index');
   Route::get('terima_toko/detail/{id}', 'TerimaTokoController@detail')->name('terimatoko.detail');
   Route::post('terima_toko/create', 'TerimaTokoController@create_jurnal')->name('terimatoko.create_jurnal');
   Route::resource('terimatoko', 'TerimaTokoController');


   Route::get('transfer/detail/{id}', 'TransferController@detail');
   Route::get('transfer/toko', 'TransferController@toko')->name('terimatoko.index');
   Route::get('transfer/toko/{id}', 'TransferController@print_toko');
   Route::post('transfer/update/{id}','TransferController@api');

   Route::resource('transfer', 'transferController');  


   
   Route::get('kirim_barang_toko/data', 'KirimBarangTokoController@listData')->name('kirim_barang_toko.data');
   Route::get('kirim_barang_toko/{id}/tambah', 'KirimBarangTokoController@create');
   Route::get('kirim_barang_toko/{id}/lihat', 'KirimBarangTokoController@show');
   Route::get('kirim_barang_toko/{id}/poPDF', 'KirimBarangTokoController@cetak');
   Route::resource('kirim_barang_toko', 'KirimBarangTokoController');   

   Route::get('kirim_barang_toko_detail/{id}/data', 'KirimBarangTokoDetailController@listData')->name('barang_toko_detail.data');
   Route::get('kirim_barang_toko_detail/loadform/{diskon}/{total}', 'KirimBarangTokoDetailController@loadForm');
   Route::resource('kirim_barang_toko_detail', 'KirimBarangTokoDetailController');
});

Route::group(['middleware' => ['web', 'cekuser:6' ]], function(){
   Route::get('produk/data', 'ProdukController@listData')->name('produk.data');
   Route::post('produk/hapus', 'ProdukController@deleteSelected');
   Route::get('produk/update/{id}', 'ProdukController@edit')->name('produk.edit');
   Route::post('produk/ubah/{id}', 'ProdukController@update')->name('produk.harga_jual');
   Route::post('produk/cetak', 'ProdukController@printBarcode');
   Route::resource('produk', 'ProdukController');
   
   // controller menu jurnal di user kp
   Route::get('jurnal_umum_kp/index', 'JurnalUmumKpController@index')->name('jurnal_umum_kp.index');
   Route::post('jurnal_umum_kp/create','JurnalUmumKpController@create')->name('jurnal_umum_kp.create');
   Route::get('jurnal_umum_kp/destroy/{id}', 'JurnalUmumKpController@destroy')->name('jurnal_umum_kp.destroy');
   Route::get('jurnal_umum_kp/approve', 'JurnalUmumKpController@approve')->name('jurnal_umum_kp.approve');
   Route::post('jurnal_umum_kp/autocomplete', 'JurnalUmumKpController@autocomplete')->name('jurnal_umum_kp.autocomplete');

});