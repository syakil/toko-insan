<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Redirect;
use Auth;
use PDF;
use App\Penjualan;
use App\Produk;
use App\KartuStok;
use App\ProdukDetail;
use App\Member;
use App\Setting;
use App\PenjualanDetail;
use App\PenjualanDetailTemporary;
use App\TabelTransaksi;
use Yajra\Datatables\Datatables;
use App\Branch;

class PenjualanDetailController extends Controller
{
   public function index(){

      $produk = Produk::where('unit', '=',  Auth::user()->unit)
                     -> where('stok', '>', '0')->get();
      $member = Member::leftjoin('musawamah','musawamah.id_member','=','member.kode_member');
      $setting = Setting::first();
     
      if(!empty(session('idpenjualan'))){
         $idpenjualan = session('idpenjualan');
         return view('penjualan_detail.index', compact('produk', 'member', 'setting', 'idpenjualan'));
      }else{
         return Redirect::route('home');  
      }

   }

   public function NewMenu(){

      $produk = Produk::where('unit', '=',  Auth::user()->unit);
      $member = Member::leftjoin('musawamah','musawamah.id_member','=','member.kode_member')
      ->get();
      $setting = Setting::first();
     
      return view('penjualan_detail.menu', compact('produk', 'member', 'setting', 'idpenjualan', Auth::user()->id));
    
   }

   public function listData($id){
      
      $detail = PenjualanDetailTemporary::leftJoin('produk', 'produk.kode_produk', '=', 'penjualan_detail_temporary.kode_produk')
         ->where('id_penjualan', '=', $id)
         -> where('unit', '=',  Auth::user()->unit)
         ->orderBy('id_penjualan_detail','desc')
         ->get();
         
      $no = 0;
      $data = array();
      $total = 0;
      $total_item = 0;

      foreach($detail as $list){
         
         $no ++;
         $row = array();
         $row[] = $no;
         $row[] = $list->kode_produk;
         $row[] = $list->nama_produk;
         $row[] = $list->stok;
         $row[] = "Rp. ".format_uang($list->harga_jual_member_insan);
         $row[] = "<input type='number' class='form-control jumlah' name='jumlah_$list->id_penjualan_detail' value='$list->jumlah' onChange='changeCount($list->id_penjualan_detail)'>";
         $row[] = $list->diskon;
         $row[] = "Rp. ".format_uang($list->sub_total);
         $row[] = '<div class="btn-group">
                  <a onclick="deleteItem('.$list->id_penjualan_detail.')" class="btn btn-danger btn-sm"><i class="fa fa-trash"></i></a>';
         $data[] = $row;
   
         $total += $list->harga_jual_member_insan * $list->jumlah - $list->diskon ;
         $total_item += $list->jumlah;

      }   
        
   
      $data[] = array("<span class='hide total'>$total</span><span class='hide totalitem'>$total_item</span>", "", "", "", "", "", "", "","");
      
      $output = array("data" => $data);
      return response()->json($output);     
      
         
   }
   
   public function store(Request $request){

      $produk = Produk::where('kode_produk', '=', $request['kode'])
                     ->where('unit', '=',  Auth::user()->unit)
                     ->first();

      $detail = new PenjualanDetailTemporary;
      $detail->id_penjualan = $request['idpenjualan'];
      $detail->kode_produk = $request['kode'];
      $detail->harga_jual = $produk->harga_jual_member_insan;
      $detail->harga_beli = $produk->harga_beli;
      $detail->promo = $produk->promo;
      $detail->jumlah = '';
      $detail->diskon = $produk->diskon;
      $detail->sub_total = ($produk->harga_jual_member_insan - ($produk->diskon)) * $detail->jumlah;
      $detail->sub_total_beli = $produk->harga_beli;  
      $detail->save();

   }

   public function update(Request $request, $id){
      
      $nama_input = "jumlah_".$id;
      $detail = PenjualanDetailTemporary::where('id_penjualan_detail',$id)->first();       
      $total_harga = ($request[$nama_input] * $detail->harga_jual);
      $detail->jumlah = $request[$nama_input];
      $detail->sub_total = $total_harga - $detail->diskon;  
      $detail->sub_total_beli = ($request[$nama_input] * $detail->harga_beli);
      $detail->update();

   }

   
   public function destroy($id){

      $detail = PenjualanDetailTemporary::find($id);
      $detail->delete();

   }

