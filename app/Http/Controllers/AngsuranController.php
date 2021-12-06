<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
use App\Setting;
use PDF;
use Ramsey\Uuid\Uuid;
use Auth;
use App\TabelTransaksi;
use App\ListToko;
use App\TunggakanToko;
use App\Musawamah;
use App\Member;



class AngsuranController extends Controller
{
    public function index(){
        $member = DB::table('member')->leftjoin('musawamah','musawamah.id_member','=','member.kode_member')->where('os','>',0)->get();
        $kelompok = DB::table('kelompok_toko')->get();
        $titipan = 0;
        return view('angsuran/index',compact('member','kelompok','titipan'));

    }


    public function getMember($id){

        $musawamah = DB::table('musawamah')->where('musawamah.id_member',$id)->first();

        
        $sum_titipan = DB::table('list_toko')->select(DB::raw(
            'SUM(KREDIT - DEBIT) AS titipan'
        ))
        ->where('id_member',$id)
        ->first();

        $data_member = new \stdClass();
        $data_member->angsuran = $musawamah->angsuran;
        $data_member->titipan = $sum_titipan->titipan;
        $data_member->os = $musawamah->os;
        
        echo json_encode($data_member);

    }


    public function listTransaksi($id){

        $member = $id;

        $detail_transaksi = DB::table('list_toko')->select(DB::raw(
            'BUSS_DATE,KET,DEBIT,KREDIT'
        ))
        ->where('id_member',$member)
        ->get();

        $saldo = 0;

        // dd($detail_transaksi);

        $no = 0;
        $data = array();
        foreach ($detail_transaksi as $list) {
        
        if($list->DEBIT == 0 || $list->DEBIT == null){
            $saldo += $list->KREDIT;
        }else{
            $saldo -= $list->DEBIT;
        }

        $no++;
        $row = array();
            $row[] = $no;
            $row[] = tanggal_indonesia($list->BUSS_DATE);
            $row[] = $list->KET;
            $row[] = number_format($list->DEBIT);
            $row[] = number_format($list->KREDIT);
            $row[] = number_format($saldo);
            $data[] = $row;
        }
        

        $output = array("data" => $data);
        return response()->json($output);

    }

    public function listTransaksiKelompok($id){

        $kelompok = $id;


        $musawamah = DB::table('musawamah')->where('musawamah.code_kel',$kelompok)->leftJoin('kelompok_toko','kelompok_toko.code_kel','musawamah.code_kel')->get();
        // dd($detail_transaksi);

        $no = 0;
        $data = array();
        foreach ($musawamah as $list) {
        $no++;
        $row = array();
            $row [] = '<input type="checkbox" name="id_member[]" id="id_member" class="id_member_check" onclick="check()" value="'.$list->id_member.'">';
            $row[] = $list->id_member;
            $row[] = $list->Cust_Short_name;
            $row[] = $list->unit;
            $row[] = $list->nama_kel;
            $row[] = number_format($list->os);
            $row[] = number_format($list->saldo_margin);
            $row[] = '<input type="text" class="jumlah" name="id_'.$list->id_member.'">';
            $row[] = number_format($list->angsuran);
            $data[] = $row;
        }
        

        $output = array("data" => $data);
        return response()->json($output);

    }


