<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Produk;
use App\KartuStok;
use DB;
use Auth;


class KartuStokTokoController extends Controller
{
    public function index(){

        return view('kartu_stok_toko.index');

    }

    public function listData(){

        $kartu = DB::table('kartu_stok')->select(DB::raw('
            produk.kode_produk,
            nama_produk,
            SUM(IF(kartu_stok.status="stok_awal",masuk,0)) AS stok_awal,
            SUM(IF(kartu_stok.status="stok_tambah",masuk,0)) AS stok_tambah,            
            SUM(IF(kartu_stok.status="terima_toko",masuk,0)) AS terima_toko,
            SUM(IF(kartu_stok.status="terima_selisih_retur_toko",masuk,0)) AS terima_selisih_retur_toko,
            SUM(IF(kartu_stok.status="stok_kurang",keluar,0)) AS stok_kurang,
            SUM(IF(kartu_stok.status="kirim_barang",keluar,0)) AS kirim_barang,
            SUM(IF(kartu_stok.status="penjualan",keluar,0)) AS penjualan
        '))
        ->groupBy('kartu_stok.kode_produk')
        ->leftJoin('produk','produk.kode_produk','kartu_stok.kode_produk')
        ->where('kartu_stok.unit',Auth::user()->unit)
        ->where('produk.unit',Auth::user()->unit)
        ->get();

        
        $no = 0;
        $data = array();
        
        foreach($kartu as $list){
            
            $stok_akhir = $list->stok_awal + $list->terima_toko + $list->stok_tambah + $list->terima_selisih_retur_toko - $list->kirim_barang - $list->stok_kurang ;

            $no ++;
            $row = array();
            $row[] = $list->kode_produk;
            $row[] = $list->nama_produk;            
            $row[] = $list->stok_awal;
            $row[] = $list->terima_toko;
            $row[] = $list->terima_selisih_retur_toko;
            $row[] = $list->stok_tambah;
            $row[] = $list->stok_kurang;
            $row[] = $list->kirim_barang;
            $row[] = $stok_akhir;
            $data[] = $row;
        }
        
        $output = array("data" => $data);
        return response()->json($output);    

    }

}
