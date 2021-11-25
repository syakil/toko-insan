<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Redirect;
use App\Kirim;
use Auth;
use PDF;
use App\Supplier;
use DB;
use App\KartuStok;
use App\KirimDetail;
use App\KirimDetailTemporary;
use App\Produk;
use App\ProdukDetail;
use App\Branch;
use App\TabelTransaksi;
use Carbon\Carbon;

class KirimAntarGudangController extends Controller{

  public function index(){
      
    $supplier = Supplier::all();
    $branch =DB::select(DB::raw('SELECT * FROM branch WHERE kode_toko = kode_gudang AND kode_gudang != '.Auth::user()->unit));
    $no = 1;
    
    $kirim = Kirim::leftJoin('branch', 'branch.kode_toko', '=', 'kirim_barang.id_supplier')
    ->where('status','hold')
    ->where('tujuan','gudang')
    ->where('kirim_barang.kode_gudang',Auth::user()->unit)
    ->orderBy('kirim_barang.updated_at', 'desc')
    ->get();

    $cek = Kirim::where('status','hold')->first();

    if ($cek == null) {
      $data = 0;
    }else{
      $data = 1;
    }

    return view('kirim_antar_gudang.index', compact('supplier','branch','kirim','no','data')); 
  
  }        


  public function listData(){
  
    if (Auth::user()->level==5){
      $pembelian = Kirim::leftJoin('branch', 'branch.kode_toko', '=', 'kirim_barang.id_supplier')
      ->where('status',1)
      ->orderBy('kirim_barang.id_pembelian', 'desc')
      ->get();
    }elseif(Auth::user()->level==4){
      $pembelian = Kirim::leftJoin('branch', 'branch.kode_toko', '=', 'kirim_barang.id_supplier')
      ->where('kirim_barang.kode_gudang',Auth::user()->unit)
      ->where('tujuan','toko')
      ->where('status',1)
      ->orderBy('kirim_barang.id_pembelian', 'desc')
      ->get();
    }
    
    $no = 0;
    $data = array();
    foreach($pembelian as $list){
  
      $no ++;
      $row = array();
      $row[] = $no;
      $row[] = $list->id_pembelian;
      $row[] = tanggal_indonesia(substr($list->created_at, 0, 10), false);
      $row[] = $list->nama_toko;
      $row[] = $list->total_item;
      $row[] = "Rp. ".format_uang($list->total_harga);
      $row[] = $list->diskon."%";
      $row[] = "Rp. ".format_uang($list->bayar);
      $row[] = '<div class="btn-group">
              <a onclick="showDetail('.$list->id_pembelian.')" class="btn btn-primary btn-sm"><i class="fa fa-eye"></i></a>
              <a href="/toko/kirim_antar_gudang/'.$list->id_pembelian.'/poPDF" class="btn btn-print btn-sm" target="_blank"><i class="fa fa-print"></i></a>
            </div>';
      $data[] = $row;
  
    }

    $output = array("data" => $data);
    return response()->json($output);
  
  }

  public function show($id){

    $detail = KirimDetail::leftJoin('produk', 'produk.kode_produk', '=', 'kirim_barang_detail.kode_produk')
      ->where('id_pembelian', '=', $id)
      ->where('unit',Auth::user()->unit)
      ->get();
      

    $no = 0;
    $data = array();
    foreach($detail as $list){
      $no ++;
      $row = array();
      $row[] = $no;
      $row[] = $list->kode_produk;
      $row[] = $list->nama_produk;
      $row[] = "Rp. ".format_uang($list->harga_beli);
      $row[] = $list->jumlah;
      $row[] = $list->jumlah_terima;
      $row[] = $list->status_jurnal;
      $row[] = "Rp. ".format_uang($list->harga_beli * $list->jumlah);
      $data[] = $row;
    }

    $output = array("data" => $data);
    return response()->json($output);
  }

  public function cetak($id){
    
    session()->forget('cetak');

    $data['produk'] = KirimDetail::leftJoin('produk','kirim_barang_detail.kode_produk','=','produk.kode_produk')
                                      ->where('id_pembelian',$id)
                                      ->where('produk.unit',Auth::user()->unit)
                                      ->get();

    $data['alamat']= Kirim::leftJoin('branch','kirim_barang.id_supplier','=','branch.kode_toko')
                            ->where('id_pembelian',$id)
                            ->first();

    $kirim = Kirim::find($id);
    $pengirim = $kirim->kode_gudang;
    $penerima = $kirim->id_supplier;
    $tanggal_kirim = Carbon::createFromFormat ( "Y-m-d H:i:s", $kirim->updated_at );
    $data['alamat_pengirim'] = Branch::where('kode_toko',$kirim->kode_gudang)->first();
    $data['alamat_penerima'] = Branch::where('kode_toko',$kirim->id_supplier)->first();

    $data['nomer_surat'] = 'TRF/'.$id.'/'.$pengirim.'/'.$penerima.'/'.$tanggal_kirim->format('m/Y');
    $data['data_surat'] = Kirim::where('id_pembelian',$id)->get();
    $data['no'] =1;
    $pdf = PDF::loadView('kirim_antar_gudang.cetak_sj', $data);
    return $pdf->stream('TRF-'.$id.'-'.$pengirim.'-'.$penerima.'-'.$tanggal_kirim->format('m/Y').'.pdf');

  }

