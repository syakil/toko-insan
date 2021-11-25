<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Kirim;
use App\ProdukWriteOff;
use App\KirimDetail;
use Ramsey\Uuid\Uuid;
use App\Produk;
use App\ProdukDetail;
use App\TabelTransaksi;
use App\SelisihKirimBarang;
use App\KartuStok;
use App\Branch;
use DB;
use Auth;


class ApproveTerimaTokoController extends Controller{
    

    public function index(){

        return view('approve_terima_toko.index');

    }

    public function listData(){

        $branch = Branch::where('kode_gudang',Auth::user()->unit)->get();
        $kode_toko = array();
        foreach ($branch as $value) {
            if ($value->kode_toko != Auth::user()->unit) {
                $kode_toko[]= $value->kode_toko;
            }
        }


        $kirim = Kirim::select('kirim_barang.*','branch.nama_toko')
        ->leftJoin('branch','branch.kode_toko','kirim_barang.kode_gudang')
        ->whereIn('id_supplier',$kode_toko)
        ->where('kirim_barang.status','approval_terima')
        ->get();


        $data = array();
        $no =1;

        foreach($kirim as $list){

            $row = array();
            $row[] = $no++;
            $row[] = tanggal_indonesia($list->created_at);
            $row[] = $list->id_pembelian;
            $row[] = $list->nama_toko;
            $row[] = $list->total_item;
            $row[] = $list->total_terima;
            $row[] =  "<div class='btn-group'>
            <a href='".route('approve_terima_toko.detail',$list->id_pembelian)."' class='btn btn-warning btn-sm'><i class='fa fa-pencil'></i></a>
            </div>";

            $data[] = $row;

        }

        $output = array("data" =>$data);
        return response()->json($output);
    }

    public function detail($id){

        return view('approve_terima_toko.detail',compact('id'));

    }


    public function listDetail($id){

        $detail = KirimDetail::
        leftJoin('status_kirim','status_kirim.id_status','kirim_barang_detail.keterangan')
        ->leftJoin('produk', 'produk.kode_produk', '=', 'kirim_barang_detail.kode_produk')
        ->where('id_pembelian', '=', $id)
        ->where('unit', '=', Auth::user()->unit)
        ->select('kirim_barang_detail.*','produk.kode_produk','produk.nama_produk','produk.stok','status_kirim.status')
        ->orderBy('updated_at','desc')        
        ->get();

        $no = 0;
        $data = array();
        $id_toko = Kirim::where('id_pembelian',$id)->first();

        foreach($detail as $list){

            $no ++;

            $stok_toko = Produk::where('kode_produk',$list->kode_produk)->where('unit',$id_toko->id_supplier)->first();
            $row = array();
            $row[] = $no;
            $row[] = $list->kode_produk;
            $row[] = $list->nama_produk;
            $row[] = $list->stok;
            $row[] = $stok_toko->stok;
            $row[] = $list->jumlah;
            $row[] = $list->jumlah_terima;
            $row[] = $list->expired_date;
            $data[] = $row;

        }

        $output = array("data" => $data);
        return response()->json($output);

    }


