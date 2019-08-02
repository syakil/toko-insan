<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Produk;
use App\ProdukDetail;
use App\ProdukTemporary;
use App\ProdukDetailTemporary;

class ApprovalKpController extends Controller
{
    public function index(){
        $produk = ProdukDetailTemporary::where('produk_detail_temporary.unit', '=','WH01')
                        ->get();
        $no=1;
        return view('approve_kp/index',['produk'=>$produk,'no'=>$no]);
    }

    public function store(Request $request){
        $data = $request->kode;
        // dd($data);
        foreach ($data as $kode ) {
            $produk_detail = ProdukDetailTemporary::where('id_produk_detail',$kode)->get();
            // dd($produk_detail);
            
            foreach ($produk_detail as $prod ) {
                
            // dd($prod->nama_produk);
                
                $produk_update = new ProdukDetail;
                $produk_update->kode_produk = $prod->kode_produk;
                $produk_update->id_kategori = $prod->id_kategori;
                $produk_update->nama_produk = $prod->nama_produk;
                $produk_update->stok_detail = $prod->stok_detail;
                $produk_update->isi_pack_detail = $prod->isi_pack_detail;
                $produk_update->satuan = $prod->satuan;
                $produk_update->harga_beli = $prod->harga_beli;
                $produk_update->expired_date = $prod->expired_date;
                $produk_update->unit = $prod->unit;
                // $prod_update->status = $prod->status;
                $produk_update->save();

                $stok_produk_detail = ProdukDetail::where('kode_produk',$prod->kode_produk)
                                                ->where('unit',$prod->unit)
                                                ->sum('stok_detail');
                $stok_inti = Produk::where('kode_produk',$prod->kode_produk)
                                    ->where('unit',$prod->unit)
                                    ->first();
                $stok_inti->stok = $stok_produk_detail;
                $stok_inti->update();

                $prod->delete();
            }
        }
        return back();
    }
}
