<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Redirect;
use Auth;
use PDF;
use App\Penjualan;
use App\Produk;
use App\ProdukDetail;
use App\KartuStok;
use App\Member;
use App\Setting;
use Yajra\Datatables\Datatables;
use App\PenjualanDetail;
use App\PenjualanDetailTemporary;
use App\TabelTransaksi; 
use App\Branch;
use App\GantiPin;
use App\Musawamah;  
use App\MusawamahDetail;  
use Alert;  
use DB;

class PenjualanDetailMemberPabrikController extends Controller{
   
   public function index(){
      $member = Member::all();
      $produk = Produk::where('unit',Auth::user()->unit)->where('stok','>',0)->get();
      $setting = Setting::first();
     
     if(!empty(session('idpenjualan'))){
      $member_id=session('idmember');
     

      $memberr = Member::leftjoin('musawamah','musawamah.id_member','=','member.kode_member')
      ->where('kode_member','=',$member_id)
      ->first();

      $status=$memberr->status_member;
      if($status=='active'){
       $idpenjualan = session('idpenjualan');
            
             
       return view('penjualan_detail_member_pabrik.index', compact('produk', 'member', 'setting', 'idpenjualan'));
      }

     else{
      session()->flash('status', 'Maaf Status Member blokir');
      return Redirect::route('transaksi.menu');
     }
      }else{
       return Redirect::route('home');  
     }
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
         $row[] = "<input type='number' class='form-control' name='jumlah_$list->id_penjualan_detail' value='$list->jumlah' onChange='changeCount($list->id_penjualan_detail)'>";
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

      $produk = DB::select('select kode_produk,id_kategori,harga_jual,harga_beli,promo,diskon from produk where kode_produk = '.$request["kode"] .' and unit = '.Auth::user()->unit);

      $detail = new PenjualanDetailTemporary;
      $detail->id_penjualan = $request['idpenjualan'];
      $detail->kode_produk = $produk[0]->kode_produk;
      $detail->harga_jual = $produk[0]->harga_jual;
      $detail->harga_beli = $produk[0]->harga_beli;
      $detail->promo = $produk[0]->promo;
      $detail->jumlah = '';
      $detail->diskon = $produk[0]->diskon;
      $detail->sub_total = ($produk[0]->harga_jual- ($produk[0]->diskon)) * $detail->jumlah;
      $detail->sub_total_beli = $produk[0]->harga_beli;  
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

   function newPin(Request $request){
      
      $id = $request->id;
      $pin_baru = $request->pin_baru;
      $pin_konf = $request->pin_konf;
      $nik = $request->nik;

      $member = Member::where('kode_member',$id)->first();

      $nik_asli = $member->ID_NUMBER;
      
      if ($pin_baru !== $pin_konf) {
    
         session()->flash('status', 'Konfirmasi PIN Tidak Sama !');
         return Redirect::route('transaksi.menu');
    
      }else if ($nik_asli != $nik) {
         
         session()->flash('status', 'NIK (No. KTP) Salah/Tidak Ditemukan, Hubungi Bagian Administrasi !');
         return Redirect::route('transaksi.menu');
   
      }else {
    
         $member = Member::where('kode_member',$id)->first();
         
         $ganti_pin = new GantiPin;
         $ganti_pin->kode_member = $id;
         $ganti_pin->nik = $member->ID_NUMBER;
         $ganti_pin->pin_lama = $member->PIN;
         $ganti_pin->pin_baru = $pin_baru;
         $ganti_pin->user = Auth::user()->id;
         $ganti_pin->save();

         
         $member->PIN = $pin_baru;
         $member->update();

         $penjualan = new Penjualan; 
         $penjualan->kode_member = $id;    
         $penjualan->total_item = 0;    
         $penjualan->total_harga = 0;    
         $penjualan->diskon = 0;    
         $penjualan->bayar = 0;    
         $penjualan->diterima = 0;    
         $penjualan->id_user = Auth::user()->id;    
         $penjualan->save();
         
         session(['idpenjualan' => $penjualan->id_penjualan]);
         session(['idmember' => $penjualan->kode_member]);
            
         return Redirect::route('memberpabrik.index');    
      }
   }


   public function newSession(Request $request){

      $id = $request->id;
      $pin = $request->pin;

      $check = Member::where('kode_member',$id)->where('PIN',$pin)->first();
      
      if (!$check) {
      
         session()->flash('status', 'Maaf PIN Salah !!');
         return Redirect::route('transaksi.menu');
      
      }else {
         
         
         $penjualan = new Penjualan; 
         $penjualan->kode_member = $id;    
         $penjualan->total_item = 0;    
         $penjualan->total_harga = 0;    
         $penjualan->diskon = 0;    
         $penjualan->unit = Auth::user()->unit;
         $penjualan->type_transaksi = 'credit';
         $penjualan->bayar = 0;    
         $penjualan->diterima = 0;    
         $penjualan->id_user = Auth::user()->id;    
         $penjualan->save();
      
         
         session(['idpenjualan' => $penjualan->id_penjualan]);
         session(['idmember' => $penjualan->kode_member]);
               
         return Redirect::route('memberpabrik.index');    
      }
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

            $now = \Carbon\Carbon::now();

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
                  
                  $harga_beli_0 = $stok_toko * $produk_detail->harga_beli; 
                  $harga_jual_0 = $stok_toko * $d->harga_jual;
            
                  
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
                  
                  $harga_beli_promo_0 = $stok_toko * $produk_detail->harga_beli; 
                  $harga_jual_promo_0 = $stok_toko * $d->harga_jual;
                           
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
                  
                     $harga_beli_0 = $produk_detail->stok_detail * $produk_detail->harga_beli; 
                     $harga_jual_0 = $produk_detail->stok_detail * $d->harga_jual;
                     
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
                     
                     $harga_beli_promo_0 = $produk_detail->stok_detail * $produk_detail->harga_beli; 
                     $harga_jual_promo_0 = $produk_detail->stok_detail * $d->harga_jual;
                     
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

         // looping mengurangi stok pada table produk
         foreach($details as $list){

            $produk = Produk::where('kode_produk',$list->kode_produk)->where('unit',Auth::user()->unit)->first();
            $produk->stok -= $list->jumlah;
            $produk->update();

         }

         $param_tgl = \App\ParamTgl::where('nama_param_tgl','tanggal_transaksi')->where('unit',Auth::user()->id)->first();

         $now = $param_tgl->param_tgl;           

         $penjualan = Penjualan::find($request['idpenjualan']);
               
         $data_member = Musawamah::find(session('idmember'));

         // jumlah_sisa_plafond
         $sisa_plafond = $data_member->Plafond - $data_member->os; 
               
         $branch_coa_aktiva_user = Branch::find(Auth::user()->unit);
         $coa_aktiva_user = $branch_coa_aktiva_user->aktiva;

         $total_item = PenjualanDetail::where('id_penjualan',$request['idpenjualan'])->sum('jumlah');
         
         $total_harga_beli_non_promo = PenjualanDetail::where('id_penjualan',$request['idpenjualan'])->whereRaw('harga_beli < harga_jual')->sum('sub_total_beli');
         $total_harga_jual_non_promo = PenjualanDetail::where('id_penjualan',$request['idpenjualan'])->whereRaw('harga_beli < harga_jual')->sum('sub_total');

         
         $total_harga_jual_keseluruhan = PenjualanDetail::where('id_penjualan',$request['idpenjualan'])->sum('sub_total');
         $total_harga_beli_keseluruhan = PenjualanDetail::where('id_penjualan',$request['idpenjualan'])->sum('sub_total_beli');
         
         $cek_promo = PenjualanDetail::where('id_penjualan',$request['idpenjualan'])->whereRaw('harga_beli > harga_jual')->first();
         
         $total_diskon = PenjualanDetail::where('id_penjualan',$request['idpenjualan'])->sum('diskon');
         
         $total_belanja = $total_harga_jual_keseluruhan - $total_diskon;
         
         $harus_dibayar = $total_belanja - max($sisa_plafond,0) ;
         
         $margin = $total_harga_jual_non_promo - $total_harga_beli_non_promo;

         $donasi = $request['donasi'];

         $os_baru = $total_belanja - max($harus_dibayar,0);

         
         if ($cek_promo) {
            
            $total_harga_beli_barang_promo = PenjualanDetail::where('id_penjualan',$request['idpenjualan'])->whereRaw('harga_beli > harga_jual')->sum('sub_total_beli');
            $total_harga_jual_barang_promo = PenjualanDetail::where('id_penjualan',$request['idpenjualan'])->whereRaw('harga_beli > harga_jual')->sum('sub_total');

            $bol = $total_harga_beli_barang_promo - $total_harga_jual_barang_promo;
         
            $persediaan_barang_dagang = $total_harga_beli_non_promo + $total_harga_jual_non_promo;

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
         
         $musawamah = Musawamah::find(session('idmember'));

         if ($musawamah->os == 0) {
            $musawamah->tgl_wakalah = date('Y-m-d h:m:s');
         }

         $musawamah->os += $os_baru;
         $musawamah->saldo_margin += $margin;
         $musawamah->angsuran = $musawamah->os / $musawamah->Tenor;
         $musawamah->ijaroh = $musawamah->saldo_margin / $musawamah->Tenor;
         $musawamah->update();
         
         $musa = Musawamah::where('id_member', '=', session('idmember'))
                           ->first();
         
         $Mdetail = new MusawamahDetail;
         $Mdetail->BUSS_DATE = $now;
         $Mdetail->NOREK = session('idmember');
         $Mdetail->UNIT = $musa->unit;
         $Mdetail->id_member = session('idmember');
         $Mdetail->code_kel = $musa->code_kel;
         $Mdetail->DEBIT = 0;
         $Mdetail->TYPE = 3;
         $Mdetail->KREDIT =   $os_baru;
         $Mdetail->USERID =  Auth::user()->id;
         $Mdetail->KET =  'musawamah';
         $Mdetail->CAO =  $musa->cao;
         $Mdetail->kode_transaksi = $request['idpenjualan'];
         $Mdetail->save();

         $saha = Member::where('kode_member', session('idmember'))->first();
         $unit_member =$saha->UNIT;
         $unit_toko = Auth::user()->unit;
         $branch_coa_aktiva_member = Branch::find($unit_member);
         $coa_aktiva_member = $branch_coa_aktiva_member->aktiva;

         $param = $musa->os;
         $plafond = $musa->Plafond;

         if($param >= $plafond){

            $saha->status_member = 'Blokir';
            $saha->update();
         
         }
         
         $saldo_musa = $plafond-$param;

      // all of case transaksi
      switch (true) {
            
         // case unit member = unit toko
         case ($unit_member == $unit_toko):
            
            switch (true) {
               
               // belanja melebihi plafon unit toko = unit member
               case ($total_belanja > $os_baru):

                  // jika ada barang promo unit toko = unit member
                  if ($cek_promo) {
                     
                     // 1412000	Piutang Musawamah
                     $jurnal = new TabelTransaksi;
                     $jurnal->unit =  Auth::user()->unit; 
                     $jurnal->kode_transaksi = $request['idpenjualan'];
                     $jurnal->kode_rekening = 1412000;
                     $jurnal->tanggal_transaksi = $now;
                     $jurnal->jenis_transaksi  = 'Jurnal System';
                     $jurnal->keterangan_transaksi = 'Musawamah ';
                     $jurnal->debet = $os_baru;
                     $jurnal->kredit = 0;
                     $jurnal->tanggal_posting = ' ';
                     $jurnal->keterangan_posting = '0';
                     $jurnal->id_admin = Auth::user()->unit; 
                     $jurnal->save();
                     
                     // 1120000	Kas Unit - Toko
                     $jurnal = new TabelTransaksi;
                     $jurnal->unit =  Auth::user()->unit; 
                     $jurnal->kode_transaksi = $request['idpenjualan'];
                     $jurnal->kode_rekening = 1120000;
                     $jurnal->tanggal_transaksi = $now;
                     $jurnal->jenis_transaksi  = 'Jurnal System';
                     $jurnal->keterangan_transaksi = 'Musawamah ';
                     $jurnal->debet =$harus_dibayar;
                     $jurnal->kredit = 0;
                     $jurnal->tanggal_posting = ' ';
                     $jurnal->keterangan_posting = ' ';
                     $jurnal->id_admin = Auth::user()->id; 
                     $jurnal->save();

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

                        
                     // // 1482000	Persediaan Barang Dagang
                     // $jurnal = new TabelTransaksi;
                     // $jurnal->unit =  Auth::user()->unit; 
                     // $jurnal->kode_transaksi = $request['idpenjualan'];
                     // $jurnal->kode_rekening = 1482000;
                     // $jurnal->tanggal_transaksi = $now;
                     // $jurnal->jenis_transaksi  = 'Jurnal System';
                     // $jurnal->keterangan_transaksi = 'Musawamah ';
                     // $jurnal->debet =0;
                     // $jurnal->kredit = $total_harga_beli_barang_promo + $total_harga_jual_non_promo;
                     // $jurnal->tanggal_posting = '';
                     // $jurnal->keterangan_posting = '';
                     // $jurnal->id_admin = Auth::user()->id; 
                     // $jurnal->save();

                  
                  // jika tidak ada promo unit toko = unit member
                  }else {
                     
                     // 1412000	Piutang Musawamah
                     $jurnal = new TabelTransaksi;
                     $jurnal->unit =  Auth::user()->unit; 
                     $jurnal->kode_transaksi = $request['idpenjualan'];
                     $jurnal->kode_rekening = 1412000;
                     $jurnal->tanggal_transaksi = $now;
                     $jurnal->jenis_transaksi  = 'Jurnal System';
                     $jurnal->keterangan_transaksi = 'Musawamah ';
                     $jurnal->debet = $os_baru;
                     $jurnal->kredit = 0;
                     $jurnal->tanggal_posting = ' ';
                     $jurnal->keterangan_posting = '0';
                     $jurnal->id_admin = Auth::user()->unit; 
                     $jurnal->save();
                     
                     // 1120000	Kas Unit - Toko
                     $jurnal = new TabelTransaksi;
                     $jurnal->unit =  Auth::user()->unit; 
                     $jurnal->kode_transaksi = $request['idpenjualan'];
                     $jurnal->kode_rekening = 1120000;
                     $jurnal->tanggal_transaksi = $now;
                     $jurnal->jenis_transaksi  = 'Jurnal System';
                     $jurnal->keterangan_transaksi = 'Musawamah ';
                     $jurnal->debet =$harus_dibayar;
                     $jurnal->kredit = 0;
                     $jurnal->tanggal_posting = ' ';
                     $jurnal->keterangan_posting = ' ';
                     $jurnal->id_admin = Auth::user()->id; 
                     $jurnal->save();
                        
                     // // 1482000	Persediaan Barang Dagang
                     // $jurnal = new TabelTransaksi;
                     // $jurnal->unit =  Auth::user()->unit; 
                     // $jurnal->kode_transaksi = $request['idpenjualan'];
                     // $jurnal->kode_rekening = 1482000;
                     // $jurnal->tanggal_transaksi = $now;
                     // $jurnal->jenis_transaksi  = 'Jurnal System';
                     // $jurnal->keterangan_transaksi = 'Musawamah ';
                     // $jurnal->debet =0;
                     // $jurnal->kredit = $persediaan_barang_dagang;
                     // $jurnal->tanggal_posting = '';
                     // $jurnal->keterangan_posting = '';
                     // $jurnal->id_admin = Auth::user()->id; 
                     // $jurnal->save();
                     
                  }
                  // end of if else barang promo unit toko = unit member

               break;
               // end of case belanja melebih plafon unit toko = unit member
                  
               // belanja tidak melebihi plafond unit toko = unit member
               default:
                     
                  // case ada barang promo unit toko = unit member
                  if ($cek_promo) {
                        
                     // 1412000	Piutang Musawamah
                     $jurnal = new TabelTransaksi;
                     $jurnal->unit =  Auth::user()->unit; 
                     $jurnal->kode_transaksi = $request['idpenjualan'];
                     $jurnal->kode_rekening = 1412000;
                     $jurnal->tanggal_transaksi = $now;
                     $jurnal->jenis_transaksi  = 'Jurnal System';
                     $jurnal->keterangan_transaksi = 'Musawamah ';
                     $jurnal->debet = $os_baru;
                     $jurnal->kredit = 0;
                     $jurnal->tanggal_posting = ' ';
                     $jurnal->keterangan_posting = '0';
                     $jurnal->id_admin = Auth::user()->unit; 
                     $jurnal->save();

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

                        
                     // // 1482000	Persediaan Barang Dagang
                     // $jurnal = new TabelTransaksi;
                     // $jurnal->unit =  Auth::user()->unit; 
                     // $jurnal->kode_transaksi = $request['idpenjualan'];
                     // $jurnal->kode_rekening = 1482000;
                     // $jurnal->tanggal_transaksi = $now;
                     // $jurnal->jenis_transaksi  = 'Jurnal System';
                     // $jurnal->keterangan_transaksi = 'Musawamah ';
                     // $jurnal->debet = 0;
                     // $jurnal->kredit = $total_harga_beli_barang_promo + $total_harga_jual_non_promo;
                     // $jurnal->tanggal_posting = '';
                     // $jurnal->keterangan_posting = '';
                     // $jurnal->id_admin = Auth::user()->id; 
                     // $jurnal->save();

                  // jika tidak ada promo unit toko = unit member
                  }else {
                     
                     // 1412000	Piutang Musawamah
                     $jurnal = new TabelTransaksi;
                     $jurnal->unit =  Auth::user()->unit; 
                     $jurnal->kode_transaksi = $request['idpenjualan'];
                     $jurnal->kode_rekening = 1412000;
                     $jurnal->tanggal_transaksi = $now;
                     $jurnal->jenis_transaksi  = 'Jurnal System';
                     $jurnal->keterangan_transaksi = 'Musawamah ';
                     $jurnal->debet = $os_baru;
                     $jurnal->kredit = 0;
                     $jurnal->tanggal_posting = ' ';
                     $jurnal->keterangan_posting = '0';
                     $jurnal->id_admin = Auth::user()->unit; 
                     $jurnal->save();

                     
                     // // 1482000	Persediaan Barang Dagang
                     // $jurnal = new TabelTransaksi;
                     // $jurnal->unit =  Auth::user()->unit; 
                     // $jurnal->kode_transaksi = $request['idpenjualan'];
                     // $jurnal->kode_rekening = 1482000;
                     // $jurnal->tanggal_transaksi = $now;
                     // $jurnal->jenis_transaksi  = 'Jurnal System';
                     // $jurnal->keterangan_transaksi = 'Musawamah ';
                     // $jurnal->debet =0;
                     // $jurnal->kredit = $persediaan_barang_dagang;
                     // $jurnal->tanggal_posting = ' ';
                     // $jurnal->keterangan_posting = ' ';
                     // $jurnal->id_admin = Auth::user()->id; 
                     // $jurnal->save();

                  }
                  // end of if else barang promo unit toko = unit member
                     
               break;
               // end of case belanja tidak melebihi plafond unit toko = unit member
            
            }

         break;
         // end of case  unit member = unit toko

         // case unit member != unit toko
         default:
            
            switch (true) {
               
               // case belanja melebihi plafond antar toko
               case ($total_belanja > $os_baru):

                  // jika ada barang promo && belanja melbih plafond antar toko
                  if ($cek_promo) {

                     // D	1412000	Piutang Musawamah
                     $jurnal = new TabelTransaksi;
                     $jurnal->unit =  $unit_member; 
                     $jurnal->kode_transaksi = $request['idpenjualan'];
                     $jurnal->kode_rekening = 1412000;
                     $jurnal->tanggal_transaksi = $now;
                     $jurnal->jenis_transaksi  = 'Jurnal System';
                     $jurnal->keterangan_transaksi = 'Musawamah ';
                     $jurnal->debet = $os_baru;
                     $jurnal->kredit = 0;
                     $jurnal->tanggal_posting = ' ';
                     $jurnal->keterangan_posting = '0';
                     $jurnal->id_admin = Auth::user()->id; 
                     $jurnal->save();                     

                     // K	2500000	RAK PASIVA - KP
                     $jurnal = new TabelTransaksi;
                     $jurnal->unit =  $unit_member; 
                     $jurnal->kode_transaksi = $request['idpenjualan'];
                     $jurnal->kode_rekening = 2500000;
                     $jurnal->tanggal_transaksi = $now;
                     $jurnal->jenis_transaksi  = 'Jurnal System';
                     $jurnal->keterangan_transaksi = 'Musawamah ';
                     $jurnal->debet = 0;
                     $jurnal->kredit = $os_baru;
                     $jurnal->tanggal_posting = ' ';
                     $jurnal->keterangan_posting = '0';
                     $jurnal->id_admin = Auth::user()->id; 
                     $jurnal->save();

                     // D	2500000	RAK PASIVA - KP
                     $jurnal = new TabelTransaksi;
                     $jurnal->unit =  $unit_toko; 
                     $jurnal->kode_transaksi = $request['idpenjualan'];
                     $jurnal->kode_rekening = 2500000;
                     $jurnal->tanggal_transaksi = $now;
                     $jurnal->jenis_transaksi  = 'Jurnal System';
                     $jurnal->keterangan_transaksi = 'Musawamah ';
                     $jurnal->debet = $os_baru;
                     $jurnal->kredit = 0;
                     $jurnal->tanggal_posting = ' ';
                     $jurnal->keterangan_posting = '0';
                     $jurnal->id_admin = Auth::user()->id; 
                     $jurnal->save();                     

                     // 1120000	Kas Unit - Toko
                     $jurnal = new TabelTransaksi;
                     $jurnal->unit =  Auth::user()->unit; 
                     $jurnal->kode_transaksi = $request['idpenjualan'];
                     $jurnal->kode_rekening = 1120000;
                     $jurnal->tanggal_transaksi = $now;
                     $jurnal->jenis_transaksi  = 'Jurnal System';
                     $jurnal->keterangan_transaksi = 'Musawamah ';
                     $jurnal->debet =$harus_dibayar;
                     $jurnal->kredit = 0;
                     $jurnal->tanggal_posting = ' ';
                     $jurnal->keterangan_posting = ' ';
                     $jurnal->id_admin = Auth::user()->id; 
                     $jurnal->save();
                     
                     // D	56412	BOL-TI Promo/Discount/Kupon
                     $jurnal = new TabelTransaksi;
                     $jurnal->unit =  $unit_toko; 
                     $jurnal->kode_transaksi = $request['idpenjualan'];
                     $jurnal->kode_rekening = 56412;
                     $jurnal->tanggal_transaksi = $now;
                     $jurnal->jenis_transaksi  = 'Jurnal System';
                     $jurnal->keterangan_transaksi = 'BOL-TI Promo';
                     $jurnal->debet = $bol;
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


                     // // K	1482000	Persediaan Musawamah/Barang Dagang
                     // $jurnal = new TabelTransaksi;
                     // $jurnal->unit =  $unit_toko; 
                     // $jurnal->kode_transaksi = $request['idpenjualan'];
                     // $jurnal->kode_rekening = 1482000;
                     // $jurnal->tanggal_transaksi = $now;
                     // $jurnal->jenis_transaksi  = 'Jurnal System';
                     // $jurnal->keterangan_transaksi = 'Musawamah ';
                     // $jurnal->debet = 0;
                     // $jurnal->kredit = $total_harga_beli_barang_promo + $total_harga_jual_non_promo;
                     // $jurnal->tanggal_posting = ' ';
                     // $jurnal->keterangan_posting = '0';
                     // $jurnal->id_admin = Auth::user()->id; 
                     // $jurnal->save();

                     // D	1833000	RAK Aktiva - Unit TI CIRANJANG 2
                     $jurnal = new TabelTransaksi;
                     $jurnal->unit = '1010'; 
                     $jurnal->kode_transaksi = $request['idpenjualan'];
                     $jurnal->kode_rekening = $coa_aktiva_member;
                     $jurnal->tanggal_transaksi = $now;
                     $jurnal->jenis_transaksi  = 'Jurnal System';
                     $jurnal->keterangan_transaksi = 'Musawamah ';
                     $jurnal->debet = $os_baru;
                     $jurnal->kredit = 0;
                     $jurnal->tanggal_posting = ' ';
                     $jurnal->keterangan_posting = '0';
                     $jurnal->id_admin = Auth::user()->id; 
                     $jurnal->save();
                  
                     // K	1831000	RAK Aktiva - Unit TI CIANJUR
                     $jurnal = new TabelTransaksi;
                     $jurnal->unit =  '1010'; 
                     $jurnal->kode_transaksi = $request['idpenjualan'];
                     $jurnal->kode_rekening = $coa_aktiva_user;
                     $jurnal->tanggal_transaksi = $now;
                     $jurnal->jenis_transaksi  = 'Jurnal System';
                     $jurnal->keterangan_transaksi = 'Musawamah ';
                     $jurnal->debet =0;
                     $jurnal->kredit = $os_baru;
                     $jurnal->tanggal_posting = ' ';
                     $jurnal->keterangan_posting = '0';
                     $jurnal->id_admin = Auth::user()->id; 
                     $jurnal->save();
   
                  // jika tidak ada barang promo && belanja melbih plafond antar toko
                  }else {
                     
                     // D	1412000	Piutang Musawamah
                     $jurnal = new TabelTransaksi;
                     $jurnal->unit =  $unit_member; 
                     $jurnal->kode_transaksi = $request['idpenjualan'];
                     $jurnal->kode_rekening = 1412000;
                     $jurnal->tanggal_transaksi = $now;
                     $jurnal->jenis_transaksi  = 'Jurnal System';
                     $jurnal->keterangan_transaksi = 'Musawamah ';
                     $jurnal->debet = $os_baru;
                     $jurnal->kredit = 0;
                     $jurnal->tanggal_posting = ' ';
                     $jurnal->keterangan_posting = '0';
                     $jurnal->id_admin = Auth::user()->id; 
                     $jurnal->save();                     

                     // K	2500000	RAK PASIVA - KP
                     $jurnal = new TabelTransaksi;
                     $jurnal->unit =  $unit_member; 
                     $jurnal->kode_transaksi = $request['idpenjualan'];
                     $jurnal->kode_rekening = 2500000;
                     $jurnal->tanggal_transaksi = $now;
                     $jurnal->jenis_transaksi  = 'Jurnal System';
                     $jurnal->keterangan_transaksi = 'Musawamah ';
                     $jurnal->debet = 0;
                     $jurnal->kredit = $os_baru;
                     $jurnal->tanggal_posting = ' ';
                     $jurnal->keterangan_posting = '0';
                     $jurnal->id_admin = Auth::user()->id; 
                     $jurnal->save();

                     // D	2500000	RAK PASIVA - KP
                     $jurnal = new TabelTransaksi;
                     $jurnal->unit =  $unit_toko; 
                     $jurnal->kode_transaksi = $request['idpenjualan'];
                     $jurnal->kode_rekening = 2500000;
                     $jurnal->tanggal_transaksi = $now;
                     $jurnal->jenis_transaksi  = 'Jurnal System';
                     $jurnal->keterangan_transaksi = 'Musawamah ';
                     $jurnal->debet = $os_baru;
                     $jurnal->kredit = 0;
                     $jurnal->tanggal_posting = ' ';
                     $jurnal->keterangan_posting = '0';
                     $jurnal->id_admin = Auth::user()->id; 
                     $jurnal->save();                     

                     // 1120000	Kas Unit - Toko
                     $jurnal = new TabelTransaksi;
                     $jurnal->unit =  Auth::user()->unit; 
                     $jurnal->kode_transaksi = $request['idpenjualan'];
                     $jurnal->kode_rekening = 1120000;
                     $jurnal->tanggal_transaksi = $now;
                     $jurnal->jenis_transaksi  = 'Jurnal System';
                     $jurnal->keterangan_transaksi = 'Musawamah ';
                     $jurnal->debet =$harus_dibayar;
                     $jurnal->kredit = 0;
                     $jurnal->tanggal_posting = ' ';
                     $jurnal->keterangan_posting = ' ';
                     $jurnal->id_admin = Auth::user()->id; 
                     $jurnal->save();                

                     // // K	1482000	Persediaan Musawamah/Barang Dagang
                     // $jurnal = new TabelTransaksi;
                     // $jurnal->unit =  $unit_toko; 
                     // $jurnal->kode_transaksi = $request['idpenjualan'];
                     // $jurnal->kode_rekening = 1482000;
                     // $jurnal->tanggal_transaksi = $now;
                     // $jurnal->jenis_transaksi  = 'Jurnal System';
                     // $jurnal->keterangan_transaksi = 'Musawamah ';
                     // $jurnal->debet = 0;
                     // $jurnal->kredit = $persediaan_barang_dagang;
                     // $jurnal->tanggal_posting = ' ';
                     // $jurnal->keterangan_posting = '0';
                     // $jurnal->id_admin = Auth::user()->id; 
                     // $jurnal->save();

                     // D	1833000	RAK Aktiva - Unit TI CIRANJANG 2
                     $jurnal = new TabelTransaksi;
                     $jurnal->unit = '1010'; 
                     $jurnal->kode_transaksi = $request['idpenjualan'];
                     $jurnal->kode_rekening = $coa_aktiva_member;
                     $jurnal->tanggal_transaksi = $now;
                     $jurnal->jenis_transaksi  = 'Jurnal System';
                     $jurnal->keterangan_transaksi = 'Musawamah ';
                     $jurnal->debet = $os_baru;
                     $jurnal->kredit = 0;
                     $jurnal->tanggal_posting = ' ';
                     $jurnal->keterangan_posting = '0';
                     $jurnal->id_admin = Auth::user()->id; 
                     $jurnal->save();
                  
                     // K	1831000	RAK Aktiva - Unit TI CIANJUR
                     $jurnal = new TabelTransaksi;
                     $jurnal->unit =  '1010'; 
                     $jurnal->kode_transaksi = $request['idpenjualan'];
                     $jurnal->kode_rekening = $coa_aktiva_user;
                     $jurnal->tanggal_transaksi = $now;
                     $jurnal->jenis_transaksi  = 'Jurnal System';
                     $jurnal->keterangan_transaksi = 'Musawamah ';
                     $jurnal->debet =0;
                     $jurnal->kredit = $os_baru;
                     $jurnal->tanggal_posting = ' ';
                     $jurnal->keterangan_posting = '0';
                     $jurnal->id_admin = Auth::user()->id; 
                     $jurnal->save();
                  }
                  // end of if else barang promo && belanja melbih plafond antar toko
                        
               break;
               // end off case belanja melebihi plafond antar toko

               // case belanja tidak melebihi plafond antar toko
               default:
                     
                  // jika belanja tidak melebihi plafond antar toko && ada barang promo
                  if ($cek_promo) {
                     
                     // D	1412000	Piutang Musawamah
                     $jurnal = new TabelTransaksi;
                     $jurnal->unit =  $unit_member; 
                     $jurnal->kode_transaksi = $request['idpenjualan'];
                     $jurnal->kode_rekening = 1412000;
                     $jurnal->tanggal_transaksi = $now;
                     $jurnal->jenis_transaksi  = 'Jurnal System';
                     $jurnal->keterangan_transaksi = 'Musawamah ';
                     $jurnal->debet = $os_baru;
                     $jurnal->kredit = 0;
                     $jurnal->tanggal_posting = ' ';
                     $jurnal->keterangan_posting = '0';
                     $jurnal->id_admin = Auth::user()->id; 
                     $jurnal->save();                     

                     // K	2500000	RAK PASIVA - KP
                     $jurnal = new TabelTransaksi;
                     $jurnal->unit =  $unit_member; 
                     $jurnal->kode_transaksi = $request['idpenjualan'];
                     $jurnal->kode_rekening = 2500000;
                     $jurnal->tanggal_transaksi = $now;
                     $jurnal->jenis_transaksi  = 'Jurnal System';
                     $jurnal->keterangan_transaksi = 'Musawamah ';
                     $jurnal->debet = 0;
                     $jurnal->kredit = $os_baru;
                     $jurnal->tanggal_posting = ' ';
                     $jurnal->keterangan_posting = '0';
                     $jurnal->id_admin = Auth::user()->id; 
                     $jurnal->save();

                     // D	2500000	RAK PASIVA - KP
                     $jurnal = new TabelTransaksi;
                     $jurnal->unit =  $unit_toko; 
                     $jurnal->kode_transaksi = $request['idpenjualan'];
                     $jurnal->kode_rekening = 2500000;
                     $jurnal->tanggal_transaksi = $now;
                     $jurnal->jenis_transaksi  = 'Jurnal System';
                     $jurnal->keterangan_transaksi = 'Musawamah ';
                     $jurnal->debet = $os_baru;
                     $jurnal->kredit = 0;
                     $jurnal->tanggal_posting = ' ';
                     $jurnal->keterangan_posting = '0';
                     $jurnal->id_admin = Auth::user()->id; 
                     $jurnal->save();                     

                     // D	56412	BOL-TI Promo/Discount/Kupon
                     $jurnal = new TabelTransaksi;
                     $jurnal->unit =  $unit_toko; 
                     $jurnal->kode_transaksi = $request['idpenjualan'];
                     $jurnal->kode_rekening = 56412;
                     $jurnal->tanggal_transaksi = $now;
                     $jurnal->jenis_transaksi  = 'Jurnal System';
                     $jurnal->keterangan_transaksi = 'BOL-TI Promo';
                     $jurnal->debet = $bol;
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


                     // // K	1482000	Persediaan Musawamah/Barang Dagang
                     // $jurnal = new TabelTransaksi;
                     // $jurnal->unit =  Auth::user()->unit; 
                     // $jurnal->kode_transaksi = $request['idpenjualan'];
                     // $jurnal->kode_rekening = 1482000;
                     // $jurnal->tanggal_transaksi = $now;
                     // $jurnal->jenis_transaksi  = 'Jurnal System';
                     // $jurnal->keterangan_transaksi = 'Musawamah ';
                     // $jurnal->debet = 0;
                     // $jurnal->kredit = $total_harga_beli_barang_promo + $total_harga_jual_non_promo;
                     // $jurnal->tanggal_posting = ' ';
                     // $jurnal->keterangan_posting = '0';
                     // $jurnal->id_admin = Auth::user()->id; 
                     // $jurnal->save();

                     // D	1833000	RAK Aktiva - Unit TI CIRANJANG 2

                     $jurnal = new TabelTransaksi;
                     $jurnal->unit = '1010'; 
                     $jurnal->kode_transaksi = $request['idpenjualan'];
                     $jurnal->kode_rekening = $coa_aktiva_member;
                     $jurnal->tanggal_transaksi = $now;
                     $jurnal->jenis_transaksi  = 'Jurnal System';
                     $jurnal->keterangan_transaksi = 'Musawamah ';
                     $jurnal->debet = $os_baru;
                     $jurnal->kredit = 0;
                     $jurnal->tanggal_posting = ' ';
                     $jurnal->keterangan_posting = '0';
                     $jurnal->id_admin = Auth::user()->id; 
                     $jurnal->save();
                  
                     // K	1831000	RAK Aktiva - Unit TI CIANJUR
                     $jurnal = new TabelTransaksi;
                     $jurnal->unit =  '1010'; 
                     $jurnal->kode_transaksi = $request['idpenjualan'];
                     $jurnal->kode_rekening = $coa_aktiva_user;
                     $jurnal->tanggal_transaksi = $now;
                     $jurnal->jenis_transaksi  = 'Jurnal System';
                     $jurnal->keterangan_transaksi = 'Musawamah ';
                     $jurnal->debet =0;
                     $jurnal->kredit = $os_baru;
                     $jurnal->tanggal_posting = ' ';
                     $jurnal->keterangan_posting = '0';
                     $jurnal->id_admin = Auth::user()->id; 
                     $jurnal->save();

                     
                  // jika belanja tidak melebihi plafond antar toko && tidak ada barang promo
                  }else {

                     // D	1412000	Piutang Musawamah
                     $jurnal = new TabelTransaksi;
                     $jurnal->unit =  $unit_member; 
                     $jurnal->kode_transaksi = $request['idpenjualan'];
                     $jurnal->kode_rekening = 1412000;
                     $jurnal->tanggal_transaksi = $now;
                     $jurnal->jenis_transaksi  = 'Jurnal System';
                     $jurnal->keterangan_transaksi = 'Musawamah ';
                     $jurnal->debet = $os_baru;
                     $jurnal->kredit = 0;
                     $jurnal->tanggal_posting = ' ';
                     $jurnal->keterangan_posting = '0';
                     $jurnal->id_admin = Auth::user()->id; 
                     $jurnal->save();                     

                     // K	2500000	RAK PASIVA - KP
                     $jurnal = new TabelTransaksi;
                     $jurnal->unit =  $unit_member; 
                     $jurnal->kode_transaksi = $request['idpenjualan'];
                     $jurnal->kode_rekening = 2500000;
                     $jurnal->tanggal_transaksi = $now;
                     $jurnal->jenis_transaksi  = 'Jurnal System';
                     $jurnal->keterangan_transaksi = 'Musawamah ';
                     $jurnal->debet = 0;
                     $jurnal->kredit = $os_baru;
                     $jurnal->tanggal_posting = ' ';
                     $jurnal->keterangan_posting = '0';
                     $jurnal->id_admin = Auth::user()->id; 
                     $jurnal->save();

                     // D	2500000	RAK PASIVA - KP
                     $jurnal = new TabelTransaksi;
                     $jurnal->unit =  $unit_toko; 
                     $jurnal->kode_transaksi = $request['idpenjualan'];
                     $jurnal->kode_rekening = 2500000;
                     $jurnal->tanggal_transaksi = $now;
                     $jurnal->jenis_transaksi  = 'Jurnal System';
                     $jurnal->keterangan_transaksi = 'Musawamah ';
                     $jurnal->debet = $os_baru;
                     $jurnal->kredit = 0;
                     $jurnal->tanggal_posting = ' ';
                     $jurnal->keterangan_posting = '0';
                     $jurnal->id_admin = Auth::user()->id; 
                     $jurnal->save();                     

                     // // K	1482000	Persediaan Musawamah/Barang Dagang
                     // $jurnal = new TabelTransaksi;
                     // $jurnal->unit =  $unit_toko; 
                     // $jurnal->kode_transaksi = $request['idpenjualan'];
                     // $jurnal->kode_rekening = 1482000;
                     // $jurnal->tanggal_transaksi = $now;
                     // $jurnal->jenis_transaksi  = 'Jurnal System';
                     // $jurnal->keterangan_transaksi = 'Musawamah ';
                     // $jurnal->debet = 0;
                     // $jurnal->kredit = $persediaan_barang_dagang;
                     // $jurnal->tanggal_posting = ' ';
                     // $jurnal->keterangan_posting = '0';
                     // $jurnal->id_admin = Auth::user()->id; 
                     // $jurnal->save();

                     // D	1833000	RAK Aktiva - Unit TI CIRANJANG 2

                     $jurnal = new TabelTransaksi;
                     $jurnal->unit = '1010'; 
                     $jurnal->kode_transaksi = $request['idpenjualan'];
                     $jurnal->kode_rekening = $coa_aktiva_member;
                     $jurnal->tanggal_transaksi = $now;
                     $jurnal->jenis_transaksi  = 'Jurnal System';
                     $jurnal->keterangan_transaksi = 'Musawamah ';
                     $jurnal->debet = $os_baru;
                     $jurnal->kredit = 0;
                     $jurnal->tanggal_posting = ' ';
                     $jurnal->keterangan_posting = '0';
                     $jurnal->id_admin = Auth::user()->id; 
                     $jurnal->save();
                  
                     // K	1831000	RAK Aktiva - Unit TI CIANJUR
                     $jurnal = new TabelTransaksi;
                     $jurnal->unit =  '1010'; 
                     $jurnal->kode_transaksi = $request['idpenjualan'];
                     $jurnal->kode_rekening = $coa_aktiva_user;
                     $jurnal->tanggal_transaksi = $now;
                     $jurnal->jenis_transaksi  = 'Jurnal System';
                     $jurnal->keterangan_transaksi = 'Musawamah ';
                     $jurnal->debet =0;
                     $jurnal->kredit = $os_baru;
                     $jurnal->tanggal_posting = ' ';
                     $jurnal->keterangan_posting = '0';
                     $jurnal->id_admin = Auth::user()->id; 
                     $jurnal->save();

                  }
                  // end of if else belanja tidak melebih plafond && barang promo antar toko

               break;
               // end of case belanja tidak melebihi plafond antar toko 
               
            }

         break;
         // end of case unit member != unit toko
      
      }
      // end of all case transaksi


   if ($margin > 0) {

            $jurnal = new TabelTransaksi;
            $jurnal->unit =  Auth::user()->unit; 
            $jurnal->kode_transaksi = $request['idpenjualan'];
            $jurnal->kode_rekening = 1483000;
            $jurnal->tanggal_transaksi  = $now;
            $jurnal->jenis_transaksi  = 'Jurnal System';
            $jurnal->keterangan_transaksi = 'Margin Penjualan';
            $jurnal->debet = $margin;
            $jurnal->kredit = 0;
            $jurnal->tanggal_posting = '';
            $jurnal->keterangan_posting = '0';
            $jurnal->id_admin = Auth::user()->id; 
            $jurnal->save();
            
         
            $jurnal = new TabelTransaksi;
            $jurnal->unit =  $unit_member; 
            $jurnal->kode_transaksi = $request['idpenjualan'];
            $jurnal->kode_rekening = 1422000;
            $jurnal->tanggal_transaksi  = $now;
            $jurnal->jenis_transaksi  = 'Jurnal System';
            $jurnal->keterangan_transaksi = 'Margin Penjualan';
            $jurnal->debet = 0;
            $jurnal->kredit = $margin;
            $jurnal->tanggal_posting = '';
            $jurnal->keterangan_posting = '0';
            $jurnal->id_admin = Auth::user()->id; 
            $jurnal->save();

            if ($unit_toko != $unit_member) {

               $jurnal = new TabelTransaksi;
               $jurnal->unit =  $unit_member; 
               $jurnal->kode_transaksi = $request['idpenjualan'];
               $jurnal->kode_rekening = 2500000;
               $jurnal->tanggal_transaksi  = $now;
               $jurnal->jenis_transaksi  = 'Jurnal System';
               $jurnal->keterangan_transaksi = 'Margin Penjualan';
               $jurnal->debet = $margin;
               $jurnal->kredit = 0;
               $jurnal->tanggal_posting = '';
               $jurnal->keterangan_posting = '0';
               $jurnal->id_admin = Auth::user()->id; 
               $jurnal->save();
            
               $jurnal = new TabelTransaksi;
               $jurnal->unit =  $unit_toko; 
               $jurnal->kode_transaksi = $request['idpenjualan'];
               $jurnal->kode_rekening = 2500000;
               $jurnal->tanggal_transaksi  = $now;
               $jurnal->jenis_transaksi  = 'Jurnal System';
               $jurnal->keterangan_transaksi = 'Margin Penjualan';
               $jurnal->debet = 0;
               $jurnal->kredit = $margin;
               $jurnal->tanggal_posting = '';
               $jurnal->keterangan_posting = '0';
               $jurnal->id_admin = Auth::user()->id; 
               $jurnal->save();

               // D	1833000	RAK Aktiva - Unit TI CIRANJANG 2
               $jurnal = new TabelTransaksi;
               $jurnal->unit = '1010'; 
               $jurnal->kode_transaksi = $request['idpenjualan'];
               $jurnal->kode_rekening = $coa_aktiva_user;
               $jurnal->tanggal_transaksi = $now;
               $jurnal->jenis_transaksi  = 'Jurnal System';
               $jurnal->keterangan_transaksi = 'Musawamah ';
               $jurnal->debet = $margin;
               $jurnal->kredit = 0;
               $jurnal->tanggal_posting = ' ';
               $jurnal->keterangan_posting = '0';
               $jurnal->id_admin = Auth::user()->id; 
               $jurnal->save();
            
               // K	1831000	RAK Aktiva - Unit TI CIANJUR
               $jurnal = new TabelTransaksi;
               $jurnal->unit =  '1010'; 
               $jurnal->kode_transaksi = $request['idpenjualan'];
               $jurnal->kode_rekening = $coa_aktiva_member;
               $jurnal->tanggal_transaksi = $now;
               $jurnal->jenis_transaksi  = 'Jurnal System';
               $jurnal->keterangan_transaksi = 'Musawamah ';
               $jurnal->debet =0;
               $jurnal->kredit = $margin;
               $jurnal->tanggal_posting = ' ';
               $jurnal->keterangan_posting = '0';
               $jurnal->id_admin = Auth::user()->id; 
               $jurnal->save();

            }
            }
         if ($donasi > 0) {

            $jurnal = new TabelTransaksi;
            $jurnal->unit =  Auth::user()->unit; 
            $jurnal->kode_transaksi = $request['idpenjualan'];
            $jurnal->kode_rekening = 1120000;
            $jurnal->tanggal_transaksi = $now;
            $jurnal->jenis_transaksi  = 'Jurnal System';
            $jurnal->keterangan_transaksi = 'Musawamah ';
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

      return Redirect::route('memberpabrik.cetak');

   }
   
   public function loadForm($diskon, $total, $diterima){
      $idmember=session('idmember');
      $datam = Musawamah::where('id_member', '=', $idmember)->first();
      $musawamah=$datam->os;
      $os=$datam->os;
      $pla=$datam->Plafond;
      
      
      $bayar = $total - ($diskon);
      $selisih =($os + $bayar)-$pla;
      

      if($selisih > 0){
         $bayar_cash=$selisih;
      }elseif($selisih < 0)
      {
         $bayar_cash=0;

      }else{

         $bayar_cash=$selisih;
      }      
       

      $sisa_os=$pla-$os;

      if($bayar < $sisa_os){
         $musawamah=$os + $bayar;
      }else{
         $musawamah=$pla;
      }   



      
       $kembali = ($diterima != 0) ? $bayar_cash - $diterima : 0;
      

      $data = array(
        "totalrp" => format_uang($total),
        "bayar" => $bayar,
        "pla" => $pla,      
        "os" => $os, 
        "musawamah" => $musawamah,
        "member" => $idmember,    
        "selisih" => $bayar_cash,                       
        "bayarrp" => format_uang($bayar),
        "terbilang" => ucwords(terbilang($selisih))." Rupiah",
        "kembalirp" => format_uang($kembali),
        "kembaliterbilang" => ucwords(terbilang($kembali))." Rupiah"
      );
     return response()->json($data);
   }

   public function printNota()
   {
      $detail = PenjualanDetail::leftJoin('produk', 'produk.kode_produk', '=', 'penjualan_detail.kode_produk')
        ->where('id_penjualan', '=', session('idpenjualan'))
        ->where('produk.unit', '=', Auth::user()->unit) 
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
           printer_draw_text($handle, $list->jumlah." x ".format_uang($list->harga_jual_pabrik), 0,$y+=15);
           printer_draw_text($handle, substr("".format_uang($list->harga_jual_pabrik*$list->jumlah), -10), 250, $y);

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
      
      return view('penjualan_detail_member_pabrik.selesai', compact('setting'));
   }

   public function notaPDF(){
     
      $detail = PenjualanDetail::leftJoin('produk', 'produk.kode_produk', '=', 'penjualan_detail.kode_produk')     
                              ->where('id_penjualan', '=', session('idpenjualan'))
                              ->where('unit', '=',Auth::user()->unit)
                              ->get();
      
      $musawamah = Musawamah::find(session('idmember'));
      $penjualan = Penjualan::find(session('idpenjualan'));
      $setting = Setting::find(1);
      $toko=Branch::where('kode_toko','=',Auth::user()->unit)-> first();
      $no = 0;
     
      $pdf = PDF::loadView('penjualan_detail_member_pabrik.notapdf', compact('musawamah','detail','toko', 'penjualan', 'setting', 'no'));
      $pdf->setPaper(array(0,0,550,440), 'potrait');      
      return $pdf->stream();

      Session::forget('idpenjualan');
   }


   
}

