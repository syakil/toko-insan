<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Redirect;
use App\Penjualan;
use App\Produk;
use DB;
use Auth;
use App\Branch;
use App\Member;
use App\PenjualanDetail;

class PenjualanController extends Controller{
  public function index() {
    
    return view('penjualan/index');

  }

  public function listData($awal,$akhir){

    $branch = Branch::where('kode_gudang',Auth::user()->unit)->get();
    $unit = array();

    foreach($branch as $list){
        $unit[] = $list->kode_toko;
    }

    $user = DB::table('users')->whereIn('unit',$unit)->where('level',2)->get();

    $id_user = array();

    foreach ($user as $list ) {
        $id_user [] = $list->id; 
    }


    $penjualan_data = DB::table('penjualan')->whereIn('id_user',$id_user)->get();

    $id_penjualan = array();

    foreach($penjualan_data as $list){
      $id_penjualan[] = $list->id_penjualan;
    }
    
    if ($awal === $akhir) {  
  
      $penjualan = Penjualan::leftJoin('users', 'users.id', '=', 'penjualan.id_user')
      ->leftJoin('member','member.kode_member','=','penjualan.kode_member')
      ->select('users.*', 'penjualan.*', 'penjualan.created_at as tanggal','member.nama as nama_member','member.CODE_KEL')
      ->where('penjualan.created_at','LIKE', $awal.'%')
      ->whereIn('id_penjualan',$id_penjualan)
      ->orderBy('penjualan.id_penjualan', 'desc')
      ->get();
    
    }else {
      
      $penjualan = Penjualan::leftJoin('users', 'users.id', '=', 'penjualan.id_user')
      ->leftJoin('member','member.kode_member','=','penjualan.kode_member')
      ->select('users.*', 'penjualan.*', 'penjualan.created_at as tanggal','member.nama as nama_member','member.CODE_KEL')
      ->whereBetween('penjualan.created_at',[$awal.'%',$akhir.'%'])
      ->whereIn('id_penjualan',$id_penjualan)
      ->orderBy('penjualan.id_penjualan', 'desc')
      ->get();
      
    }

    $penjualan = $penjualan->unique('id_penjualan');

    $no = 0;
    $data = array();

    foreach($penjualan as $list){

      $no ++;
      $row = array();
      $row[] = $list->id_penjualan;
      $row[] = $list->unit;
      $row[] = tanggal_indonesia(substr($list->tanggal, 0, 10), false);
      $row[] = $list->CODE_KEL;
      $row[] = $list->kode_member;
      $row[] = $list->nama_member;
      $row[] = $list->total_item;
      $row[] = $list->total_harga;
      $row[] = $list->type_transaksi;
      $row[] = '<div class="btn-group">
              <a onclick="deleteData('.$list->id_penjualan.')" class="btn btn-danger btn-sm"><i class="fa fa-trash"></i></a>
              </div>';
      $data[] = $row;

    }

    $output = array("data" => $data);
    return response()->json($output);

  }

  public function show($id){
   
    $detail = PenjualanDetail::leftJoin('produk', 'produk.kode_produk', '=', 'penjualan_detail.kode_produk')
      ->where('id_penjualan', '=', $id)
      ->get();
     
    $no = 0;
    $data = array();
     
    foreach($detail as $list){
      $no ++;
      $row = array();
      $row[] = $list->kode_produk;
      $row[] = $list->nama_produk;
      $row[] = "Rp. ".format_uang($list->harga_jual);
      $row[] = $list->jumlah;
      $row[] = "Rp. ".format_uang($list->sub_total);
      $data[] = $row;
    
    }
    
    $output = array("data" => $data);
    return response()->json($output);

  }
   
   public function destroy($id)
   {
      $penjualan = Penjualan::find($id);
      $penjualan->delete();
      
      if ($penjualan->id_member > '0') {
        $musawamah = MusawamahDetail::where('kode_transaksi',$id)->delete();
      }  
      
      $detail = PenjualanDetail::where('id_penjualan', '=', $id)->get();
      foreach($detail as $data){
        $produk = Produk::where('kode_produk', '=', $data->kode_produk)->first();
        $produk->stok += $data->jumlah;
        $produk->update();
        $data->delete();
      }

      $jurnal = TabelTransaksi::where('kode_transaksi',$id)->delete();

   }

