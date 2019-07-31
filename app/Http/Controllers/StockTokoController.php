<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;
use App\ProdukDetail;
use App\Produk;

class StockTokoController extends Controller
{
    public function index(){
        $produk = Produk::where('unit', '=',  Auth::user()->unit)
                        ->get();
        return view('toko/stock',['produk'=>$produk]);
    }

    public function detail($id){
        $produk = ProdukDetail::where('kode_produk',$id)
                        ->where('unit', '=',  Auth::user()->unit)
                        ->get();
        return view('toko/detail_stock',['produk'=>$produk]);
    }
}