    public function addTransaksi(Request $request){

        try{
        
            DB::beginTransaction();
            
            $kode=Uuid::uuid4()->getHex();
            $kode_t=substr($kode,25);
            $unit=Auth::user()->unit;
            $kode_t="BU/-".$unit.$kode_t;

            
            $id = $request['id'];
            $jenis_transaksi = $request['keterangan_transaksi'];

            $param_tgl = \App\ParamTgl::where('nama_param_tgl','tanggal_transaksi')->where('unit',Auth::user()->id)->first();
            $tanggal = $param_tgl->param_tgl;
            $musawamah = DB::table('musawamah')->where('id_member',$id)->first();
            $nama = $musawamah->Cust_Short_name;
            $ijaroh = $musawamah->ijaroh;
            $saldo_margin = $musawamah->saldo_margin;
            $unit_member = $musawamah->unit;

            $setoran = $request['nominal'];
            $angsuran = $musawamah->angsuran;
            $sisa = $setoran - $angsuran;
            
            $sum_titipan = DB::table('list_toko')->select(DB::raw(
                'SUM(KREDIT - DEBIT) AS titipan'
                ))
                ->where('id_member',$id)
                ->first();

            $titipan = $sum_titipan->titipan;
            $sisa_titipan = $titipan-$angsuran;
            
            $os = $musawamah->os;
            $sisa_os = $setoran - $os;


            $kode_kelompok = $musawamah->code_kel;
            $cao = $musawamah->cao;

            
            switch ($jenis_transaksi) {
                case 'titipan':

                if($titipan < $angsuran){
                    
                    $data = array(
                            "alert" => "Titipan Kurang dari Angsuran (Angsuran : ".number_format($angsuran)."|Titipan : ".number_format($titipan)." )",
                            );
                    return response()->json($data);
                
                    }

                    break;
                
                case 'pelunasan':

                    if($titipan < $os){
                    
                        $data = array(
                            "alert" => "Titipan Kurang dari Outstanding (Os : ".number_format($os)."|Titipan : ".number_format($titipan)." )",
                                );
                        return response()->json($data);
                    
                    }
    
                    
                    break;
                
                default:
                    break;
            }
            
            switch ($jenis_transaksi) {
                
                case 'titipan':
                    
                    $jurnal = new TabelTransaksi;
                    $jurnal->unit =  $unit_member; 
                    $jurnal->kode_transaksi = $kode_t;
                    $jurnal->kode_rekening = 2891000;
                    $jurnal->tanggal_transaksi  = $tanggal;
                    $jurnal->jenis_transaksi  = 'Jurnal System';
                    $jurnal->keterangan_transaksi = 'Setoran Angsuran Dari Titipan' . ' ' . $id . ' an/ ' . $nama;
                    $jurnal->debet = $angsuran;
                    $jurnal->kredit = 0;
                    $jurnal->tanggal_posting = '';
                    $jurnal->keterangan_posting = '0';
                    $jurnal->id_admin = Auth::user()->id; 
                    $jurnal->save();
                    
                    $jurnal = new TabelTransaksi;
                    $jurnal->unit =  $unit_member; 
                    $jurnal->kode_transaksi = $kode_t;
                    $jurnal->kode_rekening = 1412000;
                    $jurnal->tanggal_transaksi  = $tanggal;
                    $jurnal->jenis_transaksi  = 'Jurnal System';
                    $jurnal->keterangan_transaksi = 'Setoran Angsuran Dari Titipan' . ' ' . $id . ' an/ ' . $nama;
                    $jurnal->debet =0;
                    $jurnal->kredit = $angsuran;
                    $jurnal->tanggal_posting = '';
                    $jurnal->keterangan_posting = '0';
                    $jurnal->id_admin = Auth::user()->id; 
                    $jurnal->save();
                    
                    
                    $jurnal = new TabelTransaksi;
                    $jurnal->unit =  $unit_member; 
                    $jurnal->kode_transaksi = $kode_t;
                    $jurnal->kode_rekening = 1422000;
                    $jurnal->tanggal_transaksi  = $tanggal;
                    $jurnal->jenis_transaksi  = 'Jurnal System';
                    $jurnal->keterangan_transaksi = 'Setoran Angsuran Dari Titipan' . ' ' . $id . ' an/ ' . $nama;
                    $jurnal->debet = $ijaroh;
                    $jurnal->kredit =0;
                    $jurnal->tanggal_posting = '';
                    $jurnal->keterangan_posting = '0';
                    $jurnal->id_admin = Auth::user()->id; 
                    $jurnal->save();
                    

                    $jurnal = new TabelTransaksi;
                    $jurnal->unit =  $unit_member; 
                    $jurnal->kode_transaksi = $kode_t;
                    $jurnal->kode_rekening = 41001;
                    $jurnal->tanggal_transaksi  = $tanggal;
                    $jurnal->jenis_transaksi  = 'Jurnal System';
                    $jurnal->keterangan_transaksi = 'Setoran Angsuran Dari Titipan' . ' ' . $id . ' an/ ' . $nama;
                    $jurnal->debet =0;
                    $jurnal->kredit = $ijaroh;
                    $jurnal->tanggal_posting = '';
                    $jurnal->keterangan_posting = '0';
                    $jurnal->id_admin = Auth::user()->id; 
                    $jurnal->save();

                    $member = new ListToko;
                    $member->buss_date = $tanggal;
                    $member->norek   = $id;
                    $member->unit = $unit_member;
                    $member->id_member =$id;
                    $member->code_kel =$kode_kelompok;
                    $member->DEBIT = $angsuran;
                    $member->type ='02';
                    $member->KREDIT =0;
                    $member->userid =Auth::user()->id;
                    $member->ket ='Setoran Angsuran Dari Titipan';
                    $member->kode_transaksi = $kode_t;
                    $member->tgl_input = $tanggal;
                    $member->cao =$cao;
                    $member->save();

                    $musawamah = Musawamah::where('id_member',$id)->first();
                    
                    // mengurangi tunggakan       
                    if($musawamah->bulat > 0 ){  
                    
                        $tunggakan = new TunggakanToko;
                        $tunggakan->tgl_tunggak = $tanggal;
                        $tunggakan->NOREK = $id;
                        $tunggakan->unit = $unit_member;
                        $tunggakan->CIF = $id;
                        $tunggakan->CODE_KEL = $kode_kelompok;
                        $tunggakan->DEBIT = $angsuran;
                        $tunggakan->type = "01";
                        $tunggakan->KREDIT = 0;
                        $tunggakan->USERID = $unit;
                        $tunggakan->KET = 'Setoran Angsuran Dari Titipan' . ' ' . $id . ' an/ ' . $nama;
                        $tunggakan->cao = $cao;
                        $tunggakan->blok = 1;
                        $tunggakan->save();

                        
                        if ($os > $angsuran) {
                            
                            // os dikurangin sesuai angsuran
                            $musawamah = Musawamah::where('id_member',$id)->first();
                            $musawamah->os -= $angsuran;
                            $musawamah->saldo_margin -= $ijaroh;
                            $musawamah->update();

                            if ($musawamah->bulat > $musawamah->angsuran) {
                            
                                $musawamah->bulat -= $musawamah->angsuran;
                                $musawamah->update();
                            
                            }else {
                            
                                $musawamah->bulat =0;
                                $musawamah->update();
                            
                            }

                        }else {

                            
                            $musawamah->os = 0;
                            $musawamah->saldo_margin = 0;
                            $musawamah->bulat = 0;
                            $musawamah->ijaroh = 0;
                            $musawamah->update();   

                        }
                    
                    }else {
                        
                        
                        $musawamah = Musawamah::where('id_member',$id)->first();
                        $musawamah->os -= $angsuran;
                        $musawamah->saldo_margin -= $ijaroh;
                        $musawamah->update();
                        
                    }
                    


                    break;

                    
                // storan angsuran ketitipan
                case 'kurang':                
                                        
                    $jurnal = new TabelTransaksi;
                    $jurnal->unit =  $unit; 
                    $jurnal->kode_transaksi = $kode_t;
                    $jurnal->kode_rekening = 1120000;
                    $jurnal->tanggal_transaksi  = $tanggal;
                    $jurnal->jenis_transaksi  = 'Jurnal System';
                    $jurnal->keterangan_transaksi = 'Setoran Ke Titipan' . ' ' . $id . ' an/ ' . $nama;
                    $jurnal->debet = $setoran;
                    $jurnal->kredit = 0;
                    $jurnal->tanggal_posting = '';
                    $jurnal->keterangan_posting = '0';
                    $jurnal->id_admin = Auth::user()->id; 
                    $jurnal->save();
                    
                    $jurnal = new TabelTransaksi;
                    $jurnal->unit =  $unit; 
                    $jurnal->kode_transaksi = $kode_t;
                    $jurnal->kode_rekening = 2891000;
                    $jurnal->tanggal_transaksi  = $tanggal;
                    $jurnal->jenis_transaksi  = 'Jurnal System';
                    $jurnal->keterangan_transaksi = 'Setoran Ke Titipan' . ' ' . $id . ' an/ ' . $nama;
                    $jurnal->debet =0;
                    $jurnal->kredit = $setoran;
                    $jurnal->tanggal_posting = '';
                    $jurnal->keterangan_posting = '0';
                    $jurnal->id_admin = Auth::user()->id; 
                    $jurnal->save();

                    $member = new ListToko;
                    $member->buss_date = $tanggal;
                    $member->norek   = $id;
                    $member->unit = $unit_member;
                    $member->id_member =$id;
                    $member->code_kel =$kode_kelompok;
                    $member->DEBIT =0;
                    $member->type ='02';
                    $member->KREDIT = $setoran;
                    $member->userid =Auth::user()->id;
                    $member->ket ='Setoran Ke Titipan';
                    $member->kode_transaksi = $kode_t;
                    $member->tgl_input = $tanggal;
                    $member->cao =$cao;
                    $member->save();
                    
                    break;
                
                default:
            
                        $jurnal = new TabelTransaksi;
                        $jurnal->unit =  $unit_member; 
                        $jurnal->kode_transaksi = $kode_t;
                        $jurnal->kode_rekening = 2891000;
                        $jurnal->tanggal_transaksi  = $tanggal;
                        $jurnal->jenis_transaksi  = 'Jurnal System';
                        $jurnal->keterangan_transaksi = 'Pelunasan' . ' ' . $id . ' an/ ' . $nama;
                        $jurnal->debet = $os;
                        $jurnal->kredit = 0;
                        $jurnal->tanggal_posting = '';
                        $jurnal->keterangan_posting = '0';
                        $jurnal->id_admin = Auth::user()->id; 
                        $jurnal->save();
                        
                        $jurnal = new TabelTransaksi;
                        $jurnal->unit =  $unit_member; 
                        $jurnal->kode_transaksi = $kode_t;
                        $jurnal->kode_rekening = 1412000;
                        $jurnal->tanggal_transaksi  = $tanggal;
                        $jurnal->jenis_transaksi  = 'Jurnal System';
                        $jurnal->keterangan_transaksi = 'Pelunasan' . ' ' . $id . ' an/ ' . $nama;
                        $jurnal->debet =0;
                        $jurnal->kredit = $os;
                        $jurnal->tanggal_posting = '';
                        $jurnal->keterangan_posting = '0';
                        $jurnal->id_admin = Auth::user()->id; 
                        $jurnal->save();

                        $jurnal = new TabelTransaksi;
                        $jurnal->unit =  $unit_member; 
                        $jurnal->kode_transaksi = $kode_t;
                        $jurnal->kode_rekening = 1422000;
                        $jurnal->tanggal_transaksi  = $tanggal;
                        $jurnal->jenis_transaksi  = 'Jurnal System';
                        $jurnal->keterangan_transaksi = 'Pelunasan' . ' ' . $id . ' an/ ' . $nama;
                        $jurnal->debet = $saldo_margin;
                        $jurnal->kredit =0;
                        $jurnal->tanggal_posting = '';
                        $jurnal->keterangan_posting = '0';
                        $jurnal->id_admin = Auth::user()->id; 
                        $jurnal->save();
                        
                        $jurnal = new TabelTransaksi;
                        $jurnal->unit =  $unit_member; 
                        $jurnal->kode_transaksi = $kode_t;
                        $jurnal->kode_rekening = 41001;
                        $jurnal->tanggal_transaksi  = $tanggal;
                        $jurnal->jenis_transaksi  = 'Jurnal System';
                        $jurnal->keterangan_transaksi = 'Pelunasan' . ' ' . $id . ' an/ ' . $nama;
                        $jurnal->debet =0;
                        $jurnal->kredit = $saldo_margin;
                        $jurnal->tanggal_posting = '';
                        $jurnal->keterangan_posting = '0';
                        $jurnal->id_admin = Auth::user()->id; 
                        $jurnal->save();
        
                        $member = new ListToko;
                        $member->buss_date = $tanggal;
                        $member->norek   = $id;
                        $member->unit = $unit_member;
                        $member->id_member =$id;
                        $member->code_kel = $kode_kelompok;
                        $member->DEBIT = $os;
                        $member->type ='02';
                        $member->KREDIT =0;
                        $member->userid =Auth::user()->id;
                        $member->ket ='Pelunasan';
                        $member->kode_transaksi = $kode_t;
                        $member->tgl_input = $tanggal;
                        $member->cao =$cao;
                        $member->save();
        
                        $musawamah = Musawamah::where('id_member',$id)->first();
                        
                        if($musawamah->bulat > 0 ){
                        
                            $tunggakan = new TunggakanToko;
                            $tunggakan->tgl_tunggak = $tanggal;
                            $tunggakan->NOREK = $id;
                            $tunggakan->unit = $unit_member;
                            $tunggakan->CIF = $id;
                            $tunggakan->CODE_KEL = $kode_kelompok;
                            $tunggakan->DEBIT = $os;
                            $tunggakan->type = "01";
                            $tunggakan->KREDIT = 0;
                            $tunggakan->USERID = Auth::user()->id;
                            $tunggakan->KET = 'Pelunasan' . ' ' . $id . ' an/ ' . $nama;
                            $tunggakan->cao = $cao;
                            $tunggakan->blok = 1;
                            $tunggakan->save();
                        
                        }

                        $musawamah->bulat = 0;    
                        $musawamah->os = 0;
                        $musawamah->angsuran = 0;
                        $musawamah->saldo_margin = 0;
                        $musawamah->ijaroh = 0;
                        $musawamah->update();
                    
                        $member_status = Member::where('kode_member',$id)->first();
                        $member_status->status_member ="active";
                        $member_status->update();

                    break;
            }

           
            DB::commit();
        
        }catch(\Exception $e){

            DB::rollback();
            
            $data = array(
                    "alert" => $e->getmessage(),
                    );
            return response()->json($data);
        
        }
    
        $setting=Setting::find(1);
        $no = 0;
        $bayar = $setoran;
        $sisa = $os;
    
        $pdf = PDF::loadView('musawamah_detail.printpembayaran', compact('bayar','sisa','os','musawamah','no','setting'));
        $pdf->setPaper(array(0,0,700,600), 'potrait');      
        
        return $pdf->stream();    
    
        return back();

    }

