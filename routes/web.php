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

Route::get('/', function () {
   return redirect()->route('login');
});


Route::post('/home/store', 'HomeController@store')->name('home.store');
Route::post('/home/update/{id}', 'HomeController@update')->name('home.update');
Route::get('/logout', 'Auth\LoginController@logout');

Route::get('/ganti_password/index', 'GantiPasswordController@index')->name('ganti_password.index');
Route::post('/ganti_password/update', 'GantiPasswordController@update')->name('ganti_password.update');
Route::get('/ganti_password/reset/{id}', 'GantiPasswordController@reset')->name('ganti_password.reset');

Auth::routes();

Route::group(['middleware' => ['web', 'cekuser:2']], function(){
   
   // saldo simpanan
   Route::get('saldo_titipan/index', 'SaldoTitipanController@index')->name('saldo_titipan.index');
   Route::get('saldo_titipan/getData/{member}', 'SaldoTitipanController@getData')->name('saldo_titipan.getData');
   Route::get('saldo_titipan/getTitipan/{member}', 'SaldoTitipanController@getTitipan')->name('saldo_titipan.getTitipan');
   Route::get('saldo_titipan/listDetail/{member}', 'SaldoTitipanController@listDetail')->name('saldo_titipan.listDetail');
   Route::resource('saldo_titipan', 'SaldoTitipanController');

   // angsuran
   Route::get('angsuran/index', 'AngsuranController@index')->name('angsuran.index');
   Route::get('angsuran/member/{id}', 'AngsuranController@getMember')->name('angsuran.getMember');
   Route::get('angsuran/listTransaksi/{id}', 'AngsuranController@listTransaksi')->name('angsuran.listTransaksi');
   Route::get('angsuran/listTransaksiKelompok/{id}', 'AngsuranController@listTransaksiKelompok')->name('angsuran.listTransaksiKelompok');
   Route::post('angsuran/addTransaksi/', 'AngsuranController@addTransaksi')->name('angsuran.addTransaksi');
   Route::post('angsuran/store_kelompok/','AngsuranController@store_kelompok')->name('angsuran.store_kelompok');

   // cek harga
   Route::get('cek_harga/index', 'CekHargaController@index')->name('cek_harga.index');
   Route::get('cek_harga/data', 'CekHargaController@listData')->name('cek_harga.data');
   Route::resource('cek_harga', 'CekHargaController');

   Route::get('user/profil', 'UserController@profil')->name('user.profil');
   Route::patch('user/{id}/change', 'UserController@changeProfil');
   Route::get('transaksi/menu', 'PenjualanDetailController@NewMenu')->name('transaksi.menu');
   Route::get('transaksi/baru', 'PenjualanDetailController@newSession')->name('transaksi.new');
   Route::get('transaksi/{id}/data', 'PenjualanDetailController@listData')->name('transaksi.data');
   Route::get('transaksi/cetaknota', 'PenjualanDetailController@printNota')->name('transaksi.cetak');
   Route::get('transaksi/notapdf', 'PenjualanDetailController@notaPDF')->name('transaksi.pdf');
   Route::post('transaksi/simpan', 'PenjualanDetailController@saveData');
   Route::get('transaksi/loadform/{diskon}/{total}/{diterima}', 'PenjualanDetailController@loadForm');
   Route::get('transaksi/batal/{id}', 'PenjualanDetailController@batal')->name('transaksi.batal');
   Route::resource('transaksi', 'PenjualanDetailController');

   
   //harga member insan
   Route::get('memberinsan/menu', 'PenjualanDetailMemberInsanController@NewMenu')->name('memberinsan.menu');
   Route::post('memberinsan/baru', 'PenjualanDetailMemberInsanController@newSession')->name('memberinsan.new');
   Route::post('memberinsan/pin_baru', 'PenjualanDetailMemberInsanController@newPin')->name('memberinsan.new_pin');

   Route::get('memberinsan/{id}/data', 'PenjualanDetailMemberInsanController@listData')->name('memberinsan.data');
   Route::get('memberinsan/cetaknota', 'PenjualanDetailMemberInsanController@printNota')->name('memberinsan.cetak');
   Route::get('memberinsan/notapdf', 'PenjualanDetailMemberInsanController@notaPDF')->name('memberinsan.pdf');
   Route::post('memberinsan/simpan', 'PenjualanDetailMemberInsanController@saveData');
   Route::get('memberinsan/loadform/{diskon}/{total}/{diterima}', 'PenjualanDetailMemberInsanController@loadForm');
   Route::get('member/check/{id}','PenjualanDetailMemberInsanController@checkPin')->name('member.check');
   Route::resource('memberinsan', 'PenjualanDetailMemberInsanController');

   //harga member pbarik
   Route::get('memberpabrik/menu', 'PenjualanDetailMemberPabrikController@NewMenu')->name('memberpabrik.menu');
   Route::post('memberpabrik/baru', 'PenjualanDetailMemberPabrikController@newSession')->name('memberpabrik.new');
   Route::post('memberpabrik/pin_baru', 'PenjualanDetailMemberPabrikController@newPin')->name('memberpabrik.new_pin');
   Route::get('memberpabrik/{id}/data', 'PenjualanDetailMemberPabrikController@listData')->name('memberpabrik.data');
   Route::get('memberpabrik/cetaknota', 'PenjualanDetailMemberPabrikController@printNota')->name('memberpabrik.cetak');
   Route::get('memberpabrik/notapdf', 'PenjualanDetailMemberPabrikController@notaPDF')->name('memberpabrik.pdf');
   Route::post('memberpabrik/simpan', 'PenjualanDetailMemberPabrikController@saveData');
   Route::get('memberpabrik/loadform/{diskon}/{total}/{diterima}', 'PenjualanDetailMemberPabrikController@loadForm');
   Route::resource('memberpabrik', 'PenjualanDetailMemberPabrikController');

   //harga cash insan
   Route::get('cashinsan/menu', 'PenjualanDetailCashInsanController@NewMenu')->name('cashinsan.menu');
   Route::get('cashinsan/baru', 'PenjualanDetailCashInsanController@newSession')->name('cashinsan.new');
   Route::get('cashinsan/{id}/data', 'PenjualanDetailCashInsanController@listData')->name('cashinsan.data');
   Route::get('cashinsan/cetaknota', 'PenjualanDetailCashInsanController@printNota')->name('cashinsan.cetak');
   Route::get('cashinsan/notapdf', 'PenjualanDetailCashInsanController@notaPDF')->name('cashinsan.pdf');
   Route::post('cashinsan/simpan', 'PenjualanDetailCashInsanController@saveData');
   Route::get('cashinsan/loadform/{diskon}/{total}/{diterima}', 'PenjualanDetailCashInsanController@loadForm');
   Route::resource('cashinsan', 'PenjualanDetailCashInsanController');

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
   Route::post('kasa/printeod', 'KasaController@printKasa')->name('kasa.cetak');
   Route::resource('kasa', 'KasaController');

   Route::get('pengeluaran/data', 'PengeluaranController@listData')->name('pengeluaran.data');
   Route::resource('pengeluaran', 'PengeluaranController');

   Route::get('musawamahdetail/data', 'MusawamahDetailController@listData')->name('musawamahdetail.data');
   Route::post('musawamahdetail/cetak', 'MusawamahDetailController@printCard');
   Route::resource('musawamahdetail', 'MusawamahDetailController');
   
});

   
Route::group(['middleware' => ['web', 'cekuser:1' ]], function(){

   Route::get('report_so/index', 'ReportStokOpnameController@index')->name('report_so.index');
   Route::get('report_so/data', 'ReportStokOpnameController@listData')->name('report_so.data');

   Route::get('restruktur/index', 'RestrukturController@index')->name('restruktur.index');
   
   // reset pin
   Route::get('reset_pin/index', 'ResetPinController@index')->name('reset_pin.index');
   Route::get('reset_pin/data', 'ResetPinController@listData')->name('reset_pin.data');
   Route::get('reset_pin/reset/{kode_member}', 'ResetPinController@reset')->name('reset_pin.reset');

   Route::get('member/data', 'MemberController@listData')->name('member.data');
   Route::post('member/cetak', 'MemberController@printCard');
   Route::resource('member', 'MemberController');

   Route::get('write_off/index_admin', 'WriteOffController@index_admin')->name('write_off.index_admin');
   Route::get('write_off/proses/{id}', 'WriteOffController@proses')->name('write_off.proses');
   Route::get('write_off/list_admin', 'WriteOffController@listAdmin')->name('write_off.listAdmin');

   Route::get('write_off/index_approve/', 'WriteOffController@index_approve')->name('write_off.index_approve');
   Route::get('write_off/approve_list/', 'WriteOffController@listApprove')->name('write_off.listApprove');
   Route::post('write_off/approve', 'WriteOffController@approve')->name('write_off.approve_proses');
   
   
   Route::get('write_off/index_report', 'WriteOffController@index_report')->name('write_off.index_report');
   Route::get('write_off/print_surat/{id}', 'WriteOffController@print_surat')->name('write_off.print_surat');
   Route::get('write_off/list_report', 'WriteOffController@listReport')->name('write_off.listReport');
   Route::get('write_off/file/{id}', 'WriteOffController@file')->name('write_off.file');
   Route::get('write_off/detail_report/{id}', 'WriteOffController@detailReport')->name('write_off.detailReport');


   // report pembelian
   Route::get('report_pembelian/index', 'ReportPembelianController@index')->name('report_pembelian.index');
   Route::get('report_pembelian/data', 'ReportPembelianController@listData')->name('report_pembelian.data');
   Route::get('report_pembelian/detail/{id}', 'ReportPembelianController@detail')->name('report_pembelian.detail');
   Route::get('report_pembelian/detail/data/{id}','ReportPembelianController@listDetail')->name('report_pembelian.data_detail');
   Route::resource('report_pembelian', 'ReportPembelianController');


   Route::get('penjualan/index', 'PenjualanController@index')->name('penjualan.index');
   route::get('penjualan/data/{awal}/{akhir}','PenjualanController@listData')->name('penjualan.data');
   Route::get('penjualan/detail', 'PenjualanController@detail')->name('penjualan.detail');
   route::get('penjualan/data_detail/{awal}/{akhir}','PenjualanController@listDetail')->name('penjualan.data_detail');
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
   Route::post('pembelian_admin/simpan','PembelianAdminController@simpan')->name('pembelian.simpan');

   // controller menu jurnal di user admin
   Route::get('jurnal_umum_admin/index', 'JurnalUmumAdminController@index')->name('jurnal_umum_admin.index');
   Route::post('jurnal_umum_admin/create','JurnalUmumAdminController@create')->name('jurnal_umum_admin.create');
   Route::get('jurnal_umum_admin/destroy/{id}', 'JurnalUmumAdminController@destroy')->name('jurnal_umum_admin.destroy');
   Route::get('jurnal_umum_admin/approve', 'JurnalUmumAdminController@approve')->name('jurnal_umum_admin.approve');
   Route::post('jurnal_umum_admin/autocomplete', 'JurnalUmumAdminController@autocomplete')->name('jurnal_umum_admin.autocomplete');

   // laporan Musawamah
   Route::get('laporan/muswamah','LaporanMusawamahController@index')->name('muswamah.index');
   Route::get('muswamah/listData','LaporanMusawamahController@listData')->name('musawamah.listData');
   Route::resource('musawamah','LaporanMusawamahController');

   // approval
   Route::get('approve_admin/index', 'ApprovalAdminController@index')->name('approve_admin.index');
   Route::put('approve_admin/store', 'ApprovalAdminController@store')->name('approve_admin.store');
   Route::get('approve_admin/data', 'ApprovalAdminController@listData')->name('approve_admin.data');
   Route::resource('approve_admin', 'ApprovalAdminController');

   // report kirim barang
   Route::get('report_kirim/index', 'ReportKirimBarangController@index')->name('report_kirim.index');
   Route::get('report_kirim/data', 'ReportKirimBarangController@listData')->name('report_kirim.data');
   Route::get('report_kirim/detail/{id}', 'ReportKirimBarangController@detail')->name('report_kirim.detail');
   Route::get('report_kirim/data/detail/{id}', 'ReportKirimBarangController@listDetail')->name('report_kirim.data_detail');
   Route::resource('report_kirim', 'ReportKirimBarangController');


   Route::get('kasa_eod/eod', 'KasaController@eod')->name('kasa_eod.eod');
   Route::resource('kasa_eod', 'KasaController');
   
});


