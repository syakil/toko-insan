<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Produk;
use App\PembelianTemporary;
use App\PembelianTemporaryDetail;
use DB;
use Auth;
use App\ProdukDetail;

class ApprovalPricingController extends Controller{

    public function index(){

        return view('approve_pricing.index');

    }

    public function listData(){

        $produk_detail = ProdukDetail::where('status',3)->leftJoin('branch','branch.kode_toko','produk_detail.unit')->get();

        $no = 1;
        $data = array();
        
        foreach($produk_detail as $list){
            
            $row = array();
            
            $produk = Produk::where('kode_produk',$list->kode_produk)->where('unit',$list->unit)->first();
if($produk == null){dd($list->kode_produk);}
            $harga_pasar = array($produk->harga_indo,$produk->harga_alfa,$produk->harga_grosir,$produk->harga_olshop);

            if($produk->harga_jual == 0){
           
              $kenaikan = 100;
            
            }else{

               $kenaikan = ($list->harga_jual_insan - $produk->harga_jual)/$produk->harga_jual * 100;
            
            }
            
            $margin_baru = round(($list->harga_jual_insan - $list->harga_beli)/$list->harga_jual_insan*100);

            $row [] = $no++;
            $row [] = $list->nama_toko;
            $row [] = $list->no_faktur;
            $row [] = $list->kode_produk;
            $row [] = $list->nama_produk ;
            $row [] = $list->harga_beli;
            $row [] = number_format($produk->harga_jual);            
            $row [] = number_format($produk->harga_jual - $list->harga_beli);
            $row [] = number_format($list->harga_jual_insan);
            $row [] = number_format($list->harga_jual_insan - $list->harga_beli) . '<strong>('. $margin_baru .'%)</strong>';
            if ($kenaikan < 0 ) {
                $row[] = "<i><font color='red'><i class='fa fa-arrow-down'></i>".number_format($kenaikan)."%</font></i>";
            }else {
                $row[] = "<i><font color='green'><i class='fa fa-arrow-up'></i>".number_format($kenaikan)."%</font></i>";
            }
            $row [] = number_format(min($harga_pasar));
            $row[] = "<div class='btn-group'>
            <a href='".route('approve_pricing.approve',$list->id_produk_detail)."' class='btn btn-success btn-sm'>Approve</a>
            <a href='".route('approve_pricing.reject',$list->id_produk_detail)."' class='btn btn-danger btn-sm'>Tolak</a>
            </div>";
            $data [] = $row; 

        }

        $output = array("data" => $data);
        return response()->json($output);

    }


    public function approve($id){

        ProdukDetail::where('id_produk_detail',$id)->update(['status'=>2]);

        return back()->with(['success' => 'Harga Berhasil Di Setujui !']);

    }

    public function reject($id){

        $produk = ProdukDetail::where('id_produk_detail',$id)->first();
        $produk->status = 1;
        $produk->update();

        $pembelian_tempo = PembelianTemporary::where('id_pembelian',$produk->no_faktur)->update(['status'=>3]);
        $pembelian_detail = PembelianTemporaryDetail::where('id_pembelian',$produk->no_faktur)->where('kode_produk',$produk->kode_produk)->first();
        $pembelian_detail->status = 1;
        $pembelian_detail->update();
        
        return back()->with(['success' => 'Harga Berhasil Di Tolak !']);

    }
}
