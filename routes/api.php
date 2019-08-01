<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});



// update stok gudang di menu gudang
Route::post('/update_stock/{id}', 'StockController@update_stock')->name('updateStock.stock');
Route::post('/update_expired_stock/{id}', 'StockController@update_expired_stock')->name('updateStock.expired_date');
Route::resource('updatepo', 'TerimaController');

// update pembelian di menu gudang
Route::post('/update_jumlah/{id}', 'TerimaController@update_jumlah_terima')->name('updatepo.jumlah_terima');
Route::post('/update_expired/{id}', 'TerimaController@update_expired_date')->name('updatepo.expired_date');
Route::resource('updatepo', 'TerimaController');

// update pembelian di menu admin
Route::post('/ubah_harga/{id}', 'PembelianAdminController@ubah_harga')->name('ubah.harga');
Route::post('/ubah_jatuh_tempo/{id}', 'PembelianAdminController@ubah_jatuh_tempo')->name('ubah.jatuh_tempo');
Route::post('/ubah_tipe_bayar/{id}', 'PembelianAdminController@ubah_tipe_bayar')->name('ubah.tipe_bayar');
Route::resource('ubah', 'TerimaController');

// update terima toko
Route::post('/update_jumlah_toko/{id}', 'TerimaTokoController@update_jumlah_terima')->name('updatetoko.jumlah_terima');
Route::post('/update_expired_toko/{id}', 'TerimaTokoController@update_expired_date')->name('updatetoko.expired_date');
Route::resource('updatetoko', 'TerimaTokoController');

// update debet kredit jurnal
Route::post('/update_jumlah_debit/{id}', 'JurnalUmumAdminController@update_debet')->name('updatejurnal.debet');
Route::post('/update_jumlah_kredit/{id}', 'JurnalUmumAdminController@update_kredit')->name('updatejurnal.kredit');
Route::resource('updatejurnal', 'JurnalUmumController');

// update retur di gudang
Route::post('/update_jumlah_retur/{id}', 'ReturGudangController@update_jumlah_terima')->name('updateRetur.jumlah_terima');
Route::post('/update_expired_retur/{id}', 'ReturGudangController@update_expired_date')->name('updateRetur.expired_date');
Route::resource('updateRetur', 'ReturGudangController');
