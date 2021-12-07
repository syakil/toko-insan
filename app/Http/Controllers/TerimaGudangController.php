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

class TerimaGudangController extends Controller
{
    public function index(){

        $terima = Kirim::where('id_supplier',Auth::user()->unit)
                        ->where('tujuan','gudang')
                        ->where('status_kirim','transfer')
                        ->where('status',1)
                        ->get();
        $no = 1;
        return view('terima_gudang.index',['terima'=>$terima,'no'=>$no]);
    
    }

    public function listDetail(){
        
    }

    public function detail($id){

        $detail = KirimDetail::where('id_pembelian',$id)
                            ->join('produk','kirim_barang_detail.kode_produk','=','produk.kode_produk')
                            ->where('unit',Auth::user()->unit)
                            ->get();
        $nopo = Kirim::where('id_pembelian',$id)->first();
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

    public function terima(Request $request){
        
        $id = $request->id;
        try{
            
            DB::beginTransaction();
                
                $sj = Kirim::where('id_pembelian',$id)->first();   
                $detail_sj = KirimDetail::where('id_pembelian',$id)->get();

                $pengirim = $sj->kode_gudang;
                $penerima = $sj->id_supplier;
                $tanggal = date('Y-m-d');
                $nama_penerima = Branch::where('kode_toko',$penerima)->first();
                $nama_pengirim = Branch::where('kode_toko',$pengirim)->first();
                $gl_penerima = $nama_penerima->GL;
                $gl_pengirim = $nama_pengirim->GL;
                
                foreach ($detail_sj as $list) {
                    
                    $master_produk = Produk::where('kode_produk',$list->kode_produk)->where('unit',$pengirim)->first();

                    // cek selisih
                    if ($list->jumlah < $list->jumlah_terima) {
                        // jika terima lebih besar
                            
                        $jumlah_selisih = $list->jumlah_terima - $list->jumlah;

                        // jumlah yang sudah dikirim dijurnal terlebih dahulu
                        $jumlah_sudah_dikirim = $list->jumlah;
                        $nominal_jurnal_sudah_dikirim = $list->jumlah * $list->harga_beli;                            
                            
                        // crate kartu stok
                        $kartu_stok = new KartuStok;
                        $kartu_stok->buss_date = date('Y-m-d');
                        $kartu_stok->kode_produk = $list->kode_produk;
                        $kartu_stok->masuk = $jumlah_sudah_dikirim;
                        $kartu_stok->keluar = 0;
                        $kartu_stok->status = 'terima_retur_toko';
                        $kartu_stok->kode_transaksi = $id;
                        $kartu_stok->unit = $penerima;
                        $kartu_stok->save();
                            
                        $new_produk_detail = new ProdukDetail;
                        $new_produk_detail->kode_produk = $list->kode_produk;
                        $new_produk_detail->nama_produk = $master_produk->nama_produk;
                        $new_produk_detail->stok_detail = $jumlah_sudah_dikirim;
                        $new_produk_detail->harga_beli = $list->harga_beli;
                        $new_produk_detail->harga_jual_umum = $list->harga_jual;
                        $new_produk_detail->harga_jual_insan = $list->harga_jual;
                        $new_produk_detail->expired_date = $list->expired_date;
                        $new_produk_detail->promo = 0;
                        $new_produk_detail->tanggal_masuk = date('Y-m-d');
                        $new_produk_detail->no_faktur = $list->no_faktur;
                        $new_produk_detail->unit = $penerima;
                        $new_produk_detail->status = null;
                        $new_produk_detail->promo = 0;
                        $new_produk_detail->save();

                        $stok_gudang = Produk::where('kode_produk',$list->kode_produk)->where('unit',$penerima)->first();
                        $stok_gudang->stok += $jumlah_sudah_dikirim;
                        $stok_gudang->update();

                        // Persediaan Barang Dagang
                        $jurnal = new TabelTransaksi;
                        $jurnal->unit =  $penerima; 
                        $jurnal->kode_transaksi = $id;
                        $jurnal->kode_rekening = 1482000;
                        $jurnal->tanggal_transaksi  = $tanggal;
                        $jurnal->jenis_transaksi  = 'Jurnal System';
                        $jurnal->keterangan_transaksi = 'Terima Retur Toko ' . $master_produk->kode_produk . ' ' . $nama_penerima->nama_toko;
                        $jurnal->debet = $nominal_jurnal_sudah_dikirim;
                        $jurnal->kredit = 0;
                        $jurnal->tanggal_posting = '';
                        $jurnal->keterangan_posting = '0';
                        $jurnal->id_admin = Auth::user()->id; 
                        $jurnal->save();

                        // RAK Pasiva
                        $jurnal = new TabelTransaksi;
                        $jurnal->unit =  $penerima; 
                        $jurnal->kode_transaksi = $id;
                        $jurnal->kode_rekening = 2500000;
                        $jurnal->tanggal_transaksi  = $tanggal;
                        $jurnal->jenis_transaksi  = 'Jurnal System';
                        $jurnal->keterangan_transaksi = 'Terima Retur Toko ' . $master_produk->kode_produk . ' ' . $nama_penerima->nama_toko;
                        $jurnal->debet = 0;
                        $jurnal->kredit = $nominal_jurnal_sudah_dikirim;
                        $jurnal->tanggal_posting = '';
                        $jurnal->keterangan_posting = '0';
                        $jurnal->id_admin = Auth::user()->id; 
                        $jurnal->save();

                        //RAK Aktiva Pengirim
                        $jurnal = new TabelTransaksi;
                        $jurnal->unit =  1010; 
                        $jurnal->kode_transaksi = $id;
                        $jurnal->kode_rekening = 1010 . '-' . $gl_penerima;
                        $jurnal->tanggal_transaksi  = $tanggal;
                        $jurnal->jenis_transaksi  = 'Jurnal System';
                        $jurnal->keterangan_transaksi = 'Kirim Retur Toko ' . $master_produk->kode_produk . ' ' . $nama_pengirim->nama_toko;
                        $jurnal->debet = $nominal_jurnal_sudah_dikirim;
                        $jurnal->kredit = 0;
                        $jurnal->tanggal_posting = '';
                        $jurnal->keterangan_posting = '0';
                        $jurnal->id_admin = Auth::user()->id; 
                        $jurnal->save();

                        //RAK Aktiva Penerima
                        $jurnal = new TabelTransaksi;
                        $jurnal->unit =  1010; 
                        $jurnal->kode_transaksi = $id;
                        $jurnal->kode_rekening = 1010 . '-' . $gl_pengirim;
                        $jurnal->tanggal_transaksi  = $tanggal;
                        $jurnal->jenis_transaksi  = 'Jurnal System';
                        $jurnal->keterangan_transaksi = 'Terima Retur Toko ' . $master_produk->kode_produk . ' ' . $nama_pengirim->nama_toko;
                        $jurnal->debet = 0;
                        $jurnal->kredit = $nominal_jurnal_sudah_dikirim;
                        $jurnal->tanggal_posting = '';
                        $jurnal->keterangan_posting = '0';
                        $jurnal->id_admin = Auth::user()->id; 
                        $jurnal->save();                                

                        $cek_detail = ProdukDetail::where('unit',$pengirim)->where('kode_produk',$list->kode_produk)->sum('stok_detail');
                        $cek_master = Produk::where('kode_produk',$list->kode_produk)->where('unit',$pengirim)->first();
                        
                        if ($cek_detail < $jumlah_selisih) {

                            return back()->with(['error' => 'Stok ' .$list->kode_produk. ' ' . $cek_master->nama_produk . ' Selisih Terima! Stok Toko Tidak Cukup !']);

                        }

                        if ($cek_master->stok < $jumlah_selisih) {
                            
                            return back()->with(['error' => 'Stok ' .$list->kode_produk. ' ' . $cek_master->nama_produk . ' Selisih Terima! Stok Toko Tidak Cukup !']);

                        }

                        $master_produk = Produk::where('kode_produk',$list->kode_produk)->where('unit',$pengirim)->first();
                        $master_produk->stok -= $jumlah_selisih;
                        $master_produk->update(); 

                        Produk_lebih:
                        // mengaambil stok di produk_detail berdasar barcode dan harga beli lebih rendah (stok yang tesedria)
                        $produk_detail = ProdukDetail::where('kode_produk',$list->kode_produk)
                                                    ->where('unit',$pengirim)
                                                    ->where('stok_detail','>','0')
                                                    ->orderBy('tanggal_masuk','ASC')
                                                    ->first();
                        
                        // buat variable stok toko dari column stok_detail dari table produk_detail
                        $stok_toko = $produk_detail->stok_detail;

                        // buat variable harga_beli dari column harga_beli dari table produk_detail
                        $harga_beli = $produk_detail->harga_beli;
                        
                        // buat variable harga_jual dari column harga_jual dari table produk_detail
                        $harga_jual = $produk_detail->harga_jual_umum;
                    
                        // jika qty penjualan == jumlah stok yang tersedia ditoko
                        if ($jumlah_selisih == $stok_toko) {
                                
                            $selisih_kirim_barang = new SelisihKirimBarang;
                            $selisih_kirim_barang->id_pembelian = $id;
                            $selisih_kirim_barang->kode_produk = $list->kode_produk;
                            $selisih_kirim_barang->harga_jual = $list->harga_jual;
                            $selisih_kirim_barang->harga_beli = $produk_detail->harga_beli;
                            $selisih_kirim_barang->jumlah = $stok_toko;
                            $selisih_kirim_barang->sub_total_jual = $stok_toko * $list->harga_jual;
                            $selisih_kirim_barang->sub_total = $stok_toko * $produk_detail->harga_beli;
                            $selisih_kirim_barang->expired_date = $produk_detail->expired_date;
                            $selisih_kirim_barang->keterangan = 'Kurang Kirim Retur';
                            $selisih_kirim_barang->no_faktur = $produk_detail->no_faktur;
                            $selisih_kirim_barang->unit = $pengirim;
                            $selisih_kirim_barang->save();
                                
                            // crate kartu stok
                            $kartu_stok = new KartuStok;
                            $kartu_stok->buss_date = date('Y-m-d');
                            $kartu_stok->kode_produk = $list->kode_produk;
                            $kartu_stok->masuk = 0;
                            $kartu_stok->keluar = $stok_toko;
                            $kartu_stok->status = 'kirim_retur_toko';
                            $kartu_stok->kode_transaksi = $id;
                            $kartu_stok->unit = $pengirim;
                            $kartu_stok->save();
                            
                            if ($list->harga_jual > $produk_detail->harga_beli) {
                                
                                $harga_beli_normal = $stok_toko * $produk_detail->harga_beli; 
                                $harga_jual_normal = $stok_toko * $produk_detail->harga_jual_umum;
                                $margin_normal = $harga_jual_normal - $harga_beli_normal;
                    
                                $jurnal = new TabelTransaksi;
                                $jurnal->unit =  $pengirim; 
                                $jurnal->kode_transaksi = $id;
                                $jurnal->kode_rekening = 2500000;
                                $jurnal->tanggal_transaksi  = $tanggal;
                                $jurnal->jenis_transaksi  = 'Jurnal System';
                                $jurnal->keterangan_transaksi = 'Kirim Selisih Toko Kurang Kirim ' . $master_produk->kode_produk . ' ' . $nama_pengirim->nama_toko;
                                $jurnal->debet = $harga_beli_normal;
                                $jurnal->kredit = 0;
                                $jurnal->tanggal_posting = '';
                                $jurnal->keterangan_posting = '0';
                                $jurnal->id_admin = Auth::user()->id; 
                                $jurnal->save();
                            
                                $jurnal = new TabelTransaksi;
                                $jurnal->unit = $pengirim; 
                                $jurnal->kode_transaksi = $id;
                                $jurnal->kode_rekening = 1483000;
                                $jurnal->tanggal_transaksi  = $tanggal;
                                $jurnal->jenis_transaksi  = 'Jurnal System';
                                $jurnal->keterangan_transaksi = 'Kirim Selisih Toko Kurang Kirim ' . $master_produk->kode_produk . ' ' . $nama_pengirim->nama_toko;
                                $jurnal->debet = $margin_normal;
                                $jurnal->kredit = 0;
                                $jurnal->tanggal_posting = '';
                                $jurnal->keterangan_posting = '0';
                                $jurnal->id_admin = Auth::user()->id; 
                                $jurnal->save();
                        
                                $jurnal = new TabelTransaksi;
                                $jurnal->unit =  $pengirim; 
                                $jurnal->kode_transaksi = $id;
                                $jurnal->kode_rekening = 1482000;
                                $jurnal->tanggal_transaksi  = $tanggal;
                                $jurnal->jenis_transaksi  = 'Jurnal System';
                                $jurnal->keterangan_transaksi = 'Kirim Selisih Toko Kurang Kirim ' . $master_produk->kode_produk . ' ' . $nama_pengirim->nama_toko;
                                $jurnal->debet = 0;
                                $jurnal->kredit = $harga_jual_normal;
                                $jurnal->tanggal_posting = '';
                                $jurnal->keterangan_posting = '0';
                                $jurnal->id_admin = Auth::user()->id; 
                                $jurnal->save();

                                                                    
                            }else {
                                
                                $harga_beli_promo = $stok_toko * $produk_detail->harga_beli; 
                                
                                $jurnal = new TabelTransaksi;
                                $jurnal->unit =  $pengirim; 
                                $jurnal->kode_transaksi = $id;
                                $jurnal->kode_rekening = 2500000;
                                $jurnal->tanggal_transaksi  = $tanggal;
                                $jurnal->jenis_transaksi  = 'Jurnal System';
                                $jurnal->keterangan_transaksi = 'Kirim Selisih Toko Kurang Kirim ' . $master_produk->kode_produk . ' ' . $nama_pengirim->nama_toko;
                                $jurnal->debet = $harga_beli_promo;
                                $jurnal->kredit = 0;
                                $jurnal->tanggal_posting = '';
                                $jurnal->keterangan_posting = '0';
                                $jurnal->id_admin = Auth::user()->id; 
                                $jurnal->save();

                                $jurnal = new TabelTransaksi;
                                $jurnal->unit =  $pengirim;
                                $jurnal->kode_transaksi = $id;
                                $jurnal->kode_rekening = 1482000;
                                $jurnal->tanggal_transaksi  = $tanggal;
                                $jurnal->jenis_transaksi  = 'Jurnal System';
                                $jurnal->keterangan_transaksi = 'Kirim Selisih Toko Kurang Kirim ' . $master_produk->kode_produk . ' ' . $nama_pengirim->nama_toko;
                                $jurnal->debet = 0;
                                $jurnal->kredit = $harga_beli_promo;
                                $jurnal->tanggal_posting = '';
                                $jurnal->keterangan_posting = '0';
                                $jurnal->id_admin = Auth::user()->id; 
                                $jurnal->save();
                
                            }
                                    
                            // crate kartu stok
                            $kartu_stok = new KartuStok;
                            $kartu_stok->buss_date = date('Y-m-d');
                            $kartu_stok->kode_produk = $list->kode_produk;
                            $kartu_stok->masuk = $stok_toko;
                            $kartu_stok->keluar = 0;
                            $kartu_stok->status = 'terima_retur_toko';
                            $kartu_stok->kode_transaksi = $id;
                            $kartu_stok->unit = $penerima;
                            $kartu_stok->save();
                                                                
                            $new_produk_detail = new ProdukDetail;
                            $new_produk_detail->kode_produk = $list->kode_produk;
                            $new_produk_detail->nama_produk = $master_produk->nama_produk;
                            $new_produk_detail->stok_detail = $stok_toko;
                            $new_produk_detail->harga_beli = $list->harga_beli;
                            $new_produk_detail->harga_jual_umum = $produk_detail->harga_jual_umum;
                            $new_produk_detail->harga_jual_insan = $produk_detail->harga_jual_umum;
                            $new_produk_detail->expired_date = $list->expired_date;
                            $new_produk_detail->promo = 0;
                            $new_produk_detail->tanggal_masuk = date('Y-m-d');
                            $new_produk_detail->no_faktur = $produk_detail->no_faktur;
                            $new_produk_detail->unit = $penerima;
                            $new_produk_detail->status = null;
                            $new_produk_detail->promo = 0;
                            $new_produk_detail->save();

                            $stok_gudang = Produk::where('kode_produk',$list->kode_produk)->where('unit',$penerima)->first();
                            $stok_gudang->stok += $stok_toko;
                            $stok_gudang->update();
                            
                            $harga_beli_normal = $stok_toko * $produk_detail->harga_beli; 
                            $harga_jual_normal = $stok_toko * $produk_detail->harga_jual_umum;

                            // Persediaan Barang Dagang
                            $jurnal = new TabelTransaksi;
                            $jurnal->unit =  $penerima; 
                            $jurnal->kode_transaksi = $id;
                            $jurnal->kode_rekening = 1482000;
                            $jurnal->tanggal_transaksi  = $tanggal;
                            $jurnal->jenis_transaksi  = 'Jurnal System';
                            $jurnal->keterangan_transaksi = 'Terima Selisih Kurang Retur Toko ' . $master_produk->kode_produk . ' ' . $nama_pengirim->nama_toko;
                            $jurnal->debet = $harga_beli_normal;
                            $jurnal->kredit = 0;
                            $jurnal->tanggal_posting = '';
                            $jurnal->keterangan_posting = '0';
                            $jurnal->id_admin = Auth::user()->id; 
                            $jurnal->save();

                            // RAK Pasiva
                            $jurnal = new TabelTransaksi;
                            $jurnal->unit =  $penerima; 
                            $jurnal->kode_transaksi = $id;
                            $jurnal->kode_rekening = 2500000;
                            $jurnal->tanggal_transaksi  = $tanggal;
                            $jurnal->jenis_transaksi  = 'Jurnal System';
                            $jurnal->keterangan_transaksi = 'Terima Selisih Kurang Retur Toko ' . $master_produk->kode_produk . ' ' . $nama_pengirim->nama_toko;
                            $jurnal->debet = 0;
                            $jurnal->kredit = $harga_beli_normal;
                            $jurnal->tanggal_posting = '';
                            $jurnal->keterangan_posting = '0';
                            $jurnal->id_admin = Auth::user()->id; 
                            $jurnal->save();

                            //RAK Aktiva Pengirim
                            $jurnal = new TabelTransaksi;
                            $jurnal->unit =  1010; 
                            $jurnal->kode_transaksi = $id;
                            $jurnal->kode_rekening = 1010 . '-' . $gl_penerima;
                            $jurnal->tanggal_transaksi  = $tanggal;
                            $jurnal->jenis_transaksi  = 'Jurnal System';
                            $jurnal->keterangan_transaksi = 'Kirim Selisih Kurang Retur Toko ' . $master_produk->kode_produk . ' ' . $nama_pengirim->nama_toko;
                            $jurnal->debet = $harga_beli_normal;
                            $jurnal->kredit = 0;
                            $jurnal->tanggal_posting = '';
                            $jurnal->keterangan_posting = '0';
                            $jurnal->id_admin = Auth::user()->id; 
                            $jurnal->save();

                            //RAK Aktiva Penerima
                            $jurnal = new TabelTransaksi;
                            $jurnal->unit =  1010; 
                            $jurnal->kode_transaksi = $id;
                            $jurnal->kode_rekening = 1010 . '-' . $gl_pengirim;
                            $jurnal->tanggal_transaksi  = $tanggal;
                            $jurnal->jenis_transaksi  = 'Jurnal System';
                            $jurnal->keterangan_transaksi = 'Terima Selisih Kurang Retur Toko ' . $master_produk->kode_produk . ' ' . $nama_pengirim->nama_toko;
                            $jurnal->debet = 0;
                            $jurnal->kredit = $harga_beli_normal;
                            $jurnal->tanggal_posting = '';
                            $jurnal->keterangan_posting = '0';
                            $jurnal->id_admin = Auth::user()->id; 
                            $jurnal->save();
                                                                            
                            $produk_detail->update(['stok_detail'=>0]);
                    
                                
                            // jika ada selisih qty kirim barang dengan jumlah stok yang tersedia
                            }else {
                            
                                // mengurangi qty kirim barang dengan stok toko berdasarkan stok_detail(table produk_detail)
                                $stok = $jumlah_selisih - $stok_toko;
                                // jika hasilnya lebih dari nol atau tidak minus, stok_detail tsb tidak memenuhi qty penjualan dan harus ambil lagi record pada produk detail~
                                // ~ yang stok nya lebih dari nol         
                            
                                if ($stok >= 0) {

                                    $selisih_kirim_barang = new SelisihKirimBarang;
                                    $selisih_kirim_barang->id_pembelian = $id;
                                    $selisih_kirim_barang->kode_produk = $list->kode_produk;
                                    $selisih_kirim_barang->harga_jual = $produk_detail->harga_jual_umum;
                                    $selisih_kirim_barang->harga_beli = $produk_detail->harga_beli;
                                    $selisih_kirim_barang->jumlah = $stok_toko;
                                    $selisih_kirim_barang->sub_total_jual = $stok_toko * $list->harga_jual;
                                    $selisih_kirim_barang->sub_total = $stok_toko * $produk_detail->harga_beli;
                                    $selisih_kirim_barang->expired_date = $produk_detail->expired_date;
                                    $selisih_kirim_barang->keterangan = 'Kurang Kirim Retur';
                                    $selisih_kirim_barang->no_faktur = $produk_detail->no_faktur;
                                    $selisih_kirim_barang->unit = $pengirim;
                                    $selisih_kirim_barang->save();
                                    
                                    // crate kartu stok
                                    $kartu_stok = new KartuStok;
                                    $kartu_stok->buss_date = date('Y-m-d');
                                    $kartu_stok->kode_produk = $list->kode_produk;
                                    $kartu_stok->masuk = 0;
                                    $kartu_stok->keluar = $stok_toko;
                                    $kartu_stok->status = 'retur_toko';
                                    $kartu_stok->kode_transaksi = $id;
                                    $kartu_stok->unit = $pengirim;
                                    $kartu_stok->save();
                                                                
                                    if ($list->harga_jual > $produk_detail->harga_beli) {
                                
                                        $harga_beli_normal = $stok_toko * $produk_detail->harga_beli; 
                                        $harga_jual_normal = $stok_toko * $list->harga_jual;
                                    
                                        $jurnal = new TabelTransaksi;
                                        $jurnal->unit =  $pengirim; 
                                        $jurnal->kode_transaksi = $id;
                                        $jurnal->kode_rekening = 2500000;
                                        $jurnal->tanggal_transaksi  = $tanggal;
                                        $jurnal->jenis_transaksi  = 'Jurnal System';
                                        $jurnal->keterangan_transaksi = 'Kirim Selisih Toko Kurang Kirim ' . $master_produk->kode_produk . ' ' . $nama_pengirim->nama_toko;
                                        $jurnal->debet = $harga_beli_normal;
                                        $jurnal->kredit = 0;
                                        $jurnal->tanggal_posting = '';
                                        $jurnal->keterangan_posting = '0';
                                        $jurnal->id_admin = Auth::user()->id; 
                                        $jurnal->save();
                                
                                        $jurnal = new TabelTransaksi;
                                        $jurnal->unit = $pengirim; 
                                        $jurnal->kode_transaksi = $id;
                                        $jurnal->kode_rekening = 1483000;
                                        $jurnal->tanggal_transaksi  = $tanggal;
                                        $jurnal->jenis_transaksi  = 'Jurnal System';
                                        $jurnal->keterangan_transaksi = 'Kirim Selisih Toko Kurang Kirim ' . $master_produk->kode_produk . ' ' . $nama_pengirim->nama_toko;
                                        $jurnal->debet = $harga_jual_normal - $harga_beli_normal;
                                        $jurnal->kredit = 0;
                                        $jurnal->tanggal_posting = '';
                                        $jurnal->keterangan_posting = '0';
                                        $jurnal->id_admin = Auth::user()->id; 
                                        $jurnal->save();
                                
                                        $jurnal = new TabelTransaksi;
                                        $jurnal->unit =  $pengirim; 
                                        $jurnal->kode_transaksi = $id;
                                        $jurnal->kode_rekening = 1482000;
                                        $jurnal->tanggal_transaksi  = $tanggal;
                                        $jurnal->jenis_transaksi  = 'Jurnal System';
                                        $jurnal->keterangan_transaksi = 'Kirim Selisih Toko Kurang Kirim ' . $master_produk->kode_produk . ' ' . $nama_pengirim->nama_toko;
                                        $jurnal->debet = 0;
                                        $jurnal->kredit = $harga_jual_normal;
                                        $jurnal->tanggal_posting = '';
                                        $jurnal->keterangan_posting = '0';
                                        $jurnal->id_admin = Auth::user()->id; 
                                        $jurnal->save();
                                    
                                    }else {
                                    
                                        $harga_beli_promo = $stok_toko * $produk_detail->harga_beli; 
                                        
                                        $jurnal = new TabelTransaksi;
                                        $jurnal->unit =  $pengirim; 
                                        $jurnal->kode_transaksi = $id;
                                        $jurnal->kode_rekening = 2500000;
                                        $jurnal->tanggal_transaksi  = $tanggal;
                                        $jurnal->jenis_transaksi  = 'Jurnal System';
                                        $jurnal->keterangan_transaksi = 'Kirim Selisih Toko Kurang Kirim ' . $master_produk->kode_produk . ' ' . $nama_pengirim->nama_toko;
                                        $jurnal->debet = $harga_beli_promo;
                                        $jurnal->kredit = 0;
                                        $jurnal->tanggal_posting = '';
                                        $jurnal->keterangan_posting = '0';
                                        $jurnal->id_admin = Auth::user()->id; 
                                        $jurnal->save();
        
                                        $jurnal = new TabelTransaksi;
                                        $jurnal->unit =  $pengirim;
                                        $jurnal->kode_transaksi = $id;
                                        $jurnal->kode_rekening = 1482000;
                                        $jurnal->tanggal_transaksi  = $tanggal;
                                        $jurnal->jenis_transaksi  = 'Jurnal System';
                                        $jurnal->keterangan_transaksi = 'Kirim Selisih Toko Kurang Kirim ' . $master_produk->kode_produk . ' ' . $nama_pengirim->nama_toko;
                                        $jurnal->debet = 0;
                                        $jurnal->kredit = $harga_beli_promo;
                                        $jurnal->tanggal_posting = '';
                                        $jurnal->keterangan_posting = '0';
                                        $jurnal->id_admin = Auth::user()->id; 
                                        $jurnal->save();
                    
                                    }
                            
                                    // crate kartu stok
                                    $kartu_stok = new KartuStok;
                                    $kartu_stok->buss_date = date('Y-m-d');
                                    $kartu_stok->kode_produk = $list->kode_produk;
                                    $kartu_stok->masuk = $stok_toko;
                                    $kartu_stok->keluar = 0;
                                    $kartu_stok->status = 'terima_retur_toko';
                                    $kartu_stok->kode_transaksi = $id;
                                    $kartu_stok->unit = $penerima;
                                    $kartu_stok->save();
                                    
                                    $new_produk_detail = new ProdukDetail;
                                    $new_produk_detail->kode_produk = $list->kode_produk;
                                    $new_produk_detail->nama_produk = $master_produk->nama_produk;
                                    $new_produk_detail->stok_detail = $stok_toko;
                                    $new_produk_detail->harga_beli = $produk_detail->harga_beli;
                                    $new_produk_detail->harga_jual_umum = $produk_detail->harga_jual_umum;
                                    $new_produk_detail->harga_jual_insan = $produk_detail->harga_jual_umum;
                                    $new_produk_detail->expired_date = $produk_detail->expired_date;
                                    $new_produk_detail->promo = 0;
                                    $new_produk_detail->tanggal_masuk = date('Y-m-d');
                                    $new_produk_detail->no_faktur = $produk_detail->no_faktur;
                                    $new_produk_detail->unit = $penerima;
                                    $new_produk_detail->status = null;
                                    $new_produk_detail->promo = 0;
                                    $new_produk_detail->save();

                                    $stok_gudang = Produk::where('kode_produk',$list->kode_produk)->where('unit',$penerima)->first();
                                    $stok_gudang->stok += $stok_toko;
                                    $stok_gudang->update();
                                    
                                    $harga_beli_normal = $stok_toko * $produk_detail->harga_beli; 
                                    $harga_jual_normal = $stok_toko * $produk_detail->harga_jual_umum;
                                    
                                    // Persediaan Barang Dagang
                                    $jurnal = new TabelTransaksi;
                                    $jurnal->unit =  $penerima; 
                                    $jurnal->kode_transaksi = $id;
                                    $jurnal->kode_rekening = 1482000;
                                    $jurnal->tanggal_transaksi  = $tanggal;
                                    $jurnal->jenis_transaksi  = 'Jurnal System';
                                    $jurnal->keterangan_transaksi = 'Terima Selisih Kurang Retur Toko ' . $master_produk->kode_produk . ' ' . $nama_pengirim->nama_toko;
                                    $jurnal->debet = $harga_beli_normal;
                                    $jurnal->kredit = 0;
                                    $jurnal->tanggal_posting = '';
                                    $jurnal->keterangan_posting = '0';
                                    $jurnal->id_admin = Auth::user()->id; 
                                    $jurnal->save();

                                    // RAK Pasiva
                                    $jurnal = new TabelTransaksi;
                                    $jurnal->unit =  $penerima; 
                                    $jurnal->kode_transaksi = $id;
                                    $jurnal->kode_rekening = 2500000;
                                    $jurnal->tanggal_transaksi  = $tanggal;
                                    $jurnal->jenis_transaksi  = 'Jurnal System';
                                    $jurnal->keterangan_transaksi = 'Terima Selisih Kurang Retur Toko ' . $master_produk->kode_produk . ' ' . $nama_pengirim->nama_toko;
                                    $jurnal->debet = 0;
                                    $jurnal->kredit = $harga_beli_normal;
                                    $jurnal->tanggal_posting = '';
                                    $jurnal->keterangan_posting = '0';
                                    $jurnal->id_admin = Auth::user()->id; 
                                    $jurnal->save();

                                    //RAK Aktiva Pengirim
                                    $jurnal = new TabelTransaksi;
                                    $jurnal->unit =  1010; 
                                    $jurnal->kode_transaksi = $id;
                                    $jurnal->kode_rekening = 1010 . '-' . $gl_penerima;
                                    $jurnal->tanggal_transaksi  = $tanggal;
                                    $jurnal->jenis_transaksi  = 'Jurnal System';
                                    $jurnal->keterangan_transaksi = 'Kirim Selisih Kurang Retur Toko ' . $master_produk->kode_produk . ' ' . $nama_pengirim->nama_toko;
                                    $jurnal->debet = $harga_beli_normal;
                                    $jurnal->kredit = 0;
                                    $jurnal->tanggal_posting = '';
                                    $jurnal->keterangan_posting = '0';
                                    $jurnal->id_admin = Auth::user()->id; 
                                    $jurnal->save();

                                    //RAK Aktiva Penerima
                                    $jurnal = new TabelTransaksi;
                                    $jurnal->unit =  1010; 
                                    $jurnal->kode_transaksi = $id;
                                    $jurnal->kode_rekening =1010 . '-' .  $gl_pengirim;
                                    $jurnal->tanggal_transaksi  = $tanggal;
                                    $jurnal->jenis_transaksi  = 'Jurnal System';
                                    $jurnal->keterangan_transaksi = 'Terima Selisih Kurang Retur Toko ' . $master_produk->kode_produk . ' ' . $nama_pengirim->nama_toko;
                                    $jurnal->debet = 0;
                                    $jurnal->kredit = $harga_beli_normal;
                                    $jurnal->tanggal_posting = '';
                                    $jurnal->keterangan_posting = '0';
                                    $jurnal->id_admin = Auth::user()->id; 
                                    $jurnal->save();                                            
                                    
                                    // sisa qty kirim_barang yang dikurangi stok toko yang harganya paling rendah
                                    $jumlah_selisih = $stok;
                                    
                                    $produk_detail->update(['stok_detail'=>0]);
                        
                                    // mengulangi looping untuk mencari harga yang paling rendah
                                    goto Produk_lebih;
                                    
                                // jika pengurangan qty kirim_barang dengan stok toko hasilnya kurang dari 0 atau minus
                                }else if($stok < 0){
                                    
                                    $selisih_kirim_barang = new SelisihKirimBarang;
                                    $selisih_kirim_barang->id_pembelian = $id;
                                    $selisih_kirim_barang->kode_produk = $list->kode_produk;
                                    $selisih_kirim_barang->harga_jual = $produk_detail->harga_jual_umum;
                                    $selisih_kirim_barang->harga_beli = $produk_detail->harga_beli;
                                    $selisih_kirim_barang->jumlah = $jumlah_selisih;
                                    $selisih_kirim_barang->sub_total_jual = $stok_toko * $list->harga_jual;
                                    $selisih_kirim_barang->sub_total = $stok_toko * $produk_detail->harga_beli;
                                    $selisih_kirim_barang->expired_date = $produk_detail->expired_date;
                                    $selisih_kirim_barang->keterangan = 'Kurang Kirim Retur';
                                    $selisih_kirim_barang->no_faktur = $produk_detail->no_faktur;
                                    $selisih_kirim_barang->unit = $pengirim;
                                    $selisih_kirim_barang->save();
                                    
                                    // crate kartu stok
                                    $kartu_stok = new KartuStok;
                                    $kartu_stok->buss_date = date('Y-m-d');
                                    $kartu_stok->kode_produk = $list->kode_produk;
                                    $kartu_stok->masuk = 0;
                                    $kartu_stok->keluar = $jumlah_selisih;
                                    $kartu_stok->status = 'retur_toko';
                                    $kartu_stok->kode_transaksi = $id;
                                    $kartu_stok->unit = $pengirim;
                                    $kartu_stok->save();

                                    if ($list->harga_jual > $produk_detail->harga_beli) {
                                
                                        $harga_beli_normal = $jumlah_selisih * $produk_detail->harga_beli; 
                                        $harga_jual_normal = $jumlah_selisih * $list->harga_jual;
                                    
                                        $jurnal = new TabelTransaksi;
                                        $jurnal->unit =  $pengirim; 
                                        $jurnal->kode_transaksi = $id;
                                        $jurnal->kode_rekening = 2500000;
                                        $jurnal->tanggal_transaksi  = $tanggal;
                                        $jurnal->jenis_transaksi  = 'Jurnal System';
                                        $jurnal->keterangan_transaksi = 'Kirim Selisih Toko Kurang Kirim ' . $master_produk->kode_produk . ' ' . $nama_pengirim->nama_toko;
                                        $jurnal->debet = $harga_beli_normal;
                                        $jurnal->kredit = 0;
                                        $jurnal->tanggal_posting = '';
                                        $jurnal->keterangan_posting = '0';
                                        $jurnal->id_admin = Auth::user()->id; 
                                        $jurnal->save();
                                
                                        $jurnal = new TabelTransaksi;
                                        $jurnal->unit = $pengirim; 
                                        $jurnal->kode_transaksi = $id;
                                        $jurnal->kode_rekening = 1483000;
                                        $jurnal->tanggal_transaksi  = $tanggal;
                                        $jurnal->jenis_transaksi  = 'Jurnal System';
                                        $jurnal->keterangan_transaksi = 'Kirim Selisih Toko Kurang Kirim ' . $master_produk->kode_produk . ' ' . $nama_pengirim->nama_toko;
                                        $jurnal->debet = $harga_jual_normal - $harga_beli_normal;
                                        $jurnal->kredit = 0;
                                        $jurnal->tanggal_posting = '';
                                        $jurnal->keterangan_posting = '0';
                                        $jurnal->id_admin = Auth::user()->id; 
                                        $jurnal->save();
                                
                                        $jurnal = new TabelTransaksi;
                                        $jurnal->unit =  $pengirim; 
                                        $jurnal->kode_transaksi = $id;
                                        $jurnal->kode_rekening = 1482000;
                                        $jurnal->tanggal_transaksi  = $tanggal;
                                        $jurnal->jenis_transaksi  = 'Jurnal System';
                                        $jurnal->keterangan_transaksi = 'Kirim Selisih Toko Kurang Kirim ' . $master_produk->kode_produk . ' ' . $nama_pengirim->nama_toko;
                                        $jurnal->debet = 0;
                                        $jurnal->kredit = $harga_jual_normal;
                                        $jurnal->tanggal_posting = '';
                                        $jurnal->keterangan_posting = '0';
                                        $jurnal->id_admin = Auth::user()->id; 
                                        $jurnal->save();
                                    
                                    }else {
                                    
                                        $harga_beli_promo = $jumlah_selisih * $produk_detail->harga_beli; 
                                        
                                        $jurnal = new TabelTransaksi;
                                        $jurnal->unit =  $pengirim; 
                                        $jurnal->kode_transaksi = $id;
                                        $jurnal->kode_rekening = 2500000;
                                        $jurnal->tanggal_transaksi  = $tanggal;
                                        $jurnal->jenis_transaksi  = 'Jurnal System';
                                        $jurnal->keterangan_transaksi = 'Kirim Selisih Toko Kurang Kirim ' . $master_produk->kode_produk . ' ' . $nama_pengirim->nama_toko;
                                        $jurnal->debet = $harga_beli_promo;
                                        $jurnal->kredit = 0;
                                        $jurnal->tanggal_posting = '';
                                        $jurnal->keterangan_posting = '0';
                                        $jurnal->id_admin = Auth::user()->id; 
                                        $jurnal->save();
        
                                        $jurnal = new TabelTransaksi;
                                        $jurnal->unit =  $pengirim;
                                        $jurnal->kode_transaksi = $id;
                                        $jurnal->kode_rekening = 1482000;
                                        $jurnal->tanggal_transaksi  = $tanggal;
                                        $jurnal->jenis_transaksi  = 'Jurnal System';
                                        $jurnal->keterangan_transaksi = 'Kirim Selisih Toko Kurang Kirim ' . $master_produk->kode_produk . ' ' . $nama_pengirim->nama_toko;
                                        $jurnal->debet = 0;
                                        $jurnal->kredit = $harga_beli_promo;
                                        $jurnal->tanggal_posting = '';
                                        $jurnal->keterangan_posting = '0';
                                        $jurnal->id_admin = Auth::user()->id; 
                                        $jurnal->save();
                    
                                    }

                                    // crate kartu stok
                                    $kartu_stok = new KartuStok;
                                    $kartu_stok->buss_date = date('Y-m-d');
                                    $kartu_stok->kode_produk = $list->kode_produk;
                                    $kartu_stok->masuk = $jumlah_selisih;
                                    $kartu_stok->keluar = 0;
                                    $kartu_stok->status = 'terima_retur_toko';
                                    $kartu_stok->kode_transaksi = $id;
                                    $kartu_stok->unit = $penerima;
                                    $kartu_stok->save();
                                    
                                    $new_produk_detail = new ProdukDetail;
                                    $new_produk_detail->kode_produk = $list->kode_produk;
                                    $new_produk_detail->nama_produk = $master_produk->nama_produk;
                                    $new_produk_detail->stok_detail = $jumlah_selisih;
                                    $new_produk_detail->harga_beli = $produk_detail->harga_beli;
                                    $new_produk_detail->harga_jual_umum = $produk_detail->harga_jual_umum;
                                    $new_produk_detail->harga_jual_insan = $produk_detail->harga_jual_umum;
                                    $new_produk_detail->expired_date = $produk_detail->expired_date;
                                    $new_produk_detail->promo = 0;
                                    $new_produk_detail->tanggal_masuk = date('Y-m-d');
                                    $new_produk_detail->no_faktur = $produk_detail->no_faktur;
                                    $new_produk_detail->unit = $penerima;
                                    $new_produk_detail->status = null;
                                    $new_produk_detail->promo = 0;
                                    $new_produk_detail->save();

                                    $stok_gudang = Produk::where('kode_produk',$list->kode_produk)->where('unit',$penerima)->first();
                                    $stok_gudang->stok += $jumlah_selisih;
                                    $stok_gudang->update();
                                    
                                    $harga_beli_normal = $jumlah_selisih * $produk_detail->harga_beli; 
                                    $harga_jual_normal = $jumlah_selisih * $produk_detail->harga_jual_umum;
                                    
                                    // Persediaan Barang Dagang
                                    $jurnal = new TabelTransaksi;
                                    $jurnal->unit =  $penerima; 
                                    $jurnal->kode_transaksi = $id;
                                    $jurnal->kode_rekening = 1482000;
                                    $jurnal->tanggal_transaksi  = $tanggal;
                                    $jurnal->jenis_transaksi  = 'Jurnal System';
                                    $jurnal->keterangan_transaksi = 'Terima Selisih Kurang Retur Toko ' . $master_produk->kode_produk . ' ' . $nama_pengirim->nama_toko;
                                    $jurnal->debet = $harga_beli_normal;
                                    $jurnal->kredit = 0;
                                    $jurnal->tanggal_posting = '';
                                    $jurnal->keterangan_posting = '0';
                                    $jurnal->id_admin = Auth::user()->id; 
                                    $jurnal->save();

                                    // RAK Pasiva
                                    $jurnal = new TabelTransaksi;
                                    $jurnal->unit =  $penerima; 
                                    $jurnal->kode_transaksi = $id;
                                    $jurnal->kode_rekening = 2500000;
                                    $jurnal->tanggal_transaksi  = $tanggal;
                                    $jurnal->jenis_transaksi  = 'Jurnal System';
                                    $jurnal->keterangan_transaksi = 'Terima Selisih Kurang Retur Toko ' . $master_produk->kode_produk . ' ' . $nama_pengirim->nama_toko;
                                    $jurnal->debet = 0;
                                    $jurnal->kredit = $harga_beli_normal;
                                    $jurnal->tanggal_posting = '';
                                    $jurnal->keterangan_posting = '0';
                                    $jurnal->id_admin = Auth::user()->id; 
                                    $jurnal->save();

                                    //RAK Aktiva Pengirim
                                    $jurnal = new TabelTransaksi;
                                    $jurnal->unit =  1010; 
                                    $jurnal->kode_transaksi = $id;
                                    $jurnal->kode_rekening = 1010 . '-' . $gl_penerima;
                                    $jurnal->tanggal_transaksi  = $tanggal;
                                    $jurnal->jenis_transaksi  = 'Jurnal System';
                                    $jurnal->keterangan_transaksi = 'Kirim Selisih Kurang Retur Toko ' . $master_produk->kode_produk . ' ' . $nama_pengirim->nama_toko;
                                    $jurnal->debet = $harga_beli_normal;
                                    $jurnal->kredit = 0;
                                    $jurnal->tanggal_posting = '';
                                    $jurnal->keterangan_posting = '0';
                                    $jurnal->id_admin = Auth::user()->id; 
                                    $jurnal->save();

                                    //RAK Aktiva Penerima
                                    $jurnal = new TabelTransaksi;
                                    $jurnal->unit =  1010; 
                                    $jurnal->kode_transaksi = $id;
                                    $jurnal->kode_rekening = 1010 . '-' . $gl_pengirim;
                                    $jurnal->tanggal_transaksi  = $tanggal;
                                    $jurnal->jenis_transaksi  = 'Jurnal System';
                                    $jurnal->keterangan_transaksi = 'Terima Selisih Kurang Retur Toko ' . $master_produk->kode_produk . ' ' . $nama_pengirim->nama_toko;
                                    $jurnal->debet = 0;
                                    $jurnal->kredit = $harga_beli_normal;
                                    $jurnal->tanggal_posting = '';
                                    $jurnal->keterangan_posting = '0';
                                    $jurnal->id_admin = Auth::user()->id; 
                                    $jurnal->save();                                        
                                    
                                    // update stok_detail berdasar sisa pengurangan qty penjualan dengan stok toko hasilnya kurang dari 0 atau minus
                                    $produk_detail->update(['stok_detail'=>abs($stok)]);
                                    
                                }    
                            }

                    }elseif ($list->jumlah > $list->jumlah_terima) {
                        // jika diterima lebih sedikit dari yang dikiri
                            
                        $jumlah_selisih = $list->jumlah - $list->jumlah_terima;
                        $jumlah_terima = $list->jumlah_terima;

                        // create kartu stok jumlah yang diterima
                        $kartu_stok = new KartuStok;
                        $kartu_stok->buss_date = date('Y-m-d');
                        $kartu_stok->kode_produk = $list->kode_produk;
                        $kartu_stok->masuk = $jumlah_terima;
                        $kartu_stok->keluar = 0;
                        $kartu_stok->status = 'terima_retur_toko';
                        $kartu_stok->kode_transaksi = $id;
                        $kartu_stok->unit = $penerima;
                        $kartu_stok->save();  
                        
                        
                        $new_produk_detail = new ProdukDetail;
                        $new_produk_detail->kode_produk = $list->kode_produk;
                        $new_produk_detail->nama_produk = $master_produk->nama_produk;
                        $new_produk_detail->stok_detail = $jumlah_terima;
                        $new_produk_detail->harga_beli = $list->harga_beli;
                        $new_produk_detail->harga_jual_umum = $list->harga_jual;
                        $new_produk_detail->harga_jual_insan = $list->harga_jual;
                        $new_produk_detail->expired_date = $list->expired_date;
                        $new_produk_detail->promo = 0;
                        $new_produk_detail->tanggal_masuk = date('Y-m-d');
                        $new_produk_detail->no_faktur = $list->no_faktur;
                        $new_produk_detail->unit = $penerima;
                        $new_produk_detail->status = null;
                        $new_produk_detail->promo = 0;
                        $new_produk_detail->save();

                        $stok_gudang = Produk::where('kode_produk',$list->kode_produk)->where('unit',$penerima)->first();
                        $stok_gudang->stok += $jumlah_terima;
                        $stok_gudang->update();

                        // jurnal barang yang sudah diterima digudang
                        $harga_beli_normal = $jumlah_terima * $list->harga_beli; 
                                
                        // persediaan barang dagang
                        $jurnal = new TabelTransaksi;
                        $jurnal->unit =  $penerima; 
                        $jurnal->kode_transaksi = $id;
                        $jurnal->kode_rekening = 1482000;
                        $jurnal->tanggal_transaksi  = $tanggal;
                        $jurnal->jenis_transaksi  = 'Jurnal System';
                        $jurnal->keterangan_transaksi = 'Terima Retur Toko ' . $master_produk->kode_produk . ' ' . $nama_pengirim->nama_toko;
                        $jurnal->debet = $harga_beli_normal;
                        $jurnal->kredit = 0;
                        $jurnal->tanggal_posting = '';
                        $jurnal->keterangan_posting = '0';
                        $jurnal->id_admin = Auth::user()->id; 
                        $jurnal->save();

                        // rak pasiva
                        $jurnal = new TabelTransaksi;
                        $jurnal->unit =  $penerima; 
                        $jurnal->kode_transaksi = $id;
                        $jurnal->kode_rekening = 2500000;
                        $jurnal->tanggal_transaksi  = $tanggal;
                        $jurnal->jenis_transaksi  = 'Jurnal System';
                        $jurnal->keterangan_transaksi = 'Terima Retur Toko ' . $master_produk->kode_produk . ' ' . $nama_pengirim->nama_toko;
                        $jurnal->debet = 0;
                        $jurnal->kredit = $harga_beli_normal;
                        $jurnal->tanggal_posting = '';
                        $jurnal->keterangan_posting = '0';
                        $jurnal->id_admin = Auth::user()->id; 
                        $jurnal->save();
                        
                        //RAK Aktiva Penerima
                        $jurnal = new TabelTransaksi;
                        $jurnal->unit =  1010; 
                        $jurnal->kode_transaksi = $id;
                        $jurnal->kode_rekening = 1010 . '-' . $gl_penerima;
                        $jurnal->tanggal_transaksi  = $tanggal;
                        $jurnal->jenis_transaksi  = 'Jurnal System';
                        $jurnal->keterangan_transaksi = 'Terima Retur Toko ' . $master_produk->kode_produk . ' ' . $nama_pengirim->nama_toko;
                        $jurnal->debet = $harga_beli_normal;
                        $jurnal->kredit = 0;
                        $jurnal->tanggal_posting = '';
                        $jurnal->keterangan_posting = '0';
                        $jurnal->id_admin = Auth::user()->id; 
                        $jurnal->save();

                        //RAK AKtiva Pengirim
                        $jurnal = new TabelTransaksi;
                        $jurnal->unit =  1010; 
                        $jurnal->kode_transaksi = $id;
                        $jurnal->kode_rekening =1010 . '-' .  $gl_pengirim;
                        $jurnal->tanggal_transaksi  = $tanggal;
                        $jurnal->jenis_transaksi  = 'Jurnal System';
                        $jurnal->keterangan_transaksi = 'Kirim Retur Toko ' . $master_produk->kode_produk . ' ' . $nama_pengirim->nama_toko;
                        $jurnal->debet = 0;
                        $jurnal->kredit = $harga_beli_normal;
                        $jurnal->tanggal_posting = '';
                        $jurnal->keterangan_posting = '0';
                        $jurnal->id_admin = Auth::user()->id; 
                        $jurnal->save();

                        // create kartu stok jumlah barang rusak
                        $kartu_stok = new KartuStok;
                        $kartu_stok->buss_date = date('Y-m-d');
                        $kartu_stok->kode_produk = $list->kode_produk;
                        $kartu_stok->masuk = $jumlah_selisih;
                        $kartu_stok->keluar = 0;
                        $kartu_stok->status = 'terima_selisih_retur_toko';
                        $kartu_stok->kode_transaksi = $id;
                        $kartu_stok->unit = $pengirim;
                        $kartu_stok->save();  
                        
                        $selisih_kirim_barang = new SelisihKirimBarang;
                        $selisih_kirim_barang->id_pembelian = $id;
                        $selisih_kirim_barang->kode_produk = $list->kode_produk;
                        $selisih_kirim_barang->harga_jual = $list->harga_jual;
                        $selisih_kirim_barang->harga_beli = $list->harga_beli;
                        $selisih_kirim_barang->jumlah = $jumlah_selisih;
                        $selisih_kirim_barang->sub_total_jual = $jumlah_selisih * $list->harga_jual;
                        $selisih_kirim_barang->sub_total = $jumlah_selisih * $list->harga_beli;
                        $selisih_kirim_barang->expired_date = $list->expired_date;
                        $selisih_kirim_barang->keterangan = 'Lebih Kirim Retur';
                        $selisih_kirim_barang->no_faktur = $list->no_faktur;
                        $selisih_kirim_barang->unit = $pengirim;
                        $selisih_kirim_barang->save();
                        
                        //kembalikan Stok Toko
                        $new_produk_detail = new ProdukDetail;
                        $new_produk_detail->kode_produk = $list->kode_produk;
                        $new_produk_detail->nama_produk = $master_produk->nama_produk;
                        $new_produk_detail->stok_detail = $jumlah_selisih;
                        $new_produk_detail->harga_beli = $list->harga_beli;
                        $new_produk_detail->harga_jual_umum = $list->harga_jual;
                        $new_produk_detail->harga_jual_insan = $list->harga_jual;
                        $new_produk_detail->expired_date = $list->expired_date;
                        $new_produk_detail->promo = 0;
                        $new_produk_detail->tanggal_masuk = date('Y-m-d');
                        $new_produk_detail->no_faktur = $list->no_faktur;
                        $new_produk_detail->unit = $pengirim;
                        $new_produk_detail->status = null;
                        $new_produk_detail->promo = 0;
                        $new_produk_detail->save();

                        if ($list->harga_jual > $list->harga_beli) {
                            
                            
                            $harga_jual_normal = $jumlah_selisih * $list->harga_jual;
                            $harga_beli_normal = $jumlah_selisih * $list->harga_beli;
                            
                            // Persediaan Barang Dagang
                            $jurnal = new TabelTransaksi;
                            $jurnal->unit =  $pengirim; 
                            $jurnal->kode_transaksi = $id;
                            $jurnal->kode_rekening = 1482000;
                            $jurnal->tanggal_transaksi  = $tanggal;
                            $jurnal->jenis_transaksi  = 'Jurnal System';
                            $jurnal->keterangan_transaksi = 'Selisih Lebih Kirim Retur Toko ' . $master_produk->kode_produk . ' ' . $nama_pengirim->nama_toko ;
                            $jurnal->debet = $harga_jual_normal;
                            $jurnal->kredit = 0;
                            $jurnal->tanggal_posting = '';
                            $jurnal->keterangan_posting = '0';
                            $jurnal->id_admin = Auth::user()->id; 
                            $jurnal->save();

                            // PMYD
                            $jurnal = new TabelTransaksi;
                            $jurnal->unit =  $pengirim; 
                            $jurnal->kode_transaksi = $id;
                            $jurnal->kode_rekening = 1483000;
                            $jurnal->tanggal_transaksi  = $tanggal;
                            $jurnal->jenis_transaksi  = 'Jurnal System';
                            $jurnal->keterangan_transaksi = 'Selisih Lebih Kirim Retur Toko ' . $master_produk->kode_produk . ' ' . $nama_pengirim->nama_toko ;
                            $jurnal->debet = 0;
                            $jurnal->kredit = $harga_jual_normal - $harga_beli_normal;
                            $jurnal->tanggal_posting = '';
                            $jurnal->keterangan_posting = '0';
                            $jurnal->id_admin = Auth::user()->id; 
                            $jurnal->save();

                            // RAK Pasiva
                            $jurnal = new TabelTransaksi;
                            $jurnal->unit =  $pengirim; 
                            $jurnal->kode_transaksi = $id;
                            $jurnal->kode_rekening = 2500000;
                            $jurnal->tanggal_transaksi  = $tanggal;
                            $jurnal->jenis_transaksi  = 'Jurnal System';
                            $jurnal->keterangan_transaksi = 'Selisih Lebih Kirim Retur Toko ' . $master_produk->kode_produk . ' ' . $nama_pengirim->nama_toko ;
                            $jurnal->debet = 0;
                            $jurnal->kredit = $harga_beli_normal;
                            $jurnal->tanggal_posting = '';
                            $jurnal->keterangan_posting = '0';
                            $jurnal->id_admin = Auth::user()->id; 
                            $jurnal->save();

                        }else {
                            
                            $harga_beli_promo = $jumlah_selisih * $list->harga_beli;
                            
                            // Persediaan Barang Dagang
                            $jurnal = new TabelTransaksi;
                            $jurnal->unit =  $pengirim; 
                            $jurnal->kode_transaksi = $id;
                            $jurnal->kode_rekening = 1482000;
                            $jurnal->tanggal_transaksi  = $tanggal;
                            $jurnal->jenis_transaksi  = 'Jurnal System';
                            $jurnal->keterangan_transaksi = 'Selisih Lebih Kirim Retur Toko ' . $master_produk->kode_produk . ' ' . $nama_pengirim->nama_toko ;
                            $jurnal->debet = $harga_beli_promo;
                            $jurnal->kredit = 0;
                            $jurnal->tanggal_posting = '';
                            $jurnal->keterangan_posting = '0';
                            $jurnal->id_admin = Auth::user()->id; 
                            $jurnal->save();
                            
                            // PMYD
                            $jurnal = new TabelTransaksi;
                            $jurnal->unit =  $pengirim; 
                            $jurnal->kode_transaksi = $id;
                            $jurnal->kode_rekening = 2500000;
                            $jurnal->tanggal_transaksi  = $tanggal;
                            $jurnal->jenis_transaksi  = 'Jurnal System';
                            $jurnal->keterangan_transaksi = 'Selisih Lebih Kirim Retur Toko ' . $master_produk->kode_produk . ' ' . $nama_pengirim->nama_toko ;
                            $jurnal->debet = 0;
                            $jurnal->kredit = $harga_beli_promo;
                            $jurnal->tanggal_posting = '';
                            $jurnal->keterangan_posting = '0';
                            $jurnal->id_admin = Auth::user()->id; 
                            $jurnal->save();
                            
                        }

                        $harga_beli = $jumlah_selisih * $list->harga_beli;
                        
                        // RAK Aktiva
                        $jurnal = new TabelTransaksi;
                        $jurnal->unit =  1010; 
                        $jurnal->kode_transaksi = $id;
                        $jurnal->kode_rekening = 1010 . '-' . $gl_pengirim;
                        $jurnal->tanggal_transaksi  = $tanggal;
                        $jurnal->jenis_transaksi  = 'Jurnal System';
                        $jurnal->keterangan_transaksi = 'Selisih Lebih Kirim Lebih Retur Toko ' . $master_produk->kode_produk . ' ' . $nama_pengirim->nama_toko ;
                        $jurnal->debet = $harga_beli;
                        $jurnal->kredit = 0;
                        $jurnal->tanggal_posting = '';
                        $jurnal->keterangan_posting = '0';
                        $jurnal->id_admin = Auth::user()->id; 
                        $jurnal->save();
                    
                        // RAK Aktiva
                        $jurnal = new TabelTransaksi;
                        $jurnal->unit =  1010; 
                        $jurnal->kode_transaksi = $id;
                        $jurnal->kode_rekening = 1010 . '-' . $gl_pengirim;
                        $jurnal->tanggal_transaksi  = $tanggal;
                        $jurnal->jenis_transaksi  = 'Jurnal System';
                        $jurnal->keterangan_transaksi = 'Selisih Lebih Kirim Lebih Retur Toko ' . $master_produk->kode_produk . ' ' . $nama_pengirim->nama_toko ;
                        $jurnal->debet = 0;
                        $jurnal->kredit = $harga_beli;
                        $jurnal->tanggal_posting = '';
                        $jurnal->keterangan_posting = '0';
                        $jurnal->id_admin = Auth::user()->id; 
                        $jurnal->save();
                        
                        $produk_toko = Produk::where('kode_produk',$list->kode_produk)->where('unit',$pengirim)->first();
                        $produk_toko->stok += $jumlah_selisih;
                        $produk_toko->update();
                    }else {

                        $jumlah_sudah_dikirim = $list->jumlah;
                        $nominal_jurnal_sudah_dikirim = $list->jumlah * $list->harga_beli;                            
                            
                        // crate kartu stok
                        $kartu_stok = new KartuStok;
                        $kartu_stok->buss_date = date('Y-m-d');
                        $kartu_stok->kode_produk = $list->kode_produk;
                        $kartu_stok->masuk = $jumlah_sudah_dikirim;
                        $kartu_stok->keluar = 0;
                        $kartu_stok->status = 'terima_retur_toko';
                        $kartu_stok->kode_transaksi = $id;
                        $kartu_stok->unit = $penerima;
                        $kartu_stok->save();
                            
                        $new_produk_detail = new ProdukDetail;
                        $new_produk_detail->kode_produk = $list->kode_produk;
                        $new_produk_detail->nama_produk = $master_produk->nama_produk;
                        $new_produk_detail->stok_detail = $jumlah_sudah_dikirim;
                        $new_produk_detail->harga_beli = $list->harga_beli;
                        $new_produk_detail->harga_jual_umum = $list->harga_jual;
                        $new_produk_detail->harga_jual_insan = $list->harga_jual;
                        $new_produk_detail->expired_date = $list->expired_date;
                        $new_produk_detail->promo = 0;
                        $new_produk_detail->tanggal_masuk = date('Y-m-d');
                        $new_produk_detail->no_faktur = $list->no_faktur;
                        $new_produk_detail->unit = $penerima;
                        $new_produk_detail->status = null;
                        $new_produk_detail->promo = 0;
                        $new_produk_detail->save();

                        $stok_gudang = Produk::where('kode_produk',$list->kode_produk)->where('unit',$penerima)->first();
                        $stok_gudang->stok += $jumlah_sudah_dikirim;
                        $stok_gudang->update();

                        // Persediaan Barang Dagang
                        $jurnal = new TabelTransaksi;
                        $jurnal->unit =  $penerima; 
                        $jurnal->kode_transaksi = $id;
                        $jurnal->kode_rekening = 1482000;
                        $jurnal->tanggal_transaksi  = $tanggal;
                        $jurnal->jenis_transaksi  = 'Jurnal System';
                        $jurnal->keterangan_transaksi = 'Terima Retur Toko ' . $master_produk->kode_produk . ' ' . $nama_penerima->nama_toko;
                        $jurnal->debet = $nominal_jurnal_sudah_dikirim;
                        $jurnal->kredit = 0;
                        $jurnal->tanggal_posting = '';
                        $jurnal->keterangan_posting = '0';
                        $jurnal->id_admin = Auth::user()->id; 
                        $jurnal->save();

                        // RAK Pasiva
                        $jurnal = new TabelTransaksi;
                        $jurnal->unit =  $penerima; 
                        $jurnal->kode_transaksi = $id;
                        $jurnal->kode_rekening = 2500000;
                        $jurnal->tanggal_transaksi  = $tanggal;
                        $jurnal->jenis_transaksi  = 'Jurnal System';
                        $jurnal->keterangan_transaksi = 'Terima Retur Toko ' . $master_produk->kode_produk . ' ' . $nama_penerima->nama_toko;
                        $jurnal->debet = 0;
                        $jurnal->kredit = $nominal_jurnal_sudah_dikirim;
                        $jurnal->tanggal_posting = '';
                        $jurnal->keterangan_posting = '0';
                        $jurnal->id_admin = Auth::user()->id; 
                        $jurnal->save();

                        //RAK Aktiva Pengirim
                        $jurnal = new TabelTransaksi;
                        $jurnal->unit =  1010; 
                        $jurnal->kode_transaksi = $id;
                        $jurnal->kode_rekening = 1010 . '-' .$gl_penerima;
                        $jurnal->tanggal_transaksi  = $tanggal;
                        $jurnal->jenis_transaksi  = 'Jurnal System';
                        $jurnal->keterangan_transaksi = 'Kirim Retur Toko ' . $master_produk->kode_produk . ' ' . $nama_pengirim->nama_toko;
                        $jurnal->debet = $nominal_jurnal_sudah_dikirim;
                        $jurnal->kredit = 0;
                        $jurnal->tanggal_posting = '';
                        $jurnal->keterangan_posting = '0';
                        $jurnal->id_admin = Auth::user()->id; 
                        $jurnal->save();

                        //RAK Aktiva Penerima
                        $jurnal = new TabelTransaksi;
                        $jurnal->unit =  1010; 
                        $jurnal->kode_transaksi = $id;
                        $jurnal->kode_rekening = 1010 . '-' . $gl_pengirim;
                        $jurnal->tanggal_transaksi  = $tanggal;
                        $jurnal->jenis_transaksi  = 'Jurnal System';
                        $jurnal->keterangan_transaksi = 'Terima Retur Toko ' . $master_produk->kode_produk . ' ' . $nama_pengirim->nama_toko;
                        $jurnal->debet = 0;
                        $jurnal->kredit = $nominal_jurnal_sudah_dikirim;
                        $jurnal->tanggal_posting = '';
                        $jurnal->keterangan_posting = '0';
                        $jurnal->id_admin = Auth::user()->id; 
                        $jurnal->save();                                

                    }  
                
                }
            
            Kirim::where('id_pembelian',$id)->update(['status' => 2]);
            DB::commit();
            return redirect()->route('terima_antar_gudang.index')->with(['success' => 'Surat Jalan Berhasil Di Approve !']);;
        
        }catch(\Exception $e){
     
            DB::rollback();
            return back()->with(['error' => $e->getmessage().' ' .$e->getLine()]);
    
        }
    }
}