Route::group(['middleware' => ['web', 'cekuser:3' ]], function(){
   
   Route::get('approve_produk/index', 'ApproveProdukController@index')->name('approve_produk.index');
   Route::get('approve_produk/data', 'ApproveProdukController@listData')->name('approve_produk.data');
   Route::get('approve_produk/approve/{id}', 'ApproveProdukController@approve')->name('approve_produk.approve');
   
   Route::get('approve_supplier/index', 'ApproveSupplierController@index')->name('approve_supplier.index');
   Route::get('approve_supplier/data', 'ApproveSupplierController@listData')->name('approve_supplier.data');
   Route::get('approve_supplier/approve/{id}', 'ApproveSupplierController@approve')->name('approve_supplier.approve');
   
   // riwayat pembelian
   Route::get('riwayat_stok/index', 'RiwayatStokController@index')->name('riwayat_stok.index');
   Route::get('riwayat_stok/data', 'RiwayatStokController@listData')->name('riwayat_stok.data');
   Route::resource('riwayat_stok', 'RiwayatStokController');   

   // controller menu jurnal di user admin
   Route::get('jurnal_umum_po/index', 'JurnalUmumPoController@index')->name('jurnal_umum_po.index');
   Route::post('jurnal_umum_po/create','JurnalUmumPoController@create')->name('jurnal_umum_po.create');
   Route::get('jurnal_umum_po/destroy/{id}', 'JurnalUmumPoController@destroy')->name('jurnal_umum_po.destroy');
   Route::get('jurnal_umum_po/approve', 'JurnalUmumPoController@approve')->name('jurnal_umum_po.approve');
   Route::post('jurnal_umum_po/autocomplete', 'JurnalUmumPoController@autocomplete')->name('jurnal_umum_po.autocomplete');

   //koreksi pembelian
   Route::get('koreksi_pembelian/index', 'KoreksiPembelianController@index')->name('koreksi_pembelian.index');
   Route::get('koreksi_pembelian/listData', 'KoreksiPembelianController@listData')->name('koreksi_pembelian.listData');
   Route::get('koreksi_pembelian/show/{id}', 'KoreksiPembelianController@show')->name('koreksi_pembelian.show');
   Route::post('koreksi_pembelian/update', 'KoreksiPembelianController@update')->name('koreksi_pembelian.update');
   Route::get('koreksi_pembelian/delete/{id}', 'KoreksiPembelianController@delete')->name('koreksi_pembelian.delete');
   Route::get('koreksi/store', 'KoreksiPembelianController@store')->name('koreksi.store');
   
   Route::get('permohonan_pembelian/index', 'PermohonanPembelianController@index')->name('permohonan_pembelian.index');
   Route::get('permohonan_pembelian/listData', 'PermohonanPembelianController@listData')->name('permohonan_pembelian.data');
   Route::get('permohonan_pembelian/tambah', 'PermohonanPembelianController@tambah')->name('permohonan_pembelian.tambah');
   Route::get('permohona_pembelian/autocomplete_produk', 'PermohonanPembelianController@autocomplete_produk')->name('permohonan_pembelian.autocomplete_produk');
   Route::get('permohona_pembelian/autocomplet_supplier', 'PermohonanPembelianController@autocomplet_supplier')->name('permohonan_pembelian.autocomplet_supplier');
   Route::post('permohonan_pembelian/store', 'PermohonanPembelianController@store')->name('permohonan_pembelian.store');
   Route::get('permohonan_pembelian/edit/{id}', 'PermohonanPembelianController@edit')->name('permohonan_pembelian.edit');
   Route::get('permohonan_pembelian/delete/{id}', 'PermohonanPembelianController@destroy')->name('permohonan_pembelian.delete');
   Route::post('permohonan_pembelian/update', 'PermohonanPembelianController@update')->name('permohonan_pembelian.update');
   
});


