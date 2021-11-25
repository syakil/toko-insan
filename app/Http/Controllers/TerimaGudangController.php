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
use App\Branch;

class TerimaGudangController extends Controller
{
    public function index(){

        $terima = Kirim::where('id_supplier',Auth::user()->unit)
                        ->where('status_kirim','transfer')
                        ->where('status',1)
                        ->get();
        $no = 1;
        return view ('terima_gudang.index',['terima'=>$terima,'no'=>$no]);
    
    }

    public function listDetail(){
        
    }

    public function detail($id){

        $detail = KirimDetail::where('id_pembelian',$id)
                            ->join('produk','kirim_barang_detail.kode_produk','=','produk.kode_produk')
                            ->where('unit',Auth::user()->unit)
                            ->get();
        $nopo = Kirim::where('id_pembelian',$id)->get();
        $nomer = 1;
        return view('terima_gudang.detail',['kirim'=>$detail,'nomer'=>$nomer,'nopo'=>$nopo]);
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
        $data = $request->check;
        
        // update_stok
        foreach($data as $id){
            
            // update_stok
            $produk = DB::table('kirim_barang_detail')
                        ->where('id_pembelian',$id)
                        ->get();
            
            foreach ($produk as $p ) {

                $produk_main = Produk::where('kode_produk',$p->kode_produk)
                                    ->where('unit',Auth::user()->unit)
                                    ->first();

                $produk_main->stok = $produk_main->stok + $p->jumlah_terima;
                $produk_main->update();

                $produk_detail = new ProdukDetail;
                $produk_detail->nama_produk = $produk_main->nama_produk;
                $produk_detail->kode_produk = $p->kode_produk;
                $produk_detail->stok_detail = $p->jumlah_terima;
                $produk_detail->harga_beli = $p->harga_beli;
                $produk_detail->harga_jual_umum = $p->harga_jual;
                $produk_detail->harga_jual_insan = $p->harga_jual;
                $produk_detail->expired_date = $p->expired_date;
                $produk_detail->tanggal_masuk = date('Y-m-d');
                $produk_detail->unit = Auth::user()->unit;
                $produk_detail->status = null;
                $produk_detail->no_faktur = $p->no_faktur;
                $produk_detail->save();

            }

        }
            
        // insert_jurnal

        foreach($data as $id){


            // $param_tgl = \App\ParamTgl::where('nama_param_tgl','tanggal_transaksi')->where('unit',Auth::user()->id)->first();
            // $tanggal = $param_tgl->param_tgl;
            
            $data_jurnal = Kirim::where('id_pembelian',$id)->first();

            $pengirim = Branch::where('kode_toko',$data_jurnal->kode_gudang)->first();
            $penerima = Branch::where('kode_toko',$data_jurnal->id_supplier)->first();

            $tanggal = date('Y-m-d',strtotime($data_jurnal->created_at));
            
            $kode_penerima = $penerima->kode_toko;
            $nama_penerima = $penerima->nama_toko;
            $aktiva_penerima = $penerima->aktiva;
            
            $kode_pengirim = $pengirim->kode_toko;
            $nama_pengirim = $pengirim->nama_toko;
            $aktiva_pengirim = $pengirim->aktiva;

            $harga_beli = $data_jurnal->total_harga_terima;
            $harga_jual = $data_jurnal->total_margin_terima;

            $harga_terima = $data_jurnal->total_harga_terima;
            $harga_kirim = $data_jurnal->total_harga;

            $margin = $harga_jual - $harga_beli;

            $selisih = $harga_kirim - $harga_terima;

            $terima = $data_jurnal->total_terima;
            $kirim = $data_jurnal->total_item;
            
            $unit_kp = '1010';
            // jika yang diterima toko selisih dengan yang dikirim
            if ($kirim != $terima) {               

                // toko
                // Persediaan Musawamah/Barang Dagang
                $jurnal = new TabelTransaksi;
                $jurnal->unit =  $kode_penerima; 
                $jurnal->kode_transaksi = $id;
                $jurnal->kode_rekening = 1482000;
                $jurnal->tanggal_transaksi  = $tanggal;
                $jurnal->jenis_transaksi  = 'Jurnal System';
                $jurnal->keterangan_transaksi = 'Terima Antar Gudang' . ' ' . $id . ' ' . $nama_penerima;
                $jurnal->debet = $harga_beli;
                $jurnal->kredit = 0;
                $jurnal->tanggal_posting = '';
                $jurnal->keterangan_posting = '0';
                $jurnal->id_admin = Auth::user()->id; 
                $jurnal->save();
                
                // RAK PASIVA - KP
                $jurnal = new TabelTransaksi;
                $jurnal->unit =  $kode_penerima; 
                $jurnal->kode_transaksi = $id;
                $jurnal->kode_rekening = 2500000;
                $jurnal->tanggal_transaksi  = $tanggal;
                $jurnal->jenis_transaksi  = 'Jurnal System';
                $jurnal->keterangan_transaksi = 'Terima Antar Gudang' . ' ' . $id . ' ' . $nama_penerima;
                $jurnal->debet = 0;
                $jurnal->kredit = $harga_beli;
                $jurnal->tanggal_posting = '';
                $jurnal->keterangan_posting = '0';
                $jurnal->id_admin = Auth::user()->id; 
                $jurnal->save();
                 
                // gudang
                // Persediaan Musawamah/Barang Dagang
                $jurnal = new TabelTransaksi;
                $jurnal->unit =  $kode_pengirim; 
                $jurnal->kode_transaksi = $id;
                $jurnal->kode_rekening = 1482000;
                $jurnal->tanggal_transaksi  = $tanggal;
                $jurnal->jenis_transaksi  = 'Jurnal System';
                $jurnal->keterangan_transaksi = 'Selisih Terima Antar Gudang' . ' ' . $id . ' ' . $nama_penerima;
                $jurnal->debet = abs($selisih);
                $jurnal->kredit =0;
                $jurnal->tanggal_posting = '';
                $jurnal->keterangan_posting = '0';
                $jurnal->id_admin = Auth::user()->id; 
                $jurnal->save();

                // RAK PASIVA - KP
                $jurnal = new TabelTransaksi;
                $jurnal->unit =  $kode_pengirim; 
                $jurnal->kode_transaksi = $id;
                $jurnal->kode_rekening = 2500000;
                $jurnal->tanggal_transaksi  = $tanggal;
                $jurnal->jenis_transaksi  = 'Jurnal System';
                $jurnal->keterangan_transaksi = 'Selisih Terima Antar Gudang' . ' ' . $id . ' ' . $nama_penerima;
                $jurnal->debet = 0;
                $jurnal->kredit = abs($selisih);
                $jurnal->tanggal_posting = '';
                $jurnal->keterangan_posting = '0';
                $jurnal->id_admin = Auth::user()->id; 
                $jurnal->save();
            
                //KP
                // RAK - AKTIVA UNIT PENERIMA
                $jurnal = new TabelTransaksi;
                $jurnal->unit =  $unit_kp; 
                $jurnal->kode_transaksi = $id;
                $jurnal->kode_rekening = $aktiva_penerima;
                $jurnal->tanggal_transaksi  = $tanggal;
                $jurnal->jenis_transaksi  = 'Jurnal System';
                $jurnal->keterangan_transaksi = 'Terima Antar Gudang' . ' ' . $id . ' ' . $nama_penerima;
                $jurnal->debet = $harga_terima;
                $jurnal->kredit = 0;
                $jurnal->tanggal_posting = '';
                $jurnal->keterangan_posting = '0';
                $jurnal->id_admin = Auth::user()->id; 
                $jurnal->save();
                
                // RAK - AKTIVA UNIT PENGIRIM
                $jurnal = new TabelTransaksi;
                $jurnal->unit =  $unit_kp; 
                $jurnal->kode_transaksi = $id;
                $jurnal->kode_rekening = $aktiva_pengirim;
                $jurnal->tanggal_transaksi  = $tanggal;
                $jurnal->jenis_transaksi  = 'Jurnal System';
                $jurnal->keterangan_transaksi = 'Selisih Terima Antar Gudang' . ' ' . $id . ' ' . $nama_penerima;
                $jurnal->debet = abs($selisih);
                $jurnal->kredit = 0;
                $jurnal->tanggal_posting = '';
                $jurnal->keterangan_posting = '0';
                $jurnal->id_admin = Auth::user()->id; 
                $jurnal->save();
                
                // RAK - AKTIVA UNIT PENGIRIM
                $jurnal = new TabelTransaksi;
                $jurnal->unit =  $unit_kp; 
                $jurnal->kode_transaksi = $id;
                $jurnal->kode_rekening = $aktiva_pengirim;
                $jurnal->tanggal_transaksi  = $tanggal;
                $jurnal->jenis_transaksi  = 'Jurnal System';
                $jurnal->keterangan_transaksi = 'Kirim Antar Gudang' . ' ' . $id . ' ' . $nama_penerima;
                $jurnal->debet = 0;
                $jurnal->kredit = $harga_kirim;
                $jurnal->tanggal_posting = '';
                $jurnal->keterangan_posting = '0';
                $jurnal->id_admin = Auth::user()->id; 
                $jurnal->save();
                
            }else {

                // toko
                // Persediaan Musawamah/Barang Dagang
                $jurnal = new TabelTransaksi;
                $jurnal->unit =  $kode_penerima; 
                $jurnal->kode_transaksi = $id;
                $jurnal->kode_rekening = 1482000;
                $jurnal->tanggal_transaksi  = $tanggal;
                $jurnal->jenis_transaksi  = 'Jurnal System';
                $jurnal->keterangan_transaksi = 'Terima Antar Gudang' . ' ' . $id . ' ' . $nama_penerima;
                $jurnal->debet = $harga_beli;
                $jurnal->kredit = 0;
                $jurnal->tanggal_posting = '';
                $jurnal->keterangan_posting = '0';
                $jurnal->id_admin = Auth::user()->id; 
                $jurnal->save();
                
                // RAK PASIVA - KP
                $jurnal = new TabelTransaksi;
                $jurnal->unit =  $kode_penerima; 
                $jurnal->kode_transaksi = $id;
                $jurnal->kode_rekening = 2500000;
                $jurnal->tanggal_transaksi  = $tanggal;
                $jurnal->jenis_transaksi  = 'Jurnal System';
                $jurnal->keterangan_transaksi = 'Terima Antar Gudang' . ' ' . $id . ' ' . $nama_penerima;
                $jurnal->debet = 0;
                $jurnal->kredit = $harga_beli;
                $jurnal->tanggal_posting = '';
                $jurnal->keterangan_posting = '0';
                $jurnal->id_admin = Auth::user()->id; 
                $jurnal->save();
                
                // KP
                // RAK Aktiva Penerima
                $jurnal = new TabelTransaksi;
                $jurnal->unit =  $unit_kp; 
                $jurnal->kode_transaksi = $id;
                $jurnal->kode_rekening = $aktiva_penerima;
                $jurnal->tanggal_transaksi  = $tanggal;
                $jurnal->jenis_transaksi  = 'Jurnal System';
                $jurnal->keterangan_transaksi = 'Terima Antar Gudang' . ' ' . $id . ' ' . $nama_penerima;
                $jurnal->debet = $harga_beli;
                $jurnal->kredit = 0;
                $jurnal->tanggal_posting = '';
                $jurnal->keterangan_posting = '0';
                $jurnal->id_admin = Auth::user()->id; 
                $jurnal->save();
                
                // RAK Aktuiva Pengirim
                $jurnal = new TabelTransaksi;
                $jurnal->unit =  $unit_kp; 
                $jurnal->kode_transaksi = $id;
                $jurnal->kode_rekening = $aktiva_pengirim;
                $jurnal->tanggal_transaksi  = $tanggal;
                $jurnal->jenis_transaksi  = 'Jurnal System';
                $jurnal->keterangan_transaksi = 'Kirim Antar Gudang' . ' ' . $id . ' ' . $nama_penerima;
                $jurnal->debet = 0;
                $jurnal->kredit = $harga_beli;
                $jurnal->tanggal_posting = '';
                $jurnal->keterangan_posting = '0';
                $jurnal->id_admin = Auth::user()->id; 
                $jurnal->save();
            }

            $kirim_status = Kirim::where('id_pembelian',$id)->update(['status'=>2]);
        } 
            
        return redirect('terima_gudang/index');
    }
}

