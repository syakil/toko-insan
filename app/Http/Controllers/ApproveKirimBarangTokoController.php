<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Kirim;
use App\KirimDetail;
use App\KirimDetailTemporary;
use App\KartuStok;
use App\TabelTransaksi;
use Redirect;
use App\Produk;
use App\ProdukDetail;
use DB;
use Auth;
use App\Branch;



class ApproveKirimBarangTokoController extends Controller{
    
    public function index(){

        return view('approve_kirim_barang_toko.index');

    }


    public function listData(){

        $branch = Branch::where('kode_gudang',Auth::user()->unit)->get();
        $kode_toko = array();

        foreach($branch as $list){
            if ($list->kode_toko != Auth::user()->unit) {
                $kode_toko[] = $list->kode_toko;
            }
        }

        $kirim_barang = Kirim::leftJoin('branch', 'branch.kode_toko', '=', 'kirim_barang.id_supplier')
        ->where('status','approval')
        ->whereIn('kirim_barang.kode_gudang',$kode_toko)
        ->select('kirim_barang.*','branch.nama_toko')
        ->orderBy('kirim_barang.id_pembelian', 'desc')
        ->get();

        $no = 0;
        $data = array();

        foreach($kirim_barang as $list){
            
            $nama_pengirim = Branch::where('kode_toko',$list->kode_gudang)->first();
        
            $no ++;
            $row = array();
            $row[] = $no;
            $row[] = $list->id_pembelian;
            $row[] = tanggal_indonesia(substr($list->created_at, 0, 10), false);
            $row[] = $nama_pengirim->nama_toko;            
            $row[] = $list->nama_toko;
            $row[] = $list->status_kirim;
            $row[] = $list->total_item;
            $row[] = "<div class='btn-group'>
                    <a href='".route('approve_kirim_barang_toko.detail',$list->id_pembelian)."' class='btn btn-primary btn-sm'><i class='fa fa-eye'></i></a>
                    </div>";
            $data[] = $row;
        
        }
  
        $output = array("data" => $data);
        return response()->json($output);

    }


    public function detail($id){

        return view('approve_kirim_barang_toko.detail',compact('id'));

    }

    public function listDetail($id){

        $no = 0;
        $data = array();
        $id_toko = Kirim::where('id_pembelian',$id)->first();

 $detail = KirimDetailTemporary::
        leftJoin('produk', 'produk.kode_produk', '=', 'kirim_barang_detail_temporary.kode_produk')
        ->leftJoin('status_kirim','status_kirim.id_status','kirim_barang_detail_temporary.keterangan')
        ->where('id_pembelian', '=', $id)
        ->where('unit', '=', $id_toko->kode_gudang)
        ->select('kirim_barang_detail_temporary.*','produk.kode_produk','produk.nama_produk','status_kirim.status','produk.stok')
        ->orderBy('updated_at','desc')        
        ->get();

        foreach($detail as $list){

            $no ++;

            $stok_toko = Produk::where('kode_produk',$list->kode_produk)->where('unit',$id_toko->id_supplier)->first();
            $row = array();
            $row[] = $no;
            $row[] = $list->kode_produk;
            $row[] = $list->nama_produk;
            $row[] = $list->stok;
            $row[] = $list->jumlah;
            $row[] = $list->status;
            $data[] = $row;

        }

        $output = array("data" => $data);
        return response()->json($output);

    }