    public function store_kelompok(Request $request){

        $data = $request->id_member;
    
        foreach ($data as $id) {
            
            $id_member = $id;
            $nama_input = "id_".$id;      
            $setoran = $request[$nama_input];

            $kode=Uuid::uuid4()->getHex();
            $kode_t=substr($kode,25);
            $unit=Auth::user()->unit;
            $kode_t="BU/-".$unit.$kode_t;
    
            $param_tgl = \App\ParamTgl::where('nama_param_tgl','tanggal_transaksi')->where('unit',Auth::user()->id)->first();
            $tanggal = $param_tgl->param_tgl;
            $musawamah = DB::table('musawamah')->where('id_member',$id_member)->first();
            $angsuran = $musawamah->angsuran;            
            $ijaroh = $musawamah->ijaroh;
            $saldo_margin = $musawamah->saldo_margin;
            $os = $musawamah->os;

            $nama = $musawamah->Cust_Short_name;
            $sisa = $setoran - $angsuran;
            

            $kode_kelompok = $musawamah->code_kel;
            $cao = $musawamah->cao;

            // jika dia tidak bayar
            if ($setoran == 0 || $setoran == null) {
                
          
           }else if ($sisa > 0) {
                
                // KAS
                $jurnal = new TabelTransaksi;
                $jurnal->unit =  $unit; 
                $jurnal->kode_transaksi = $kode_t;
                $jurnal->kode_rekening = 1120000;
                $jurnal->tanggal_transaksi  = $tanggal;
                $jurnal->jenis_transaksi  = 'Jurnal System';
                $jurnal->keterangan_transaksi = 'Setoran Angsuran' . ' ' . $id . ' an/ ' . $nama;
                $jurnal->debet = $setoran;
                $jurnal->kredit = 0;
                $jurnal->tanggal_posting = '';
                $jurnal->keterangan_posting = '0';
                $jurnal->id_admin = Auth::user()->id; 
                $jurnal->save();

                // Piutang
                $jurnal = new TabelTransaksi;
                $jurnal->unit =  $unit; 
                $jurnal->kode_transaksi = $kode_t;
                $jurnal->kode_rekening = 1412000;
                $jurnal->tanggal_transaksi  = $tanggal;
                $jurnal->jenis_transaksi  = 'Jurnal System';
                $jurnal->keterangan_transaksi = 'Setoran Angsuran' . ' ' . $id . ' an/ ' . $nama;
                $jurnal->debet =0;
                $jurnal->kredit = $angsuran;
                $jurnal->tanggal_posting = '';
                $jurnal->keterangan_posting = '0';
                $jurnal->id_admin = Auth::user()->id; 
                $jurnal->save();

                //ESCROW/Titipan selisih lebihnya
                $jurnal = new TabelTransaksi;
                $jurnal->unit =  $unit; 
                $jurnal->kode_transaksi = $kode_t;
                $jurnal->kode_rekening = 2891000;
                $jurnal->tanggal_transaksi  = $tanggal;
                $jurnal->jenis_transaksi  = 'Jurnal System';
                $jurnal->keterangan_transaksi = 'Setoran Angsuran' . ' ' . $id . ' an/ ' . $nama;
                $jurnal->debet =0;
                $jurnal->kredit = $sisa;
                $jurnal->tanggal_posting = '';
                $jurnal->keterangan_posting = '0';
                $jurnal->id_admin = Auth::user()->id; 
                $jurnal->save();                

                // PMYD
                $jurnal = new TabelTransaksi;
                $jurnal->unit =  $unit; 
                $jurnal->kode_transaksi = $kode_t;
                $jurnal->kode_rekening = 1422000;
                $jurnal->tanggal_transaksi  = $tanggal;
                $jurnal->jenis_transaksi  = 'Jurnal System';
                $jurnal->keterangan_transaksi = 'Setoran Angsuran' . ' ' . $id . ' an/ ' . $nama;
                $jurnal->debet = $ijaroh;
                $jurnal->kredit =0;
                $jurnal->tanggal_posting = '';
                $jurnal->keterangan_posting = '0';
                $jurnal->id_admin = Auth::user()->id; 
                $jurnal->save();
                
                // PM
                $jurnal = new TabelTransaksi;
                $jurnal->unit =  $unit; 
                $jurnal->kode_transaksi = $kode_t;
                $jurnal->kode_rekening = 41001;
                $jurnal->tanggal_transaksi  = $tanggal;
                $jurnal->jenis_transaksi  = 'Jurnal System';
                $jurnal->keterangan_transaksi = 'Setoran Angsuran' . ' ' . $id . ' an/ ' . $nama;
                $jurnal->debet =0;
                $jurnal->kredit = $ijaroh;
                $jurnal->tanggal_posting = '';
                $jurnal->keterangan_posting = '0';
                $jurnal->id_admin = Auth::user()->id; 
                $jurnal->save();

                // Kredit list_toko sebesar setoran
                $member = new ListToko;
                $member->buss_date = $tanggal;
                $member->norek   = $id;
                $member->unit = $unit;
                $member->id_member =$id;
                $member->code_kel =$kode_kelompok;
                $member->DEBIT =0;
                $member->type ='02';
                $member->KREDIT = $setoran;
                $member->userid =Auth::user()->id;
                $member->ket ='Setoran Angsuran';
                $member->kode_transaksi = $kode_t;
                $member->tgl_input = $tanggal;
                $member->cao =$cao;
                $member->save();

                // Debet list Toko sebesar angsuran
                $member = new ListToko;
                $member->buss_date = $tanggal;
                $member->norek   = $id;
                $member->unit = $unit;
                $member->id_member =$id;
                $member->code_kel =$kode_kelompok;
                $member->DEBIT =$angsuran;
                $member->type ='02';
                $member->KREDIT =0;
                $member->userid =Auth::user()->id;
                $member->ket ='Setoran Angsuran';
                $member->kode_transaksi = $kode_t;
                $member->tgl_input = $tanggal;
                $member->cao =$cao;
                $member->save();
                
                $musawamah = Musawamah::where('id_member',$id)->first();
                
                if($musawamah->bulat >0){
                // Debit tunggakan toko sebesar angsuran
                $tunggakan = new TunggakanToko;
                $tunggakan->tgl_tunggak = $tanggal;
                $tunggakan->NOREK = $id;
                $tunggakan->unit = $unit;
                $tunggakan->CIF = $id;
                $tunggakan->CODE_KEL = $kode_kelompok;
                $tunggakan->DEBIT = $angsuran;
                $tunggakan->type = "01";
                $tunggakan->KREDIT = 0;
                $tunggakan->USERID = $unit;
                $tunggakan->KET = 'Setoran Angsuran Dari Titipan' . ' ' . $id . ' an/ ' . $nama;
                $tunggakan->cao = $cao;
                $tunggakan->blok = 1;
                $tunggakan->save();

                 // mengurangi tunggakan    
                 //jika tunggakannya lebih dari angsuran / sama dengan angsuran
                 if ($musawamah->bulat >= $angsuran){

                    $musawamah = Musawamah::where('id_member',$id)->first();
                    $musawamah->bulat -= $angsuran;
                    $musawamah->update();
                
                 }else {
                    
                    $musawamah = Musawamah::where('id_member',$id)->first();
                    $musawamah->bulat = 0;
                    $musawamah->update();

                 }
                }

                // mengurangi os
                if ($os >= $angsuran) {
                    // os dikurangin sesuai angsuran
                    $musawamah = Musawamah::where('id_member',$id)->first();
                    $musawamah->os -= $angsuran;
                    $musawamah->saldo_margin -= $ijaroh;
                    $musawamah->update();

                }else {
                    
                    $musawamah->os = 0;
                    $musawamah->saldo_margin = 0;
                    $musawamah->angsuran = 0;
                    $musawamah->ijaroh = 0;
                    $musawamah->bulat = 0;
                    $musawamah->update();   

                }

                // ambil data_musawamah terbaru 
                $musawamah = Musawamah::where('id_member',$id)->first();       

                // jika tidak ada lgi tunggakan
                if ($musawamah->bulat == 0) {
                    
                    $member_status = Member::where('kode_member',$id)->first();
                    // status member di aktifkan kembali
                    $member_status->status_member ="active";
                    $member_status->update();            
                    
                    // jika tidak ada lgi os
                    if ($musawamah->os == 0) {
                        
                        $musawamah->ijaroh = 0;
                        $musawamah->angsuran = 0;
                        $musawamah->os = 0;
                        $musawamah->saldo_margin = 0;
                        $musawamah->bulat = 0;
                        $musawamah->update();   

                    }
    
                }
               
                
            }elseif ($sisa < 0) { //Jika Setoran Kurang
                
                $jurnal = new TabelTransaksi;
                $jurnal->unit =  $unit; 
                $jurnal->kode_transaksi = $kode_t;
                $jurnal->kode_rekening = 1120000;
                $jurnal->tanggal_transaksi  = $tanggal;
                $jurnal->jenis_transaksi  = 'Jurnal System';
                $jurnal->keterangan_transaksi = 'Setoran Ke Titipan' . ' ' . $id . ' an/ ' . $nama;
                $jurnal->debet = $setoran;
                $jurnal->kredit = 0;
                $jurnal->tanggal_posting = '';
                $jurnal->keterangan_posting = '0';
                $jurnal->id_admin = Auth::user()->id; 
                $jurnal->save();
                
                // masuk ke titipan/ESCROW
                $jurnal = new TabelTransaksi;
                $jurnal->unit =  $unit; 
                $jurnal->kode_transaksi = $kode_t;
                $jurnal->kode_rekening = 2891000;
                $jurnal->tanggal_transaksi  = $tanggal;
                $jurnal->jenis_transaksi  = 'Jurnal System';
                $jurnal->keterangan_transaksi = 'Setoran Ke Titipan' . ' ' . $id . ' an/ ' . $nama;
                $jurnal->debet =0;
                $jurnal->kredit = $setoran;
                $jurnal->tanggal_posting = '';
                $jurnal->keterangan_posting = '0';
                $jurnal->id_admin = Auth::user()->id; 
                $jurnal->save();

                
                $member = new ListToko;
                $member->buss_date = $tanggal;
                $member->norek   = $id;
                $member->unit = $unit;
                $member->id_member =$id;
                $member->code_kel =$kode_kelompok;
                $member->DEBIT =0;
                $member->type ='02';
                $member->KREDIT = $setoran;
                $member->userid =Auth::user()->id;
                $member->ket ='Setoran Ke Titipan';
                $member->kode_transaksi = $kode_t;
                $member->tgl_input = $tanggal;
                $member->cao =$cao;
                $member->save();


                $member_status = Member::where('kode_member',$id)->first();
                // status member di blokir
                $member_status->status_member ="Blok";
                $member_status->update();            
                

            }else {

                // jika yang di setorkan sesuai angsuran
                $jurnal = new TabelTransaksi;
                $jurnal->unit =  $unit; 
                $jurnal->kode_transaksi = $kode_t;
                $jurnal->kode_rekening = 1120000;
                $jurnal->tanggal_transaksi  = $tanggal;
                $jurnal->jenis_transaksi  = 'Jurnal System';
                $jurnal->keterangan_transaksi = 'Setoran Angsuran' . ' ' . $id . ' an/ ' . $nama;
                $jurnal->debet = $setoran;
                $jurnal->kredit = 0;
                $jurnal->tanggal_posting = '';
                $jurnal->keterangan_posting = '0';
                $jurnal->id_admin = Auth::user()->id; 
                $jurnal->save();

                $jurnal = new TabelTransaksi;
                $jurnal->unit =  $unit; 
                $jurnal->kode_transaksi = $kode_t;
                $jurnal->kode_rekening = 1412000;
                $jurnal->tanggal_transaksi  = $tanggal;
                $jurnal->jenis_transaksi  = 'Jurnal System';
                $jurnal->keterangan_transaksi = 'Setoran Angsuran' . ' ' . $id . ' an/ ' . $nama;
                $jurnal->debet =0;
                $jurnal->kredit = $angsuran;
                $jurnal->tanggal_posting = '';
                $jurnal->keterangan_posting = '0';
                $jurnal->id_admin = Auth::user()->id; 
                $jurnal->save();

                $jurnal = new TabelTransaksi;
                $jurnal->unit =  $unit; 
                $jurnal->kode_transaksi = $kode_t;
                $jurnal->kode_rekening = 1422000;
                $jurnal->tanggal_transaksi  = $tanggal;
                $jurnal->jenis_transaksi  = 'Jurnal System';
                $jurnal->keterangan_transaksi = 'Setoran Angsuran' . ' ' . $id . ' an/ ' . $nama;
                $jurnal->debet = $ijaroh;
                $jurnal->kredit =0;
                $jurnal->tanggal_posting = '';
                $jurnal->keterangan_posting = '0';
                $jurnal->id_admin = Auth::user()->id; 
                $jurnal->save();
                

                $jurnal = new TabelTransaksi;
                $jurnal->unit =  $unit; 
                $jurnal->kode_transaksi = $kode_t;
                $jurnal->kode_rekening = 41001;
                $jurnal->tanggal_transaksi  = $tanggal;
                $jurnal->jenis_transaksi  = 'Jurnal System';
                $jurnal->keterangan_transaksi = 'Setoran Angsuran' . ' ' . $id . ' an/ ' . $nama;
                $jurnal->debet =0;
                $jurnal->kredit = $ijaroh;
                $jurnal->tanggal_posting = '';
                $jurnal->keterangan_posting = '0';
                $jurnal->id_admin = Auth::user()->id; 
                $jurnal->save();

                $member = new ListToko;
                $member->buss_date = $tanggal;
                $member->norek   = $id;
                $member->unit = $unit;
                $member->id_member =$id;
                $member->code_kel =$kode_kelompok;
                $member->DEBIT =0;
                $member->type ='02';
                $member->KREDIT = $setoran;
                $member->userid =Auth::user()->id;
                $member->ket ='Setoran Angsuran';
                $member->kode_transaksi = $kode_t;
                $member->tgl_input = $tanggal;
                $member->cao =$cao;
                $member->save();
                
                $member = new ListToko;
                $member->buss_date = $tanggal;
                $member->norek   = $id;
                $member->unit = $unit;
                $member->id_member =$id;
                $member->code_kel =$kode_kelompok;
                $member->DEBIT =$angsuran;
                $member->type ='02';
                $member->KREDIT =0;
                $member->userid =Auth::user()->id;
                $member->ket ='Setoran Angsuran';
                $member->kode_transaksi = $kode_t;
                $member->tgl_input = $tanggal;
                $member->cao =$cao;
                $member->save();

                $musawamah = Musawamah::where('id_member',$id)->first();
                
                // mengurangi tunggakan    
                if ($musawamah->bulat >= $angsuran){
                     
                    $musawamah = Musawamah::where('id_member',$id)->first();
                    $tunggakan = new TunggakanToko;
                    $tunggakan->tgl_tunggak = $tanggal;
                    $tunggakan->NOREK = $id;
                    $tunggakan->unit = $unit;
                    $tunggakan->CIF = $id;
                    $tunggakan->CODE_KEL = $kode_kelompok;
                    $tunggakan->DEBIT = $angsuran;
                    $tunggakan->type = "01";
                    $tunggakan->KREDIT = 0;
                    $tunggakan->USERID = $unit;
                    $tunggakan->KET = 'Setoran Angsuran Dari Titipan' . ' ' . $id . ' an/ ' . $nama;
                    $tunggakan->cao = $cao;
                    $tunggakan->blok = 1;
                    $tunggakan->save();

                    $musawamah = Musawamah::where('id_member',$id)->first();
                    $musawamah->bulat -= $angsuran;
                    $musawamah->update();
                
                }else {
                    
                    $musawamah = Musawamah::where('id_member',$id)->first();
                    $musawamah->bulat = 0;
                    $musawamah->update();

                }


                if ($os > $angsuran) {
                    
                    $musawamah = Musawamah::where('id_member',$id)->first();
                    $musawamah->os -= $angsuran;
                    $musawamah->saldo_margin -= $ijaroh;
                    $musawamah->update();

                }else {

                    
                    $musawamah->os = 0;
                    $musawamah->saldo_margin = 0;
                    $musawamah->angsuran = 0;
                    $musawamah->ijaroh = 0 ;
                    $musawamah->bulat = 0;
                    $musawamah->update();   

                }
    

                    $musawamah = Musawamah::where('id_member',$id)->first();       
                    // jika tidak ada lgi tunggakan 
                if ($musawamah->bulat == 0) {
                    
                    $member_status = Member::where('kode_member',$id)->first();
                    // status member di aktifkan kembali
                    $member_status->status_member ="active";
                    $member_status->update();            
    
                }

            }
        }

        return back();

    }

}