Route::group(['middleware' => ['web', 'cekuser:4' ]], function(){
   // stock opname
   
   Route::get('stock_opname/index', 'StockOpnameGudangController@index')->name('stock_opname.index');
   Route::get('stock_opname/data', 'StockOpnameGudangController@listData')->name('stock_opname.data');
   Route::post('stock_opname/proses', 'StockOpnameGudangController@proses')->name('stock_opname.proses');
   Route::post('stock_opname/import_excel', 'StockOpnameGudangController@import_excel')->name('stock_opname.import_excel');
   Route::get('stock_opname/export_excel', 'StockOpnameTokoController@export_excel')->name('stock_opname.export_excel');
   
   Route::resource('stock_opname', 'StockOpnameGudangController');   

   // retur ke supplier
   Route::get('retur_supplier/data', 'ReturSupplierController@listData')->name('retur_supplier.data');
   Route::get('retur_supplier/{id}/tambah', 'ReturSupplierController@create');
   Route::get('retur_supplier/{id}/lihat', 'ReturSupplierController@show');
   Route::get('retur_supplier/{id}/poPDF', 'ReturSupplierController@cetak');
   Route::resource('retur_supplier', 'ReturSupplierController');   

   Route::get('retur_supplier_detail/{id}/data', 'ReturSupplierDetailController@listData')->name('retur_supplier_detail.data');
   Route::get('retur_supplier_detail/loadform/{diskon}/{total}', 'ReturSupplierDetailController@loadForm');
   Route::resource('retur_supplier_detail', 'ReturSupplierDetailController');   

   // merubah resource TranferController menjadi ReturGudang Controller untuk controller terima barang retur
   Route::get('retur/gudang', 'ReturGudangController@index')->name('retur.index');
   Route::get('retur/detail/{id}', 'ReturGudangController@show')->name('retur.detail');   
   Route::post('retur/store', 'ReturGudangController@store')->name('retur.store');
   Route::resource('retur', 'ReturGudangController');
   // sotck gudang
   Route::get('stock/index','StockController@index')->name('stock.index');
   Route::get('stock/detail/{id}','StockController@detail')->name('stock.detail');
   Route::get('stock/listData', 'StockController@listData')->name('stock.data');
   Route::get('stock/delete/{id}','StockController@delete')->name('stock.delete');
   // so
   Route::put('stock/store/', 'StockController@store')->name('stock.store');
   Route::resource('stock', 'StockController');
   // 

   // laporan so
   Route::get('laporan/Gudang', 'LaporanSoGudangController@index')->name('laporanGudang.index');
   Route::get('laporan/Gudang/listData', 'LaporanSoGudangController@listData')->name('laporanGudang.data');

   // terima barang dari PO
   Route::get('terima/index', 'TerimaController@index')->name('terima.index');
   Route::get('terima/detail/{id}', 'TerimaController@detail')->name('terima.detail');
   Route::post('terima/create', 'TerimaController@update_status')->name('terima.update_status');
   Route::post('terima/create_stok', 'TerimaController@input_stok')->name('terima.input_stok');
   Route::resource('terima', 'TerimaController');
   
   // controller terima barang terbaru
   Route::get('terima_po/data', 'TerimaPoController@listData')->name('terima_po.data');
   Route::get('terima_po/edit/{id}', 'TerimaPoController@edit')->name('terima_po.edit');
   Route::get('terima_po/{id}/tambah', 'TerimaPoController@create');
   Route::get('terima_po/{id}/lihat', 'TerimaPoController@show');
   Route::get('terima_po/{id}/poPDF', 'TerimaPoController@cetak');
   Route::resource('terima_po', 'TerimaPoController');   

   Route::get('terima_po_detail/{id}/data', 'TerimaPoDetailController@listData')->name('terima_po_detail.data');
   Route::get('terima_po_detail/loadform/{diskon}/{total}', 'TerimaPoDetailController@loadForm');
   Route::resource('terima_po_detail', 'TerimaPoDetailController');   

   // approval
   Route::get('approve/index', 'ApprovalGudangController@index')->name('approve.index');
   Route::get('approve/listData/{unit}', 'ApprovalGudangController@listData')->name('approve.data');
   Route::put('approve/store', 'ApprovalGudangController@store')->name('approve.store');
   Route::resource('approve', 'ApprovalGudangController');
   // ----//
   
   Route::get('transfer/gudang', 'TransferController@gudang')->name('kirim.index');
   Route::get('transfer/detail/{id}', 'TransferController@detail');
   Route::get('/transfer/gudang/{id}', 'TransferController@print_gudang');
   
   //
   Route::get('kirim_barang_hold/index', 'KirimBarangHoldController@index')->name('kirim_hold.index');
   Route::get('kirim_barang_hold/data', 'KirimBarangHoldController@listData')->name('kirim_hold.data');

   Route::get('kirim_barang/data', 'KirimBarangController@listData')->name('kirim_barang.data');
   Route::get('kirim_barang/{id}/tambah', 'KirimBarangController@create');
   Route::get('kirim_barang/{id}/lihat', 'KirimBarangController@show');
   Route::get('kirim_barang/{id}/poPDF', 'KirimBarangController@cetak')->name('kirim_barang.cetak');
   Route::resource('kirim_barang', 'KirimBarangController');   

   Route::get('kirim_barang_detail/{id}/data', 'KirimBarangDetailController@listData')->name('barang_detail.data');
   Route::get('kirim_barang_detail/continued/{id}', 'KirimBarangDetailController@continued_hold')->name('barang_detail.continued');
   Route::get('kirim_barang_detail/update/{id}', 'KirimBarangDetailController@update')->name('barang_detail.update');
   Route::get('kirim_barang_detail/expired/{id}', 'KirimBarangDetailController@expired')->name('barang_detail.update_expired');
   Route::delete('kirim_barang_detail/destroy/{id}', 'KirimBarangDetailController@destroy')->name('barang_detail.destroy');
   Route::get('kirim_barang_detail/loadform/{id}', 'KirimBarangDetailController@loadForm')->name('barang_detail.loadForm');
   Route::resource('kirim_barang_detail', 'KirimBarangDetailController');  
   //

   Route::get('kirim_barang_detail/{id}/data', 'KirimBarangDetailController@listData')->name('barang_detail.data');
   Route::get('kirim_barang_detail/loadform/{diskon}/{total}', 'KirimBarangDetailController@loadForm');
   Route::resource('kirim_barang_detail', 'KirimBarangDetailController');  

   // antar_gudang
   Route::get('kirim_antar_gudang/index', 'KirimAntarGudangController@index')->name('kirim_antar_gudang.index');
   Route::get('kirim_antar_gudang/data', 'KirimAntarGudangController@listData')->name('kirim_antar_gudang.data');
   Route::get('kirim_antar_gudang/{id}/tambah', 'KirimAntarGudangController@create')->name('kirim_antar_gudang.tambah');
   Route::get('kirim_antar_gudang/{id}/lihat', 'KirimAntarGudangController@show');
   Route::get('kirim_antar_gudang/{id}/poPDF', 'KirimAntarGudangController@cetak')->name('kirim_antar_gudang.cetak');
   Route::post('kirim_antar_gudang/store', 'KirimAntarGudangController@store')->name('kirim_antar_gudang.store');
   
   Route::post('kirim_antar_gudang_detail/store', 'KirimAntarGudangDetailController@store')->name('kirim_antar_gudang_detail.store');
   Route::get('kirim_antar_gudang_detail/index', 'KirimAntarGudangDetailController@index')->name('kirim_antar_gudang_detail.index');
   Route::get('kirim_antar_gudang_detail/{id}/data', 'KirimAntarGudangDetailController@listData')->name('kirim_antar_gudang_detail.data');
   Route::get('kirim_antar_gudang_detail/continued/{id}', 'KirimAntarGudangDetailController@continued_hold')->name('kirim_antar_gudang_detail.continued');
   Route::post('kirim_antar_gudang_detail/update/{id}', 'KirimAntarGudangDetailController@update')->name('kirim_antar_gudang_detail.update');
   Route::get('kirim_antar_gudang_detail/expired/{id}', 'KirimAntarGudangDetailController@expired')->name('kirim_antar_gudang_detail.update_expired');
   Route::delete('kirim_antar_gudang_detail/destroy/{id}', 'KirimAntarGudangDetailController@destroy')->name('kirim_antar_gudang_detail.destroy');
   Route::get('kirim_antar_gudang_detail/loadform/{id}', 'KirimAntarGudangDetailController@loadForm')->name('kirim_antar_gudang_detail.loadForm');

   // terima gudang
   Route::get('terima_antar_gudang/index', 'TerimaGudangController@index')->name('terima_antar_gudang.index');
   Route::get('terima_antar_gudang/detail/{id}', 'TerimaGudangController@detail')->name('terima_antar_gudang.detail');
   Route::post('terima_antar_gudang/terima', 'TerimaGudangController@terima')->name('terima_antar_gudang.terima');
   Route::resource('terima_antar_gudang', 'TerimaGudangController');
   // ----//
   
   // retur tukar barang
   Route::get('retur_tukar_barang/index', 'ReturTukarBarangController@index')->name('retur_tukar_barang.index');
   Route::get('retur_tukar_barang/data', 'ReturTukarBarangController@listData')->name('retur_tukar_barang.data');
   Route::get('retur_tukar_barang/{id}/tambah', 'ReturTukarBarangController@create')->name('retur_tukar_barang.create');
   Route::get('retur_tukar_barang/{id}/lihat', 'ReturTukarBarangController@show')->name('retur_tukar_barang.detail');
   Route::get('retur_tukar_barang/{id}/delete', 'ReturTukarBarangController@delete')->name('retur_tukar_barang.delete');
   Route::get('retur_tukar_barang/{id}/poPDF', 'ReturTukarBarangController@cetak')->name('retur_tukar_barang.cetak');
   Route::post('retur_tukar_barang/store', 'ReturTukarBarangController@store')->name('retur_tukar_barang.store');
   Route::get('retur_tukar_barang/hold/{id}', 'ReturTukarBarangController@hold')->name('retur_tukar_barang.hold');
   Route::get('retur_tukar_barang/hold/{id}', 'ReturTukarBarangController@hold')->name('retur_tukar_barang.hold');

   Route::get('retur_tukar_barang_detail/index', 'ReturTukarBarangDetailController@index')->name('retur_tukar_barang_detail.index');
   Route::get('retur_tukar_barang_detail/{id}/data', 'ReturTukarBarangDetailController@listData')->name('retur_tukar_barang_detail.data');
   Route::post('retur_tukar_barang_detail/store', 'ReturTukarBarangDetailController@store')->name('retur_tukar_barang_detail.store');
   Route::get('retur_tukar_barang_detail/continued/{id}', 'ReturTukarBarangDetailController@continued_hold')->name('retur_tukar_barang_detail.continued');
   Route::get('retur_tukar_barang_detail/update/{id}', 'ReturTukarBarangDetailController@update')->name('retur_tukar_barang_detail.update');
   Route::get('retur_tukar_barang_detail/expired/{id}', 'ReturTukarBarangDetailController@expired')->name('retur_tukar_barang_detail.update_expired');
   Route::delete('retur_tukar_barang_detail/destroy/{id}', 'ReturTukarBarangDetailController@destroy')->name('retur_tukar_barang_detail.destroy');
   Route::get('retur_tukar_barang_detail/loadform/{id}', 'ReturTukarBarangDetailController@loadForm')->name('retur_tukar_barang_detail.loadForm');
   
   Route::get('terima_retur_tukar_barang/gudang', 'TerimaReturTukarBarangController@index')->name('terima_retur_tukar_barang.index');
   Route::get('terima_retur_tukar_barang/detail/{id}', 'TerimaReturTukarBarangController@show')->name('terima_retur_tukar_barang.detail');   
   Route::post('terima_retur_tukar_barang/store', 'TerimaReturTukarBarangController@store')->name('terima_retur_tukar_barang.store');

   Route::get('write_off/index', 'WriteOffController@index')->name('write_off.index');
   Route::get('write_off/load_data', 'WriteOffController@loadData')->name('write_off.loadData');
   Route::get('write_off/load_stok/{id}', 'WriteOffController@loadstok')->name('write_off.loadstok');
   Route::post('write_off/store', 'WriteOffController@store')->name('write_off.store');

   Route::get('stok_opname_parsial/index', 'StokOpnameParsialController@index')->name('stok_opname_parsial.index');
   Route::post('stok_opname_parsial/store', 'StokOpnameParsialController@store')->name('stok_opname_parsial.store');
   
});


