<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Produk;
use App\KartuStok;
use DB;
use Auth;


class KartuStokController extends Controller{

    public function index(){

        return view('kartu_stok.index');

    }

    public function listData(){

        $kartu = DB::table('kartu_stok')->select(DB::raw('
            produk.kode_produk,
            nama_produk,
            SUM(IF(kartu_stok.status="stok_awal",masuk,0)) AS stok_awal,
            SUM(IF(kartu_stok.status="pembelian",masuk,0)) AS pembelian,
            SUM(IF(kartu_stok.status="terima_gudang",masuk,0)) AS terima_gudang,
            SUM(IF(kartu_stok.status="terima_retur_toko",masuk,0)) AS terima_barang_retur,
            SUM(IF(kartu_stok.status="kirim_barang",keluar,0)) AS kirim_barang,
            SUM(IF(kartu_stok.status="write_off",keluar,0)) AS write_off
        '))

        ->groupBy('kartu_stok.kode_produk')
        ->leftJoin('produk','produk.kode_produk','kartu_stok.kode_produk')
        ->where('kartu_stok.unit',Auth::user()->unit)
        ->where('produk.unit',Auth::user()->unit)
        ->get();

        
        $no = 0;
        $data = array();
        
        foreach($kartu as $list){
            
            $stok_akhir = $list->stok_awal + $list->pembelian + $list->terima_barang_retur + $list->terima_gudang - $list->kirim_barang - $list->write_off;

            $no ++;
            $row = array();
            $row[] = $no;
            $row[] = $list->kode_produk;
            $row[] = $list->nama_produk;            
            $row[] = $list->stok_awal;
            $row[] = $list->pembelian;
            $row[] = $list->terima_gudang;
            $row[] = $list->terima_barang_retur;
            $row[] = $list->kirim_barang;
            $row[] = $list->write_off;
            $row[] = $stok_akhir;
            $data[] = $row;
        }
        
        $output = array("data" => $data);
        return response()->json($output);    

    }

}
