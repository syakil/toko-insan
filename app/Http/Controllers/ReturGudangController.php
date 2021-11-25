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

class ReturGudangController extends Controller{
    
    public function index(){
        // menampilkan data Kirim where status app = gudang
        // barang dari gudang ke toko
        $transfer = Kirim::leftJoin('branch','kirim_barang.kode_gudang','=','branch.kode_toko')
                            ->where('id_supplier',Auth::user()->unit)
                            ->where('status_kirim','retur')
                            ->where('tujuan','gudang')
                            ->get();
        $no = 1;
        return view('terima_retur/index',['transfer'=>$transfer,'no'=>$no]);
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

    // public function update_status(Request $request){
        
    //     // menampung id_pembelian yang di checklist
    //     $data = $request->check;
        
    //     // update_stok
    //     foreach($data as $id){
            
    //         // update_stok
    //         $produk = DB::table('kirim_barang_detail')
    //                     ->where('id_pembelian',$id)
    //                     ->get();
            
    //         foreach ($produk as $p ) {

    //             $produk_main = Produk::where('kode_produk',$p->kode_produk)
    //                                 ->where('unit',Auth::user()->unit)
    //                                 ->first();
            
    //             if ($p->keterangan == 'Promo') {

    //                 $produk_main->stok = $produk_main->stok + $p->jumlah_terima;
    //                 $produk_main->update();
                    
    //                 $produk_detail = new ProdukDetail;
    //                 $produk_detail->nama_produk = $produk_main->nama_produk;
    //                 $produk_detail->kode_produk = $p->kode_produk;
    //                 $produk_detail->stok_detail = $p->jumlah_terima;
    //                 $produk_detail->harga_beli = $p->harga_beli;
    //                 $produk_detail->harga_jual_umum = $p->harga_jual;
    //                 $produk_detail->harga_jual_insan = $p->harga_jual;
    //                 $produk_detail->expired_date = $p->expired_date;
    //                 $produk_detail->tanggal_masuk = date('Y-m-d');
    //                 $produk_detail->unit = Auth::user()->unit;
    //                 $produk_detail->status = null;
    //                 $produk_detail->no_faktur = $p->no_faktur;
    //                 $produk_detail->save();
                
    //             }else {
                    
    //                 $unit = Auth::user()->unit;
    //                 $uuid=Uuid::uuid4()->getHex();
    //                 $rndm=substr($uuid,25);
    //                 $kode_rndm="WO/-".$unit.$rndm;

    //                 $produk_w0 = new ProdukWriteOff;
    //                 $produk_w0->kode_produk = $p->kode_produk;
    //                 $produk_w0->kode_transaksi = $kode_rndm;
    //                 $produk_w0->nama_produk = $produk_main->nama_produk;
    //                 $produk_w0->harga_beli = $p->harga_beli;
    //                 $produk_w0->harga_jual = $p->harga_jual;
    //                 $produk_w0->stok = $p->jumlah_terima;
    //                 $produk_w0->tanggal_wo = '';
    //                 $produk_w0->tanggal_input = date('Y-m-d');
    //                 $produk_w0->param_status= 1;
    //                 $produk_w0->tanggal_expired = '';
    //                 $produk_w0->unit = $unit;
    //                 $produk_w0->harga_jual_member_insan = $p->harga_jual;
    //                 $produk_w0->harga_jual_insan = $p->harga_jual;
    //                 $produk_w0->harga_jual_pabrik = $p->harga_jual;
    //                 $produk_w0->save();    

    //                 $nominal = $p->jumlah_terima * $p->harga_beli;
                    
    //                 $jurnal = new TabelTransaksi;
    //                 $jurnal->unit =  $unit; 
    //                 $jurnal->kode_transaksi = $kode_rndm;
    //                 $jurnal->kode_rekening = 1484000;
    //                 $jurnal->tanggal_transaksi  = date('Y-m-d');
    //                 $jurnal->jenis_transaksi  = 'Jurnal System';
    //                 $jurnal->keterangan_transaksi = 'Persediaan Barang Rusak ' . $p->kode_produk . ' ' . $produk_main->nama_produk;
    //                 $jurnal->debet = $nominal;
    //                 $jurnal->kredit = 0;
    //                 $jurnal->tanggal_posting = '';
    //                 $jurnal->keterangan_posting = '0';
    //                 $jurnal->id_admin = Auth::user()->id; 
    //                 $jurnal->save();

    //                 // Persediaan
    //                 $jurnal = new TabelTransaksi;
    //                 $jurnal->unit =  $unit; 
    //                 $jurnal->kode_transaksi = $kode_rndm;
    //                 $jurnal->kode_rekening = 1482000;
    //                 $jurnal->tanggal_transaksi  = date('Y-m-d');
    //                 $jurnal->jenis_transaksi  = 'Jurnal System';
    //                 $jurnal->keterangan_transaksi = 'Persediaan Barang Dagang ' . $p->kode_produk . ' ' . $produk_main->nama_produk;
    //                 $jurnal->debet = 0;
    //                 $jurnal->kredit = $nominal;
    //                 $jurnal->tanggal_posting = '';
    //                 $jurnal->keterangan_posting = '0';
    //                 $jurnal->id_admin = Auth::user()->id; 
    //                 $jurnal->save();
                
    //             }

    //         }

    //     }
            
    //     // insert_jurnal

    //     foreach($data as $id){

    //         $param_tgl = \App\ParamTgl::where('nama_param_tgl','tanggal_transaksi')->where('unit',Auth::user()->id)->first();
    //         $tanggal = $param_tgl->param_tgl;
            
    //         $data_jurnal = Kirim::where('id_pembelian',$id)->first();

    //         $pengirim = Branch::where('kode_toko',$data_jurnal->kode_gudang)->first();
    //         $penerima = Branch::where('kode_toko',$data_jurnal->id_supplier)->first();

    //         $kode_penerima = $penerima->kode_toko;
    //         $nama_penerima = $penerima->nama_toko;
    //         $aktiva_penerima = $penerima->aktiva;
            
    //         $kode_pengirim = $pengirim->kode_toko;
    //         $nama_pengirim = $pengirim->nama_toko;
    //         $aktiva_pengirim = $pengirim->aktiva;

    //         $harga_beli_terima = $data_jurnal->total_harga_terima;
    //         $harga_jual_terima = $data_jurnal->total_margin_terima;

    //         $harga_beli_kirim = $data_jurnal->total_harga;
    //         $harga_jual_kirim = $data_jurnal->total_margin;

    //         $margin_terima = $harga_jual_terima - $harga_beli_terima;

    //         $margin_kirim = $harga_jual_kirim - $harga_beli_kirim;
            
    //         $unit_kp = '1010';

    //         // jika yang diterima gudang selisih dengan yang dikirim
    //         if ($harga_beli_kirim != $harga_beli_terima) {               

    //             // toko
    //             // Persediaan Musawamah/Barang Dagang
    //             $jurnal = new TabelTransaksi;
    //             $jurnal->unit =  $kode_pengirim; 
    //             $jurnal->kode_transaksi = $id;
    //             $jurnal->kode_rekening = 1482000;
    //             $jurnal->tanggal_transaksi  = $tanggal;
    //             $jurnal->jenis_transaksi  = 'Jurnal System';
    //             $jurnal->keterangan_transaksi = 'Selisih Terima Gudang' . ' ' . $id . ' ' . $nama_penerima;
    //             $jurnal->debet = abs($harga_jual_kirim - $harga_jual_terima);
    //             $jurnal->kredit = 0;
    //             $jurnal->tanggal_posting = '';
    //             $jurnal->keterangan_posting = '0';
    //             $jurnal->id_admin = Auth::user()->id; 
    //             $jurnal->save();
                
    //             // RAK PASIVA - KP
    //             $jurnal = new TabelTransaksi;
    //             $jurnal->unit =  $kode_pengirim; 
    //             $jurnal->kode_transaksi = $id;
    //             $jurnal->kode_rekening = 2500000;
    //             $jurnal->tanggal_transaksi  = $tanggal;
    //             $jurnal->jenis_transaksi  = 'Jurnal System';
    //             $jurnal->keterangan_transaksi = 'Selisih Terima Gudang' . ' ' . $id . ' ' . $nama_penerima;
    //             $jurnal->debet = 0;
    //             $jurnal->kredit = abs($harga_beli_kirim - $harga_beli_terima);
    //             $jurnal->tanggal_posting = '';
    //             $jurnal->keterangan_posting = '0';
    //             $jurnal->id_admin = Auth::user()->id; 
    //             $jurnal->save();
                
    //             // PMYD-PYD Musawamah
    //             $jurnal = new TabelTransaksi;
    //             $jurnal->unit =  $kode_pengirim; 
    //             $jurnal->kode_transaksi = $id;
    //             $jurnal->kode_rekening = 1422000;
    //             $jurnal->tanggal_transaksi  = $tanggal;
    //             $jurnal->jenis_transaksi  = 'Jurnal System';
    //             $jurnal->keterangan_transaksi = 'Selisih Terima Gudang' . ' ' . $id . ' ' . $nama_penerima;
    //             $jurnal->debet = 0;
    //             $jurnal->kredit = $margin_kirim - $margin_terima;
    //             $jurnal->tanggal_posting = '';
    //             $jurnal->keterangan_posting = '0';
    //             $jurnal->id_admin = Auth::user()->id; 
    //             $jurnal->save();
                
    //             // gudang
    //             // Persediaan Musawamah/Barang Dagang
    //             $jurnal = new TabelTransaksi;
    //             $jurnal->unit =  $kode_penerima; 
    //             $jurnal->kode_transaksi = $id;
    //             $jurnal->kode_rekening = 1482000;
    //             $jurnal->tanggal_transaksi  = $tanggal;
    //             $jurnal->jenis_transaksi  = 'Jurnal System';
    //             $jurnal->keterangan_transaksi = 'Terima Gudang' . ' ' . $id . ' ' . $nama_penerima;
    //             $jurnal->debet = abs($harga_beli_terima);
    //             $jurnal->kredit =0;
    //             $jurnal->tanggal_posting = '';
    //             $jurnal->keterangan_posting = '0';
    //             $jurnal->id_admin = Auth::user()->id; 
    //             $jurnal->save();

    //             // RAK PASIVA - KP
    //             $jurnal = new TabelTransaksi;
    //             $jurnal->unit =  $kode_penerima; 
    //             $jurnal->kode_transaksi = $id;
    //             $jurnal->kode_rekening = 2500000;
    //             $jurnal->tanggal_transaksi  = $tanggal;
    //             $jurnal->jenis_transaksi  = 'Jurnal System';
    //             $jurnal->keterangan_transaksi = 'Terima Gudang' . ' ' . $id . ' ' . $nama_penerima;
    //             $jurnal->debet = 0;
    //             $jurnal->kredit = abs($harga_beli_terima);
    //             $jurnal->tanggal_posting = '';
    //             $jurnal->keterangan_posting = '0';
    //             $jurnal->id_admin = Auth::user()->id; 
    //             $jurnal->save();
            
    //             //KP
    //             // RAK - AKTIVA UNIT PENERIMA
    //             $jurnal = new TabelTransaksi;
    //             $jurnal->unit =  $unit_kp; 
    //             $jurnal->kode_transaksi = $id;
    //             $jurnal->kode_rekening = $aktiva_penerima;
    //             $jurnal->tanggal_transaksi  = $tanggal;
    //             $jurnal->jenis_transaksi  = 'Jurnal System';
    //             $jurnal->keterangan_transaksi = 'Terima Gudang' . ' ' . $id . ' ' . $nama_penerima;
    //             $jurnal->debet = $harga_beli_terima;
    //             $jurnal->kredit = 0;
    //             $jurnal->tanggal_posting = '';
    //             $jurnal->keterangan_posting = '0';
    //             $jurnal->id_admin = Auth::user()->id; 
    //             $jurnal->save();
                
    //             // RAK - AKTIVA UNIT PENGIRIM
    //             $jurnal = new TabelTransaksi;
    //             $jurnal->unit =  $unit_kp; 
    //             $jurnal->kode_transaksi = $id;
    //             $jurnal->kode_rekening = $aktiva_pengirim;
    //             $jurnal->tanggal_transaksi  = $tanggal;
    //             $jurnal->jenis_transaksi  = 'Jurnal System';
    //             $jurnal->keterangan_transaksi = 'Selisih Terima Gudang' . ' ' . $id . ' ' . $nama_penerima;
    //             $jurnal->debet = abs($harga_beli_kirim - $harga_beli_terima);
    //             $jurnal->kredit = 0;
    //             $jurnal->tanggal_posting = '';
    //             $jurnal->keterangan_posting = '0';
    //             $jurnal->id_admin = Auth::user()->id; 
    //             $jurnal->save();
                
    //             // RAK - AKTIVA UNIT PENGIRIM
    //             $jurnal = new TabelTransaksi;
    //             $jurnal->unit =  $unit_kp; 
    //             $jurnal->kode_transaksi = $id;
    //             $jurnal->kode_rekening = $aktiva_pengirim;
    //             $jurnal->tanggal_transaksi  = $tanggal;
    //             $jurnal->jenis_transaksi  = 'Jurnal System';
    //             $jurnal->keterangan_transaksi = 'Kirim Gudang' . ' ' . $id . ' ' . $nama_penerima;
    //             $jurnal->debet = 0;
    //             $jurnal->kredit = $harga_beli_kirim;
    //             $jurnal->tanggal_posting = '';
    //             $jurnal->keterangan_posting = '0';
    //             $jurnal->id_admin = Auth::user()->id; 
    //             $jurnal->save();
                
    //         }else {

    //             // gudang
    //             // Persediaan Musawamah/Barang Dagang
    //             $jurnal = new TabelTransaksi;
    //             $jurnal->unit =  $kode_penerima; 
    //             $jurnal->kode_transaksi = $id;
    //             $jurnal->kode_rekening = 1482000;
    //             $jurnal->tanggal_transaksi  = $tanggal;
    //             $jurnal->jenis_transaksi  = 'Jurnal System';
    //             $jurnal->keterangan_transaksi = 'Terima Gudang' . ' ' . $id . ' ' . $nama_penerima;
    //             $jurnal->debet = $harga_beli_terima;
    //             $jurnal->kredit = 0;
    //             $jurnal->tanggal_posting = '';
    //             $jurnal->keterangan_posting = '0';
    //             $jurnal->id_admin = Auth::user()->id; 
    //             $jurnal->save();
                
    //             // RAK PASIVA - KP
    //             $jurnal = new TabelTransaksi;
    //             $jurnal->unit =  $kode_penerima; 
    //             $jurnal->kode_transaksi = $id;
    //             $jurnal->kode_rekening = 2500000;
    //             $jurnal->tanggal_transaksi  = $tanggal;
    //             $jurnal->jenis_transaksi  = 'Jurnal System';
    //             $jurnal->keterangan_transaksi = 'Terima Gudang' . ' ' . $id . ' ' . $nama_penerima;
    //             $jurnal->debet = 0;
    //             $jurnal->kredit = $harga_beli_terima;
    //             $jurnal->tanggal_posting = '';
    //             $jurnal->keterangan_posting = '0';
    //             $jurnal->id_admin = Auth::user()->id; 
    //             $jurnal->save();
                
    //             // KP
    //             // RAK Aktiva Penerima
    //             $jurnal = new TabelTransaksi;
    //             $jurnal->unit =  $unit_kp; 
    //             $jurnal->kode_transaksi = $id;
    //             $jurnal->kode_rekening = $aktiva_penerima;
    //             $jurnal->tanggal_transaksi  = $tanggal;
    //             $jurnal->jenis_transaksi  = 'Jurnal System';
    //             $jurnal->keterangan_transaksi = 'Terima Gudang' . ' ' . $id . ' ' . $nama_penerima;
    //             $jurnal->debet = $harga_beli_terima;
    //             $jurnal->kredit = 0;
    //             $jurnal->tanggal_posting = '';
    //             $jurnal->keterangan_posting = '0';
    //             $jurnal->id_admin = Auth::user()->id; 
    //             $jurnal->save();
                
    //             // RAK Aktuiva Pengirim
    //             $jurnal = new TabelTransaksi;
    //             $jurnal->unit =  $unit_kp; 
    //             $jurnal->kode_transaksi = $id;
    //             $jurnal->kode_rekening = $aktiva_pengirim;
    //             $jurnal->tanggal_transaksi  = $tanggal;
    //             $jurnal->jenis_transaksi  = 'Jurnal System';
    //             $jurnal->keterangan_transaksi = 'Kirim Gudang' . ' ' . $id . ' ' . $nama_penerima;
    //             $jurnal->debet = 0;
    //             $jurnal->kredit = $harga_beli_kirim;
    //             $jurnal->tanggal_posting = '';
    //             $jurnal->keterangan_posting = '0';
    //             $jurnal->id_admin = Auth::user()->id; 
    //             $jurnal->save();
    //         }

    //         $kirim_status = Kirim::where('id_pembelian',$id)->update(['status'=>2]);
    //     } 
            
    //     return back();
    // }
    
    public function store(Request $request){

        try{

            DB::beginTransaction();

            $id = $request->id;
            $total_harga_terima = KirimDetail::where('id_pembelian',$id)->sum('sub_total_terima');
            $total_terima = KirimDetail::where('id_pembelian',$id)->sum('jumlah_terima');
            $total_margin_terima = KirimDetail::where('id_pembelian',$id)->sum('sub_total_margin_terima');

            $kirim = Kirim::where('id_pembelian',$id)->first();
            $kirim->total_harga_terima = $total_harga_terima;
            $kirim->total_terima = $total_terima;
            $kirim->total_margin_terima = $total_margin_terima;
            $kirim->status = 'approve';
            $kirim->update();

            DB::commit();
            return redirect()->route('retur.index')->with(['success' => 'Surat Jalan Berhasil Di  Proses !']);
        
        }catch(\Exception $e){
        
            DB::rollback();
            return back()->with(['error' => $e->getmessage()]);

        }

    }
    
        
}