Route::group(['middleware' => ['web', 'cekuser:5' ]], function(){

   // kartu stok
   Route::get('kartu_stok_toko/index', 'KartuStokTokoController@index')->name('kartu_stok_toko.index');
   Route::get('kartu_stok_toko/data', 'KartuStokTokoController@listData')->name('kartu_stok_toko.data');

   
   // koreksi penjualan
   Route::get('koreksi_penjualan/index', 'KoreksiPenjualanController@index')->name('koreksi_penjualan.index');  
   Route::get('koreksi_penjualan/data', 'KoreksiPenjualanController@listData')->name('koreksi_penjualan.data');
   Route::get('koreksi_penjualan/newSession/{id}', 'KoreksiPenjualanController@newSession')->name('koreksi_penjualan.newSession'); 
   Route::get('koreksi_penjualan/check/{id}','KoreksiPenjualanController@checkPin')->name('koreksi_penjualan.check');
   Route::post('koreksi_penjualan/pin_baru', 'KoreksiPenjualanController@newPin')->name('koreksi_penjualan.new_pin');
   Route::get('koreksi_penjualan/new_transaksi/{id}', 'KoreksiPenjualanController@new_transaksi')->name('koreksi_penjualan.new_transaksi');
   Route::get('koreksi_penjualan/batal/{id}', 'KoreksiPenjualanController@batal')->name('koreksi_penjualan.batal');

   Route::get('koreksi_penjualan/newSessionCash/{id}', 'KoreksiPenjualanController@newSessionCash')->name('koreksi_penjualan.newSessionCash');
   Route::get('koreksi_penjualan_newSessionInsan/index/{id}', 'KoreksiPenjualanController@newSessionInsan')->name('koreksi_penjualan.newSessionInsan');
   Route::get('koreksi_penjualan_newSessionPabrik/index/{id}', 'KoreksiPenjualanController@newSessionPabrik')->name('koreksi_penjualan.newSessionPabrik');

   Route::get('koreksi_penjualan_cash/index', 'KoreksiPenjualanCashController@index')->name('koreksi_penjualan_cash.index');
   Route::get('koreksi_penjualan_cash/data/{id}', 'KoreksiPenjualanCashController@listData')->name('koreksi_penjualan_cash.data');

   Route::get('koreksi_penjualan_cash/listDetail/{id}', 'KoreksiPenjualanCashController@listDetail')->name('koreksi_penjualan_cash.listDetail'); 
   Route::put('koreksi_penjualan_cash/update/{id}', 'KoreksiPenjualanCashController@update')->name('koreksi_penjualan_cash.update');
   Route::post('koreksi_penjualan_cash/store', 'KoreksiPenjualanCashController@store')->name('koreksi_penjualan_cash.store');
   Route::get('koreksi_penjualan_cash/destroy/{id}', 'KoreksiPenjualanCashController@destroy')->name('koreksi_penjualan_cash.destroy');
   Route::get('koreksi_penjualan_cash/loadform/{diskon}/{total}/{diterima}/{idpenjualan}', 'KoreksiPenjualanCashController@loadform')->name('koreksi_penjualan_cash.loadform');
   Route::post('koreksi_penjualan_cash/simpan', 'KoreksiPenjualanCashController@simpan')->name('koreksi_penjualan_cash.simpan');
   Route::get('koreksi_penjualan_cash/cetaknota', 'KoreksiPenjualanCashController@printNota')->name('koreksi_penjualan_cash.cetak');
   Route::get('koreksi_penjualan_cash/notapdf', 'KoreksiPenjualanCashController@notaPDF')->name('koreksi_penjualan_cash.pdf');
   
   Route::post('koreksi_penjualan/newSessionCredit', 'KoreksiPenjualanController@newSessionCredit')->name('koreksi_penjualan.newSessionCredit');
   
   
   Route::get('koreksi_penjualan_insan/index', 'KoreksiPenjualanCreditInsanController@index')->name('koreksi_penjualan_insan.index');
   Route::get('koreksi_penjualan_insan/data/{id}', 'KoreksiPenjualanCreditInsanController@listData')->name('koreksi_penjualan_insan.data');
   Route::get('koreksi_penjualan_insan/listDetail/{id}', 'KoreksiPenjualanCreditInsanController@listDetail')->name('koreksi_penjualan_insan.listDetail'); 
   Route::put('koreksi_penjualan_insan/update/{id}', 'KoreksiPenjualanCreditInsanController@update')->name('koreksi_penjualan_insan.update');
   Route::post('koreksi_penjualan_insan/store', 'KoreksiPenjualanCreditInsanController@store')->name('koreksi_penjualan_insan.store');
   Route::get('koreksi_penjualan_insan/destroy/{id}', 'KoreksiPenjualanCreditInsanController@destroy')->name('koreksi_penjualan_insan.destroy');
   Route::get('koreksi_penjualan_insan/loadform/{diskon}/{total}/{diterima}/{idpenjualan}', 'KoreksiPenjualanCreditInsanController@loadform')->name('koreksi_penjualan_insan.loadform');
   Route::post('koreksi_penjualan_insan/simpan', 'KoreksiPenjualanCreditInsanController@simpan')->name('koreksi_penjualan_insan.simpan');
   Route::get('koreksi_penjualan_insan/cetaknota', 'KoreksiPenjualanCreditInsanController@printNota')->name('koreksi_penjualan_insan.cetak');
   Route::get('koreksi_penjualan_insan/notapdf', 'KoreksiPenjualanCreditInsanController@notaPDF')->name('koreksi_penjualan_insan.pdf');
      
   Route::get('koreksi_penjualan_pabrik/index', 'KoreksiPenjualanCreditPabrikController@index')->name('koreksi_penjualan_pabrik.index');
   Route::get('koreksi_penjualan_pabrik/data/{id}', 'KoreksiPenjualanCreditPabrikController@listData')->name('koreksi_penjualan_pabrik.data');
   Route::get('koreksi_penjualan_pabrik/listDetail/{id}', 'KoreksiPenjualanCreditPabrikController@listDetail')->name('koreksi_penjualan_pabrik.listDetail'); 
   Route::put('koreksi_penjualan_pabrik/update/{id}', 'KoreksiPenjualanCreditPabrikController@update')->name('koreksi_penjualan_pabrik.update');
   Route::post('koreksi_penjualan_pabrik/store', 'KoreksiPenjualanCreditPabrikController@store')->name('koreksi_penjualan_pabrik.store');
   Route::get('koreksi_penjualan_pabrik/destroy/{id}', 'KoreksiPenjualanCreditPabrikController@destroy')->name('koreksi_penjualan_pabrik.destroy');
   Route::get('koreksi_penjualan_pabrik/loadform/{diskon}/{total}/{diterima}/{idpenjualan}', 'KoreksiPenjualanCreditPabrikController@loadform')->name('koreksi_penjualan_pabrik.loadform');
   Route::post('koreksi_penjualan_pabrik/simpan', 'KoreksiPenjualanCreditPabrikController@simpan')->name('koreksi_penjualan_pabrik.simpan');
   Route::get('koreksi_penjualan_pabrik/cetaknota', 'KoreksiPenjualanCreditPabrikController@printNota')->name('koreksi_penjualan_pabrik.cetak');
   Route::get('koreksi_penjualan_pabrik/notapdf', 'KoreksiPenjualanCreditPabrikController@notaPDF')->name('koreksi_penjualan_pabrik.pdf');
      
   // sotck toko
   Route::get('stock_toko/index','StockTokoController@index')->name('stockToko.index');
   Route::get('stock_toko/detail/{id}','StockTokoController@detail')->name('stockToko.detail');
   Route::get('stock_toko/edit/{id}','StockTokoController@edit')->name('stockToko.edit');
   Route::post('stock_toko/tambah/','StockTokoController@tambah')->name('stockToko.tambah');
   Route::get('stock_toko/delete/{id}','StockTokoController@delete')->name('stockToko.delete');
   Route::put('stock_toko/store/', 'StockTokoController@store')->name('stockToko.store');
   
   Route::get('stock_toko/listData', 'StockTokoController@listData')->name('stockToko.data');
   
   Route::resource('stockToko', 'StockTokoController');

   // laporan so
   Route::get('laporan/toko', 'LaporanSoTokoController@index')->name('laporanToko.index');
   Route::get('laporan/toko/listData', 'LaporanSoTokoController@listData')->name('laporanToko.data');

   // 
   Route::get('terima_toko/index', 'TerimaTokoController@index')->name('terimaToko.index');
   Route::get('terima_toko/detail/{id}', 'TerimaTokoController@detail')->name('terimatoko.detail');
   Route::post('terima_toko/create', 'TerimaTokoController@create_jurnal')->name('terimatoko.create_jurnal');
   Route::resource('terimatoko', 'TerimaTokoController');

   Route::get('transfer/detail/{id}', 'TransferController@detail');
   Route::get('transfer/toko', 'TransferController@toko')->name('terimatoko.index');
   Route::get('transfer/toko/{id}', 'TransferController@print_toko');
   Route::post('transfer/update/{id}','TransferController@api');

   Route::resource('transfer', 'transferController');  
 ////////////////
   Route::get('kirim_barang_toko_hold/index', 'KirimBarangHoldController@index')->name('kirim_hold.index');
   Route::get('kirim_barang_toko_hold/data', 'KirimBarangHoldController@listData')->name('kirim_hold.data');

   Route::get('kirim_barang_toko/data', 'KirimBarangTokoController@listData')->name('kirim_barang_toko.data');
   Route::get('kirim_barang_toko/{id}/tambah', 'KirimBarangTokoController@create');
   Route::get('kirim_barang_toko/{id}/lihat', 'KirimBarangTokoController@show');
   Route::get('kirim_barang_toko/{id}/poPDF', 'KirimBarangTokoController@cetak')->name('kirim_barang_toko.cetak');
   Route::resource('kirim_barang_toko', 'KirimBarangTokoController');   

   Route::get('kirim_barang_toko_detail/{id}/data', 'KirimBarangTokoDetailController@listData')->name('barang_toko_detail.data');
   Route::get('kirim_barang_toko_detail/continued/{id}', 'KirimBarangTokoDetailController@continued_hold')->name('barang_toko_detail.continued');
   Route::get('kirim_barang_toko_detail/update/{id}', 'KirimBarangTokoDetailController@update')->name('barang_toko_detail.update');
   Route::get('kirim_barang_toko_detail/keterangan/{id}', 'KirimBarangTokoDetailController@keterangan')->name('barang_toko_detail.update_keterangan');
   Route::delete('kirim_barang_toko_detail/destroy/{id}', 'KirimBarangTokoDetailController@destroy')->name('barang_toko_detail.destroy');
   Route::get('kirim_barang_toko_detail/loadform/{id}', 'KirimBarangTokoDetailController@loadForm')->name('barang_toko_detail.loadForm');
   Route::resource('kirim_barang_toko_detail', 'KirimBarangTokoDetailController');  
   ///////////////////////////////

   Route::get('stock_opname_toko/index', 'StockOpnameTokoController@index')->name('stock_opname_toko.index');
   Route::get('stock_opname_toko/data', 'StockOpnameTokoController@listData')->name('stock_opname_toko.data');
   Route::post('stock_opname_toko/proses', 'StockOpnameTokoController@proses')->name('stock_opname_toko.proses');
   Route::post('stock_opname_toko/import_excel', 'StockOpnameTokoController@import_excel')->name('stock_opname_toko.import_excel');
   Route::get('stock_opname_toko/export_excel', 'StockOpnameTokoController@export_excel')->name('stock_opname_toko.export_excel');

   Route::resource('stock_opname_toko', 'StockOpnameTokoController');
   
   Route::get('stok_opname_parsial_toko/index', 'StokOpnameParsialTokoController@index')->name('stok_opname_parsial_toko.index');
   Route::post('stok_opname_parsial_toko/store', 'StokOpnameParsialTokoController@store')->name('stok_opname_parsial_toko.store');

});