   public function detail(){

      return view('penjualan/detail');

   }


  public function listDetail($awal,$akhir){

    $branch = Branch::where('kode_gudang',Auth::user()->unit)->get();
    $unit = array();

    foreach($branch as $list){
      $unit[] = $list->kode_toko;
    }

    $user = DB::table('users')->whereIn('unit',$unit)->where('level',2)->get();

    $id_user = array();

    foreach ($user as $list ) {
      $id_user [] = $list->id; 
    }


    $penjualan_data = DB::table('penjualan')->whereIn('id_user',$id_user)->get();

    $id_penjualan = array();

    foreach($penjualan_data as $list){
      $id_penjualan [] = $list->id_penjualan;
    }

    if ($awal == $akhir) {

    
      $penjualan = DB::table('penjualan_detail')->select(DB::raw('SUBSTR(penjualan.created_at,1,10) AS tanggal,        penjualan_detail.harga_sebelum_margin,penjualan_detail.id_penjualan,users.unit,penjualan.kode_member as member,member.nama,penjualan_detail.kode_produk,produk.nama_produk , penjualan_detail.harga_jual,penjualan_detail.harga_beli,penjualan_detail.sub_total_beli,sub_total_sebelum_margin,penjualan_detail.jumlah,sub_total,penjualan_detail.id_penjualan_detail as detail
      '))
      ->leftJoin('penjualan','penjualan_detail.id_penjualan','penjualan.id_penjualan')
      ->leftJoin('member','penjualan.kode_member','member.kode_member')
      ->leftJoin('produk','penjualan_detail.kode_produk','produk.kode_produk')
      ->leftJoin('users','penjualan.id_user','users.id')
      ->where('penjualan_detail.created_at','LIKE',$awal."%")
      ->whereIn('penjualan_detail.id_penjualan',$id_penjualan)
      ->where('produk.unit',auth::user()->unit)
      ->orderBy('penjualan.id_penjualan', 'desc')
      ->get();


    }else {
      
      $penjualan = DB::table('penjualan_detail')->select(DB::raw('SUBSTR(penjualan.created_at,1,10) AS tanggal,
        penjualan_detail.id_penjualan,
        users.unit,
        penjualan.kode_member as member,
        member.nama,
        penjualan_detail.kode_produk,
        produk.nama_produk ,
        penjualan_detail.harga_jual,
        penjualan_detail.promo,
        penjualan_detail.harga_sebelum_margin,
        penjualan_detail.jumlah,
        penjualan_detail.harga_beli,
        sub_total,sub_total_beli,sub_total_sebelum_margin,
        penjualan_detail.id_penjualan_detail as detail
      '))

      ->leftJoin('penjualan','penjualan_detail.id_penjualan','penjualan.id_penjualan')
      ->leftJoin('member','penjualan.kode_member','member.kode_member')
      ->leftJoin('produk','penjualan_detail.kode_produk','produk.kode_produk')
      ->leftJoin('users','penjualan.id_user','users.id')
      ->whereBetween('penjualan_detail.created_at',[$awal."%",$akhir."%"])
      ->where('produk.unit',auth::user()->unit)
      ->whereIn('penjualan_detail.id_penjualan',$id_penjualan)
      ->orderBy('penjualan.id_penjualan', 'desc')
      ->get();
      
    }

    $penjualan = $penjualan->unique('detail');

    $no = 0;
    $data = array();

    foreach($penjualan as $list){

      if($list->member == 0 || $list->member == null){
                
        $nama_member = '';
      
      }else{                
      
        $nama_member = $list->nama;
      
      }

      $no ++;
      $row = array();
      $row[] = $list->tanggal;
      $row[] = $list->unit;
      $row[] = $list->id_penjualan;
      $row[] = $list->member;
      $row[] = $nama_member;
      $row[] = $list->kode_produk;
      $row[] = $list->nama_produk;
      $row[] = $list->harga_jual;
      $row[] = $list->harga_sebelum_margin;
      $row[] = $list->harga_beli;
      $row[] = $list->jumlah;
      $row[] = $list->sub_total;
      $row[] = $list->sub_total_sebelum_margin;
      $row[] = $list->sub_total_beli;
      $data[] = $row;

    }
     
    $output = array("data" => $data);
    return response()->json($output);
 
  }
   
}
  


