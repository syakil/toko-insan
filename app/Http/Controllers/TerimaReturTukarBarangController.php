<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Kirim;
use App\KirimDetail;
use Auth;
use App\TabelTransaksi;
use App\ProdukWriteOff;
use Ramsey\Uuid\Uuid;
use PDF;
use DB;
use App\Produk;
use App\ProdukDetail;
use App\Branch;

class TerimaReturTukarBarangController extends Controller{
    
    public function index(){
        // menampilkan data Kirim where status app = gudang
        // barang dari gudang ke toko
        $transfer = Kirim::leftJoin('branch','kirim_barang.kode_gudang','=','branch.kode_toko')
                            ->where('kirim_barang.kode_gudang',Auth::user()->unit)
                            ->where('status_kirim','tukar_barang')
                            ->where('kirim_barang.status',1)
                            ->where('tujuan','gudang')
                            ->get();
        $no = 1;
        return view('terima_retur_tukar_barang/index',['transfer'=>$transfer,'no'=>$no]);
    }

    public function show($id){
        // mengambil data detail_Kirim berdasar id_Kirim yang ingin dilihat
        $detail = KirimDetail::leftJoin('produk','kirim_barang_detail.kode_produk','=','produk.kode_produk')
                                    ->leftJoin('status_kirim','status_kirim.id_status','kirim_barang_detail.keterangan')
                                    ->where('id_pembelian',$id)
                                    ->where('produk.unit',Auth::user()->unit)
                                    ->get();
        // mengambil no surat jalan berdsar id_Kirim yang dipilih
        $no_surat = Kirim::where('id_pembelian',$id)->first();
        $nomer = 1;
        return view('terima_retur_tukar_barang/detail',['pembelian'=>$detail,'nomer'=>$nomer,'no_surat'=>$no_surat]);
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

    
    public function store(Request $request){

        try{

            DB::beginTransaction();
            
            $id = $request->id;
            
            $cek = KirimDetail::where('id_pembelian',$id)->get();

            foreach ($cek as $value) {
                
                if ($value->jumlah != $value->jumlah_terima) {
                    return back()->with(['error' => 'Jumlah Barang Harus Sesuai !']);
                }

            }
            
            
            $total_harga_terima = KirimDetail::where('id_pembelian',$id)->sum('sub_total_terima');
            $total_terima = KirimDetail::where('id_pembelian',$id)->sum('jumlah_terima');
            $total_margin_terima = KirimDetail::where('id_pembelian',$id)->sum('sub_total_margin_terima');

            $kirim = Kirim::where('id_pembelian',$id)->first();
            $kirim->total_harga_terima = $total_harga_terima;
            $kirim->total_terima = $total_terima;
            $kirim->total_margin_terima = $total_margin_terima;
            $kirim->status = '4';
            $kirim->update();

            DB::commit();
            return redirect()->route('terima_retur_tukar_barang.index')->with(['success' => 'Surat Jalan Berhasil Di  Proses !']);
        
        }catch(\Exception $e){
        
            DB::rollback();
            return back()->with(['error' => $e->getmessage()]);

        }

    }
    
        
}