Route::group(['middleware' => ['web', 'cekuser:6' ]], function(){
   
   //pricing_kp
   Route::get('pricing_kp/index', 'PricingKPController@index')->name('pricing_kp.index');
   Route::get('pricing_kp/data', 'PricingKPController@listData')->name('pricing_kp.data');
   Route::get('pricing_kp/detail/{id}', 'PricingKPController@detail')->name('pricing_kp.detail');
   Route::get('pricing_kp/data_detail/{id}', 'PricingKPController@listDetail')->name('pricing_kp.data_detail');
   Route::post('pricing_kp/update_invoice/{id}', 'PricingKPController@update_invoice')->name('pricing_kp.update_invoice');
   Route::post('pricing_kp/harga_jual/{id}', 'PricingKPController@update_harga_jual')->name('pricing_kp.harga_jual');
   Route::post('pricing_kp/harga_ni/{id}', 'PricingKPController@update_harga_jual_ni')->name('pricing_kp.harga_ni');
   Route::post('pricing_kp/simpan/{id}', 'PricingKPController@simpan')->name('pricing_kp.simpan');
   Route::resource('pricing_ko', 'PricingKPController');

   // pricing produk existing
   Route::get('pricing/tambah', 'PricingController@tambah')->name('pricing.tambah');
   Route::post('pricing/add', 'PricingController@add')->name('pricing.add');
   Route::get('pricing/index', 'PricingController@index')->name('pricing.index');
   Route::get('pricing/data', 'PricingController@listData')->name('pricing.data');
   Route::get('pricing/edit/{id}', 'PricingController@edit')->name('pricing.edit');
   Route::post('pricing/update/', 'PricingController@update')->name('pricing.update');
   Route::get('pricing/promo/{id}', 'PricingController@tambah_promo')->name('pricing.promo');
   Route::post('pricing/update_promo', 'PricingController@update_promo')->name('pricing.update_promo');
   Route::put('pricing/update_margin/', 'PricingController@show')->name('pricing.margin');
   
   // pricing kompetitor
   Route::get('pricing_kompetitor/index', 'PricingKompetitorController@index')->name('pricing_kompetitor.index');
   Route::get('pricing_kompetitor/data', 'PricingKompetitorController@listData')->name('pricing_kompetitor.data');
   Route::get('pricing_kompetitor/edit/{id}', 'PricingKompetitorController@edit')->name('pricing_kompetitor.edit');
   Route::post('pricing_kompetitor/update/', 'PricingKompetitorController@update')->name('pricing_kompetitor.update');
   
});