    public function approve(Request $request){

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
                        $nominal_harga_jual_sudah_dikirim = $list->jumlah * $list->harga_jual;    
                        $nominal_harga_beli_sudah_dikirim = $list->jumlah * $list->harga_beli;                            
                        $nominal_margin_sudah_dikirim = $nominal_harga_jual_sudah_dikirim - $nominal_harga_beli_sudah_dikirim;    
                            
                        // crate kartu stok
                        $kartu_stok = new KartuStok;
                        $kartu_stok->buss_date = date('Y-m-d');
                        $kartu_stok->kode_produk = $list->kode_produk;
                        $kartu_stok->masuk = $jumlah_sudah_dikirim;
                        $kartu_stok->keluar = 0;
                        $kartu_stok->status = 'terima_toko';
                        $kartu_stok->kode_transaksi = $id;
                        $kartu_stok->unit = $penerima;
                        $kartu_stok->save();
                        
                        //kembalikan Stok Toko
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
                        $new_produk_detail->no_faktur = $list->faktur;
                        $new_produk_detail->unit = $penerima;
                        $new_produk_detail->status = null;
                        $new_produk_detail->promo = 0;
                        $new_produk_detail->save();

                        $stok_toko = Produk::where('kode_produk',$list->kode_produk)->where('unit',$penerima)->first();
                        $stok_toko->stok += $jumlah_sudah_dikirim;
                        $stok_toko->update(); 

                        if ($list->harga_jual > $list->harga_beli) {
                                                        
                            // Persediaan Barang Dagang
                            $jurnal = new TabelTransaksi;
                            $jurnal->unit =  $penerima; 
                            $jurnal->kode_transaksi = $id;
                            $jurnal->kode_rekening = 1482000;
                            $jurnal->tanggal_transaksi  = $tanggal;
                            $jurnal->jenis_transaksi  = 'Jurnal System';
                            $jurnal->keterangan_transaksi = 'Terima Barang Dagang ' . $master_produk->kode_produk . ' ' . $nama_penerima->nama_toko;
                            $jurnal->debet = $nominal_harga_jual_sudah_dikirim;
                            $jurnal->kredit = 0;
                            $jurnal->tanggal_posting = '';
                            $jurnal->keterangan_posting = '0';
                            $jurnal->id_admin = Auth::user()->id; 
                            $jurnal->save();

                            
                            // PMYD
                            $jurnal = new TabelTransaksi;
                            $jurnal->unit =  $penerima; 
                            $jurnal->kode_transaksi = $id;
                            $jurnal->kode_rekening = 1483000;
                            $jurnal->tanggal_transaksi  = $tanggal;
                            $jurnal->jenis_transaksi  = 'Jurnal System';
                            $jurnal->keterangan_transaksi = 'Terima Barang Dagang ' . $master_produk->kode_produk . ' ' . $nama_penerima->nama_toko;
                            $jurnal->debet = 0;
                            $jurnal->kredit = $nominal_margin_sudah_dikirim;
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
                            $jurnal->keterangan_transaksi = 'Terima Barang Dagang ' . $master_produk->kode_produk . ' ' . $nama_penerima->nama_toko;
                            $jurnal->debet = 0;
                            $jurnal->kredit = $nominal_harga_beli_sudah_dikirim;
                            $jurnal->tanggal_posting = '';
                            $jurnal->keterangan_posting = '0';
                            $jurnal->id_admin = Auth::user()->id; 
                            $jurnal->save();


                        }else {
                                                        
                            // Persediaan Barang Dagang
                            $jurnal = new TabelTransaksi;
                            $jurnal->unit =  $penerima; 
                            $jurnal->kode_transaksi = $id;
                            $jurnal->kode_rekening = 1482000;
                            $jurnal->tanggal_transaksi  = $tanggal;
                            $jurnal->jenis_transaksi  = 'Jurnal System';
                            $jurnal->keterangan_transaksi = 'Terima Barang Dagang ' . $master_produk->kode_produk . ' ' . $nama_penerima->nama_toko;
                            $jurnal->debet = $nominal_harga_beli_sudah_dikirim;
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
                            $jurnal->keterangan_transaksi = 'Terima Barang Dagang ' . $master_produk->kode_produk . ' ' . $nama_penerima->nama_toko;
                            $jurnal->debet = 0;
                            $jurnal->kredit = $nominal_harga_beli_sudah_dikirim;
                            $jurnal->tanggal_posting = '';
                            $jurnal->keterangan_posting = '0';
                            $jurnal->id_admin = Auth::user()->id; 
                            $jurnal->save();


                        }

                        //RAK Aktiva Pengirim
                        $jurnal = new TabelTransaksi;
                        $jurnal->unit =  1010; 
                        $jurnal->kode_transaksi = $id;
                        $jurnal->kode_rekening = 1010 .'-'. $gl_penerima;
                        $jurnal->tanggal_transaksi  = $tanggal;
                        $jurnal->jenis_transaksi  = 'Jurnal System';
                        $jurnal->keterangan_transaksi = 'Terima Barang Dagang ' . $master_produk->kode_produk . ' ' . $nama_pengirim->nama_toko;
                        $jurnal->debet = $nominal_harga_beli_sudah_dikirim;
                        $jurnal->kredit = 0;
                        $jurnal->tanggal_posting = '';
                        $jurnal->keterangan_posting = '0';
                        $jurnal->id_admin = Auth::user()->id; 
                        $jurnal->save();

                        //RAK Aktiva Penerima
                        $jurnal = new TabelTransaksi;
                        $jurnal->unit =  1010; 
                        $jurnal->kode_transaksi = $id;
                        $jurnal->kode_rekening = 1010 .'-'. $gl_pengirim;
                        $jurnal->tanggal_transaksi  = $tanggal;
                        $jurnal->jenis_transaksi  = 'Jurnal System';
                        $jurnal->keterangan_transaksi = 'Kirim Barang Dagang ' . $master_produk->kode_produk . ' ' . $nama_pengirim->nama_toko;
                        $jurnal->debet = 0;
                        $jurnal->kredit = $nominal_harga_beli_sudah_dikirim;
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

                        Produk:
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
                            $selisih_kirim_barang->keterangan = 'Selisih_Kurang_Kirim_Barang';
                            $selisih_kirim_barang->no_faktur = $produk_detail->no_faktur;
                            $selisih_kirim_barang->unit = $pengirim;
                            $selisih_kirim_barang->save();
                                
                            // crate kartu stok
                            $kartu_stok = new KartuStok;
                            $kartu_stok->buss_date = date('Y-m-d');
                            $kartu_stok->kode_produk = $list->kode_produk;
                            $kartu_stok->masuk = 0;
                            $kartu_stok->keluar = $stok_toko;
                            $kartu_stok->status = 'kirim_barang';
                            $kartu_stok->kode_transaksi = $id;
                            $kartu_stok->unit = $pengirim;
                            $kartu_stok->save();

                            $harga_beli = $stok_toko * $produk_detail->harga_beli;
                            $harga_jual = $stok_toko * $list->harga_jual;
                            $margin = $harga_jual - $harga_beli;

                            $jurnal = new TabelTransaksi;
                            $jurnal->unit =  $pengirim; 
                            $jurnal->kode_transaksi = $id;
                            $jurnal->kode_rekening = 2500000;
                            $jurnal->tanggal_transaksi  = $tanggal;
                            $jurnal->jenis_transaksi  = 'Jurnal System';
                            $jurnal->keterangan_transaksi = 'Kirim Selisih Gudang Kurang Kirim ' . $master_produk->kode_produk . ' ' . $nama_pengirim->nama_toko;
                            $jurnal->debet = $harga_beli;
                            $jurnal->kredit = 0;
                            $jurnal->tanggal_posting = '';
                            $jurnal->keterangan_posting = '0';
                            $jurnal->id_admin = Auth::user()->id; 
                            $jurnal->save();
                    
                            $jurnal = new TabelTransaksi;
                            $jurnal->unit = $pengirim; 
                            $jurnal->kode_transaksi = $id;
                            $jurnal->kode_rekening = 1482000;
                            $jurnal->tanggal_transaksi  = $tanggal;
                            $jurnal->jenis_transaksi  = 'Jurnal System';
                            $jurnal->keterangan_transaksi = 'Kirim Selisih Gudang Kurang Kirim ' . $master_produk->kode_produk . ' ' . $nama_pengirim->nama_toko;
                            $jurnal->debet = 0;
                            $jurnal->kredit = $harga_beli;
                            $jurnal->tanggal_posting = '';
                            $jurnal->keterangan_posting = '0';
                            $jurnal->id_admin = Auth::user()->id; 
                            $jurnal->save();
                    
                            // crate kartu stok
                            $kartu_stok = new KartuStok;
                            $kartu_stok->buss_date = date('Y-m-d');
                            $kartu_stok->kode_produk = $list->kode_produk;
                            $kartu_stok->masuk = $stok_toko;
                            $kartu_stok->keluar = 0;
                            $kartu_stok->status = 'terima_toko';
                            $kartu_stok->kode_transaksi = $id;
                            $kartu_stok->unit = $penerima;
                            $kartu_stok->save();
                            
                            $stok_toko = Produk::where('kode_produk',$list->kode_produk)->where('unit',$penerima)->first();
                            $stok_toko->stok += $stok_toko;
                            $stok_toko->update(); 
                            
                            $new_produk_detail = new ProdukDetail;
                            $new_produk_detail->kode_produk = $list->kode_produk;
                            $new_produk_detail->nama_produk = $master_produk->nama_produk;
                            $new_produk_detail->stok_detail = $stok_toko;
                            $new_produk_detail->harga_beli = $list->harga_beli;
                            $new_produk_detail->harga_jual_umum = $list->harga_jual;
                            $new_produk_detail->harga_jual_insan = $list->harga_jual;
                            $new_produk_detail->expired_date = $list->expired_date;
                            $new_produk_detail->promo = 0;
                            $new_produk_detail->tanggal_masuk = date('Y-m-d');
                            $new_produk_detail->no_faktur = $list->faktur;
                            $new_produk_detail->unit = $penerima;
                            $new_produk_detail->status = null;
                            $new_produk_detail->promo = 0;
                            $new_produk_detail->save();

                            if ($list->harga_jual > $produk_detail->harga_beli) {
                                                    
                                $jurnal = new TabelTransaksi;
                                $jurnal->unit =  $penerima; 
                                $jurnal->kode_transaksi = $id;
                                $jurnal->kode_rekening = 1482000;
                                $jurnal->tanggal_transaksi  = $tanggal;
                                $jurnal->jenis_transaksi  = 'Jurnal System';
                                $jurnal->keterangan_transaksi = 'Terima Selisih Gudang Kurang Kirim ' . $master_produk->kode_produk . ' ' . $nama_pengirim->nama_toko;
                                $jurnal->debet = $harga_jual;
                                $jurnal->kredit = 0;
                                $jurnal->tanggal_posting = '';
                                $jurnal->keterangan_posting = '0';
                                $jurnal->id_admin = Auth::user()->id; 
                                $jurnal->save();
                        
                                $jurnal = new TabelTransaksi;
                                $jurnal->unit = $penerima; 
                                $jurnal->kode_transaksi = $id;
                                $jurnal->kode_rekening = 1483000;
                                $jurnal->tanggal_transaksi  = $tanggal;
                                $jurnal->jenis_transaksi  = 'Jurnal System';
                                $jurnal->keterangan_transaksi = 'Terima Selisih Gudang Kurang Kirim ' . $master_produk->kode_produk . ' ' . $nama_pengirim->nama_toko;
                                $jurnal->debet = 0;
                                $jurnal->kredit = $margin;
                                $jurnal->tanggal_posting = '';
                                $jurnal->keterangan_posting = '0';
                                $jurnal->id_admin = Auth::user()->id; 
                                $jurnal->save();
                        
                                $jurnal = new TabelTransaksi;
                                $jurnal->unit =  $penerima; 
                                $jurnal->kode_transaksi = $id;
                                $jurnal->kode_rekening = 2500000;
                                $jurnal->tanggal_transaksi  = $tanggal;
                                $jurnal->jenis_transaksi  = 'Jurnal System';
                                $jurnal->keterangan_transaksi = 'Terima Selisih Gudang Kurang Kirim ' . $master_produk->kode_produk . ' ' . $nama_pengirim->nama_toko;
                                $jurnal->debet = 0;
                                $jurnal->kredit = $harga_beli;
                                $jurnal->tanggal_posting = '';
                                $jurnal->keterangan_posting = '0';
                                $jurnal->id_admin = Auth::user()->id; 
                                $jurnal->save();

                                                                    
                            }else {
                            
                                $jurnal = new TabelTransaksi;
                                $jurnal->unit =  $penerima; 
                                $jurnal->kode_transaksi = $id;
                                $jurnal->kode_rekening = 1482000;
                                $jurnal->tanggal_transaksi  = $tanggal;
                                $jurnal->jenis_transaksi  = 'Jurnal System';
                                $jurnal->keterangan_transaksi = 'Terima Selisih Gudang Kurang Kirim ' . $master_produk->kode_produk . ' ' . $nama_pengirim->nama_toko;
                                $jurnal->debet = $harga_beli;
                                $jurnal->kredit = 0;
                                $jurnal->tanggal_posting = '';
                                $jurnal->keterangan_posting = '0';
                                $jurnal->id_admin = Auth::user()->id; 
                                $jurnal->save();

                                $jurnal = new TabelTransaksi;
                                $jurnal->unit =  $penerima;
                                $jurnal->kode_transaksi = $id;
                                $jurnal->kode_rekening = 2500000;
                                $jurnal->tanggal_transaksi  = $tanggal;
                                $jurnal->jenis_transaksi  = 'Jurnal System';
                                $jurnal->keterangan_transaksi = 'Terima Selisih Gudang Kurang Kirim ' . $master_produk->kode_produk . ' ' . $nama_pengirim->nama_toko;
                                $jurnal->debet = 0;
                                $jurnal->kredit = $harga_beli;
                                $jurnal->tanggal_posting = '';
                                $jurnal->keterangan_posting = '0';
                                $jurnal->id_admin = Auth::user()->id; 
                                $jurnal->save();
            
                            }
                                
                            //RAK Aktiva Pengirim
                            $jurnal = new TabelTransaksi;
                            $jurnal->unit =  1010; 
                            $jurnal->kode_transaksi = $id;
                            $jurnal->kode_rekening = 1010 .'-'. $gl_penerima;
                            $jurnal->tanggal_transaksi  = $tanggal;
                            $jurnal->jenis_transaksi  = 'Jurnal System';
                            $jurnal->keterangan_transaksi = 'Terima Selisih Gudang Kurang Kirim ' . $master_produk->kode_produk . ' ' . $nama_pengirim->nama_toko;
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
                            $jurnal->kode_rekening = 1010 .'-'. $gl_pengirim;
                            $jurnal->tanggal_transaksi  = $tanggal;
                            $jurnal->jenis_transaksi  = 'Jurnal System';
                            $jurnal->keterangan_transaksi = 'Kirim Selisih Gudang Kurang Kirim ' . $master_produk->kode_produk . ' ' . $nama_pengirim->nama_toko;
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
                            
                                $harga_beli = $stok_toko * $produk_detail->harga_beli;
                                $harga_jual = $stok_toko * $list->harga_jual;
                                $margin = $harga_jual - $harga_beli;    

                                $selisih_kirim_barang = new SelisihKirimBarang;
                                $selisih_kirim_barang->id_pembelian = $id;
                                $selisih_kirim_barang->kode_produk = $list->kode_produk;
                                $selisih_kirim_barang->harga_jual = $produk_detail->harga_jual_umum;
                                $selisih_kirim_barang->harga_beli = $produk_detail->harga_beli;
                                $selisih_kirim_barang->jumlah = $stok_toko;
                                $selisih_kirim_barang->sub_total_jual = $stok_toko * $list->harga_jual;
                                $selisih_kirim_barang->sub_total = $stok_toko * $produk_detail->harga_beli;
                                $selisih_kirim_barang->expired_date = $produk_detail->expired_date;
                                $selisih_kirim_barang->keterangan = 'Selisih_Kurang_Kirim_Barang';
                                $selisih_kirim_barang->no_faktur = $produk_detail->no_faktur;
                                $selisih_kirim_barang->unit = $pengirim;
                                $selisih_kirim_barang->save();
                                
                                // crate kartu stok
                                $kartu_stok = new KartuStok;
                                $kartu_stok->buss_date = date('Y-m-d');
                                $kartu_stok->kode_produk = $list->kode_produk;
                                $kartu_stok->masuk = 0;
                                $kartu_stok->keluar = $stok_toko;
                                $kartu_stok->status = 'kirim_barang';
                                $kartu_stok->kode_transaksi = $id;
                                $kartu_stok->unit = $pengirim;
                                $kartu_stok->save();

                                $jurnal = new TabelTransaksi;
                                $jurnal->unit =  $pengirim; 
                                $jurnal->kode_transaksi = $id;
                                $jurnal->kode_rekening = 2500000;
                                $jurnal->tanggal_transaksi  = $tanggal;
                                $jurnal->jenis_transaksi  = 'Jurnal System';
                                $jurnal->keterangan_transaksi = 'Kirim Selisih Gudang Kurang Kirim ' . $master_produk->kode_produk . ' ' . $nama_pengirim->nama_toko;
                                $jurnal->debet = $harga_beli;
                                $jurnal->kredit = 0;
                                $jurnal->tanggal_posting = '';
                                $jurnal->keterangan_posting = '0';
                                $jurnal->id_admin = Auth::user()->id; 
                                $jurnal->save();
                        
                                $jurnal = new TabelTransaksi;
                                $jurnal->unit = $pengirim; 
                                $jurnal->kode_transaksi = $id;
                                $jurnal->kode_rekening = 1482000;
                                $jurnal->tanggal_transaksi  = $tanggal;
                                $jurnal->jenis_transaksi  = 'Jurnal System';
                                $jurnal->keterangan_transaksi = 'Kirim Selisih Gudang Kurang Kirim ' . $master_produk->kode_produk . ' ' . $nama_pengirim->nama_toko;
                                $jurnal->debet = 0;
                                $jurnal->kredit = $harga_beli;
                                $jurnal->tanggal_posting = '';
                                $jurnal->keterangan_posting = '0';
                                $jurnal->id_admin = Auth::user()->id; 
                                $jurnal->save();
                            
                                // crate kartu stok
                                $kartu_stok = new KartuStok;
                                $kartu_stok->buss_date = date('Y-m-d');
                                $kartu_stok->kode_produk = $list->kode_produk;
                                $kartu_stok->masuk = $stok_toko;
                                $kartu_stok->keluar = 0;
                                $kartu_stok->status = 'terima_toko';
                                $kartu_stok->kode_transaksi = $id;
                                $kartu_stok->unit = $penerima;
                                $kartu_stok->save();
                                
                                $stok_toko = Produk::where('kode_produk',$list->kode_produk)->where('unit',$penerima)->first();
                                $stok_toko->stok += $stok_toko;
                                $stok_toko->update(); 
                                
                                $new_produk_detail = new ProdukDetail;
                                $new_produk_detail->kode_produk = $list->kode_produk;
                                $new_produk_detail->nama_produk = $master_produk->nama_produk;
                                $new_produk_detail->stok_detail = $stok_toko;
                                $new_produk_detail->harga_beli = $list->harga_beli;
                                $new_produk_detail->harga_jual_umum = $list->harga_jual;
                                $new_produk_detail->harga_jual_insan = $list->harga_jual;
                                $new_produk_detail->expired_date = $list->expired_date;
                                $new_produk_detail->promo = 0;
                                $new_produk_detail->tanggal_masuk = date('Y-m-d');
                                $new_produk_detail->no_faktur = $list->faktur;
                                $new_produk_detail->unit = $penerima;
                                $new_produk_detail->status = null;
                                $new_produk_detail->promo = 0;
                                $new_produk_detail->save();

                                if ($list->harga_jual > $produk_detail->harga_beli) {
                                
                                    $jurnal = new TabelTransaksi;
                                    $jurnal->unit =  $penerima; 
                                    $jurnal->kode_transaksi = $id;
                                    $jurnal->kode_rekening = 1482000;
                                    $jurnal->tanggal_transaksi  = $tanggal;
                                    $jurnal->jenis_transaksi  = 'Jurnal System';
                                    $jurnal->keterangan_transaksi = 'Terima Selisih Gudang Kurang Kirim ' . $master_produk->kode_produk . ' ' . $nama_pengirim->nama_toko;
                                    $jurnal->debet = $harga_jual;
                                    $jurnal->kredit = 0;
                                    $jurnal->tanggal_posting = '';
                                    $jurnal->keterangan_posting = '0';
                                    $jurnal->id_admin = Auth::user()->id; 
                                    $jurnal->save();
                            
                                    $jurnal = new TabelTransaksi;
                                    $jurnal->unit = $penerima; 
                                    $jurnal->kode_transaksi = $id;
                                    $jurnal->kode_rekening = 1483000;
                                    $jurnal->tanggal_transaksi  = $tanggal;
                                    $jurnal->jenis_transaksi  = 'Jurnal System';
                                    $jurnal->keterangan_transaksi = 'Terima Selisih Gudang Kurang Kirim ' . $master_produk->kode_produk . ' ' . $nama_pengirim->nama_toko;
                                    $jurnal->debet = 0;
                                    $jurnal->kredit = $margin;
                                    $jurnal->tanggal_posting = '';
                                    $jurnal->keterangan_posting = '0';
                                    $jurnal->id_admin = Auth::user()->id; 
                                    $jurnal->save();
                                
                                    $jurnal = new TabelTransaksi;
                                    $jurnal->unit =  $penerima; 
                                    $jurnal->kode_transaksi = $id;
                                    $jurnal->kode_rekening = 2500000;
                                    $jurnal->tanggal_transaksi  = $tanggal;
                                    $jurnal->jenis_transaksi  = 'Jurnal System';
                                    $jurnal->keterangan_transaksi = 'Terima Selisih Gudang Kurang Kirim ' . $master_produk->kode_produk . ' ' . $nama_pengirim->nama_toko;
                                    $jurnal->debet = 0;
                                    $jurnal->kredit = $harga_beli;
                                    $jurnal->tanggal_posting = '';
                                    $jurnal->keterangan_posting = '0';
                                    $jurnal->id_admin = Auth::user()->id; 
                                    $jurnal->save();
                                    
                                }else {
                                    
                                    $harga_beli_promo = $stok_toko * $produk_detail->harga_beli; 
                                    
                                    $jurnal = new TabelTransaksi;
                                    $jurnal->unit =  $penerima; 
                                    $jurnal->kode_transaksi = $id;
                                    $jurnal->kode_rekening = 1482000;
                                    $jurnal->tanggal_transaksi  = $tanggal;
                                    $jurnal->jenis_transaksi  = 'Jurnal System';
                                    $jurnal->keterangan_transaksi = 'Terima Selisih Gudang Kurang Kirim ' . $master_produk->kode_produk . ' ' . $nama_pengirim->nama_toko;
                                    $jurnal->debet = $harga_beli;
                                    $jurnal->kredit = 0;
                                    $jurnal->tanggal_posting = '';
                                    $jurnal->keterangan_posting = '0';
                                    $jurnal->id_admin = Auth::user()->id; 
                                    $jurnal->save();
    
                                    $jurnal = new TabelTransaksi;
                                    $jurnal->unit =  $penerima;
                                    $jurnal->kode_transaksi = $id;
                                    $jurnal->kode_rekening = 2500000;
                                    $jurnal->tanggal_transaksi  = $tanggal;
                                    $jurnal->jenis_transaksi  = 'Jurnal System';
                                    $jurnal->keterangan_transaksi = 'Terima Selisih Gudang Kurang Kirim ' . $master_produk->kode_produk . ' ' . $nama_pengirim->nama_toko;
                                    $jurnal->debet = 0;
                                    $jurnal->kredit = $harga_beli;
                                    $jurnal->tanggal_posting = '';
                                    $jurnal->keterangan_posting = '0';
                                    $jurnal->id_admin = Auth::user()->id; 
                                    $jurnal->save();
                
                                }
                            
                                //RAK Aktiva Pengirim
                                $jurnal = new TabelTransaksi;
                                $jurnal->unit =  1010; 
                                $jurnal->kode_transaksi = $id;
                                $jurnal->kode_rekening = 1010 . '-' . $gl_penerima;
                                $jurnal->tanggal_transaksi  = $tanggal;
                                $jurnal->jenis_transaksi  = 'Jurnal System';
                                $jurnal->keterangan_transaksi = 'Terima Selisih Gudang Kurang Kirim ' . $master_produk->kode_produk . ' ' . $nama_pengirim->nama_toko;
                                $jurnal->debet = $harga_beli;
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
                                $jurnal->keterangan_transaksi = 'Kirim Selisih Gudang Kurang Kirim ' . $master_produk->kode_produk . ' ' . $nama_pengirim->nama_toko;
                                $jurnal->debet = 0;
                                $jurnal->kredit = $harga_beli;
                                $jurnal->tanggal_posting = '';
                                $jurnal->keterangan_posting = '0';
                                $jurnal->id_admin = Auth::user()->id; 
                                $jurnal->save();
                                                                                
                                
                                // sisa qty kirim_barang yang dikurangi stok toko yang harganya paling rendah
                                $jumlah_selisih = $stok;
                                
                                $produk_detail->update(['stok_detail'=>0]);
                    
                                // mengulangi looping untuk mencari harga yang paling rendah
                                goto Produk;
                                    
                                // jika pengurangan qty kirim_barang dengan stok toko hasilnya kurang dari 0 atau minus
                            }else if($stok < 0){
                                
                                $harga_beli = $jumlah_selisih * $produk_detail->harga_beli;
                                $harga_jual = $jumlah_selisih * $list->harga_jual;
                                $margin = $harga_jual - $harga_beli;    

                                $selisih_kirim_barang = new SelisihKirimBarang;
                                $selisih_kirim_barang->id_pembelian = $id;
                                $selisih_kirim_barang->kode_produk = $list->kode_produk;
                                $selisih_kirim_barang->harga_jual = $produk_detail->harga_jual_umum;
                                $selisih_kirim_barang->harga_beli = $produk_detail->harga_beli;
                                $selisih_kirim_barang->jumlah = $jumlah_selisih;
                                $selisih_kirim_barang->sub_total_jual = $jumlah_selisih * $list->harga_jual;
                                $selisih_kirim_barang->sub_total = $jumlah_selisih * $produk_detail->harga_beli;
                                $selisih_kirim_barang->expired_date = $produk_detail->expired_date;
                                $selisih_kirim_barang->keterangan = 'Selisih_Kurang_Kirim_Barang';
                                $selisih_kirim_barang->no_faktur = $produk_detail->no_faktur;
                                $selisih_kirim_barang->unit = $pengirim;
                                $selisih_kirim_barang->save();
                                
                                // crate kartu stok
                                $kartu_stok = new KartuStok;
                                $kartu_stok->buss_date = date('Y-m-d');
                                $kartu_stok->kode_produk = $list->kode_produk;
                                $kartu_stok->masuk = 0;
                                $kartu_stok->keluar = $jumlah_selisih;
                                $kartu_stok->status = 'kirim_barang';
                                $kartu_stok->kode_transaksi = $id;
                                $kartu_stok->unit = $pengirim;
                                $kartu_stok->save();

                                
                                $jurnal = new TabelTransaksi;
                                $jurnal->unit =  $pengirim; 
                                $jurnal->kode_transaksi = $id;
                                $jurnal->kode_rekening = 2500000;
                                $jurnal->tanggal_transaksi  = $tanggal;
                                $jurnal->jenis_transaksi  = 'Jurnal System';
                                $jurnal->keterangan_transaksi = 'Kirim Selisih Gudang Kurang Kirim ' . $master_produk->kode_produk . ' ' . $nama_pengirim->nama_toko;
                                $jurnal->debet = $harga_beli;
                                $jurnal->kredit = 0;
                                $jurnal->tanggal_posting = '';
                                $jurnal->keterangan_posting = '0';
                                $jurnal->id_admin = Auth::user()->id; 
                                $jurnal->save();
                        
                                $jurnal = new TabelTransaksi;
                                $jurnal->unit = $pengirim; 
                                $jurnal->kode_transaksi = $id;
                                $jurnal->kode_rekening = 1482000;
                                $jurnal->tanggal_transaksi  = $tanggal;
                                $jurnal->jenis_transaksi  = 'Jurnal System';
                                $jurnal->keterangan_transaksi = 'Kirim Selisih Gudang Kurang Kirim ' . $master_produk->kode_produk . ' ' . $nama_pengirim->nama_toko;
                                $jurnal->debet = 0;
                                $jurnal->kredit = $harga_beli;
                                $jurnal->tanggal_posting = '';
                                $jurnal->keterangan_posting = '0';
                                $jurnal->id_admin = Auth::user()->id; 
                                $jurnal->save();
                            
                                // crate kartu stok
                                $kartu_stok = new KartuStok;
                                $kartu_stok->buss_date = date('Y-m-d');
                                $kartu_stok->kode_produk = $list->kode_produk;
                                $kartu_stok->masuk = $jumlah_selisih;
                                $kartu_stok->keluar = 0;
                                $kartu_stok->status = 'terima_toko';
                                $kartu_stok->kode_transaksi = $id;
                                $kartu_stok->unit = $penerima;
                                $kartu_stok->save();
                                
                                $stok_toko = Produk::where('kode_produk',$list->kode_produk)->where('unit',$penerima)->first();
                                $stok_toko->stok += $jumlah_selisih;
                                $stok_toko->update(); 
                                
                                $new_produk_detail = new ProdukDetail;
                                $new_produk_detail->kode_produk = $list->kode_produk;
                                $new_produk_detail->nama_produk = $master_produk->nama_produk;
                                $new_produk_detail->stok_detail = $jumlah_selisih;
                                $new_produk_detail->harga_beli = $produk_detail->harga_beli;
                                $new_produk_detail->harga_jual_umum = $list->harga_jual;
                                $new_produk_detail->harga_jual_insan = $list->harga_jual;
                                $new_produk_detail->expired_date = $list->expired_date;
                                $new_produk_detail->promo = 0;
                                $new_produk_detail->tanggal_masuk = date('Y-m-d');
                                $new_produk_detail->no_faktur = $list->faktur;
                                $new_produk_detail->unit = $penerima;
                                $new_produk_detail->status = null;
                                $new_produk_detail->promo = 0;
                                $new_produk_detail->save();

                                
                                if ($list->harga_jual > $produk_detail->harga_beli) {
                                
                                    $jurnal = new TabelTransaksi;
                                    $jurnal->unit =  $penerima; 
                                    $jurnal->kode_transaksi = $id;
                                    $jurnal->kode_rekening = 1482000;
                                    $jurnal->tanggal_transaksi  = $tanggal;
                                    $jurnal->jenis_transaksi  = 'Jurnal System';
                                    $jurnal->keterangan_transaksi = 'Terima Selisih Gudang Kurang Kirim ' . $master_produk->kode_produk . ' ' . $nama_pengirim->nama_toko;
                                    $jurnal->debet = $harga_jual;
                                    $jurnal->kredit = 0;
                                    $jurnal->tanggal_posting = '';
                                    $jurnal->keterangan_posting = '0';
                                    $jurnal->id_admin = Auth::user()->id; 
                                    $jurnal->save();
                            
                                    $jurnal = new TabelTransaksi;
                                    $jurnal->unit = $penerima; 
                                    $jurnal->kode_transaksi = $id;
                                    $jurnal->kode_rekening = 1483000;
                                    $jurnal->tanggal_transaksi  = $tanggal;
                                    $jurnal->jenis_transaksi  = 'Jurnal System';
                                    $jurnal->keterangan_transaksi = 'Terima Selisih Gudang Kurang Kirim ' . $master_produk->kode_produk . ' ' . $nama_pengirim->nama_toko;
                                    $jurnal->debet = 0;
                                    $jurnal->kredit = $margin;
                                    $jurnal->tanggal_posting = '';
                                    $jurnal->keterangan_posting = '0';
                                    $jurnal->id_admin = Auth::user()->id; 
                                    $jurnal->save();
                                
                                    $jurnal = new TabelTransaksi;
                                    $jurnal->unit =  $penerima; 
                                    $jurnal->kode_transaksi = $id;
                                    $jurnal->kode_rekening = 2500000;
                                    $jurnal->tanggal_transaksi  = $tanggal;
                                    $jurnal->jenis_transaksi  = 'Jurnal System';
                                    $jurnal->keterangan_transaksi = 'Terima Selisih Gudang Kurang Kirim ' . $master_produk->kode_produk . ' ' . $nama_pengirim->nama_toko;
                                    $jurnal->debet = 0;
                                    $jurnal->kredit = $harga_beli;
                                    $jurnal->tanggal_posting = '';
                                    $jurnal->keterangan_posting = '0';
                                    $jurnal->id_admin = Auth::user()->id; 
                                    $jurnal->save();
                                    
                                }else {
                                       
                                    $jurnal = new TabelTransaksi;
                                    $jurnal->unit =  $penerima; 
                                    $jurnal->kode_transaksi = $id;
                                    $jurnal->kode_rekening = 1482000;
                                    $jurnal->tanggal_transaksi  = $tanggal;
                                    $jurnal->jenis_transaksi  = 'Jurnal System';
                                    $jurnal->keterangan_transaksi = 'Terima Selisih Gudang Kurang Kirim ' . $master_produk->kode_produk . ' ' . $nama_pengirim->nama_toko;
                                    $jurnal->debet = $harga_beli;
                                    $jurnal->kredit = 0;
                                    $jurnal->tanggal_posting = '';
                                    $jurnal->keterangan_posting = '0';
                                    $jurnal->id_admin = Auth::user()->id; 
                                    $jurnal->save();
    
                                    $jurnal = new TabelTransaksi;
                                    $jurnal->unit =  $penerima;
                                    $jurnal->kode_transaksi = $id;
                                    $jurnal->kode_rekening = 2500000;
                                    $jurnal->tanggal_transaksi  = $tanggal;
                                    $jurnal->jenis_transaksi  = 'Jurnal System';
                                    $jurnal->keterangan_transaksi = 'Terima Selisih Gudang Kurang Kirim ' . $master_produk->kode_produk . ' ' . $nama_pengirim->nama_toko;
                                    $jurnal->debet = 0;
                                    $jurnal->kredit = $harga_beli;
                                    $jurnal->tanggal_posting = '';
                                    $jurnal->keterangan_posting = '0';
                                    $jurnal->id_admin = Auth::user()->id; 
                                    $jurnal->save();
                
                                }

                                //RAK Aktiva Pengirim
                                $jurnal = new TabelTransaksi;
                                $jurnal->unit =  1010; 
                                $jurnal->kode_transaksi = $id;
                                $jurnal->kode_rekening = 1010 .'-'. $gl_penerima;
                                $jurnal->tanggal_transaksi  = $tanggal;
                                $jurnal->jenis_transaksi  = 'Jurnal System';
                                $jurnal->keterangan_transaksi = 'Terima Selisih Gudang Kurang Kirim ' . $master_produk->kode_produk . ' ' . $nama_pengirim->nama_toko;
                                $jurnal->debet = $harga_beli;
                                $jurnal->kredit = 0;
                                $jurnal->tanggal_posting = '';
                                $jurnal->keterangan_posting = '0';
                                $jurnal->id_admin = Auth::user()->id; 
                                $jurnal->save();

                                //RAK Aktiva Penerima
                                $jurnal = new TabelTransaksi;
                                $jurnal->unit =  1010; 
                                $jurnal->kode_transaksi = $id;
                                $jurnal->kode_rekening = 1010 .'-'. $gl_pengirim;
                                $jurnal->tanggal_transaksi  = $tanggal;
                                $jurnal->jenis_transaksi  = 'Jurnal System';
                                $jurnal->keterangan_transaksi = 'Kirim Selisih Gudang Kurang Kirim ' . $master_produk->kode_produk . ' ' . $nama_pengirim->nama_toko;
                                $jurnal->debet = 0;
                                $jurnal->kredit = $harga_beli;
                                $jurnal->tanggal_posting = '';
                                $jurnal->keterangan_posting = '0';
                                $jurnal->id_admin = Auth::user()->id; 
                                $jurnal->save();
                                
                                
                                // update stok_detail berdasar sisa pengurangan qty penjualan dengan stok toko hasilnya kurang dari 0 atau minus
                                $produk_detail->update(['stok_detail'=>abs($stok)]);
                                    
                            }    
                        }

                    }elseif ($list->jumlah > $list->jumlah_terima) {
                    // jika diterima lebih sedikit dari yang dikirim
                    
                        $jumlah_selisih = $list->jumlah - $list->jumlah_terima;

                        // jumlah yang sudah dikirim dijurnal terlebih dahulu
                        $jumlah_sudah_dikirim = $list->jumlah_terima;
                        $nominal_harga_jual_sudah_dikirim = $list->jumlah_terima * $list->harga_jual;    
                        $nominal_harga_beli_sudah_dikirim = $list->jumlah_terima * $list->harga_beli;                            
                        $nominal_margin_sudah_dikirim = $nominal_harga_jual_sudah_dikirim - $nominal_harga_beli_sudah_dikirim;    

                        // crate kartu stok
                        $kartu_stok = new KartuStok;
                        $kartu_stok->buss_date = date('Y-m-d');
                        $kartu_stok->kode_produk = $list->kode_produk;
                        $kartu_stok->masuk = $jumlah_sudah_dikirim;
                        $kartu_stok->keluar = 0;
                        $kartu_stok->status = 'terima_toko';
                        $kartu_stok->kode_transaksi = $id;
                        $kartu_stok->unit = $penerima;
                        $kartu_stok->save();
                        
                        //stok toko
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
                        $new_produk_detail->no_faktur = $list->faktur;
                        $new_produk_detail->unit = $penerima;
                        $new_produk_detail->status = null;
                        $new_produk_detail->promo = 0;
                        $new_produk_detail->save();

                        $stok_toko = Produk::where('kode_produk',$list->kode_produk)->where('unit',$penerima)->first();
                        $stok_toko->stok += $jumlah_sudah_dikirim;
                        $stok_toko->update(); 
                        
                        if ($list->harga_jual > $list->harga_beli) {
                                                        
                            // Persediaan Barang Dagang
                            $jurnal = new TabelTransaksi;
                            $jurnal->unit =  $penerima; 
                            $jurnal->kode_transaksi = $id;
                            $jurnal->kode_rekening = 1482000;
                            $jurnal->tanggal_transaksi  = $tanggal;
                            $jurnal->jenis_transaksi  = 'Jurnal System';
                            $jurnal->keterangan_transaksi = 'Terima Barang Dagang ' . $master_produk->kode_produk . ' ' . $nama_penerima->nama_toko;
                            $jurnal->debet = $nominal_harga_jual_sudah_dikirim;
                            $jurnal->kredit = 0;
                            $jurnal->tanggal_posting = '';
                            $jurnal->keterangan_posting = '0';
                            $jurnal->id_admin = Auth::user()->id; 
                            $jurnal->save();
                            
                            // PMYD
                            $jurnal = new TabelTransaksi;
                            $jurnal->unit =  $penerima; 
                            $jurnal->kode_transaksi = $id;
                            $jurnal->kode_rekening = 1483000;
                            $jurnal->tanggal_transaksi  = $tanggal;
                            $jurnal->jenis_transaksi  = 'Jurnal System';
                            $jurnal->keterangan_transaksi = 'Terima Barang Dagang ' . $master_produk->kode_produk . ' ' . $nama_penerima->nama_toko;
                            $jurnal->debet = 0;
                            $jurnal->kredit = $nominal_margin_sudah_dikirim;
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
                            $jurnal->keterangan_transaksi = 'Terima Barang Dagang ' . $master_produk->kode_produk . ' ' . $nama_penerima->nama_toko;
                            $jurnal->debet = 0;
                            $jurnal->kredit = $nominal_harga_beli_sudah_dikirim;
                            $jurnal->tanggal_posting = '';
                            $jurnal->keterangan_posting = '0';
                            $jurnal->id_admin = Auth::user()->id; 
                            $jurnal->save();


                        }else {
                                                        
                            // Persediaan Barang Dagang
                            $jurnal = new TabelTransaksi;
                            $jurnal->unit =  $penerima; 
                            $jurnal->kode_transaksi = $id;
                            $jurnal->kode_rekening = 1482000;
                            $jurnal->tanggal_transaksi  = $tanggal;
                            $jurnal->jenis_transaksi  = 'Jurnal System';
                            $jurnal->keterangan_transaksi = 'Terima Barang Dagang ' . $master_produk->kode_produk . ' ' . $nama_penerima->nama_toko;
                            $jurnal->debet = $nominal_harga_beli_sudah_dikirim;
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
                            $jurnal->keterangan_transaksi = 'Terima Barang Dagang ' . $master_produk->kode_produk . ' ' . $nama_penerima->nama_toko;
                            $jurnal->debet = 0;
                            $jurnal->kredit = $nominal_harga_beli_sudah_dikirim;
                            $jurnal->tanggal_posting = '';
                            $jurnal->keterangan_posting = '0';
                            $jurnal->id_admin = Auth::user()->id; 
                            $jurnal->save();


                        }

                        //RAK Aktiva Pengirim
                        $jurnal = new TabelTransaksi;
                        $jurnal->unit =  1010; 
                        $jurnal->kode_transaksi = $id;
                        $jurnal->kode_rekening = 1010 .'-'. $gl_penerima;
                        $jurnal->tanggal_transaksi  = $tanggal;
                        $jurnal->jenis_transaksi  = 'Jurnal System';
                        $jurnal->keterangan_transaksi = 'Terima Barang Dagang ' . $master_produk->kode_produk . ' ' . $nama_pengirim->nama_toko;
                        $jurnal->debet = $nominal_harga_beli_sudah_dikirim;
                        $jurnal->kredit = 0;
                        $jurnal->tanggal_posting = '';
                        $jurnal->keterangan_posting = '0';
                        $jurnal->id_admin = Auth::user()->id; 
                        $jurnal->save();

                        //RAK Aktiva Penerima
                        $jurnal = new TabelTransaksi;
                        $jurnal->unit =  1010; 
                        $jurnal->kode_transaksi = $id;
                        $jurnal->kode_rekening = 1010 .'-'. $gl_pengirim;
                        $jurnal->tanggal_transaksi  = $tanggal;
                        $jurnal->jenis_transaksi  = 'Jurnal System';
                        $jurnal->keterangan_transaksi = 'Kirim Barang Dagang ' . $master_produk->kode_produk . ' ' . $nama_pengirim->nama_toko;
                        $jurnal->debet = 0;
                        $jurnal->kredit = $nominal_harga_beli_sudah_dikirim;
                        $jurnal->tanggal_posting = '';
                        $jurnal->keterangan_posting = '0';
                        $jurnal->id_admin = Auth::user()->id; 
                        $jurnal->save();
                         
                        //kembalikan Stok Gudang
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
                        $new_produk_detail->no_faktur = $list->faktur;
                        $new_produk_detail->unit = $pengirim;
                        $new_produk_detail->status = null;
                        $new_produk_detail->promo = 0;
                        $new_produk_detail->save();

                        $stok_gudang = Produk::where('kode_produk',$list->kode_produk)->where('unit',$pengirim)->first();
                        $stok_gudang->stok += $jumlah_selisih;
                        $stok_gudang->update(); 
                        
                        // crate kartu stok
                        $kartu_stok = new KartuStok;
                        $kartu_stok->buss_date = date('Y-m-d');
                        $kartu_stok->kode_produk = $list->kode_produk;
                        $kartu_stok->masuk = $jumlah_selisih;
                        $kartu_stok->keluar = 0;
                        $kartu_stok->status = 'terima_gudang';
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
                        $selisih_kirim_barang->keterangan = 'Selisih_Lebih_Kirim_Barang';
                        $selisih_kirim_barang->no_faktur = $list->no_faktur;
                        $selisih_kirim_barang->unit = $pengirim;
                        $selisih_kirim_barang->save();

                        // Persediaan Barang Dagang
                        $jurnal = new TabelTransaksi;
                        $jurnal->unit =  $pengirim; 
                        $jurnal->kode_transaksi = $id;
                        $jurnal->kode_rekening = 1482000;
                        $jurnal->tanggal_transaksi  = $tanggal;
                        $jurnal->jenis_transaksi  = 'Jurnal System';
                        $jurnal->keterangan_transaksi = 'Selisih Gudang Lebih Kirim Barang Dagang ' . $master_produk->kode_produk . ' ' . $nama_pengirim->nama_toko ;
                        $jurnal->debet = $list->harga_beli * $jumlah_selisih;
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
                        $jurnal->keterangan_transaksi = 'Selisih Gudang Lebih Kirim Barang Dagang ' . $master_produk->kode_produk . ' ' . $nama_pengirim->nama_toko ;
                        $jurnal->debet = 0;
                        $jurnal->kredit = $list->harga_beli * $jumlah_selisih;
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
                        $jurnal->keterangan_transaksi = 'Selisih Gudang Lebih Kirim Barang Dagang ' . $master_produk->kode_produk . ' ' . $nama_pengirim->nama_toko ;
                        $jurnal->debet = $list->harga_beli * $jumlah_selisih;
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
                        $jurnal->keterangan_transaksi = 'Selisih Gudang Lebih Kirim Barang Dagang ' . $master_produk->kode_produk . ' ' . $nama_pengirim->nama_toko ;
                        $jurnal->debet = 0;
                        $jurnal->kredit = $list->harga_beli * $jumlah_selisih;
                        $jurnal->tanggal_posting = '';
                        $jurnal->keterangan_posting = '0';
                        $jurnal->id_admin = Auth::user()->id; 
                        $jurnal->save();

                            
                    }else {
                        
                        // jumlah yang sudah dikirim dijurnal terlebih dahulu
                        $jumlah_sudah_dikirim = $list->jumlah_terima;
                        $nominal_harga_jual_sudah_dikirim = $list->jumlah_terima * $list->harga_jual;    
                        $nominal_harga_beli_sudah_dikirim = $list->jumlah_terima * $list->harga_beli;                            
                        $nominal_margin_sudah_dikirim = $nominal_harga_jual_sudah_dikirim - $nominal_harga_beli_sudah_dikirim;    

                        // crate kartu stok
                        $kartu_stok = new KartuStok;
                        $kartu_stok->buss_date = date('Y-m-d');
                        $kartu_stok->kode_produk = $list->kode_produk;
                        $kartu_stok->masuk = $jumlah_sudah_dikirim;
                        $kartu_stok->keluar = 0;
                        $kartu_stok->status = 'terima_toko';
                        $kartu_stok->kode_transaksi = $id;
                        $kartu_stok->unit = $penerima;
                        $kartu_stok->save();
                        
                        //stok toko
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
                        $new_produk_detail->no_faktur = $list->faktur;
                        $new_produk_detail->unit = $penerima;
                        $new_produk_detail->status = null;
                        $new_produk_detail->promo = 0;
                        $new_produk_detail->save();

                        $stok_toko = Produk::where('kode_produk',$list->kode_produk)->where('unit',$penerima)->first();
                        $stok_toko->stok += $jumlah_sudah_dikirim;
                        $stok_toko->update(); 
                        
                        if ($list->harga_jual > $list->harga_beli) {
                                                        
                            // Persediaan Barang Dagang
                            $jurnal = new TabelTransaksi;
                            $jurnal->unit =  $penerima; 
                            $jurnal->kode_transaksi = $id;
                            $jurnal->kode_rekening = 1482000;
                            $jurnal->tanggal_transaksi  = $tanggal;
                            $jurnal->jenis_transaksi  = 'Jurnal System';
                            $jurnal->keterangan_transaksi = 'Terima Barang Dagang ' . $master_produk->kode_produk . ' ' . $nama_penerima->nama_toko;
                            $jurnal->debet = $nominal_harga_jual_sudah_dikirim;
                            $jurnal->kredit = 0;
                            $jurnal->tanggal_posting = '';
                            $jurnal->keterangan_posting = '0';
                            $jurnal->id_admin = Auth::user()->id; 
                            $jurnal->save();
                            
                            // PMYD
                            $jurnal = new TabelTransaksi;
                            $jurnal->unit =  $penerima; 
                            $jurnal->kode_transaksi = $id;
                            $jurnal->kode_rekening = 1483000;
                            $jurnal->tanggal_transaksi  = $tanggal;
                            $jurnal->jenis_transaksi  = 'Jurnal System';
                            $jurnal->keterangan_transaksi = 'Terima Barang Dagang ' . $master_produk->kode_produk . ' ' . $nama_penerima->nama_toko;
                            $jurnal->debet = 0;
                            $jurnal->kredit = $nominal_margin_sudah_dikirim;
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
                            $jurnal->keterangan_transaksi = 'Terima Barang Dagang ' . $master_produk->kode_produk . ' ' . $nama_penerima->nama_toko;
                            $jurnal->debet = 0;
                            $jurnal->kredit = $nominal_harga_beli_sudah_dikirim;
                            $jurnal->tanggal_posting = '';
                            $jurnal->keterangan_posting = '0';
                            $jurnal->id_admin = Auth::user()->id; 
                            $jurnal->save();


                        }else {
                                                        
                            // Persediaan Barang Dagang
                            $jurnal = new TabelTransaksi;
                            $jurnal->unit =  $penerima; 
                            $jurnal->kode_transaksi = $id;
                            $jurnal->kode_rekening = 1482000;
                            $jurnal->tanggal_transaksi  = $tanggal;
                            $jurnal->jenis_transaksi  = 'Jurnal System';
                            $jurnal->keterangan_transaksi = 'Terima Barang Dagang ' . $master_produk->kode_produk . ' ' . $nama_penerima->nama_toko;
                            $jurnal->debet = $nominal_harga_beli_sudah_dikirim;
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
                            $jurnal->keterangan_transaksi = 'Terima Barang Dagang ' . $master_produk->kode_produk . ' ' . $nama_penerima->nama_toko;
                            $jurnal->debet = 0;
                            $jurnal->kredit = $nominal_harga_beli_sudah_dikirim;
                            $jurnal->tanggal_posting = '';
                            $jurnal->keterangan_posting = '0';
                            $jurnal->id_admin = Auth::user()->id; 
                            $jurnal->save();


                        }

                        //RAK Aktiva Pengirim
                        $jurnal = new TabelTransaksi;
                        $jurnal->unit =  1010; 
                        $jurnal->kode_transaksi = $id;
                        $jurnal->kode_rekening = 1010 .'-'. $gl_penerima;
                        $jurnal->tanggal_transaksi  = $tanggal;
                        $jurnal->jenis_transaksi  = 'Jurnal System';
                        $jurnal->keterangan_transaksi = 'Terima Barang Dagang ' . $master_produk->kode_produk . ' ' . $nama_pengirim->nama_toko;
                        $jurnal->debet = $nominal_harga_beli_sudah_dikirim;
                        $jurnal->kredit = 0;
                        $jurnal->tanggal_posting = '';
                        $jurnal->keterangan_posting = '0';
                        $jurnal->id_admin = Auth::user()->id; 
                        $jurnal->save();

                        //RAK Aktiva Penerima
                        $jurnal = new TabelTransaksi;
                        $jurnal->unit =  1010; 
                        $jurnal->kode_transaksi = $id;
                        $jurnal->kode_rekening = 1010 .'-'. $gl_pengirim;
                        $jurnal->tanggal_transaksi  = $tanggal;
                        $jurnal->jenis_transaksi  = 'Jurnal System';
                        $jurnal->keterangan_transaksi = 'Kirim Barang Dagang ' . $master_produk->kode_produk . ' ' . $nama_pengirim->nama_toko;
                        $jurnal->debet = 0;
                        $jurnal->kredit = $nominal_harga_beli_sudah_dikirim;
                        $jurnal->tanggal_posting = '';
                        $jurnal->keterangan_posting = '0';
                        $jurnal->id_admin = Auth::user()->id; 
                        $jurnal->save();
                         
                        


                    }  
                }
            
            Kirim::where('id_pembelian',$id)->update(['status' => 2]);
            DB::commit();
            return redirect()->route('approve_terima_toko.index')->with(['success' => 'Surat Jalan Berhasil Di Approve !']);;
        
        }catch(\Exception $e){
     
            DB::rollback();
            return back()->with(['error' => $e->getmessage() .' '. $e->getLine()]);
    
        }

    }

    public function reject($id){

        $kirim = Kirim::where('id_pembelian',$id)->first();
        $kirim->status = 1;
        $kirim->update();

        return redirect()->route('approve_terima_toko.index')->with(['success' => 'Surat Jalan Berhasil Di Reject !']);;
    }


}
