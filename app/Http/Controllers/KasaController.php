<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Kasa;
use App\TabelTransaksi;
use PDF;
use Auth;

class KasaController extends Controller
{
   public function index()
   {
      $now = \Carbon\Carbon::now();
      $now=$now->format('Y-m-d');
      //dd($now);
      $pendapatan= TabelTransaksi::groupBy('kode_rekening')
   ->select('kode_rekening', \DB::raw('sum(debet-kredit) as pendapatan'))
   ->where('tanggal_transaksi', '=', $now)
   ->where('kode_rekening', '=', '1120000')
   ->first();
   

      return view('kasa.index',compact('pendapatan')); 
   }

   public function listData()
   {
   
     $kasa = Kasa::orderBy('id_kasa', 'desc')->get();
     $no = 0;
     $data = array();
     foreach($kasa as $list){
       $no ++;
       $row = array();
       $row[] = "<input type='checkbox' name='id[]'' value='".$list->id_kasa."'>";
       $row[] = $no;
       $row[] = $list->tgl;
       $row[] = $list->kode_kasir;
       $row[] = $list->seratus_ribu;
       $row[] = $list->limapuluh_ribu;
       $row[] = $list->duapuluh;
       $row[] = $list->sepuluh;
       $row[] = $list->limaribu;
       $row[] = $list->duaribu;
       $row[] = $list->seribu;
       $row[] = $list->seratus;
       $row[] = $list->lima_puluh;
       $row[] = $list->jumlah;
       $row[] = '<div class="btn-group">
               <a onclick="editForm('.$list->id.')" class="btn btn-primary btn-sm"><i class="fa fa-pencil"></i></a>
               <a onclick="deleteData('.$list->id.')" class="btn btn-danger btn-sm"><i class="fa fa-trash"></i></a></div>';
       $data[] = $row;
     }

     $output = array("data" => $data);
     return response()->json($output);
   }

   public function store(Request $request)
   {
     if($request['id_kasa']==$request['pendapatan']){
      $jml = Kasa::where('id_kasa', '=', $request['id_kasa'])->count();
     if($jml < 1){
      $kasa = new Kasa;
      $kasa->seratus_ribu = $request['seratus_ribu'];
      $kasa->limapuluh_ribu  = $request['limapuluh_ribu'];
      $kasa->duapuluh = $request['duapuluh'];
      $kasa->sepuluh = $request['sepuluh'];
      $kasa->limaribu = $request['limaribu'];
      $kasa->duaribu = $request['duaribu'];
      $kasa->seribu = $request['seribu'];
      $kasa->limaratus = $request['limaratus'];
      $kasa->seratus = $request['seratus'];
      $kasa->lima_puluh = $request['lima_puluh'];
      $kasa->jumlah = $request['jumlah'];
      $kasa->tgl = date('y-m-d');
      $kasa->kode_kasir = Auth::user()->id;
      $kasa->kode_toko = Auth::user()->unit;

      $kasa->save();
      echo json_encode(array('msg'=>'success'));
     }else{
      echo json_encode(array('msg'=>'error'));
     }
   }else{
      echo json_encode(array('msg'=>'tidaksama'));
   }

   }

   public function edit($id)
   {
     $member = Member::find($id);
     echo json_encode($member);
   }

   public function update(Request $request, $id)
   {
      $member = Member::find($id);
      $member->nama = $request['nama'];
      $member->alamat = $request['alamat'];
      $member->telpon = $request['telpon'];
      $member->update();
      echo json_encode(array('msg'=>'success'));
   }

   public function destroy($id)
   {
      $member = Member::find($id);
      $member->delete();
   }

    public function printCard(Request $request)
   {
      $datamember = array();
      foreach($request['id'] as $id){
         $member = Member::find($id);
         $datamember[] = $member;
      }
      
      $pdf = PDF::loadView('member.card', compact('datamember'));
      $pdf->setPaper(array(0, 0, 566.93, 850.39), 'potrait');     
      return $pdf->stream();
   }
}
