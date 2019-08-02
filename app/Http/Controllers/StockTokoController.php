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
                        
        $nama = Produk::where('kode_produk',$id)->first();
        return view('toko/detail_stock',['produk'=>$produk,'nama'=>$nama]);
    }

    public function store(Request $request){
        $unit = Auth::user()->unit;
        // dd($unit);
        $produk_detail = new ProdukDetail;
        $produk_detail->kode_produk = $request->barcode;
        $produk_detail->nama_produk = $request->nama;
        $produk_detail->unit = Auth::user()->unit;
        $produk_detail->stok_detail = $request->stok;
        $produk_detail->expired_date = $request->tanggal;
        $produk_detail->save();

        $stok = ProdukDetail::where('kode_produk',$request->barcode)
                        ->where('unit',Auth::user()->unit)
                        ->sum('stok_detail');

        $update_stok = Produk::where('kode_produk',$request->barcode)
                            ->where('unit',Auth::user()->unit)
                            ->first();
        $update_stok->stok = $stok;
        $update_stok->update();
    
        return redirect()->route('stockToko.detail', ['id' => $request->barcode]);
    }
}
