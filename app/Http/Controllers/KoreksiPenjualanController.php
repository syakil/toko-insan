<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Penjualan;
use App\PenjualanDetailTemporary;
use App\PenjualanDetail;
use App\Produk;
use App\ProdukDetail;
use App\TabelTransaksi;
use App\KartuStok;
use App\Musawamah;
use App\MusawamahDetail;
use App\GantiPin;
use App\Pabrik;
use App\Member;
use App\Branch;
use DB;
use Auth;
use Redirect;
use App\User;
use App\Setting;

class KoreksiPenjualanController extends Controller{

   public function index(){

      return view('koreksi_penjualan.index');

   }

   public function listData(){

      $users = array();

      $data_users = User::where('unit',Auth::user()->unit)->get();

      foreach($data_users as $list){
            $users[] = $list->id;
      }
      
      $tanggal_sekarang = date('Y-m-d',strtotime("+1 days"));
      $tanggal_kemarin = date('Y-m-d',strtotime("-1 days"));

      $penjualan = Penjualan::leftJoin('member','member.kode_member','penjualan.kode_member')
      ->select('penjualan.*','member.nama')
      ->whereIn('id_user',$users)
      ->where('total_item','>',0)
      ->whereBetween('penjualan.created_at',[$tanggal_kemarin.'%',$tanggal_sekarang.'%'])
      ->get();

      $no = 0;
      $data = array();
      foreach($penjualan as $list){
         $no ++;
         $row = array();
         $row[] = $no;
         $row[] = tanggal_indonesia(substr($list->created_at, 0, 10), false);
         $row[] = $list->id_penjualan;
         $row[] = $list->kode_member;
         $row[] = $list->nama;
         $row[] = $list->total_item;
         $row[] = "Rp. ".format_uang($list->total_harga);
         
         if($list->type_transaksi == 'credit'){
         
            $row[] = "<div class='btn-group'>
               <button onclick='getMember(".$list->id_penjualan.")' class='btn btn-primary'><i class='fa fa-check-circle'></i> Pilih</button>
            </div>";
         
         }else {
         
            $row[] = "<div class='btn-group'>
               <a href='".route('koreksi_penjualan.new_transaksi',$list->id_penjualan)."'class='btn btn-primary'><i class='fa fa-check-circle'></i> Pilih</a>
            </div>";
            
         }
         $data[] = $row;
      }

      $output = array("data" => $data);
      return response()->json($output);

   }

   public function checkPin($id){

      $member = Penjualan::where('id_penjualan',$id)->leftJoin('member','member.kode_member','penjualan.kode_member')
      ->first();
      echo json_encode($member);
   
   }

   public function newSessionInsan($id){
      
      PenjualanDetailTemporary::where('id_penjualan',$id)->delete();
      $penjualan_detail = PenjualanDetail::select(DB::raw('kode_produk,harga_jual,harga_beli,promo,sum(jumlah) as jumlah,diskon'))->where('id_penjualan',$id)->groupBy('kode_produk')->get();
      $member = Penjualan::where('id_penjualan',$id)->first();
   
      foreach ($penjualan_detail as $list) {

         $detail = new PenjualanDetailTemporary;
         $detail->id_penjualan = $id;
         $detail->kode_produk = $list->kode_produk;
         $detail->harga_jual = $list->harga_jual;
         $detail->harga_sebelum_margin = $list->harga_sebelum_margin;
         $detail->harga_beli = $list->harga_beli;
         $detail->promo = $list->promo;
         $detail->jumlah_awal = $list->jumlah;
         $detail->jumlah = $list->jumlah;
         $detail->diskon = $list->diskon;
         $detail->sub_total = $list->jumlah * $list->harga_jual;
         $detail->sub_total_sebelum_margin = $list->jumlah * $list->harga_jual_sebelum_margin;
         $detail->sub_total_beli = $list->jumlah * $list->harga_beli;  
         $detail->save();

      }

      session(['idmember' =>$member->kode_member]);
      session(['idpenjualan' => $id]);

      return Redirect::route('koreksi_penjualan_insan.index');    

   
   }

   function newPin(Request $request){
      
      $id_member = $request->id_member_baru;
      $pin_baru = $request->pin_baru;
      $pin_konf = $request->pin_konf;
      $nik = $request->nik;
      $penjualan = $request->id_penjualan_baru;

      $member = Member::where('kode_member',$id_member)->first();

      $nik_asli = $member->ID_NUMBER;
      
      if ($pin_baru !== $pin_konf) {
    
         return back()->with(['error' => 'Konfirmasi PIN Tidak Sama !']);
         
    
      }else if ($nik_asli != $nik) {
         
         return back()->with(['error' => 'NIK (No. KTP) Salah/Tidak Ditemukan, Hubungi Bagian Administrasi !']);
      
   
      }else {
    
         $member = Member::where('kode_member',$id_member)->first();
         
         $ganti_pin = new GantiPin;
         $ganti_pin->kode_member = $id_member;
         $ganti_pin->nik = $member->ID_NUMBER;
         $ganti_pin->pin_lama = $member->PIN;
         $ganti_pin->pin_baru = $pin_baru;
         $ganti_pin->user = Auth::user()->id;
         $ganti_pin->save();         
         $member->PIN = $pin_baru;
         $member->update();

         $pabrik = Pabrik::where('kode_pabrik',$member->CODE_KEL)->first();

         if ($pabrik){
            
            return redirect()->route('koreksi_penjualan.newSessionPabrik', ['id' => $penjualan]);

         }else{

            return redirect()->route('koreksi_penjualan.newSessionInsan', ['id' => $penjualan]);

         }

      }
  
   }