  public function create($id){

    $pembelian = new Kirim;
    $pembelian->id_supplier = $id;     
    $pembelian->total_item = 0;     
    $pembelian->total_harga = 0;     
    $pembelian->total_margin = 0;
    $pembelian->total_terima = 0;
    $pembelian->total_harga_terima = 0;
    $pembelian->total_margin_terima = 0;
    $pembelian->kode_gudang = 0;
    $pembelian->status = 'hold';
    $pembelian->id_user = Auth::user()->id;
    $pembelian->kode_gudang = Auth::user()->unit;
    $pembelian->tujuan = 'gudang';
    $pembelian->status_kirim = 'transfer'; 
    $pembelian->save();    

    session(['idpembelian' => $pembelian->id_pembelian]);
    session(['idsupplier' => $id]);
    session(['kode_toko' => $id]);

    return Redirect::route('kirim_antar_gudang_detail.index');      
  }

  public function hold(Request $request){
    
    $pembelian = Kirim::find($request['idpembelian']);
    $pembelian->total_item = $request['totalitem'];
    $pembelian->total_harga = $request['total'];
    $pembelian->diskon = $request['diskon'];
    $pembelian->bayar = $request['bayar'];
    $pembelian->update();
    
    return view('kirim_antar_gudang.index', compact('supplier','branch')); 
    
  }

  public function store(Request $request){

    try {

      DB::beginTransaction();

      $param_tgl = \App\ParamTgl::where('nama_param_tgl','tanggal_transaksi')->where('unit',Auth::user()->id)->first();
      $tanggal = $param_tgl->param_tgl;
      
      $id_pembelian = $request->idpembelian;
      
      $details = KirimDetailTemporary::where('id_pembelian', '=', $request->idpembelian)->orderBy('id_pembelian_detail','desc')->get();
      
      $data_kirim = Kirim::where('id_pembelian', '=', $request->idpembelian)->first();
      $pengirim = $data_kirim->kode_gudang;

      foreach($details as $list){
        
        $cek_sum_kirim= KirimDetailTemporary::where('id_pembelian', $request->idpembelian)->where('kode_produk',$list->kode_produk)->sum('jumlah');
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
        
        // jika qty pengiriman == jumlah stok yang tersedia
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
          // jika selisih qty pengiriman dengan jumlah stok yang tersedia
              
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
            $jurnal->keterangan_transaksi = 'Kirim Barang Antar Gudang '. $produk_detail->kode_produk;
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
            $jurnal->keterangan_transaksi = 'Kirim Barang Antar Gudang ' . $produk_detail->kode_produk;
            $jurnal->debet =0;
            $jurnal->kredit = $harga_beli;
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
            $jurnal->keterangan_transaksi = 'Kirim Barang Antar Gudang ' . $produk_detail->kode_produk;
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
            $jurnal->keterangan_transaksi = 'Kirim Barang Antar Gudang ' . $produk_detail->kode_produk;
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
              $jurnal->keterangan_transaksi = 'Kirim Barang Antar Gudang ' . $produk_detail->kode_produk;
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
              $jurnal->keterangan_transaksi = 'Kirim Barang Antar Gudang ' . $produk_detail->kode_produk;
              $jurnal->debet =0;
              $jurnal->kredit = $harga_beli;
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
              $jurnal->keterangan_transaksi = 'Kirim Barang Antar Gudang ' . $produk_detail->kode_produk;
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
              $jurnal->keterangan_transaksi = 'Kirim Barang Antar Gudang ' . $produk_detail->kode_produk;
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
              $jurnal->keterangan_transaksi = 'Kirim Barang Antar Gudang ' . $produk_detail->kode_produk;
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
              $jurnal->keterangan_transaksi = 'Kirim Barang Antar Gudang ' . $produk_detail->kode_produk;
              $jurnal->debet =0;
              $jurnal->kredit = $harga_beli;
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
              $jurnal->keterangan_transaksi = 'Kirim Barang Antar Gudang ' . $produk_detail->kode_produk;
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
              $jurnal->keterangan_transaksi = 'Kirim Barang Antar Gudang ' . $produk_detail->kode_produk;
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
  
      DB::commit();
      
    }catch(\Exception $e){
  
        DB::rollback();
        return back()->with(['error' => $e->getmessage()]);
        
    }
  
    return Redirect::route('kirim_antar_gudang.index')->with(['success' => 'Surat Jalan Berhasil Di Proses !']); 
      
  }

  public function destroy($id){
    $pembelian = Kirim::find($id);
    $pembelian->delete();

    $detail = KirimDetail::where('id_pembelian', '=', $id)->get();
    foreach($detail as $data){
      $produk = ProdukDetail::where('kode_produk', '=', $data->kode_produk)
                              ->where('expired_date',$data->expired_date)                      
                              ->first();
      $produk->stok += $data->jumlah;
      $produk->update();
      $data->delete();
    }
  }
}
