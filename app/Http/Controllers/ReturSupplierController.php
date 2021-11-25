<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Redirect;
use App\Kirim;
use App\KartuStok;
use Auth;
use PDF;
use App\Supplier;
use App\KirimDetail;
use App\KirimDetailTemporary;
use App\Produk;
use App\ProdukWriteOff;
use App\ProdukDetail;
use App\Branch;
use App\TabelTransaksi;
use DB;


class ReturSupplierController extends Controller{

   public function index(){
      $supplier = Supplier::all();
      $branch = Branch::all();
      return view('retur_supplier.index', compact('supplier','branch')); 
   }

   public function listData(){
      $pembelian = Kirim::leftJoin('supplier', 'supplier.id_supplier', '=', 'kirim_barang.id_supplier')
                        ->select('kirim_barang.*','supplier.nama')
                        ->orderBy('kirim_barang.id_pembelian', 'desc')
                        ->where('tujuan','supplier') 
                        ->where('kode_gudang',Auth::user()->unit)
                        ->get();
   
      $no = 0;
      $data = array();
      foreach($pembelian as $list){
         $no ++;
         $row = array();
         $row[] = $no;
         $row[] = tanggal_indonesia($list->created_at);
         $row[] = $list->nama;
         $row[] = $list->total_item;
         $row[] = "Rp. ".format_uang($list->total_harga);
         $row[] = '<div class="btn-group">
                  <a onclick="showDetail('.$list->id_pembelian.')" class="btn btn-primary btn-sm"><i class="fa fa-eye"></i></a>
                  <a href="/toko/retur_supplier/'.$list->id_pembelian.'/poPDF" class="btn btn-print btn-sm" target="_blank"><i class="fa fa-print"></i></a>
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

      $data['produk'] = KirimDetail::leftJoin('produk','kirim_barang_detail.kode_produk','=','produk.kode_produk')
                                       ->where('id_pembelian',$id)
                                       ->where('produk.unit',Auth::user()->unit)
                                       ->get();

      $data['alamat']= Kirim::leftJoin('supplier','kirim_barang.id_supplier','=','supplier.id_supplier')
      ->leftJoin('branch','kirim_barang.kode_gudang','=','branch.kode_gudang')
      ->select('supplier.*','branch.kode_gudang','branch.nama_gudang')
      ->where('id_pembelian',$id)
      ->first();
      $data['nosurat'] = Kirim::where('id_pembelian',$id)->get();
      $data['no'] =1;
      $pdf = PDF::loadView('retur_supplier.cetak_sj', $data);
      return $pdf->stream('surat_jalan.pdf');
   
   }

   public function create($id){
   
      $pembelian = new Kirim;
      $pembelian->id_supplier = $id;     
      $pembelian->total_item = 0;     
      $pembelian->total_harga = 0;     
      $pembelian->total_terima = 0;
      $pembelian->total_harga_terima = 0;
      $pembelian->id_user = Auth::user()->id;
      $pembelian->kode_gudang = Auth::user()->unit;
      $pembelian->tujuan = 'supplier';
      $pembelian->status = null;
      $pembelian->status_kirim = 'retur'; 
      $pembelian->save();    

      session(['idpembelian' => $pembelian->id_pembelian]);
      session(['idsupplier' => $id]);
      session(['kode_gudang' => $pembelian->kode_gudang]);
      return Redirect::route('retur_supplier_detail.index');      
   
   }

   public function store(Request $request){
      
      try {
         
         DB::beginTransaction();
            
            $id_pembelian = $request['idpembelian'];

            $total_item = KirimDetailTemporary::where('id_pembelian',$id_pembelian)->sum('jumlah');
            $total_harga = KirimDetailTemporary::where('id_pembelian',$id_pembelian)->sum('sub_total');
            $total_margin = KirimDetailTemporary::where('id_pembelian',$id_pembelian)->sum('sub_total_margin');
   
            $kirim_barang = Kirim::where('id_pembelian',$id_pembelian)->first();
            $kirim_barang->total_item = $total_item;
            $kirim_barang->total_margin = $total_margin;
            $kirim_barang->total_harga = $total_harga;
            $kirim_barang->status = 'approve';
            $kirim_barang->update();

            $request->session()->forget('idpembelian');
         
         DB::commit();
         return redirect()->route('retur_supplier.index')->with(['success' => 'Retur Berhasil di Buat !']);

      }catch(\Exception $e){
         
         DB::rollback();
         return back()->with(['error' => $e->getmessage()]);
   
      }
      // try {

      //    DB::beginTransaction();

         
      //    $pembelian = Kirim::find($request['idpembelian']);
      //    //kode syakil
      //    $details = KirimDetailTemporary::where('id_pembelian', '=', $request['idpembelian'])->orderBy('id_pembelian_detail','desc')->get();
 
      //    // --- //
      //    foreach($details as $list){
         
      //      $cek_sum_kirim= KirimDetailTemporary::where('id_pembelian', $request['idpembelian'])->where('kode_produk',$list->kode_produk)->sum('jumlah');
      //      $produk = Produk::where('kode_produk',$list->kode_produk)->where('unit',Auth::user()->unit)->first();
      //      $produk_detail = ProdukDetail::where('kode_produk',$list->kode_produk)
      //      ->where('unit',Auth::user()->unit)
      //      ->where('status',null)
      //      ->sum('stok');
           
 
      //      if($cek_sum_kirim > $produk_detail){
      //        return back()->with(['error' => 'Stock '. $list->kode_produk . ' Kurang']);
      //      }      
                 
      //      if($cek_sum_kirim > $produk->stok){
      //        return back()->with(['error' => 'Stock '. $list->kode_produk . ' Kurang']);
      //      }
         
      //    }
 
      //    foreach($details as $d){
 
      //       $kode = $d->kode_produk;
      //       $jumlah_penjualan = $d->jumlah;
      //       $id_penjualan = $d->id_penjualan;
   
      //       $now = \Carbon\Carbon::now();
   
      //       // mengaambil stok di produk_detail berdasar barcode dan harga beli lebih rendah (stok yang tesedria) yang terdapat di penjualan_detail_temporary
      //       produk:
      //       $produk_detail = ProdukWo::where('kode_produk',$kode)
      //       ->where('unit',Auth::user()->unit)
      //       ->where('stok','>','0')
      //       ->where('param_status',2)
      //       ->orderBy('tanggal_input','ASC')
      //       ->first();
            
      //       // buat variable stok toko dari column stok dari table produk_detail
      //       $stok_toko = $produk_detail->stok;
      //       // buat variable harga_beli dari column harga_beli dari table produk_detail
      //       $harga_beli = $produk_detail->harga_beli;
           
      //      // jika qty penjualan == jumlah stok yang tersedia ditoko
      //       if ($jumlah_penjualan == $stok_toko) {
             
      //          $detail = new KirimDetail;
      //          $detail->id_pembelian = $request['idpembelian'];
      //          $detail->kode_produk = $kode;
      //          $detail->harga_jual = $produk_detail->harga_jual;
      //          $detail->harga_beli = $produk_detail->harga_beli;
      //          $detail->jumlah = $jumlah_penjualan;
      //          $detail->jumlah_terima = 0;
      //          $detail->sub_total = $produk_detail->harga_beli * $jumlah_penjualan;
      //          $detail->sub_total_terima = 0;
      //          $detail->sub_total_margin = $produk_detail->harga_jual * $jumlah_penjualan;
      //          $detail->sub_total_margin_terima = 0;
      //          $detail->expired_date = $d->expired_date;
      //          $detail->jurnal_status = 0;
      //          $detail->no_faktur = $produk_detail->no_faktur;
      //          $detail->save();

      //          $kartu_stok = new KartuStok;
      //          $kartu_stok->buss_date = date('Y-m-d');
      //          $kartu_stok->kode_produk = $produk_detail->kode_produk;
      //          $kartu_stok->masuk = 0;
      //          $kartu_stok->keluar = $jumlah_penjualan;
      //          $kartu_stok->status = 'kirim_barang_retur';
      //          $kartu_stok->kode_transaksi = $request['idpembelian'];
      //          $kartu_stok->unit = Auth::user()->unit;
      //          $kartu_stok->save();

      //          $produk_detail->update(['stok'=>0]);
      //          // jika selisih qty penjualan dengan jumlah stok yang tersedia
           
      //       }else {
             
      //          // mengurangi qty penjualan dengan stok toko berdasarkan stok(table produk_detail)
      //          $stok = $jumlah_penjualan - $stok_toko;
   
      //          // jika hasilnya lebih dari nol atau tidak minus, stok tsb tidak memenuhi qty penjualan dan harus ambil lagi record pada produk detail~
      //          // ~ yang stok nya lebih dari nol
   
      //          if ($stok >= 0) {
   
                  
      //             // update produk_detail->stok menjadi nol berdasarkan $produk_detail 
                  
      //             $detail = new KirimDetail;
      //             $detail->id_pembelian = $request['idpembelian'];
      //             $detail->kode_produk = $kode;
      //             $detail->harga_jual = $produk_detail->harga_jual;
      //             $detail->harga_beli = $produk_detail->harga_beli;
      //             $detail->jumlah = $stok_toko;
      //             $detail->jumlah_terima = 0;
      //             $detail->sub_total = $produk_detail->harga_beli * $stok_toko;
      //             $detail->sub_total_terima = 0;
      //             $detail->sub_total_margin = $produk_detail->harga_jual * $stok_toko;
      //             $detail->sub_total_margin_terima = 0;
      //             $detail->expired_date = $d->expired_date;
      //             $detail->jurnal_status = 0;
      //             $detail->no_faktur = $produk_detail->no_faktur;
      //             $detail->save();

      //             $kartu_stok = new KartuStok;
      //             $kartu_stok->buss_date = date('Y-m-d');
      //             $kartu_stok->kode_produk = $produk_detail->kode_produk;
      //             $kartu_stok->masuk = 0;
      //             $kartu_stok->keluar = $stok_toko;
      //             $kartu_stok->status = 'kirim_barang_retur';
      //             $kartu_stok->kode_transaksi = $request['idpembelian'];
      //             $kartu_stok->unit = Auth::user()->unit;
      //             $kartu_stok->save();
                  
      //             $produk_detail->update(['stok'=>0]);
      //             // sisa qty penjualan yang dikurangi stok toko yang harganya paling rendah
      //             $jumlah_penjualan = $stok;
   
      //             // mengulangi looping untuk mencari harga yang paling rendah
      //             goto produk;
                  
      //          // jika pengurangan qty penjualan dengan stok toko hasilnya kurang dari 0 atau minus
      //          }else if($stok < 0){
   
      //             // update stok berdasar sisa pengurangan qty penjualan dengan stok toko hasilnya kurang dari 0 atau minus
                  
      //             $detail = new KirimDetail;
      //             $detail->id_pembelian = $request['idpembelian'];
      //             $detail->kode_produk = $kode;
      //             $detail->harga_jual = $produk_detail->harga_jual;
      //             $detail->harga_beli = $produk_detail->harga_beli;
      //             $detail->jumlah = $jumlah_penjualan;
      //             $detail->jumlah_terima = 0;
      //             $detail->sub_total = $produk_detail->harga_beli * $jumlah_penjualan;
      //             $detail->sub_total_terima = 0;
      //             $detail->sub_total_margin = $produk_detail->harga_jual * $jumlah_penjualan;
      //             $detail->sub_total_margin_terima = 0;
      //             $detail->expired_date = $d->expired_date;
      //             $detail->jurnal_status = 0;
      //             $detail->no_faktur = $produk_detail->no_faktur;
      //             $detail->save();
                  
      //             $kartu_stok = new KartuStok;
      //             $kartu_stok->buss_date = date('Y-m-d');
      //             $kartu_stok->kode_produk = $produk_detail->kode_produk;
      //             $kartu_stok->masuk = 0;
      //             $kartu_stok->keluar = $jumlah_penjualan;
      //             $kartu_stok->status = 'kirim_barang_retur';
      //             $kartu_stok->kode_transaksi = $request['idpembelian'];
      //             $kartu_stok->unit = Auth::user()->unit;
      //             $kartu_stok->save();
                 
      //             $produk_detail->update(['stok'=>abs($stok)]);
             
      //           }    
      //       }
      //    }
         
      //    foreach($details as $list){
 
      //      $produk = Produk::where('kode_produk',$list->kode_produk)->where('unit',Auth::user()->unit)->first();
      //      $produk->stok -= $list->jumlah;
      //      $produk->update();
 
      //    }
 
      //    //  update table kirim_barang
      //    $total_item = KirimDetail::where('id_pembelian',$id_pembelian)->sum('jumlah');
      //    $total_harga = KirimDetail::where('id_pembelian',$id_pembelian)->sum('sub_total');
      //    $total_margin = KirimDetail::where('id_pembelian',$id_pembelian)->sum('sub_total_margin');
 
      //    $kirim_barang = Kirim::where('id_pembelian',$id_pembelian)->first();
      //    $kirim_barang->total_item = $total_item;
      //    $kirim_barang->total_margin = $total_margin;
      //    $kirim_barang->total_harga = $total_harga;
      //    $kirim_barang->update();
 
      //    //insert jurnal 
      //    $d = Kirim::leftJoin('supplier','kirim_barang.id_supplier','=','supplier.id_supplier')
      //                ->where('id_pembelian',$request['idpembelian'])
      //                ->first();
         
      //    $param_tgl = \App\ParamTgl::where('nama_param_tgl','tanggal_transaksi')->where('unit',Auth::user()->id)->first();
      //    $tanggal = $param_tgl->param_tgl;
             
      
      //    $jurnal = new TabelTransaksi;
      //    $jurnal->unit =  Auth::user()->unit; 
      //    $jurnal->kode_transaksi = $d->id_pembelian;
      //    $jurnal->kode_rekening = 2500000;
      //    $jurnal->tanggal_transaksi  = $tanggal;
      //    $jurnal->jenis_transaksi  = 'Jurnal System';
      //    $jurnal->keterangan_transaksi = 'Retur Supplier' . ' ' . $d->id_pembelian . ' ' . $d->nama;
      //    $jurnal->debet =$d->total_harga;
      //    $jurnal->kredit = 0;
      //    $jurnal->tanggal_posting = '';
      //    $jurnal->keterangan_posting = '0';
      //    $jurnal->id_admin = Auth::user()->id; 
      //    $jurnal->save();

      //    $jurnal = new TabelTransaksi;
      //    $jurnal->unit =  Auth::user()->unit; 
      //    $jurnal->kode_transaksi = $d->id_pembelian;
      //    $jurnal->kode_rekening = 1482000;
      //    $jurnal->tanggal_transaksi  = $tanggal;
      //    $jurnal->jenis_transaksi  = 'Jurnal System';
      //    $jurnal->keterangan_transaksi = 'Retur Supplier' . ' ' . $d->id_pembelian . ' ' . $d->nama;
      //    $jurnal->debet =0;
      //    $jurnal->kredit =$d->total_harga;
      //    $jurnal->tanggal_posting = '';
      //    $jurnal->keterangan_posting = '0';
      //    $jurnal->id_admin = Auth::user()->id; 
      //    $jurnal->save();
         
      //    // --- /kode syakil ---
      
      //    $supplier = Supplier::all();
      //    $branch = Branch::all();
      
      //    DB::commit();

      // }catch(\Exception $e){
         
      //    DB::rollback();
      //    return back()->with(['error' => $e->getmessage()]);
 
      // }
        

      // return view('retur_supplier.index', compact('supplier','branch')); 
      
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