   public function newSessionCredit(Request $request){
      
      // dd($request);
      $id_member = $request->id_member;
      $pin = $request->pin;
      $penjualan = $request->id_penjualan;

      $check = Member::where('kode_member',$id_member)->where('PIN',$pin)->first();
      
      if (!$check) {
         
         return back()->with(['error' => 'PIN Salah !']);
      
      }else {

         $penjualan = Penjualan::where('id_penjualan',$penjualan)->first();      
         $data_member = Member::where('kode_member',$penjualan->kode_member)->first();
         $pabrik = Pabrik::where('kode_pabrik',$data_member->CODE_KEL)->first();

         if ($pabrik){
            
            return redirect()->route('koreksi_penjualan.newSessionPabrik', ['id' => $penjualan]);

         }else{

            return redirect()->route('koreksi_penjualan.newSessionInsan', ['id' => $penjualan]);

         }
      }

   }

   public function newSessionCash($id){

      PenjualanDetailTemporary::where('id_penjualan',$id)->delete();
      $penjualan_detail = PenjualanDetail::select(DB::raw('kode_produk,harga_jual,harga_beli,promo,sum(jumlah) as jumlah,diskon'))->where('id_penjualan',$id)->groupBy('kode_produk')->get();
      $member = Penjualan::where('id_penjualan',$id)->first();
   
      foreach ($penjualan_detail as $list) {

         $detail = new PenjualanDetailTemporary;
         $detail->id_penjualan = $id;
         $detail->kode_produk = $list->kode_produk;
         $detail->harga_jual = $list->harga_jual;
         $detail->harga_sebelum_margin = $list->harga_sebelum_margin;
         $detail->harga_beli = $list->harga_beli;
         $detail->promo = $list->promo;
         $detail->jumlah_awal = $list->jumlah;
         $detail->jumlah = $list->jumlah;
         $detail->diskon = $list->diskon;
         $detail->sub_total = $list->jumlah * $list->harga_jual;
         $detail->sub_total_beli = $list->jumlah * $list->harga_beli;  
         $detail->sub_total_sebelum_margin = $list->jumlah * $list->harga_jual_sebelum_margin;
         $detail->save();

      }

      session(['idmember' =>$member->kode_member]);
      session(['idpenjualan' => $id]);

      return Redirect::route('koreksi_penjualan_cash.index');    
   
   }
   
   public function newSessionPabrik($id){
      
      PenjualanDetailTemporary::where('id_penjualan',$id)->delete();
      $penjualan_detail = PenjualanDetail::select(DB::raw('kode_produk,harga_jual,harga_beli,promo,sum(jumlah) as jumlah,diskon'))->where('id_penjualan',$id)->groupBy('kode_produk')->get();
      $member = Penjualan::where('id_penjualan',$id)->first();
   
      foreach ($penjualan_detail as $list) {

         $detail = new PenjualanDetailTemporary;
         $detail->id_penjualan = $id;
         $detail->kode_produk = $list->kode_produk;
         $detail->harga_sebelum_margin = $list->harga_sebelum_margin;
         $detail->harga_jual = $list->harga_jual;
         $detail->harga_beli = $list->harga_beli;
         $detail->promo = $list->promo;
         $detail->jumlah_awal = $list->jumlah;
         $detail->jumlah = $list->jumlah;
         $detail->diskon = $list->diskon;
         $detail->sub_total = $list->jumlah * $list->harga_jual;
         $detail->sub_total_sebelum_margin = $list->jumlah * $list->harga_jual_sebelum_margin;
         $detail->sub_total_beli = $list->jumlah * $list->harga_beli;  
         $detail->save();

      }

      session(['idmember' =>$member->kode_member]);
      session(['idpenjualan' => $id]);

      return Redirect::route('koreksi_penjualan_pabrik.index');    
   
   }

   public function new_transaksi($id){
      
      $penjualan = Penjualan::where('id_penjualan',$id)->first();
      
      if ($penjualan->type_transaksi == 'credit') {
         
         $data_member = Member::where('kode_member',$penjualan->kode_member)->first();
         $pabrik = Pabrik::where('kode_pabrik',$data_member->CODE_KEL)->first();

         if ($pabrik){
            
            return redirect()->route('koreksi_penjualan.newSessionCredit', ['id' => $id]);

         }else{

            return redirect()->route('koreksi_penjualan.newSessionInsan', ['id' => $id]);

         }


      }else {
         
         return redirect()->route('koreksi_penjualan.newSessionCash', ['id' => $id]);

      }

   }
    
   public function batal($id){

      $detail = PenjualanDetailTemporary::where('id_penjualan',$id);
      $detail->delete();

      return redirect()->route('koreksi_penjualan.index');

   }
   
}
