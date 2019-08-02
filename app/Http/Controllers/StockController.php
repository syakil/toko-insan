<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Auth;
use App\ProdukDetailTemporary;
use App\ProdukTemporary;
use App\Kategori;
use Yajra\Datatables\Datatables;
use PDF;

class StockController extends Controller
{
    public function index(){
        $produk = ProdukTemporary::where('unit', '=',  Auth::user()->unit)
                        ->get();
        return view('gudang/stock',['produk'=>$produk]);
    }

    public function detail($id){
        $produk = ProdukDetailTemporary::where('kode_produk',$id)
                        ->where('unit', '=',  Auth::user()->unit)
                        ->get();
        $nama = ProdukTemporary::where('kode_produk',$id)->first();
        return view('gudang/detail_stock',['produk'=>$produk,'nama'=>$nama]);
    }

    public function update_stock(Request $request,$id){

        $detail = ProdukDetailTemporary::where('id_produk_detail',$id)->first();
        $detail->stok_detail = $request->value;
        $detail->update();
                
        $stok = ProdukDetailTemporary::where('kode_produk',$detail->kode_produk)
                            ->where('unit',$detail->unit)
                            ->sum('stok_detail');
        $produk = ProdukTemporary::where('kode_produk',$detail->kode_produk)
                        ->where('unit',$detail->unit)->first();
        $produk->stok_temporary = $stok;
        $produk->update();

    }

    
    public function update_expired_stock(Request $request,$id){

        $detail = ProdukDetailTemporary::where('id_produk_detail',$id);
        $detail->expired_date = $request->value;
        $detail->update();

    }

    public function store(Request $request){
        $unit = Auth::user()->unit;
        // dd($unit);
        $produk_detail = new ProdukDetailTemporary;
        $produk_detail->kode_produk = $request->barcode;
        $produk_detail->nama_produk = $request->nama;
        $produk_detail->unit = Auth::user()->unit;
        $produk_detail->stok_detail = $request->stok;
        $produk_detail->expired_date = $request->tanggal;
        $produk_detail->save();

        $stok = ProdukDetailTemporary::where('kode_produk',$request->barcode)
                        ->where('unit',Auth::user()->unit)
                        ->sum('stok_detail');

        $update_stok = ProdukTemporary::where('kode_produk',$request->barcode)
                            ->where('unit',Auth::user()->unit)
                            ->first();
        $update_stok->stok_temporary = $stok;
        $update_stok->update();
    
        return redirect()->route('stock.detail', ['id' => $request->barcode]);
    }

}