   public function newSession(){

      $penjualan = new Penjualan; 
      $penjualan->kode_member = null;    
      $penjualan->total_item = 0;    
      $penjualan->total_harga = 0; 
      $penjualan->total_harga_beli = 0;    
      $penjualan->unit = Auth::user()->unit;
      $penjualan->type_transaksi = 'cash';
      $penjualan->diskon = 0;    
      $penjualan->bayar = 0;    
      $penjualan->diterima = 0;    
      $penjualan->id_user = Auth::user()->id;    
      $penjualan->save();
      
      session(['idpenjualan' => $penjualan->id_penjualan]);

      return Redirect::route('transaksi.index');    
   
   }
 
   public function batal($id){

      try {
         
         DB::beginTransaction();
         
         $detail = PenjualanDetailTemporary::where('id_penjualan',$id)->first();
         
         if ($detail) {
            
            return back()->with(['error' => 'Hapus Semua Data Transaksi !']);   

         }

         $penjualan = Penjualan::find($id);
         $penjualan->delete();

         
         DB::commit();
      
      }catch(\Exception $e){
         
         DB::rollback();
         return back()->with(['error' => $e->getmessage()]);
 
      }


      return redirect()->route('transaksi.menu');
      
   }

   public function saveData(Request $request){
      
      try {
         DB::beginTransaction();

         $details = PenjualanDetailTemporary::where('id_penjualan', '=', $request['idpenjualan'])->get();
      
         // cek stok tersedia, jika melibih akan kembali kemenu transaksi dan memberikan notifikasi bahwa stok kurang/kosong
         foreach($details as $list){

            $cek_sum_penjualan = PenjualanDetailTemporary::where('id_penjualan', $list->id_penjualan)->where('kode_produk',$list->kode_produk)->sum('jumlah');
            $produk = Produk::where('kode_produk',$list->kode_produk)->where('unit',Auth::user()->unit)->first();
            $produk_detail = ProdukDetail::where('kode_produk',$list->kode_produk)
            ->where('unit',Auth::user()->unit)
            ->sum('stok_detail');
                           
            if($cek_sum_penjualan > $produk->stok){
               return back()->with(['error' => 'Stock '. $list->kode_produk . ' Kurang']);
            }

            if($cek_sum_penjualan > $produk_detail){
               return back()->with(['error' => 'Stock '. $list->kode_produk . ' Kurang']);
            }
            
            if($list->jumlah == 0){
               return back()->with(['error' => 'Masukan Qty '. $list->kode_produk]);
            }

         }

         
         
         // looping mengurangi stok pada table produk_detail
         foreach($details as $d){

            $kode = $d->kode_produk;
            $jumlah_penjualan = $d->jumlah;
            $id_penjualan = $d->id_penjualan;

            $param_tgl = \App\ParamTgl::where('nama_param_tgl','tanggal_transaksi')->where('unit',Auth::user()->id)->first();

            $now = $param_tgl->param_tgl;           
      
            // mengaambil stok di produk_detail berdasar barcode dan harga beli lebih rendah (stok yang tesedria) yang terdapat di penjualan_detail_temporary
            produk:
            $produk_detail = ProdukDetail::where('kode_produk',$kode)
            ->where('unit',Auth::user()->unit)
            ->where('stok_detail','>','0')
            ->orderBy('tanggal_masuk','ASC')
            ->first();
            
            // buat variable stok toko dari column stok_detail dari table produk_detail
            $stok_toko = $produk_detail->stok_detail;
            // buat variable harga_beli dari column harga_beli dari table produk_detail
            $harga_beli = $produk_detail->harga_beli;
      
            // jika qty penjualan == jumlah stok yang tersedia ditoko
            if ($jumlah_penjualan == $stok_toko) {
            
               if ($d->harga_jual > $produk_detail->harga_beli) {
                  
                  $harga_beli_0 = $jumlah_penjualan * $produk_detail->harga_beli; 
                  $harga_jual_0 = $jumlah_penjualan * $d->harga_jual;
                  
                  // persediaan barang dagang
                  $jurnal = new TabelTransaksi;
                  $jurnal->unit =  Auth::user()->unit; 
                  $jurnal->kode_transaksi = $request['idpenjualan'];
                  $jurnal->kode_rekening = 1482000;
                  $jurnal->tanggal_transaksi  = $now;
                  $jurnal->jenis_transaksi  = 'Jurnal System';
                  $jurnal->keterangan_transaksi = 'Persediaan Barang Dagang '.$produk_detail->kode_produk;
                  $jurnal->debet = 0;
                  $jurnal->kredit = $harga_jual_0;
                  $jurnal->tanggal_posting = '';
                  $jurnal->keterangan_posting = '0';
                  $jurnal->id_admin = Auth::user()->id; 
                  $jurnal->save();
                  
               }else {
                  
                  $stok_detail = $jumlah_penjualan;
                  $harga_beli_promo_0 = $jumlah_penjualan * $produk_detail->harga_beli; 
                  $harga_jual_promo_0 = $jumlah_penjualan * $d->harga_jual;
                  
                  // persediaan barang dagang
                  $jurnal = new TabelTransaksi;
                  $jurnal->unit =  Auth::user()->unit; 
                  $jurnal->kode_transaksi = $request['idpenjualan'];
                  $jurnal->kode_rekening = 1482000;
                  $jurnal->tanggal_transaksi  = $now;
                  $jurnal->jenis_transaksi  = 'Jurnal System';
                  $jurnal->keterangan_transaksi = 'Persediaan Barang Dagang'.$produk_detail->kode_produk;
                  $jurnal->debet = 0;
                  $jurnal->kredit = $harga_beli_promo_0;
                  $jurnal->tanggal_posting = '';
                  $jurnal->keterangan_posting = '0';
                  $jurnal->id_admin = Auth::user()->id; 
                  $jurnal->save();

               }
               
               
               $produk_detail->update(['stok_detail'=>0]);

               // crate penjualan_detail berdasarkan penjualan_detail_temporarary
               $new_detail = new PenjualanDetail;
               $new_detail->id_penjualan = $id_penjualan;
               $new_detail->kode_produk = $kode;
               // harga_jual disesuaikan dengan yang ada dimaster produk/table produk, yang sudah ter record pada penjualan_detail_temporary
               $new_detail->harga_jual = $d->harga_jual;
               // harga_beli disesuaikan dengan produk_detail
               $new_detail->harga_beli = $produk_detail->harga_beli;
               $new_detail->promo = $d->promo;
               $new_detail->jumlah = $stok_toko;
               $new_detail->diskon = $d->diskon;
               $new_detail->sub_total = $d->harga_jual * $stok_toko;
               $new_detail->sub_total_beli = $produk_detail->harga_beli * $stok_toko;  
               $new_detail->no_faktur = $produk_detail->no_faktur;
               $new_detail->save();
               
               $kartu_stok = new KartuStok;
               $kartu_stok->buss_date = date('Y-m-d');
               $kartu_stok->kode_produk = $kode;
               $kartu_stok->masuk = 0;
               $kartu_stok->keluar = $stok_toko;
               $kartu_stok->status = 'penjualan';
               $kartu_stok->kode_transaksi = $id_penjualan;
               $kartu_stok->unit = Auth::user()->unit;
               $kartu_stok->save();

            // jika selisih qty penjualan dengan jumlah stok yang tersedia
            }else {
            
               // mengurangi qty penjualan dengan stok toko berdasarkan stok_detail(table produk_detail)
               $stok = $jumlah_penjualan - $stok_toko;

               // jika hasilnya lebih dari nol atau tidak minus, stok_detail tsb tidak memenuhi qty penjualan dan harus ambil lagi record pada produk detail~
               // ~ yang stok nya lebih dari nol

               if ($stok >= 0) {
               
                  if ($d->harga_jual > $produk_detail->harga_beli) {
                  
                     $harga_beli_0 = $stok_toko * $produk_detail->harga_beli; 
                     $harga_jual_0 = $stok_toko * $d->harga_jual;
                     
                     // persediaan barang dagang
                     $jurnal = new TabelTransaksi;
                     $jurnal->unit =  Auth::user()->unit; 
                     $jurnal->kode_transaksi = $request['idpenjualan'];
                     $jurnal->kode_rekening = 1482000;
                     $jurnal->tanggal_transaksi  = $now;
                     $jurnal->jenis_transaksi  = 'Jurnal System';
                     $jurnal->keterangan_transaksi = 'Persediaan Barang Dagang '.$produk_detail->kode_produk;
                     $jurnal->debet = 0;
                     $jurnal->kredit = $harga_jual_0;
                     $jurnal->tanggal_posting = '';
                     $jurnal->keterangan_posting = '0';
                     $jurnal->id_admin = Auth::user()->id; 
                     $jurnal->save();
                     
                  }else {
                     
                     $stok_detail = $stok_toko;
                     $harga_beli_promo_0 = $stok_toko * $produk_detail->harga_beli; 
                     $harga_jual_promo_0 = $stok_toko * $d->harga_jual;
                     
                     // persediaan barang dagang
                     $jurnal = new TabelTransaksi;
                     $jurnal->unit =  Auth::user()->unit; 
                     $jurnal->kode_transaksi = $request['idpenjualan'];
                     $jurnal->kode_rekening = 1482000;
                     $jurnal->tanggal_transaksi  = $now;
                     $jurnal->jenis_transaksi  = 'Jurnal System';
                     $jurnal->keterangan_transaksi = 'Persediaan Barang Dagang '.$produk_detail->kode_produk;
                     $jurnal->debet = 0;
                     $jurnal->kredit = $harga_beli_promo_0;
                     $jurnal->tanggal_posting = '';
                     $jurnal->keterangan_posting = '0';
                     $jurnal->id_admin = Auth::user()->id; 
                     $jurnal->save();
      
                  }
                  
                  $produk_detail->update(['stok_detail'=>0]);
                  
                  // crate penjualan_detail berdasarkan penjualan_detail_temporarary
                  $new_detail = new PenjualanDetail;
                  $new_detail->id_penjualan = $id_penjualan;
                  $new_detail->kode_produk = $kode;
                  // harga_jual disesuaikan dengan yang ada dimaster produk/table produk, yang sudah ter record pada penjualan_detail_temporary
                  $new_detail->harga_jual = $d->harga_jual;
                  // harga_beli disesuaikan dengan produk_detail   
                  $new_detail->harga_beli = $produk_detail->harga_beli;
                  $new_detail->promo = $d->promo;
                  // jumlah yang di record adalah jumlah stok_detail pada produk_detail yang harganya paling rendah
                  $new_detail->jumlah = $stok_toko;
                  $new_detail->diskon = $d->diskon;
                  $new_detail->sub_total = $d->harga_jual * $stok_toko;
                  $new_detail->sub_total_beli = $produk_detail->harga_beli * $stok_toko;
                  $new_detail->no_faktur = $produk_detail->no_faktur;
                  $new_detail->save();

               
                  $kartu_stok = new KartuStok;
                  $kartu_stok->buss_date = date('Y-m-d');
                  $kartu_stok->kode_produk = $kode;
                  $kartu_stok->masuk = 0;
                  $kartu_stok->keluar = $stok_toko;
                  $kartu_stok->status = 'penjualan';
                  $kartu_stok->kode_transaksi = $id_penjualan;
                  $kartu_stok->unit = Auth::user()->unit;
                  $kartu_stok->save();
                  // sisa qty penjualan yang dikurangi stok toko yang harganya paling rendah
                  $jumlah_penjualan = $stok;

                  // mengulangi looping untuk mencari harga yang paling rendah
                  goto produk;
                  
               // jika pengurangan qty penjualan dengan stok toko hasilnya kurang dari 0 atau minus
               }else if($stok < 0){

                  if ($d->harga_jual > $produk_detail->harga_beli) {
                  
                     $harga_beli_0 = $jumlah_penjualan * $produk_detail->harga_beli; 
                     $harga_jual_0 = $jumlah_penjualan * $d->harga_jual;
                     
                     // perseediaan barang dagang
                     $jurnal = new TabelTransaksi;
                     $jurnal->unit =  Auth::user()->unit; 
                     $jurnal->kode_transaksi = $request['idpenjualan'];
                     $jurnal->kode_rekening = 1482000;
                     $jurnal->tanggal_transaksi  = $now;
                     $jurnal->jenis_transaksi  = 'Jurnal System';
                     $jurnal->keterangan_transaksi = 'Persediaan Barang Dagang '.$produk_detail->kode_produk;
                     $jurnal->debet = 0;
                     $jurnal->kredit = $harga_jual_0;
                     $jurnal->tanggal_posting = '';
                     $jurnal->keterangan_posting = '0';
                     $jurnal->id_admin = Auth::user()->id; 
                     $jurnal->save();
                     
                  }else {
                     
                     
                     $harga_beli_promo_0 = $jumlah_penjualan * $produk_detail->harga_beli; 
                     $harga_jual_promo_0 = $jumlah_penjualan * $d->harga_jual;
                     
                     // persediaan barang dagang
                     $jurnal = new TabelTransaksi;
                     $jurnal->unit =  Auth::user()->unit; 
                     $jurnal->kode_transaksi = $request['idpenjualan'];
                     $jurnal->kode_rekening = 1482000;
                     $jurnal->tanggal_transaksi  = $now;
                     $jurnal->jenis_transaksi  = 'Jurnal System';
                     $jurnal->keterangan_transaksi = 'Persediaan Barang Dagang '.$produk_detail->kode_produk;
                     $jurnal->debet = 0;
                     $jurnal->kredit = $harga_beli_promo_0;
                     $jurnal->tanggal_posting = '';
                     $jurnal->keterangan_posting = '0';
                     $jurnal->id_admin = Auth::user()->id; 
                     $jurnal->save();
      
                  }
                  
                  // update stok_detail berdasar sisa pengurangan qty penjualan dengan stok toko hasilnya kurang dari 0 atau minus
                  $produk_detail->update(['stok_detail'=>abs($stok)]);
                  
                  $new_detail = new PenjualanDetail;
                  $new_detail->id_penjualan = $id_penjualan;
                  $new_detail->kode_produk = $kode;
                  $new_detail->harga_jual = $d->harga_jual;
                  $new_detail->harga_beli = $produk_detail->harga_beli;
                  $new_detail->promo = $d->promo;
                  $new_detail->jumlah = $jumlah_penjualan;
                  $new_detail->diskon = $d->diskon;
                  $new_detail->sub_total = $d->harga_jual * $jumlah_penjualan;
                  $new_detail->sub_total_beli = $produk_detail->harga_beli * $jumlah_penjualan;
                  $new_detail->no_faktur = $produk_detail->no_faktur;
                  $new_detail->save();
                  
                  $kartu_stok = new KartuStok;
                  $kartu_stok->buss_date = date('Y-m-d');
                  $kartu_stok->kode_produk = $kode;
                  $kartu_stok->masuk = 0;
                  $kartu_stok->keluar = $jumlah_penjualan;
                  $kartu_stok->status = 'penjualan';
                  $kartu_stok->kode_transaksi = $id_penjualan;
                  $kartu_stok->unit = Auth::user()->unit;
                  $kartu_stok->save();
               }
            }
         }
         
         foreach($details as $list){

            $produk = Produk::where('kode_produk',$list->kode_produk)->where('unit',Auth::user()->unit)->first();
            $produk->stok -= $list->jumlah;
            $produk->update();

         }

         $param_tgl = \App\ParamTgl::where('nama_param_tgl','tanggal_transaksi')->where('unit',Auth::user()->id)->first();

         $now = $param_tgl->param_tgl;           

         $penjualan = Penjualan::find($request['idpenjualan']);

         $total_item = PenjualanDetail::where('id_penjualan',$request['idpenjualan'])->sum('jumlah');
         
         $total_harga_beli_non_promo = PenjualanDetail::where('id_penjualan',$request['idpenjualan'])->whereRaw('harga_beli < harga_jual')->sum('sub_total_beli');
         $total_harga_jual_non_promo = PenjualanDetail::where('id_penjualan',$request['idpenjualan'])->whereRaw('harga_beli < harga_jual')->sum('sub_total');

         $total_harga_jual_keseluruhan = PenjualanDetail::where('id_penjualan',$request['idpenjualan'])->sum('sub_total');
         $total_harga_beli_keseluruhan = PenjualanDetail::where('id_penjualan',$request['idpenjualan'])->sum('sub_total_beli');
         
         $cek_promo = PenjualanDetail::where('id_penjualan',$request['idpenjualan'])->whereRaw('harga_beli > harga_jual')->first();
         
         $total_diskon = PenjualanDetail::where('id_penjualan',$request['idpenjualan'])->sum('diskon');
         
         $total_belanja = $total_harga_jual_keseluruhan - $total_diskon;
         
         $harus_dibayar = $total_belanja;
         
         $margin = $total_harga_jual_non_promo - $total_harga_beli_non_promo;

         $donasi = $request['donasi'];

         $os_baru = $total_belanja - max($harus_dibayar,0);
         
         if ($cek_promo) {
            
            $total_harga_beli_barang_promo = PenjualanDetail::where('id_penjualan',$request['idpenjualan'])->whereRaw('harga_beli > harga_jual')->sum('sub_total_beli');
            $total_harga_jual_barang_promo = PenjualanDetail::where('id_penjualan',$request['idpenjualan'])->whereRaw('harga_beli > harga_jual')->sum('sub_total');
         
            $bol = $total_harga_beli_barang_promo - $total_harga_jual_barang_promo;
         
            $persediaan_barang_dagang = $total_harga_beli_barang_promo + $total_harga_jual_non_promo;

         }else {

            $persediaan_barang_dagang = $total_harga_jual_keseluruhan;
         
         }
      
         $penjualan->total_item = $total_item;
         $penjualan->total_harga = $total_harga_jual_keseluruhan;
         $penjualan->total_harga_beli = $total_harga_beli_keseluruhan;
         $penjualan->diskon = $total_diskon;
         $penjualan->bayar = $harus_dibayar;
         $penjualan->diterima = $request['diterima'];
         $penjualan->update();
         
         // Kas 
         $jurnal = new TabelTransaksi;
         $jurnal->unit =  Auth::user()->unit; 
         $jurnal->kode_transaksi = $request['idpenjualan'];
         $jurnal->kode_rekening = 1120000;
         $jurnal->tanggal_transaksi  = $now;
         $jurnal->jenis_transaksi  = 'Jurnal Umum';
         $jurnal->keterangan_transaksi = 'Penjualan';
         $jurnal->debet = $total_belanja;
         $jurnal->kredit = 0;
         $jurnal->tanggal_posting = '';
         $jurnal->keterangan_posting = '0';
         $jurnal->id_admin = Auth::user()->id; 
         $jurnal->save();
         

         if ($margin > 0) {

            $jurnal = new TabelTransaksi;
            $jurnal->unit =  Auth::user()->unit; 
            $jurnal->kode_transaksi = $request['idpenjualan'];
            $jurnal->kode_rekening = 1483000;
            $jurnal->tanggal_transaksi  = $now;
            $jurnal->jenis_transaksi  = 'Jurnal Umum';
            $jurnal->keterangan_transaksi = 'Margin Penjualan';
            $jurnal->debet = $margin;
            $jurnal->kredit = 0;
            $jurnal->tanggal_posting = '';
            $jurnal->keterangan_posting = '0';
            $jurnal->id_admin = Auth::user()->id; 
            $jurnal->save();
            
         
            $jurnal = new TabelTransaksi;
            $jurnal->unit =  Auth::user()->unit; 
            $jurnal->kode_transaksi = $request['idpenjualan'];
            $jurnal->kode_rekening = 41001;
            $jurnal->tanggal_transaksi  = $now;
            $jurnal->jenis_transaksi  = 'Jurnal Umum';
            $jurnal->keterangan_transaksi = 'Margin Penjualan';
            $jurnal->debet = 0;
            $jurnal->kredit = $margin;
            $jurnal->tanggal_posting = '';
            $jurnal->keterangan_posting = '0';
            $jurnal->id_admin = Auth::user()->id; 
            $jurnal->save();
            // end jurnal margin
         
         }

         if ($cek_promo) {
            
            //BOL-TI Promo/Discount/Kupon
            $jurnal = new TabelTransaksi;
            $jurnal->unit =  Auth::user()->unit; 
            $jurnal->kode_transaksi = $request['idpenjualan'];
            $jurnal->kode_rekening = 56412;
            $jurnal->tanggal_transaksi = $now;
            $jurnal->jenis_transaksi  = 'Jurnal System';
            $jurnal->keterangan_transaksi = 'BOL-TI Promo 1';
            $jurnal->debet = $bol;
            $jurnal->kredit = 0;
            $jurnal->tanggal_posting = ' ';
            $jurnal->keterangan_posting = '0';
            $jurnal->id_admin = Auth::user()->unit; 
            $jurnal->save();

            $jurnal = new TabelTransaksi;
            $jurnal->unit =  Auth::user()->unit; 
            $jurnal->kode_transaksi = $request['idpenjualan'];
            $jurnal->kode_rekening = 1482000;
            $jurnal->tanggal_transaksi = $now;
            $jurnal->jenis_transaksi  = 'Jurnal System';
            $jurnal->keterangan_transaksi = 'BOL-TI Promo 1';
            $jurnal->debet = $bol;
            $jurnal->kredit = 0;
            $jurnal->tanggal_posting = ' ';
            $jurnal->keterangan_posting = '0';
            $jurnal->id_admin = Auth::user()->unit; 
            $jurnal->save();

            $jurnal = new TabelTransaksi;
            $jurnal->unit =  Auth::user()->unit; 
            $jurnal->kode_transaksi = $request['idpenjualan'];
            $jurnal->kode_rekening = 1483000;
            $jurnal->tanggal_transaksi = $now;
            $jurnal->jenis_transaksi  = 'Jurnal System';
            $jurnal->keterangan_transaksi = 'BOL-TI Promo 1';
            $jurnal->debet = 0;
            $jurnal->kredit = $bol;
            $jurnal->tanggal_posting = ' ';
            $jurnal->keterangan_posting = '0';
            $jurnal->id_admin = Auth::user()->unit; 
            $jurnal->save();            
         
         }
         
         // jika ada donasi
         if($request['donasi']>0){
            
            $jurnal = new TabelTransaksi;
            $jurnal->unit =  Auth::user()->unit; 
            $jurnal->kode_transaksi = $request['idpenjualan'];
            $jurnal->kode_rekening = 1120000;
            $jurnal->tanggal_transaksi = $now;
            $jurnal->jenis_transaksi  = 'Jurnal System';
            $jurnal->keterangan_transaksi = 'donasi ';
            $jurnal->debet = $request['donasi'];
            $jurnal->kredit = 0;
            $jurnal->tanggal_posting = ' ';
            $jurnal->keterangan_posting = '0';
            $jurnal->id_admin = Auth::user()->id; 
            $jurnal->save();

            $jurnal = new TabelTransaksi;
            $jurnal->unit =  Auth::user()->unit; 
            $jurnal->kode_transaksi = $request['idpenjualan'];
            $jurnal->kode_rekening = 1482000;
            $jurnal->tanggal_transaksi = $now;
            $jurnal->jenis_transaksi  = 'Jurnal System';
            $jurnal->keterangan_transaksi = 'Donasi dari Penjualan ';
            $jurnal->debet =0;
            $jurnal->kredit = $request['donasi'];
            $jurnal->tanggal_posting = ' ';
            $jurnal->keterangan_posting = '0';
            $jurnal->id_admin = Auth::user()->id; 
            $jurnal->save();
         
         }

         PenjualanDetailTemporary::where('id_penjualan', '=', $request['idpenjualan'])->delete();
      
         DB::commit();
      
      }catch(\Exception $e){
         
         DB::rollback();
         return back()->with(['error' => $e->getmessage()]);
 
      }

      return Redirect::route('cashinsan.cetak');
   }
   
