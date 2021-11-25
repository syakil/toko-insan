<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\ProdukWriteOff;
use DB;
use Auth;


class StockWriteOffController extends Controller{
    
    public function index(){

        return view('stok_wo.index');

    }


    public function listData(){

        $produk = ProdukWriteOff::where('unit',Auth::user()->unit)->get();


        $data = array();
        $no = 0;
        
        foreach ($produk as $value) {
            
            $row = array();
            $no++;
            $row[] = $no;
            $row[] = $value->tanggal_input;
            $row[] = $value->kode_produk;
            $row[] = $value->nama_produk;
            $row[] = $value->stok;
            
            $data[] = $row;

        }

        $output = array("data" => $data);
        return response()->json($output);
    
    }


}
