<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;
use DB;
use App\SelisihKirimBarang;
use App\Produk;



class SelisihKirimBarangController extends Controller
{
    public function index(){

        return view('selisih_kirim_barang.index');

    }

    public function listData(){

        $produk = SelisihKirimBarang::select('selisih_kirim_barang.*','produk.nama_produk','branch.nama_toko')
        ->leftJoin('produk','produk.kode_produk','selisih_kirim_barang.kode_produk')
        ->leftJoin('branch','branch.kode_toko','selisih_kirim_barang.unit')->where('produk.unit',3000)->get();

        $no = 1;
        $data = array();
        
        foreach($produk as $list){

            $row = array();
            $row[] = $no++;
            $row[] = tanggal_indonesia($list->created_at);
            $row[] = $list->id_pembelian;
            $row[] = $list->nama_toko;
            $row[] = $list->kode_produk;
            $row[] = $list->nama_produk;            
            $row[] = $list->jumlah;
            $row[] = $list->keterangan;
            $data[] = $row;
        }
        
        $output = array("data" => $data);
        return response()->json($output);    


    }
}
