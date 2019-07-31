<?php

use Illuminate\Http\Request;
namespace App\Http\Controllers;
use Auth;
use App\ProdukDetail;
use App\Produk;
use App\Kategori;
use Yajra\Datatables\Datatables;
use PDF;

class StockController extends Controller
{
    public function index(){
        $produk = Produk::where('unit', '=',  Auth::user()->unit)
                        ->get();
        return view('gudang/stock',['produk'=>$produk]);
    }

    public function detail($id){
        $produk = ProdukDetail::where('kode_produk',$id)
                        ->where('unit', '=',  Auth::user()->unit)
                        ->get();
        return view('gudang/detail_stock',['produk'=>$produk]);
    }
}