Route::group(['middleware' => ['web', 'cekuser:7' ]], function(){

   Route::get('eod/index', 'EODController@index')->name('eod.index'); 
   Route::get('eod/store', 'EODController@store')->name('eod.store');   

   Route::get('user/data', 'UserController@listData')->name('user.data');
   Route::resource('user', 'UserController');
   
   Route::get('all_stok/index','AllStokController@index')->name('all_stok.index');
   Route::get('all_stok/data/{unit}', 'AllStokController@listData')->name('all_stok.data');
   route::get('all_stok/detail/{id}','AllStokController@detail')->name('all_stok.detail');
   Route::get('all_stok/delete/{id}', 'AllStokController@delete')->name('all_stok.delete');
   Route::post('all_stok/store', 'AllStokController@store')->name('all_stok.store');

});


Route::group(['middleware' => ['web', 'cekuser:8' ]], function(){

   // supplier
   Route::get('supplier/index', 'SupplierController@index')->name('supplier.index');
   Route::post('supplier/tambah', 'SupplierController@tambah')->name('supplier.tambah');
   Route::get('supplier/{id}/edit', 'SupplierController@edit')->name('supplier.edit');
   Route::post('supplier/update_supplier/{id}', 'SupplierController@update_supplier')->name('supplier.update_supplier');
   Route::get('supplier/data', 'SupplierController@listData')->name('supplier.data');
   Route::get('supplier/delete/{id}', 'SupplierController@delete')->name('supplier.delete');

   // produk
   Route::get('produk/index', 'ProdukController@index')->name('produk.index');
   Route::get('produk/data', 'ProdukController@listData')->name('produk.data');
   Route::post('produk/store', 'ProdukController@store')->name('produk.store');
   Route::get('produk/{id}/edit', 'ProdukController@edit')->name('produk.edit');
   Route::post('produk/update', 'ProdukController@update')->name('produk.update');
   Route::post('produk/hapus', 'ProdukController@deleteSelected');
   Route::post('produk/cetak', 'ProdukController@printBarcode');
   
   // kategori
   Route::get('kategori/index', 'KategoriController@index')->name('kategori.index');
   Route::post('kategori/store', 'KategoriController@store')->name('kategori.store');
   Route::get('kategori/edit/{id}', 'KategoriController@edit')->name('kategori.edit');
   Route::post('kategori/update/{id}', 'KategoriController@update')->name('kategori.update');
   Route::get('kategori/data', 'KategoriController@listData')->name('kategori.data');

   //invoice
   Route::get('invoice/index', 'InvoiceController@index')->name('invoice.index');
   Route::get('invoice/data', 'InvoiceController@listData')->name('invoice.data');
   Route::get('invoice/detail/{id}', 'InvoiceController@detail')->name('invoice.detail');
   Route::get('invoice/data/diskon/{id}', 'InvoiceController@listDiskonLainya')->name('invoice.listDiskonLainya');
   Route::get('invoice/data/detail/{id}', 'InvoiceController@listDetail')->name('invoice.listDetail');
   Route::get('invoice/data/spesial_diskon/{id}', 'InvoiceController@listSpesialDiskon')->name('invoice.listSpesial');
   Route::post('invoice/data/addspesial', 'InvoiceController@add_spesial_diskon')->name('invoice.addSpesial');
   Route::get('invoice/delete/spesial/{id}', 'InvoiceController@delete_spesial_diskon')->name('invoice.deleteSpesial');
   Route::post('invoice/update/spesial/{id}','InvoiceController@update_spesial_diskon')->name('invoice.updatespesial');
   Route::post('invoice/update/spesial_2/{id}','InvoiceController@update_spesial_diskon')->name('invoice.updatespesial_2');
   
   Route::post('invoice/update/invoice/{id}','InvoiceController@update_invoice')->name('invoice.updateinvoice');
   Route::post('invoice/update/regular_ppn/{id}','InvoiceController@update_regular_diskon_ppn')->name('invoice.updateregularppn');
   Route::post('invoice/update/regular/{id}','InvoiceController@update_regular_diskon')->name('invoice.updateregular');
   
   Route::post('invoice/add/diskon-lainya', 'InvoiceController@add_diskon_lainya')->name('invoice.addDiskonLainya');
   Route::get('invoice/delete/diskon/{id}', 'InvoiceController@delete_diskon_lainya')->name('invoice.deleteDiskonLainya');
   Route::get('invoice/proses/diskon/{id}', 'InvoiceController@perhitungan_diskon')->name('invoice.perhitungan');
   Route::post('invoice/simpan', 'InvoiceController@simpan')->name('invoice.simpan');
   Route::post('invoice/hitung/{id}', 'InvoiceController@hitung')->name('invoice.hitung');
   Route::resource('invoice', 'InvoiceController');
   
   // pembelian
   Route::get('pembelian/data', 'PembelianController@listData')->name('pembelian.data');
   Route::get('pembelian_detail/{id}/update_harga', 'PembelianDetailController@update_harga')->name('pembelian_detail.update_harga');
   Route::get('pembelian/{id}/tambah', 'PembelianController@create');
   Route::get('pembelian/{id}/lihat', 'PembelianController@show');
   Route::get('pembelian/{id}/poPDF', 'PembelianController@cetak');
   Route::resource('pembelian', 'PembelianController');   

   Route::get('pembelian_detail/{id}/data', 'PembelianDetailController@listData')->name('pembelian_detail.data');
   Route::get('pembelian_detail/loadform/{diskon}/{total}', 'PembelianDetailController@loadForm');
   Route::resource('pembelian_detail', 'PembelianDetailController');
   
});