   public function loadForm($diskon, $total, $diterima){

      $bayar = $total -$diskon ;
 
      $kembali = ($diterima != 0) ? $diterima - $bayar : 0;

      $data = array(
        "totalrp" => format_uang($total),
        "bayar" => $bayar,
        "bayarrp" => format_uang($bayar),
        "terbilang" => ucwords(terbilang($bayar))." Rupiah",
        "kembalirp" => format_uang($kembali),
        "kembaliterbilang" => ucwords(terbilang($kembali))." Rupiah"
      );
     
      return response()->json($data);
   
   }

   public function printNota(){

      $detail = PenjualanDetail::leftJoin('produk', 'produk.kode_produk', '=', 'penjualan_detail.kode_produk')
                              ->where('id_penjualan', '=', session('idpenjualan'))
                              ->where('produk.unit', '=', Auth::user()->id) 
                              ->get();

      $penjualan = Penjualan::find(session('idpenjualan'));
      $setting = Setting::find(1);
      
      if($setting->tipe_nota == 0){
      
         $handle = printer_open(); 
         printer_start_doc($handle, "Nota");
         printer_start_page($handle);

         $font = printer_create_font("Arial", 10, 11, 10, false, false, false, 0);
         printer_select_font($handle, $font);
         printer_draw_text($handle, $setting->logo, 0, 80);
         printer_draw_text($handle, $setting->nama_perusahaan, 0, 90);

         $font = printer_create_font("Arial", 10, 11, 10, false, false, false, 0);
         printer_select_font($handle, $font);
         printer_draw_text($handle, $setting->alamat, 0, 100);

         printer_draw_text($handle, date('Y-m-d'), 0, 120);
         printer_draw_text($handle, substr("".Auth::user()->name, 0), 0, 130);

         printer_draw_text($handle, "No : ".substr("00000000".$penjualan->id_penjualan, 0), 0, 140);

         printer_draw_text($handle, "============================", 0, 150);
        
        $y = 170;
        
         foreach($detail as $list){           
           printer_draw_text($handle, $list->kode_produk." ".$list->nama_produk, 0,$y+=15);
           printer_draw_text($handle, $list->jumlah." x ".format_uang($list->harga_jual), 0,$y+=15);
           printer_draw_text($handle, substr("".format_uang($list->harga_jual*$list->jumlah), -10), 250, $y);

           if($list->diskon != 0){
              printer_draw_text($handle, "Diskon", 0,$y+= 140);
              printer_draw_text($handle, substr("-".format_uang($list->diskon/100*$list->sub_total),-10),850,$y);
           }
         }
        
        printer_draw_text($handle, "------------------------------------", 0, $y+=15);

        printer_draw_text($handle, "Total Harga     : ", 0, $y+=15);
        printer_draw_text($handle, substr("          ".format_uang($penjualan->total_harga), 0),155,$y);

        printer_draw_text($handle, "Total Item      : ", 0, $y+=15);
        printer_draw_text($handle, substr("          ".$penjualan->total_item, 0),155,$y);

        printer_draw_text($handle, "Diskon Member : ", 0, $y+=15);
        printer_draw_text($handle, substr("           ".$penjualan->diskon."%",0),155,$y);

        printer_draw_text($handle, "Total Bayar   : ", 0, $y+=15);
        printer_draw_text($handle, substr("            ".format_uang($penjualan->bayar),0), 155, $y);

        printer_draw_text($handle, "Diterima      : ", 0, $y+=15);
        printer_draw_text($handle, substr("            ".format_uang($penjualan->diterima), 0), 155,$y);

        printer_draw_text($handle, "Kembali        : ", 0, $y+=15);
        printer_draw_text($handle, substr("            ".format_uang($penjualan->diterima-$penjualan->bayar),0), 155,$y);
        printer_draw_text($handle, "============================", 0, $y+=15);
        printer_draw_text($handle, "Id Member     : ", 0, $y+=15);
        printer_draw_text($handle, substr("          ", 0),155,$y);
        printer_draw_text($handle, "Nama     : ", 0, $y+=15);
        printer_draw_text($handle, substr("          ", 0),155,$y);
        printer_draw_text($handle, "Maxsimal Belanja     : ", 0, $y+=15);
        printer_draw_text($handle, substr("          ", 0),155,$y);

        printer_draw_text($handle, "Belanja Sekarang     : ", 0, $y+=15);
        printer_draw_text($handle, substr("          ".format_uang($penjualan->total_harga), 0),155,$y);


        printer_draw_text($handle, "============================", 0, $y+=15);
        printer_draw_text($handle, "-= TERIMA KASIH =-", 10, $y+=15);
        printer_draw_text($handle, "-Barang Yang sdh dibeli tidak dpt ditukar/kembali-", 10, $y+=15);
        
        printer_delete_font($font);
        
        printer_end_page($handle);
        printer_end_doc($handle);
        printer_close($handle);
      }
       
      return view('penjualan_detail.selesai', compact('setting'));
   }

   public function notaPDF(){
      
      $detail = PenjualanDetail::leftJoin('produk', 'produk.kode_produk', '=', 'penjualan_detail.kode_produk')
                              ->select('penjualan_detail.*','penjualan_detail.harga_jual as harga_jualnya','produk.*')          
                              ->where('id_penjualan', '=', session('idpenjualan'))
                              ->where('unit', '=',Auth::user()->unit)
                              ->get();

      $penjualan = Penjualan::find(session('idpenjualan'));
      $setting = Setting::find(1);
      $toko=Branch::where('kode_toko','=',Auth::user()->unit)->first();
      $no = 0;
     
      $pdf = PDF::loadView('penjualan_detail.notapdf', compact('detail','toko', 'penjualan', 'setting', 'no'));
      $pdf->setPaper(array(0,0,700,600), 'potrait');      
      
      return $pdf->stream();

      Session::forget('idpenjualan');
   
   }
}

