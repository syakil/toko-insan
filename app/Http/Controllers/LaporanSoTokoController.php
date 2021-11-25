<?php

namespace App\Http\Controllers;
use DB;

use Auth;
use Illuminate\Http\Request;

class LaporanSoTokoController extends Controller
{
    public function index(){
        return view('toko/laporan');
    }

    public function ListData(){
        $produk = DB::table('produk_detail_temporary')
        ->select('kode_produk','stok_detail','nama_produk','expired_date')
        ->where('unit', Auth::user()->unit)
        ->where('expired_date','<',date('Y-m-d'))
        ->where('updated_at','>',0)
        ->where('stok_detail','>',0)
        ->get();

        $no = 0;
        $data = array();
        foreach($produk as $list){
        $no ++;
        $row = array();
        $row[] = $no;
        $row[] = $list->kode_produk;
        $row[] = $list->nama_produk;
        $row[] = $list->stok_detail;
        $row[] = $list->expired_date;
        $data[] = $row;
        }
        //   dd($data);
        $output = array("data" => $data);
        return response()->json($output);
    }
}