Route::group(['middleware' => ['web', 'cekuser:9' ]], function(){
   
   Route::get('selisih_kirim_barang/index', 'SelisihKirimBarangController@index')->name('selisih_kirim_barang.index');
   Route::get('selisih_kirim_barang/data', 'SelisihKirimBarangController@listData')->name('selisih_kirim_barang.data');


   Route::get('approve_stok_opname_parsial_gudang/index', 'ApproveStokOpnameParsialGudangController@index')->name('approve_stok_opname_parsial_gudang.index');
   Route::get('approve_stok_opname_parsial_gudang/data', 'ApproveStokOpnameParsialGudangController@listData')->name('approve_stok_opname_parsial_gudang.data');
   Route::post('approve_stok_opname_parsial_gudang/store', 'ApproveStokOpnameParsialGudangController@store')->name('approve_stok_opname_parsial_gudang.store');


   // controller menu jurnal di user kp
   Route::get('jurnal_umum_kp/index', 'JurnalUmumKpController@index')->name('jurnal_umum_kp.index');
   Route::post('jurnal_umum_kp/create','JurnalUmumKpController@create')->name('jurnal_umum_kp.create');
   Route::get('jurnal_umum_kp/destroy/{id}', 'JurnalUmumKpController@destroy')->name('jurnal_umum_kp.destroy');
   Route::get('jurnal_umum_kp/approve', 'JurnalUmumKpController@approve')->name('jurnal_umum_kp.approve');
   Route::post('jurnal_umum_kp/autocomplete', 'JurnalUmumKpController@autocomplete')->name('jurnal_umum_kp.autocomplete');

   // approval stok opname
   Route::get('approve_kp/index', 'ApprovalKpController@index')->name('approve_kp.index');
   Route::put('approve_kp/store', 'ApprovalKpController@store')->name('approve_kp.store');
   Route::get('approve_kp/data/{id}', 'ApprovalKpController@listData')->name('approve_kp.data');
   Route::resource('approve_kp', 'ApprovalKpController');

   // report jatpo
   Route::get('report_jatpo/index','ReportJatuhTempoController@index')->name('report_jatpo.index');
   Route::get('report_jatpo/data','ReportJatuhTempoController@listData')->name('report_jatpo.data');
   Route::get('report_jatpo/{id}/update','ReportJatuhTempoController@update')->name('report_jatpo.update');

   // approval pricing
   Route::get('approve_pricing/index', 'ApprovalPricingController@index')->name('approve_pricing.index');
   Route::get('approve_pricing/data', 'ApprovalPricingController@listData')->name('approve_pricing.data');
   Route::get('approve_pricing/approve/{id}', 'ApprovalPricingController@approve')->name('approve_pricing.approve');
   Route::get('approve_pricing/reject/{id}', 'ApprovalPricingController@reject')->name('approve_pricing.reject');

});