    public function approve(Request $request){

        date_default_timezone_set("Asia/Bangkok");

        try {

            DB::beginTransaction();

            $param_tgl = \App\ParamTgl::where('nama_param_tgl','tanggal_transaksi')->where('unit',Auth::user()->id)->first();
            $tanggal = $param_tgl->param_tgl;
            
            $id_pembelian = $request->idpembelian;
            
            $details = KirimDetailTemporary::where('id_pembelian', '=', $request->idpembelian)->orderBy('id_pembelian_detail','desc')->get();
            
            $data_kirim = Kirim::where('id_pembelian', '=', $request->idpembelian)->first();
            $pengirim = $data_kirim->kode_gudang;


            // --- //
            foreach($details as $list){
              
                $cek_sum_kirim= KirimDetail::where('id_pembelian', $request->idpembelian)->where('kode_produk',$list->kode_produk)->sum('jumlah');
                $produk = Produk::where('kode_produk',$list->kode_produk)->where('unit',$pengirim)->first();
                $produk_detail = ProdukDetail::where('kode_produk',$list->kode_produk)
                ->where('unit',$pengirim)
                ->sum('stok_detail');
        
                if($cek_sum_kirim > $produk_detail){
                    return back()->with(['error' => 'Stock '.$list->kode_produk .' ' .$produk->nama_produk . ' Kurang']);
                }      
                
                if($cek_sum_kirim > $produk->stok){
                    return back()->with(['error' => 'Stock '. $list->kode_produk .' ' .$produk->nama_produk . ' Kurang']);
                }
        
                if ($list->keterangan == null) {
                    return back()->with(['error' => 'Pilih keterangan retur produk '.$list->kode_produk .' ' .$produk->nama_produk]);
                }
              
            }
      
            foreach($details as $d){
      
                $kode = $d->kode_produk;
                $jumlah_kirim = $d->jumlah;
            
                // mengaambil stok di produk_detail berdasar barcode dan harga beli lebih rendah (stok yang tesedria) yang terdapat di penjualan_detail_temporary
                produk:
                $produk_detail = ProdukDetail::where('kode_produk',$kode)
                ->where('unit',$pengirim)
                ->where('stok_detail','>','0')
                ->orderBy('tanggal_masuk','ASC')
                ->first();
              
                // buat variable stok toko dari column stok_detail dari table produk_detail
                $stok_toko = $produk_detail->stok_detail;
              
                // jika qty penjualan == jumlah stok yang tersedia ditoko
                if ($jumlah_kirim == $stok_toko) {
                    
                    $produk_detail->update(['stok_detail'=>0]);
            
                    $detail = new KirimDetail;
                    $detail->id_pembelian = $request->idpembelian;
                    $detail->kode_produk = $kode;
                    $detail->harga_jual = $produk_detail->harga_jual_umum;
                    $detail->harga_beli = $produk_detail->harga_beli;
                    $detail->jumlah = $jumlah_kirim;
                    $detail->jumlah_terima = 0;
                    $detail->sub_total = $produk_detail->harga_beli * $jumlah_kirim;
                    $detail->sub_total_terima = 0;
                    $detail->sub_total_margin = $produk_detail->harga_jual_umum * $jumlah_kirim;
                    $detail->sub_total_margin_terima = 0;
                    $detail->expired_date = $produk_detail->expired_date;
                    $detail->jurnal_status = 0;
                    $detail->keterangan = $d->keterangan;
                    $detail->no_faktur = $produk_detail->no_faktur;
                    $detail->save();
                    
                    $kartu_stok = new KartuStok;
                    $kartu_stok->buss_date = date('Y-m-d');
                    $kartu_stok->kode_produk = $kode;
                    $kartu_stok->masuk = 0;
                    $kartu_stok->keluar = $jumlah_kirim;
                    $kartu_stok->status = 'kirim_barang';
                    $kartu_stok->kode_transaksi = $request->idpembelian;
                    $kartu_stok->unit = $pengirim;
                    $kartu_stok->save();
                    // jika selisih qty penjualan dengan jumlah stok yang tersedia
                    
                    if ($produk_detail->harga_jual_umum > $produk_detail->harga_beli) {
     
                        $harga_beli = $jumlah_kirim * $produk_detail->harga_beli;
                        $harga_jual = $jumlah_kirim * $produk_detail->harga_jual_umum;
                        $margin = $harga_jual - $harga_beli;


                        $jurnal = new TabelTransaksi;
                        $jurnal->unit =  $pengirim; 
                        $jurnal->kode_transaksi = $request->idpembelian;
                        $jurnal->kode_rekening = 2500000;
                        $jurnal->tanggal_transaksi  = $tanggal;
                        $jurnal->jenis_transaksi  = 'Jurnal System';
                        $jurnal->keterangan_transaksi = 'Kirim Barang Toko' . ' ' . $produk_detail->kode_produk . ' ' . $produk_detail->nama_produk;
                        $jurnal->debet = $harga_beli;
                        $jurnal->kredit = 0;
                        $jurnal->tanggal_posting = '';
                        $jurnal->keterangan_posting = '0';
                        $jurnal->id_admin = Auth::user()->id; 
                        $jurnal->save();
                
                        $jurnal = new TabelTransaksi;
                        $jurnal->unit =  $pengirim; 
                        $jurnal->kode_transaksi = $d->id_pembelian;
                        $jurnal->kode_rekening = 1483000;
                        $jurnal->tanggal_transaksi  = $tanggal;
                        $jurnal->jenis_transaksi  = 'Jurnal System';
                        $jurnal->keterangan_transaksi = 'Kirim Barang Toko' . ' ' . $produk_detail->kode_produk . ' ' . $produk_detail->nama_produk;
                        $jurnal->debet = $margin;
                        $jurnal->kredit =0;
                        $jurnal->tanggal_posting = '';
                        $jurnal->keterangan_posting = '0';
                        $jurnal->id_admin = Auth::user()->id; 
                        $jurnal->save();
                
                        $jurnal = new TabelTransaksi;
                        $jurnal->unit =  $pengirim; 
                        $jurnal->kode_transaksi = $d->id_pembelian;
                        $jurnal->kode_rekening = 1482000;
                        $jurnal->tanggal_transaksi  = $tanggal;
                        $jurnal->jenis_transaksi  = 'Jurnal System';
                        $jurnal->keterangan_transaksi = 'Kirim Barang Toko' . ' ' . $produk_detail->kode_produk . ' ' . $produk_detail->nama_produk;
                        $jurnal->debet =0;
                        $jurnal->kredit = $harga_jual;
                        $jurnal->tanggal_posting = '';
                        $jurnal->keterangan_posting = '0';
                        $jurnal->id_admin = Auth::user()->id; 
                        $jurnal->save();
               
                    }else {
                        
                        
                        $harga_beli = $jumlah_kirim * $produk_detail->harga_beli;
                        $harga_jual = $jumlah_kirim * $produk_detail->harga_jual_umum;
                        $margin = $harga_jual - $harga_beli;


                        $jurnal = new TabelTransaksi;
                        $jurnal->unit =  $pengirim; 
                        $jurnal->kode_transaksi = $request->idpembelian;
                        $jurnal->kode_rekening = 2500000;
                        $jurnal->tanggal_transaksi  = $tanggal;
                        $jurnal->jenis_transaksi  = 'Jurnal System';
                        $jurnal->keterangan_transaksi = 'Kirim Barang Toko' . ' ' . $produk_detail->kode_produk . ' ' . $produk_detail->nama_produk;
                        $jurnal->debet = $harga_beli;
                        $jurnal->kredit = 0;
                        $jurnal->tanggal_posting = '';
                        $jurnal->keterangan_posting = '0';
                        $jurnal->id_admin = Auth::user()->id; 
                        $jurnal->save();
                
                        $jurnal = new TabelTransaksi;
                        $jurnal->unit =  $pengirim; 
                        $jurnal->kode_transaksi = $d->id_pembelian;
                        $jurnal->kode_rekening = 1482000;
                        $jurnal->tanggal_transaksi  = $tanggal;
                        $jurnal->jenis_transaksi  = 'Jurnal System';
                        $jurnal->keterangan_transaksi = 'Kirim Barang Toko' . ' ' . $produk_detail->kode_produk . ' ' . $produk_detail->nama_produk;
                        $jurnal->debet =0;
                        $jurnal->kredit = $harga_beli;
                        $jurnal->tanggal_posting = '';
                        $jurnal->keterangan_posting = '0';
                        $jurnal->id_admin = Auth::user()->id; 
                        $jurnal->save();

                    }

                }else {
                
                    // mengurangi qty penjualan dengan stok toko berdasarkan stok_detail(table produk_detail)
                    $stok = $jumlah_kirim - $stok_toko;
        
                    // jika hasilnya lebih dari nol atau tidak minus, stok_detail tsb tidak memenuhi qty penjualan dan harus ambil lagi record pada produk detail~
                    // ~ yang stok nya lebih dari nol
        
                    if ($stok >= 0) {
                    
                        // update produk_detail->stok_detail menjadi nol berdasarkan $produk_detail 
                        $produk_detail->update(['stok_detail'=>0]);
            
                        $detail = new KirimDetail;
                        $detail->id_pembelian = $request->idpembelian;
                        $detail->kode_produk = $kode;
                        $detail->harga_jual = $produk_detail->harga_jual_umum;
                        $detail->harga_beli = $produk_detail->harga_beli;
                        $detail->jumlah = $stok_toko;
                        $detail->jumlah_terima = 0;
                        $detail->sub_total = $produk_detail->harga_beli * $stok_toko;
                        $detail->sub_total_terima = 0;
                        $detail->sub_total_margin = $produk_detail->harga_jual_umum * $stok_toko;
                        $detail->sub_total_margin_terima = 0;
                        $detail->expired_date = $produk_detail->expired_date;
                        $detail->jurnal_status = 0;
                        $detail->keterangan = $d->keterangan;
                        $detail->no_faktur = $produk_detail->no_faktur;
                        $detail->save();
                        
                        $kartu_stok = new KartuStok;
                        $kartu_stok->buss_date = date('Y-m-d');
                        $kartu_stok->kode_produk = $kode;
                        $kartu_stok->masuk = 0;
                        $kartu_stok->keluar = $stok_toko;
                        $kartu_stok->status = 'kirim_barang';
                        $kartu_stok->kode_transaksi = $request->idpembelian;
                        $kartu_stok->unit = $pengirim;
                        $kartu_stok->save();
                            
                        if ($produk_detail->harga_jual_umum > $produk_detail->harga_beli) {
        
                            $harga_beli = $stok_toko * $produk_detail->harga_beli;
                            $harga_jual = $stok_toko * $produk_detail->harga_jual_umum;
                            $margin = $harga_jual - $harga_beli;


                            $jurnal = new TabelTransaksi;
                            $jurnal->unit =  $pengirim; 
                            $jurnal->kode_transaksi = $request->idpembelian;
                            $jurnal->kode_rekening = 2500000;
                            $jurnal->tanggal_transaksi  = $tanggal;
                            $jurnal->jenis_transaksi  = 'Jurnal System';
                            $jurnal->keterangan_transaksi = 'Kirim Barang Toko' . ' ' . $produk_detail->kode_produk . ' ' . $produk_detail->nama_produk;
                            $jurnal->debet = $harga_beli;
                            $jurnal->kredit = 0;
                            $jurnal->tanggal_posting = '';
                            $jurnal->keterangan_posting = '0';
                            $jurnal->id_admin = Auth::user()->id; 
                            $jurnal->save();
                    
                            $jurnal = new TabelTransaksi;
                            $jurnal->unit =  $pengirim; 
                            $jurnal->kode_transaksi = $d->id_pembelian;
                            $jurnal->kode_rekening = 1483000;
                            $jurnal->tanggal_transaksi  = $tanggal;
                            $jurnal->jenis_transaksi  = 'Jurnal System';
                            $jurnal->keterangan_transaksi = 'Kirim Barang Toko' . ' ' . $produk_detail->kode_produk . ' ' . $produk_detail->nama_produk;
                            $jurnal->debet = $margin;
                            $jurnal->kredit =0;
                            $jurnal->tanggal_posting = '';
                            $jurnal->keterangan_posting = '0';
                            $jurnal->id_admin = Auth::user()->id; 
                            $jurnal->save();
                    
                            $jurnal = new TabelTransaksi;
                            $jurnal->unit =  $pengirim; 
                            $jurnal->kode_transaksi = $d->id_pembelian;
                            $jurnal->kode_rekening = 1482000;
                            $jurnal->tanggal_transaksi  = $tanggal;
                            $jurnal->jenis_transaksi  = 'Jurnal System';
                            $jurnal->keterangan_transaksi = 'Kirim Barang Toko' . ' ' . $produk_detail->kode_produk . ' ' . $produk_detail->nama_produk;
                            $jurnal->debet =0;
                            $jurnal->kredit = $harga_jual;
                            $jurnal->tanggal_posting = '';
                            $jurnal->keterangan_posting = '0';
                            $jurnal->id_admin = Auth::user()->id; 
                            $jurnal->save();
                
                        }else {
                            
                            
                            $harga_beli = $stok_toko * $produk_detail->harga_beli;
                            $harga_jual = $stok_toko * $produk_detail->harga_jual_umum;
                            $margin = $harga_jual - $harga_beli;


                            $jurnal = new TabelTransaksi;
                            $jurnal->unit =  $pengirim; 
                            $jurnal->kode_transaksi = $request->idpembelian;
                            $jurnal->kode_rekening = 2500000;
                            $jurnal->tanggal_transaksi  = $tanggal;
                            $jurnal->jenis_transaksi  = 'Jurnal System';
                            $jurnal->keterangan_transaksi = 'Kirim Barang Toko' . ' ' . $produk_detail->kode_produk . ' ' . $produk_detail->nama_produk;
                            $jurnal->debet = $harga_beli;
                            $jurnal->kredit = 0;
                            $jurnal->tanggal_posting = '';
                            $jurnal->keterangan_posting = '0';
                            $jurnal->id_admin = Auth::user()->id; 
                            $jurnal->save();
                    
                            $jurnal = new TabelTransaksi;
                            $jurnal->unit =  $pengirim; 
                            $jurnal->kode_transaksi = $d->id_pembelian;
                            $jurnal->kode_rekening = 1482000;
                            $jurnal->tanggal_transaksi  = $tanggal;
                            $jurnal->jenis_transaksi  = 'Jurnal System';
                            $jurnal->keterangan_transaksi = 'Kirim Barang Toko' . ' ' . $produk_detail->kode_produk . ' ' . $produk_detail->nama_produk;
                            $jurnal->debet =0;
                            $jurnal->kredit = $harga_beli;
                            $jurnal->tanggal_posting = '';
                            $jurnal->keterangan_posting = '0';
                            $jurnal->id_admin = Auth::user()->id; 
                            $jurnal->save();

                        }
                        // sisa qty penjualan yang dikurangi stok toko yang harganya paling rendah
                        $jumlah_kirim = $stok;
        
                        // mengulangi looping untuk mencari harga yang paling rendah
                        goto produk;
                        
                    // jika pengurangan qty penjualan dengan stok toko hasilnya kurang dari 0 atau minus
                    }else if($stok < 0){
        
                        // update stok_detail berdasar sisa pengurangan qty penjualan dengan stok toko hasilnya kurang dari 0 atau minus
                        $produk_detail->update(['stok_detail'=>abs($stok)]);
                        
                        $detail = new KirimDetail;
                        $detail->id_pembelian = $request->idpembelian;
                        $detail->kode_produk = $kode;
                        $detail->harga_jual = $produk_detail->harga_jual_umum;
                        $detail->harga_beli = $produk_detail->harga_beli;
                        $detail->jumlah = $jumlah_kirim;
                        $detail->jumlah_terima = 0;
                        $detail->sub_total = $produk_detail->harga_beli * $jumlah_kirim;
                        $detail->sub_total_terima = 0;
                        $detail->sub_total_margin = $produk_detail->harga_jual_umum * $jumlah_kirim;
                        $detail->sub_total_margin_terima = 0;
                        $detail->expired_date = $produk_detail->expired_date;
                        $detail->jurnal_status = 0;
                        $detail->keterangan = $d->keterangan;
                        $detail->no_faktur = $produk_detail->no_faktur;
                        $detail->save();
            
                        $kartu_stok = new KartuStok;
                        $kartu_stok->buss_date = date('Y-m-d');
                        $kartu_stok->kode_produk = $kode;
                        $kartu_stok->masuk = 0;
                        $kartu_stok->keluar = $jumlah_kirim;
                        $kartu_stok->status = 'kirim_barang';
                        $kartu_stok->kode_transaksi = $request->idpembelian;
                        $kartu_stok->unit = $pengirim;
                        $kartu_stok->save();
                        
                        
                        if ($produk_detail->harga_jual_umum > $produk_detail->harga_beli) {
        
                            $harga_beli = $jumlah_kirim * $produk_detail->harga_beli;
                            $harga_jual = $jumlah_kirim * $produk_detail->harga_jual_umum;
                            $margin = $harga_jual - $harga_beli;


                            $jurnal = new TabelTransaksi;
                            $jurnal->unit =  $pengirim; 
                            $jurnal->kode_transaksi = $request->idpembelian;
                            $jurnal->kode_rekening = 2500000;
                            $jurnal->tanggal_transaksi  = $tanggal;
                            $jurnal->jenis_transaksi  = 'Jurnal System';
                            $jurnal->keterangan_transaksi = 'Kirim Barang Toko' . ' ' . $produk_detail->kode_produk . ' ' . $produk_detail->nama_produk;
                            $jurnal->debet = $harga_beli;
                            $jurnal->kredit = 0;
                            $jurnal->tanggal_posting = '';
                            $jurnal->keterangan_posting = '0';
                            $jurnal->id_admin = Auth::user()->id; 
                            $jurnal->save();
                    
                            $jurnal = new TabelTransaksi;
                            $jurnal->unit =  $pengirim; 
                            $jurnal->kode_transaksi = $d->id_pembelian;
                            $jurnal->kode_rekening = 1483000;
                            $jurnal->tanggal_transaksi  = $tanggal;
                            $jurnal->jenis_transaksi  = 'Jurnal System';
                            $jurnal->keterangan_transaksi = 'Kirim Barang Toko' . ' ' . $produk_detail->kode_produk . ' ' . $produk_detail->nama_produk;
                            $jurnal->debet = $margin;
                            $jurnal->kredit =0;
                            $jurnal->tanggal_posting = '';
                            $jurnal->keterangan_posting = '0';
                            $jurnal->id_admin = Auth::user()->id; 
                            $jurnal->save();
                    
                            $jurnal = new TabelTransaksi;
                            $jurnal->unit =  $pengirim; 
                            $jurnal->kode_transaksi = $d->id_pembelian;
                            $jurnal->kode_rekening = 1482000;
                            $jurnal->tanggal_transaksi  = $tanggal;
                            $jurnal->jenis_transaksi  = 'Jurnal System';
                            $jurnal->keterangan_transaksi = 'Kirim Barang Toko' . ' ' . $produk_detail->kode_produk . ' ' . $produk_detail->nama_produk;
                            $jurnal->debet =0;
                            $jurnal->kredit = $harga_jual;
                            $jurnal->tanggal_posting = '';
                            $jurnal->keterangan_posting = '0';
                            $jurnal->id_admin = Auth::user()->id; 
                            $jurnal->save();
                
                        }else {
                            
                            
                            $harga_beli = $jumlah_kirim * $produk_detail->harga_beli;
                            $harga_jual = $jumlah_kirim * $produk_detail->harga_jual_umum;
                            $margin = $harga_jual - $harga_beli;


                            $jurnal = new TabelTransaksi;
                            $jurnal->unit =  $pengirim; 
                            $jurnal->kode_transaksi = $request->idpembelian;
                            $jurnal->kode_rekening = 2500000;
                            $jurnal->tanggal_transaksi  = $tanggal;
                            $jurnal->jenis_transaksi  = 'Jurnal System';
                            $jurnal->keterangan_transaksi = 'Kirim Barang Toko' . ' ' . $produk_detail->kode_produk . ' ' . $produk_detail->nama_produk;
                            $jurnal->debet = $harga_beli;
                            $jurnal->kredit = 0;
                            $jurnal->tanggal_posting = '';
                            $jurnal->keterangan_posting = '0';
                            $jurnal->id_admin = Auth::user()->id; 
                            $jurnal->save();
                    
                            $jurnal = new TabelTransaksi;
                            $jurnal->unit =  $pengirim; 
                            $jurnal->kode_transaksi = $d->id_pembelian;
                            $jurnal->kode_rekening = 1482000;
                            $jurnal->tanggal_transaksi  = $tanggal;
                            $jurnal->jenis_transaksi  = 'Jurnal System';
                            $jurnal->keterangan_transaksi = 'Kirim Barang Toko' . ' ' . $produk_detail->kode_produk . ' ' . $produk_detail->nama_produk;
                            $jurnal->debet =0;
                            $jurnal->kredit = $harga_beli;
                            $jurnal->tanggal_posting = '';
                            $jurnal->keterangan_posting = '0';
                            $jurnal->id_admin = Auth::user()->id; 
                            $jurnal->save();

                        }
                    }    
                }
            }
      
            foreach($details as $list){
      
              $produk = Produk::where('kode_produk',$list->kode_produk)->where('unit',$pengirim)->first();
              $produk->stok -= $list->jumlah;
              $produk->update();
      
            }
      
            //  update table kirim_barang
            $total_item = KirimDetail::where('id_pembelian',$id_pembelian)->sum('jumlah');
            $total_harga = KirimDetail::where('id_pembelian',$id_pembelian)->sum('sub_total');
            $total_margin = KirimDetail::where('id_pembelian',$id_pembelian)->sum('sub_total_margin');
      
            $kirim_barang = Kirim::where('id_pembelian',$id_pembelian)->first();
            $kirim_barang->total_item = $total_item;
            $kirim_barang->total_margin = $total_margin;
            $kirim_barang->total_harga = $total_harga;
            $kirim_barang->update();
      
            //insert jurnal 
            $data = Kirim::leftJoin('branch','kirim_barang.id_supplier','=','branch.kode_toko')
                        ->where('id_pembelian',$request->idpembelian)
                        ->get();
             
            $kirim_status = Kirim::where('id_pembelian',$request->idpembelian)->update(['status'=>1]);
            
            $request->session()->forget('idpembelian');
      
            $id = $request->idpembelian;
            session(['cetak'=>$id]);
            KirimDetailTemporary::where('id_pembelian', '=', $request->idpembelian)->orderBy('id_pembelian_detail','desc')->delete();
      
            
            DB::commit();
          
        }catch(\Exception $e){
      
            DB::rollback();
            return back()->with(['error' => $e->getmessage() . '|' . $e->getLine() .'|'. $e->getFile()]);
            
        }
      
        return Redirect::route('approve_kirim_barang_toko.index')->with(['success' => 'Surat Jalan Berhasil Di Approve !']); 
          
    }

    public function reject($id){

        try {
            
            DB::beginTransaction();

            $pembelian = Kirim::find($id);
            $pembelian->status = 'hold';
            $pembelian->update();
            
            DB::commit();        
            return Redirect::route('approve_kirim_barang_toko.index')->with(['success' => 'Surat Jalan Berhasil Di Reject !']);     
        
        }catch(\Exception $e){
                
            DB::rollback();
            return back()->with(['error' => $e->getmessage()]);

        }
            
    }

}
