<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Member;
use App\Musawamah;
use App\Branch;
use App\ListToko;
use App\TabelTransaksi;
use PDF;
use Auth;
use App\Setting;

class MusawamahDetailController extends Controller
{
   public function index()
   {
      return view('musawamah_detail.index'); 
   }

   public function listData()
   {
   
     $musawamah = Musawamah::leftJoin('member', 'member.kode_member', '=', 'musawamah.id_member')
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
    //$now = \Carbon\Carbon::now();
    
$param_tgl = \App\ParamTgl::where('nama_param_tgl','tanggal_transaksi')->where('unit',Auth::user()->id)->first();
      $now = $param_tgl->param_tgl;

     $jml = Member::where('kode_member', '=', $request['kode'])->count();
     $musawamah = Musawamah::find($request['kode']);

     $unit_member=$musawamah->unit;

     
      $branch_coa_aktiva_member = Branch::find($unit_member);
      $coa_aktiva_member=$branch_coa_aktiva_member->aktiva;

      $branch_coa_aktiva_user = Branch::find(Auth::user()->unit);
      $coa_aktiva_user=$branch_coa_aktiva_user->aktiva;
    
     if($jml > 0){
      $member = new ListToko;
      $member->buss_date = $now;
      $member->norek   = $musawamah->id_member;
      $member->unit = $musawamah->unit;
      $member->id_member =$musawamah->id_member;
      $member->code_kel =$musawamah->code_kel;
      $member->DEBIT =0;
      $member->type ='02';
      $member->KREDIT =$request['setoran'];
      $member->userid =Auth::user()->id;
      $member->ket ='Setoran Angsuran';
      $member->cao =$musawamah->cao;
      $member->save();


      if ($unit_member == Auth::user()->unit)
      {
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
               $jurnal->kode_rekening = 2891000;
               $jurnal->tanggal_transaksi = $now;
               $jurnal->jenis_transaksi  = 'Jurnal System';
               $jurnal->keterangan_transaksi = 'Setoran angsuran Musawamah ';
               $jurnal->debet =0;
               $jurnal->kredit = $request['setoran'];
               $jurnal->tanggal_posting = '2019-07-03';
               $jurnal->keterangan_posting = ' ';
               $jurnal->id_admin = Auth::user()->id; 
               $jurnal->save();

      }else{
                  $jurnal = new TabelTransaksi;
         $jurnal->unit =  Auth::user()->unit; 
         $jurnal->kode_transaksi = $request['kode'];
         $jurnal->kode_rekening = 1120000;
         $jurnal->tanggal_transaksi = $now;
         $jurnal->jenis_transaksi  = 'Jurnal System';
         $jurnal->keterangan_transaksi = 'Setoran angsuran Musawamah ';
         $jurnal->debet = $request['setoran'];
         $jurnal->kredit = 0;
         $jurnal->tanggal_posting = '';
         $jurnal->keterangan_posting = '0';
         $jurnal->id_admin = Auth::user()->id; 
         $jurnal->save();

         $jurnal = new TabelTransaksi;
         $jurnal->unit =  Auth::user()->unit; 
         $jurnal->kode_transaksi = $request['kode'];
         $jurnal->kode_rekening = 2853000;
         $jurnal->tanggal_transaksi = $now;
         $jurnal->jenis_transaksi  = 'Jurnal System';
         $jurnal->keterangan_transaksi = 'Setoran angsuran Musawamah ';
         $jurnal->debet =0;
         $jurnal->kredit = $request['setoran'];
         $jurnal->tanggal_posting = '';
         $jurnal->keterangan_posting = ' ';
         $jurnal->id_admin = Auth::user()->id; 
         $jurnal->save();

         $jurnal = new TabelTransaksi;
         $jurnal->unit =  Auth::user()->unit; 
         $jurnal->kode_transaksi = $request['kode'];
         $jurnal->kode_rekening = 2853000;
         $jurnal->tanggal_transaksi = $now;
         $jurnal->jenis_transaksi  = 'Jurnal System';
         $jurnal->keterangan_transaksi = 'Setoran angsuran Musawamah ';
         $jurnal->debet =$request['setoran'];
         $jurnal->kredit = 0;
         $jurnal->tanggal_posting = '';
         $jurnal->keterangan_posting = ' ';
         $jurnal->id_admin = Auth::user()->id; 
         $jurnal->save();

         $jurnal = new TabelTransaksi;
         $jurnal->unit =  Auth::user()->unit; 
         $jurnal->kode_transaksi = $request['kode'];
         $jurnal->kode_rekening = 2500000;
         $jurnal->tanggal_transaksi = $now;
         $jurnal->jenis_transaksi  = 'Jurnal System';
         $jurnal->keterangan_transaksi = 'Setoran angsuran Musawamah ';
         $jurnal->debet =0;
         $jurnal->kredit = $request['setoran'];
         $jurnal->tanggal_posting = '';
         $jurnal->keterangan_posting = ' ';
         $jurnal->id_admin = Auth::user()->id; 
         $jurnal->save();

         $jurnal = new TabelTransaksi;
         $jurnal->unit =  $unit_member; 
         $jurnal->kode_transaksi = $request['kode'];
         $jurnal->kode_rekening = 2500000;
         $jurnal->tanggal_transaksi = $now;
         $jurnal->jenis_transaksi  = 'Jurnal System';
         $jurnal->keterangan_transaksi = 'Setoran angsuran Musawamah ';
         $jurnal->debet =$request['setoran'];
         $jurnal->kredit = 0;
         $jurnal->tanggal_posting = '';
         $jurnal->keterangan_posting = ' ';
         $jurnal->id_admin = Auth::user()->id; 
         $jurnal->save();

         $jurnal = new TabelTransaksi;
         $jurnal->unit =  $unit_member; 
         $jurnal->kode_transaksi = $request['kode'];
         $jurnal->kode_rekening = 2891000;
         $jurnal->tanggal_transaksi = $now;
         $jurnal->jenis_transaksi  = 'Jurnal System';
         $jurnal->keterangan_transaksi = 'Setoran angsuran Musawamah ';
         $jurnal->debet =0;
         $jurnal->kredit = $request['setoran'];
         $jurnal->tanggal_posting = '';
         $jurnal->keterangan_posting = ' ';
         $jurnal->id_admin = Auth::user()->id; 
         $jurnal->save();
         
 $jurnal = new TabelTransaksi;
            $jurnal->unit = '1010'; 
            $jurnal->kode_transaksi = $request['kode'];
            $jurnal->kode_rekening = $coa_aktiva_user;
            $jurnal->tanggal_transaksi = $now;
            $jurnal->jenis_transaksi  = 'Jurnal System';
            $jurnal->keterangan_transaksi = 'Setoran angsuran Musawamah';
            $jurnal->debet = $request['setoran'];
            $jurnal->kredit = 0;
            $jurnal->tanggal_posting = ' ';
            $jurnal->keterangan_posting = '0';
            $jurnal->id_admin = Auth::user()->id; 
            $jurnal->save();

            $jurnal = new TabelTransaksi;
            $jurnal->unit =  '1010'; 
            $jurnal->kode_transaksi = $request['kode'];
            $jurnal->kode_rekening = $coa_aktiva_member;
            $jurnal->tanggal_transaksi = $now;
            $jurnal->jenis_transaksi  = 'Jurnal System';
            $jurnal->keterangan_transaksi = 'Setoran angsuran Musawamah';
            $jurnal->debet =0;
            $jurnal->kredit = $request['setoran'];
            $jurnal->tanggal_posting = ' ';
            $jurnal->keterangan_posting = '0';
            $jurnal->id_admin = Auth::user()->id; 
            $jurnal->save();

      }
    $setting=Setting::find(1);
            $no = 0;
            $bayar = $request['setoran'];
            $os = $musawamah->os;
            $sisa = $os - $bayar;
        
            $pdf = PDF::loadView('musawamah_detail.printpembayaran', compact('bayar','sisa','os','musawamah','no','setting'));
            $pdf->setPaper(array(0,0,700,600), 'potrait');      
      
            return $pdf->stream();
               return view('musawamah_detail.index'); 
     }else{
      echo json_encode(array('msg'=>'error'));
     }
   }

  
}