Route::group(['middleware' => ['web', 'cekuser:10' ]], function(){

   // approval terima po
   Route::get('approve_terima_po/index', 'ApprovalTerimaPoController@index')->name('approve_terima_po.index');
   Route::get('approve_terima_po/listData', 'ApprovalTerimaPoController@listData')->name('approve_terima_po.data');
   Route::get('approve_terima_po/detail/{id}', 'ApprovalTerimaPoController@detail')->name('approve_terima_po.detail');
   Route::get('approve_terima_po/listDetail/{id}', 'ApprovalTerimaPoController@listDetail')->name('approve_terima_po.listDetail');
   Route::get('approve_terima_po/approve/{id}', 'ApprovalTerimaPoController@approve')->name('approve_terima_po.approve');

   // kartu stok
   Route::get('kartu_stok/index', 'KartuStokController@index')->name('kartu_stok.index');
   Route::get('kartu_stok/data', 'KartuStokController@listData')->name('kartu_stok.data');

   // approval kirim barang gudang
   Route::get('approve_kirim_barang/index', 'ApproveKirimBarangGudangController@index')->name('approve_kirim_barang.index');
   Route::get('approve_kirim_barang/data','ApproveKirimBarangGudangController@listData')->name('approve_kirim_barang.data');
   Route::get('approve_kirim_barang/detail/{id}','ApproveKirimBarangGudangController@detail')->name('approve_kirim_barang.detail');
   Route::get('approve_kirim_barang/listDetail/{id}', 'ApproveKirimBarangGudangController@listDetail')->name('approve_kirim_barang.listDetail');
   Route::post('approve_kirim_barang/approve', 'ApproveKirimBarangGudangController@approve')->name('approve_kirim_barang.approve');
   Route::get('approve_kirim_barang/reject/{id}', 'ApproveKirimBarangGudangController@reject')->name('approve_kirim_barang.reject');
   
   // approval retur supplier
   Route::get('approve_retur_supplier/index', 'ApproveReturSupplierController@index')->name('approve_retur_supplier.index');
   Route::get('approve_retur_supplier/data', 'ApproveReturSupplierController@listData')->name('approve_retur_supplier.data');
   Route::get('approve_retur_supplier/show/{id}', 'ApproveReturSupplierController@show')->name('approve_retur_supplier.show');
   Route::get('approve_retur_supplier/reject/{id}', 'ApproveReturSupplierController@reject')->name('approve_retur_supplier.reject');
   Route::get('approve_retur_supplier/approve/{id}', 'ApproveReturSupplierController@approve')->name('approve_retur_supplier.approve'); 
   
   // approval retur supplier
   Route::get('approve_retur_tukar_barang/index', 'ApproveReturTukarBarangController@index')->name('approve_retur_tukar_barang.index');
   Route::get('approve_retur_tukar_barang/data', 'ApproveReturTukarBarangController@listData')->name('approve_retur_tukar_barang.data');
   Route::get('approve_retur_tukar_barang/show/{id}', 'ApproveReturTukarBarangController@show')->name('approve_retur_tukar_barang.show');
   Route::get('approve_retur_tukar_barang/reject/{id}', 'ApproveReturTukarBarangController@reject')->name('approve_retur_tukar_barang.reject');
   Route::get('approve_retur_tukar_barang/approve/{id}', 'ApproveReturTukarBarangController@approve')->name('approve_retur_tukar_barang.approve');  

   
   // approval retur supplier
   Route::get('approve_terima_retur_tukar_barang/index', 'ApproveTerimaReturTukarBarangController@index')->name('approve_terima_retur_tukar_barang.index');
   Route::get('approve_terima_retur_tukar_barang/data', 'ApproveTerimaReturTukarBarangController@listData')->name('approve_terima_retur_tukar_barang.data');
   Route::get('approve_terima_retur_tukar_barang/show/{id}', 'ApproveTerimaReturTukarBarangController@show')->name('approve_terima_retur_tukar_barang.show');
   Route::get('approve_terima_retur_tukar_barang/reject/{id}', 'ApproveTerimaReturTukarBarangController@reject')->name('approve_terima_retur_tukar_barang.reject');
   Route::get('approve_terima_retur_tukar_barang/approve/{id}', 'ApproveTerimaReturTukarBarangController@approve')->name('approve_terima_retur_tukar_barang.approve');  


   Route::get('approve_stok_opname_parsial_toko/index', 'ApproveStokOpnameParsialTokoController@index')->name('approve_stok_opname_parsial_toko.index');
   Route::get('approve_stok_opname_parsial_toko/data', 'ApproveStokOpnameParsialTokoController@listData')->name('approve_stok_opname_parsial_toko.data');
   Route::post('approve_stok_opname_parsial_toko/store', 'ApproveStokOpnameParsialTokoController@store')->name('approve_stok_opname_parsial_toko.store');

   // approval_stok_opname_toko
   Route::get('approve_gudang/index', 'ApprovalGudangController@index')->name('approve_gudang.index');
   Route::put('approve_gudang/store', 'ApprovalGudangController@store')->name('approve_gudang.store');
   Route::get('approve_gudang/data/{id}', 'ApprovalGudangController@listData')->name('approve_gudang.data');
   Route::resource('approve_gudang', 'ApprovalGudangController');

   Route::get('stok_wo/index', 'StockWriteOffController@index')->name('stok_wo.index');
   Route::get('stok_wo/data', 'StockWriteOffController@listData')->name('stok_wo.data');

   Route::get('approve_terima_retur_toko/index', 'ApprovalTerimaReturController@index')->name('approve_terima_retur_toko.index');
   Route::get('approve_terima_retur_toko/data','ApprovalTerimaReturController@listData')->name('approve_terima_retur_toko.data');
   Route::get('approve_terima_retur_toko/detail/{id}','ApprovalTerimaReturController@detail')->name('approve_terima_retur_toko.detail');
   Route::get('approve_terima_retur_toko/listDetail/{id}','ApprovalTerimaReturController@listDetail')->name('approve_terima_retur_toko.listDetail');
   Route::post('approve_terima_retur_toko/approve', 'ApprovalTerimaReturController@approve')->name('approve_terima_retur_toko.approve');

   
   // approval kirim barang toko
   Route::get('approve_kirim_barang_toko/index', 'ApproveKirimBarangTokoController@index')->name('approve_kirim_barang_toko.index');
   Route::get('approve_kirim_barang_toko/data','ApproveKirimBarangTokoController@listData')->name('approve_kirim_barang_toko.data');
   Route::get('approve_kirim_barang_toko/detail/{id}','ApproveKirimBarangTokoController@detail')->name('approve_kirim_barang_toko.detail');
   Route::get('approve_kirim_barang_toko/listDetail/{id}', 'ApproveKirimBarangTokoController@listDetail')->name('approve_kirim_barang_toko.listDetail');
   Route::post('approve_kirim_barang_toko/approve', 'ApproveKirimBarangTokoController@approve')->name('approve_kirim_barang_toko.approve');
   Route::get('approve_kirim_barang_toko/reject/{id}', 'ApproveKirimBarangTokoController@reject')->name('approve_kirim_barang_toko.reject');
   
   // approval terima tok
   Route::get('approve_terima_toko/index', 'ApproveTerimaTokoController@index')->name('approve_terima_toko.index');
   Route::get('approve_terima_toko/data','ApproveTerimaTokoController@listData')->name('approve_terima_toko.data');
   Route::get('approve_terima_toko/detail/{id}','ApproveTerimaTokoController@detail')->name('approve_terima_toko.detail');
   Route::get('approve_terima_toko/listDetail/{id}','ApproveTerimaTokoController@listDetail')->name('approve_terima_toko.listDetail');
   Route::post('approve_terima_toko/approve', 'ApproveTerimaTokoController@approve')->name('approve_terima_toko.approve');
   Route::get('approve_terima_toko/reject/{id}', 'ApproveTerimaTokoController@reject')->name('approve_terima_toko.reject');



});
