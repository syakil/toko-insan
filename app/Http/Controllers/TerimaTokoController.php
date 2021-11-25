<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Kirim;
use App\KirimDetail;
use App\TabelTransaksi;
use App\ProdukDetail;
use App\KartuStok;
use App\Produk;
use Illuminate\Support\Facades\DB;
use PDF;
use Auth;
use App\Branch;

class TerimaTokoController extends Controller
{
    public function index(){

        $terima = Kirim::where('id_supplier',Auth::user()->unit)
                        ->where('status_kirim','transfer')
                        ->where('status',1)
                        ->get();
        $no = 1;
        return view ('terima_toko.index',['terima'=>$terima,'no'=>$no]);
    
    }

    public function detail($id){

        $detail = KirimDetail::where('id_pembelian',$id)
                            ->join('produk','kirim_barang_detail.kode_produk','=','produk.kode_produk')
                            ->where('unit',Auth::user()->unit)
                            ->get();
        $nopo = Kirim::where('id_pembelian',$id)->get();
        $nomer = 1;
        return view('terima_toko.detail',['kirim'=>$detail,'nomer'=>$nomer,'nopo'=>$nopo]);
    }

    public function update_jumlah_terima(Request $request,$id){

        $kirim_detail = KirimDetail::where('id_pembelian_detail',$id)->first();
        $kirim_detail->update(['jumlah_terima'=>$request->value]);
        
        $total = KirimDetail::where('id_pembelian',$kirim_detail->id_pembelian)->sum('jumlah_terima');

        $kirim = Kirim::where('id_pembelian',$kirim_detail->id_pembelian)->first();
        $kirim->total_terima = $total;
        $kirim->update();


        
        $produk_detail = KirimDetail::where('id_pembelian_detail',$id)
                                        ->get();

        // ubah sub_total
        foreach($produk_detail as $detail){

            // harga sub total kirim_barang_detail
            $sub_total = $detail->harga_beli * $request->value;
            $sub_total_margin = $detail->harga_jual * $request->value;
            $produk_sub_total = KirimDetail::where('id_pembelian_detail',$id)->first();
            $produk_sub_total->sub_total_terima = $sub_total;
            $produk_sub_total->sub_total_margin_terima = $sub_total_margin;
            $produk_sub_total->update();

        }

        
        $total_harga_terima = KirimDetail::where('id_pembelian',$kirim_detail->id_pembelian)->sum('sub_total_terima');
        $total_terima = KirimDetail::where('id_pembelian',$kirim_detail->id_pembelian)->sum('jumlah_terima');
        $total_margin_terima = KirimDetail::where('id_pembelian',$kirim_detail->id_pembelian)->sum('sub_total_margin_terima');

        $kirim = Kirim::where('id_pembelian',$kirim_detail->id_pembelian)->first();
        $kirim->total_harga_terima = $total_harga_terima;
        $kirim->total_terima = $total_terima;
        $kirim->total_margin_terima = $total_margin_terima;
        $kirim->update();

    }

    
    public function update_expired_date(Request $request,$id){

        $detail = KirimDetail::where('id_pembelian_detail',$id);
        $detail->update(['expired_date'=>$request->value]);

    }

    public function create_jurnal(Request $request){
        // menampung id_pembelian yang di checklist
        $data = $request->check;

        try{

            DB::beginTransaction();        
                           
     
            // insert_jurnal

            foreach($data as $id){      
                $kirim_status = Kirim::where('id_pembelian',$id)->update(['status'=>'approval_terima']);
            }

            DB::commit();

        }catch(\Exception $e){
         
            DB::rollback();
            return back()->with(['error' => $e->getmessage()]);
    
        }
            
        return redirect('terima_toko/index')->with(['success' => 'Transaksi Berhasil']);
    }
}


