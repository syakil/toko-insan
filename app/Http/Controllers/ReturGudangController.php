<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Kirim;
use App\KirimDetail;
use Auth;
use PDF;
use DB;
use App\Produk;
use App\ProdukDetail;

class ReturGudangController extends Controller
{
    public function index(){
        // menampilkan data Kirim where status app = gudang
        // barang dari gudang ke toko
        $transfer = Kirim::leftJoin('branch','kirim_barang.id_user','=','branch.kode_toko')
                        ->where('status_kirim','retur')
                        ->where('tujuan','gudang')
                        ->get();
        $no = 1;
        return view('terima_retur/index',['transfer'=>$transfer,'no'=>$no]);
    }

    public function show($id){
        // mengambil data detail_Kirim berdasar id_Kirim yang ingin dilihat
        $detail = KirimDetail::leftJoin('produk','kirim_barang_detail.kode_produk','=','produk.kode_produk')
                                    ->where('id_pembelian',$id)
                                    ->where('produk.unit',Auth::user()->unit)
                                    ->get();
        // mengambil no surat jalan berdsar id_Kirim yang dipilih
        $no_surat = Kirim::where('id_pembelian',$id)->get();
        $nomer = 1;
        return view('terima_retur/detail',['pembelian'=>$detail,'nomer'=>$nomer,'no_surat'=>$no_surat]);
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
            $sub_total = $detail->harga_jual * $request->value;
            $produk_sub_total = KirimDetail::where('id_pembelian_detail',$id);
            $produk_sub_total->update(['sub_total_terima'=>$sub_total]);

        }

        
        $total_terima = KirimDetail::where('id_pembelian',$kirim_detail->id_pembelian)->sum('sub_total_terima');

        $kirim = Kirim::where('id_pembelian',$kirim_detail->id_pembelian)->first();
        $kirim->total_harga_terima = $total_terima;
        $kirim->update();
                    
    
        }
        
        public function update_expired_date(Request $request,$id){
    
            $detail = KirimDetail::where('id_pembelian_detail',$id);
            $detail->update(['expired_date'=>$request->value]);
    
        }
    
        public function update_status(Request $request){
            
            $data = $request->check;
            // looping id yang diceklis
            foreach($data as $d){
                // update status_app menjadi 1
                $pembelian=Kirim::where('id_pembelian',$d);
                $pembelian->update(['status'=>1]);
    
                // input ke gudang
                $produk = DB::table('pembelian_detail','produk')
                            ->select('pembelian_detail.*','produk.kode_produk','produk.nama_produk','produk.id_kategori','produk.unit')
                            ->leftJoin('produk','pembelian_detail.kode_produk','=','produk.kode_produk')
                            ->where('unit',Auth::user()->unit)
                            ->where('id_pembelian',$d)
                            ->get();
                
                
                foreach ($produk as $p ) {
                    // update table produk
                    $produk_main = Produk::where('kode_produk',$p->kode_produk)
                                        ->where('unit',$p->unit)
                                        ->first();
                    $new_stok = $produk_main->stok + $p->jumlah_terima;
                    $produk_main->update(['stok'=>$new_stok]);
                    
    
                    //insert to produk_detail 
                    $insert_produk = new ProdukDetail;
                    $insert_produk->kode_produk = $p->kode_produk;
                    $insert_produk->id_kategori = $p->id_kategori;
                    $insert_produk->nama_produk = $p->nama_produk;
                    $insert_produk->stok_detail = $p->jumlah_terima;
                    $insert_produk->harga_beli = $produk_main->harga_beli;
                    $insert_produk->expired_date = $p->expired_date;
                    $insert_produk->unit = $p->unit;
                    $insert_produk->save();
    
                }
            }
            
            return redirect()->route('retur.index');
        }
        
    
        public function input_stok(Request $request){
            // dd($request);  
            // dd($request->check);
            // die;
            $data = $request->check;
            // looping id yang diceklis
            foreach($data as $d){
                
                // input ke gudang
                $produk = DB::table('kirim_barang_detail','produk')
                            ->select('kirim_barang_detail.*','produk.kode_produk','produk.nama_produk','produk.id_kategori','produk.unit')
                            ->leftJoin('produk','kirim_barang_detail.kode_produk','=','produk.kode_produk')
                            ->where('unit',Auth::user()->unit)
                            ->where('id_pembelian_detail',$d)
                            ->get();
                
                            
                foreach ($produk as $p ) {
                
                    // update status_app menjadi 1
                    $pembelian=Kirim::where('id_pembelian',$p->id_pembelian);
                    $pembelian->update(['status'=>1]);
                    
                    // update table produk
                    $produk_main = Produk::where('kode_produk',$p->kode_produk)
                                        ->where('unit',$p->unit)
                                        ->first();
                    $new_stok = $produk_main->stok + $p->jumlah_terima;
                    $produk_main->update(['stok'=>$new_stok]);
                    
    
                    //insert to produk_detail 
                    $insert_produk = new ProdukDetail;
                    $insert_produk->kode_produk = $p->kode_produk;
                    $insert_produk->id_kategori = $p->id_kategori;
                    $insert_produk->nama_produk = $p->nama_produk;
                    $insert_produk->stok_detail = $p->jumlah_terima;
                    $insert_produk->harga_beli = $produk_main->harga_beli;
                    $insert_produk->expired_date = $p->expired_date;
                    $insert_produk->unit = $p->unit;
                    $insert_produk->save();
    
                }
            }
            
            return redirect()->route('retur.index');
        }
    
        
}