<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Member;
use App\Musawamah;
use App\MusawamahDetail;
use App\TabelTransaksi;
use PDF;
use Auth;

class MusawamahDetailController extends Controller
{
   public function index()
   {
      return view('musawamah_detail.index'); 
   }

   public function listData()
   {
   
     $musawamah = Musawamah::leftJoin('member', 'member.kode_member', '=', 'musawamah.id_member')
     -> where('musawamah.unit', '=',  Auth::user()->unit)
     -> orderBy('musawamah.id_member', 'desc')->get();
     $no = 0;
     $data = array();
     foreach($musawamah as $list){
       $no ++;
       $row = array();
       $row[] = "<input type='checkbox' name='id[]'' value='".$list->kode_member."'>";
       $row[] = $no;
       $row[] = $list->kode_member;
       $row[] = $list->nama;
       $row[] = $list->os;
       $row[] = $list->angsuran;
       $row[] = '<div class="btn-group">
               <a onclick="addForm()('.$list->kode_member.')" class="btn btn-success"><i class="fa fa-plus-circle"></i></a>
               </div>';
       $data[] = $row;
     }

     $output = array("data" => $data);
     return response()->json($output);
   }

   public function store(Request $request)
   {
    $now = \Carbon\Carbon::now();
    
     $jml = Member::where('kode_member', '=', $request['kode'])->count();
     $musawamah = Musawamah::find($request['kode']);
     if($jml > 0){
      $member = new MusawamahDetail;
      $member->buss_date = $now;
      $member->norek   = $musawamah->no_akad;
      $member->unit = $musawamah->unit;
      $member->id_member =$musawamah->id_member;
      $member->code_kel =$musawamah->code_kel;
      $member->debet =$request['setoran'];
      $member->type ='02';
      $member->kredit ='';
      $member->userid =Auth::user()->id;
      $member->ket ='Setoran Angsuran';
      $member->cao =$musawamah->cao;
      $member->save();

      $jurnal = new TabelTransaksi;
               $jurnal->unit =  Auth::user()->unit; 
               $jurnal->kode_transaksi = $request['kode'];
               $jurnal->kode_rekening = 1120000;
               $jurnal->tanggal_transaksi = $now;
               $jurnal->jenis_transaksi  = 'Jurnal System';
               $jurnal->keterangan_transaksi = 'Setoran angsuran Musawamah ';
               $jurnal->debet = $request['setoran'];
               $jurnal->kredit = 0;
               $jurnal->tanggal_posting = '2019-07-03';
               $jurnal->keterangan_posting = '0';
               $jurnal->id_admin = Auth::user()->id; 
               $jurnal->save();
   
               $jurnal = new TabelTransaksi;
               $jurnal->unit =  Auth::user()->unit; 
               $jurnal->kode_transaksi = $request['kode'];
               $jurnal->kode_rekening = 2853000;
               $jurnal->tanggal_transaksi = '2019-07-03';
               $jurnal->jenis_transaksi  = 'Jurnal System';
               $jurnal->keterangan_transaksi = 'Setoran angsuran Musawamah ';
               $jurnal->debet =0;
               $jurnal->kredit = $request['setoran'];
               $jurnal->tanggal_posting = '2019-07-03';
               $jurnal->keterangan_posting = ' ';
               $jurnal->id_admin = Auth::user()->id; 
               $jurnal->save();

               return view('musawamah_detail.index'); 
     }else{
      echo json_encode(array('msg'=>'error'));
     }
   }

   public function edit($id)
   {
     $member = Member::find($id);
     echo json_encode($member);
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
