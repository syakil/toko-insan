<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
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

    public function update_stock(Request $request,$id){

        $detail = ProdukDetail::where('id_produk_detail',$id)->first();
        $detail->stok_detail = $request->value;
        $detail->update();
                
        $stok = ProdukDetail::where('kode_produk',$detail->kode_produk)
                            ->where('unit',$detail->unit)
                            ->sum('stok_detail');
        $produk = Produk::where('kode_produk',$detail->kode_produk)
                        ->where('unit',$detail->unit)->first();
        $produk->stok = $stok;
        $produk->update();

    }

    
    public function update_expired_stock(Request $request,$id){

        $detail = ProdukDetail::where('id_produk_detail',$id);
        $detail->expired_date = $request->value;
        $detail->update();

    }

}
