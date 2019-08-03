<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Kirim;
use App\KirimDetail;
use App\TabelTransaksi;
use App\ProdukDetail;

use App\Produk;
use Illuminate\Support\Facades\DB;
use PDF;
use Auth;

class TerimaTokoController extends Controller
{
    public function index(){

        $terima = Kirim::where('id_supplier',Auth::user()->unit)
                        ->where('status_kirim','transfer')
                        ->where('status',null)
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

    public function create_jurnal(Request $request){
        // menampung id_pembelian yang di checklist
        $data = $request->check;
        // dd($data);
        foreach($data as $id){
            
            // insert produk dari kirim_barang_detail ke produk detail
            $produk = DB::table('kirim_barang_detail','produk')
                        ->select('kirim_barang_detail.*','produk.kode_produk','produk.nama_produk','produk.id_kategori','produk.unit')
                        ->leftJoin('produk','kirim_barang_detail.kode_produk','=','produk.kode_produk')
                        ->where('unit',Auth::user()->unit)
                        ->where('id_pembelian',$id)
                        ->get();
            
            foreach ($produk as $p ) {
                $produk_main = Produk::where('kode_produk',$p->kode_produk)
                                    ->where('unit',Auth::user()->unit)
                                    ->first();
                $new_stok = $produk_main->stok + $p->jumlah_terima;
                $produk_main->update(['stok'=>$new_stok]);
                
                $insert_produk = new ProdukDetail;
                $insert_produk->kode_produk = $p->kode_produk;
                $insert_produk->id_kategori = $p->id_kategori;
                $insert_produk->nama_produk = $p->nama_produk;
                $insert_produk->stok_detail = $p->jumlah_terima;
                $insert_produk->harga_beli = $produk_main->harga_beli;
                $insert_produk->expired_date = $p->expired_date;
                $insert_produk->unit = Auth::user()->unit;
                $insert_produk->save();

            }

            // insert ke jurnal
            $jurnal_field = Kirim::where('id_pembelian',$id)->first();

            $jurnal = new TabelTransaksi;
            $jurnal->unit =  Auth::user()->unit; 
            $jurnal->kode_transaksi = $jurnal_field->id_pembelian;
            $jurnal->kode_rekening = 1482000;
            $jurnal->tanggal_transaksi  = date('Y-m-d');
            $jurnal->jenis_transaksi  = 'Jurnal System';
            $jurnal->keterangan_transaksi = 'TerimaToko' . ' ' . $jurnal_field->id_pembelian . ' ' . $jurnal_field->nama_toko;
            $jurnal->debet = $jurnal_field->total_harga_terima;
            $jurnal->kredit = 0;
            $jurnal->tanggal_posting = '';
            $jurnal->keterangan_posting = '0';
            $jurnal->id_admin = Auth::user()->id; 
            $jurnal->save();
            
            $jurnal = new TabelTransaksi;
            $jurnal->unit =  Auth::user()->unit; 
            $jurnal->kode_transaksi = $jurnal_field->id_pembelian;
            $jurnal->kode_rekening = 2500000;
            $jurnal->tanggal_transaksi  = date('Y-m-d');
            $jurnal->jenis_transaksi  = 'Jurnal System';
            $jurnal->keterangan_transaksi = 'TerimaToko' . ' ' . $jurnal_field->id_pembelian . ' ' . $jurnal_field->nama_toko;
            $jurnal->debet =0;
            $jurnal->kredit =$jurnal_field->total_harga_terima;
            $jurnal->tanggal_posting = '';
            $jurnal->keterangan_posting = '0';
            $jurnal->id_admin = Auth::user()->id; 
            $jurnal->save();

            // Acc Kp
            $jurnal = new TabelTransaksi;
            $jurnal->unit =  'KP'; 
            $jurnal->kode_transaksi = $jurnal_field->id_pembelian;
            $jurnal->kode_rekening = 1831000;
            $jurnal->tanggal_transaksi  = date('Y-m-d');
            $jurnal->jenis_transaksi  = 'Jurnal System';
            $jurnal->keterangan_transaksi = 'TerimaToko' . ' ' . $jurnal_field->id_pembelian . ' ' . $jurnal_field->nama_toko;
            $jurnal->debet =$jurnal_field->total_harga_terima;
            $jurnal->kredit = 0;
            $jurnal->tanggal_posting = '';
            $jurnal->keterangan_posting = '0';
            $jurnal->id_admin = Auth::user()->id; 
            $jurnal->save();
            
            $jurnal = new TabelTransaksi;
            $jurnal->unit =  'KP'; 
            $jurnal->kode_transaksi = $jurnal_field->id_pembelian;
            $jurnal->kode_rekening = 1830000;
            $jurnal->tanggal_transaksi  = date('Y-m-d');
            $jurnal->jenis_transaksi  = 'Jurnal System';
            $jurnal->keterangan_transaksi = 'TerimaToko' . ' ' . $jurnal_field->id_pembelian . ' ' . $jurnal_field->nama_toko;
            $jurnal->debet =0;
            $jurnal->kredit =$jurnal_field->total_harga_terima;
            $jurnal->tanggal_posting = '';
            $jurnal->keterangan_posting = '0';
            $jurnal->id_admin = Auth::user()->id; 
            $jurnal->save();

            // jika yang diterima toko selisih dengan yang dikirim
            if ($jurnal_field->total_item != $jurnal_field->total_terima) {

                $jurnal = new TabelTransaksi;
                $jurnal->unit =  Auth::user()->unit; 
                $jurnal->kode_transaksi = $jurnal_field->id_pembelian;
                $jurnal->kode_rekening = 1482000;
                $jurnal->tanggal_transaksi  = date('Y-m-d');
                $jurnal->jenis_transaksi  = 'Jurnal System';
                $jurnal->keterangan_transaksi = 'TerimaToko' . ' ' . $jurnal_field->id_pembelian . ' ' . $jurnal_field->nama_toko;
                $jurnal->debet = $jurnal_field->total_harga_terima;
                $jurnal->kredit = 0;
                $jurnal->tanggal_posting = '';
                $jurnal->keterangan_posting = '0';
                $jurnal->id_admin = Auth::user()->id; 
                $jurnal->save();
                
                $jurnal = new TabelTransaksi;
                $jurnal->unit =  Auth::user()->unit; 
                $jurnal->kode_transaksi = $jurnal_field->id_pembelian;
                $jurnal->kode_rekening = 2500000;
                $jurnal->tanggal_transaksi  = date('Y-m-d');
                $jurnal->jenis_transaksi  = 'Jurnal System';
                $jurnal->keterangan_transaksi = 'TerimaToko' . ' ' . $jurnal_field->id_pembelian . ' ' . $jurnal_field->nama_toko;
                $jurnal->debet =0;
                $jurnal->kredit = $jurnal_field->total_harga_terima;;
                $jurnal->tanggal_posting = '';
                $jurnal->keterangan_posting = '0';
                $jurnal->id_admin = Auth::user()->id; 
                $jurnal->save();
                


                $jurnal = new TabelTransaksi;
                $jurnal->unit =  $jurnal_field->kode_gudang; 
                $jurnal->kode_transaksi = $jurnal_field->id_pembelian;
                $jurnal->kode_rekening = 1969000;
                $jurnal->tanggal_transaksi  = date('Y-m-d');
                $jurnal->jenis_transaksi  = 'Jurnal System';
                $jurnal->keterangan_transaksi = 'Selisih Terima Toko' . ' ' . $jurnal_field->id_pembelian . ' ' . $jurnal_field->nama_toko;
                $jurnal->debet = $jurnal_field->total_harga - $jurnal_field->total_harga_terima;
                $jurnal->kredit = 0;
                $jurnal->tanggal_posting = '';
                $jurnal->keterangan_posting = '0';
                $jurnal->id_admin = Auth::user()->id; 
                $jurnal->save();
                
                $jurnal = new TabelTransaksi;
                $jurnal->unit =  $jurnal_field->kode_gudang; 
                $jurnal->kode_transaksi = $jurnal_field->id_pembelian;
                $jurnal->kode_rekening = 2500000;
                $jurnal->tanggal_transaksi  = date('Y-m-d');
                $jurnal->jenis_transaksi  = 'Jurnal System';
                $jurnal->keterangan_transaksi = 'Selisih Terima Toko' . ' ' . $jurnal_field->id_pembelian . ' ' . $jurnal_field->nama_toko;
                $jurnal->debet =0;
                $jurnal->kredit = $jurnal_field->total_harga - $jurnal_field->total_harga_terima;
                $jurnal->tanggal_posting = '';
                $jurnal->keterangan_posting = '0';
                $jurnal->id_admin = Auth::user()->id; 
                $jurnal->save();
                
                // junal manaual posting
                $jurnal = new TabelTransaksi;
                $jurnal->unit =  'KP'; 
                $jurnal->kode_transaksi = $jurnal_field->id_pembelian;
                $jurnal->kode_rekening = 1831000;
                $jurnal->tanggal_transaksi  = date('Y-m-d');
                $jurnal->jenis_transaksi  = 'Jurnal System';
                $jurnal->keterangan_transaksi = 'Selisih Terima Toko' . ' ' . $jurnal_field->id_pembelian . ' ' . $jurnal_field->nama_toko;
                $jurnal->debet = $jurnal_field->total_harga_terima;
                $jurnal->kredit = 0;
                $jurnal->tanggal_posting = '';
                $jurnal->keterangan_posting = '0';
                $jurnal->id_admin = Auth::user()->id; 
                $jurnal->save();
            
                
                $jurnal = new TabelTransaksi;
                $jurnal->unit =  'KP'; 
                $jurnal->kode_transaksi = $jurnal_field->id_pembelian;
                $jurnal->kode_rekening = 1830000;
                $jurnal->tanggal_transaksi  = date('Y-m-d');
                $jurnal->jenis_transaksi  = 'Jurnal System';
                $jurnal->keterangan_transaksi = 'Terima Toko' . ' ' . $jurnal_field->id_pembelian . ' ' . $jurnal_field->nama_toko;
                $jurnal->debet = 0;
                $jurnal->kredit = $jurnal_field->total_harga_terima;
                $jurnal->tanggal_posting = '';
                $jurnal->keterangan_posting = '0';
                $jurnal->id_admin = Auth::user()->id; 
                $jurnal->save();

                $jurnal = new TabelTransaksi;
                $jurnal->unit =  'KP'; 
                $jurnal->kode_transaksi = $jurnal_field->id_pembelian;
                $jurnal->kode_rekening = 1830000;
                $jurnal->tanggal_transaksi  = date('Y-m-d');
                $jurnal->jenis_transaksi  = 'Jurnal System';
                $jurnal->keterangan_transaksi = 'Selisih TerimaToko' . ' ' . $jurnal_field->id_pembelian . ' ' . $jurnal_field->nama_toko;
                $jurnal->debet = $jurnal_field->total_harga;
                $jurnal->kredit = 0;
                $jurnal->tanggal_posting = '';
                $jurnal->keterangan_posting = '0';
                $jurnal->id_admin = Auth::user()->id; 
                $jurnal->save();

                
            }

            $kirim_status = Kirim::where('id_pembelian',$id)->update(['status'=>1]);
        } 
            
        return redirect('terima_toko/index');
    }
}
