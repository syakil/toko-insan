<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;
use App\TabelTransaksi;
use Ramsey\Uuid\Uuid;
use DB;
use App\Musawamah;
use App\Member;
use App\Restruktur;
use App\Branch;

class RestrukturController extends Controller
{
    protected $projects;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    
    public function __construct()
    {
        $this->middleware(function ($request, $next) {

            if (is_null(Auth::user())) {
                return redirect()->route('login')->withErrors(['Sesi Anda Telah Berakhir']);;
            }else{   
                $this->projects = Auth::user()->projects;
                return $next($request);
            }
            
        });
    }
    
    public function index(){

        return view('restruktur.index');

    }

    public function loadData(Request $request){

        $cari = $request['query'];
        
        $data = DB::table('musawamah')->select('id_member', 'Cust_Short_name')->where('id_member', 'LIKE', '%'.$cari.'%')->orWhere('Cust_Short_name', 'LIKE', '%'.$cari.'%')->limit('5')->get();
        $output = '<ul class="dropdown-menu" style="display:block; position:relative">';


        foreach($data as $row){

            $output .= '
            <li class="member_list"><a href="#">'.$row->id_member.' - '.$row->Cust_Short_name.'</a></li>
            ';

        }

        $output .= '</ul>';
        echo $output;

    }

    public function listData($kode){

        $detail = Musawamah::where('id_member',$kode)->first();
        
        $data = array();
        
        $row = array();
        $row[] = $kode;
        $row[] = $detail->Cust_Short_name;
        $row[] = $detail->Tenor;
        $row[] = "Rp. ".format_uang($detail->os);
        $row[] = "Rp. ".format_uang($detail->angsuran);
        $row[] = "Rp. ".format_uang($detail->saldo_margin);
        $row[] = "Rp. ".format_uang($detail->ijaroh);
        $row[] = '<div class="btn-group">
                <button type="button" class="btn btn-success" onclick="proses()">Proses</button>
                </div>';
        $data[] = $row;  
        
        $output = array("data" => $data);
        return response()->json($output);  
    }

    public function proses(Request $request){

        
        try{
        
            DB::beginTransaction();
       
            $jenisTransaksi = $request->jenis_data;
            $tenor = $request->tenor_data;
            
            $idMember = $request->kode;
            
            $musawamah = Musawamah::where('id_member',$idMember)->first();
            $param_tgl = \App\ParamTgl::where('nama_param_tgl','tanggal_transaksi')->where('unit',Auth::user()->id)->first();
            $tanggal = $param_tgl->param_tgl;
            $id = $musawamah->id_member;
            $nama = $musawamah->Cust_Short_name;

            $DataSimpanan = DB::table('list_toko')->select(DB::raw('SUM(debit-kredit) as simpanan'))->where('id_member',$idMember)->first();
            $simpanan = $DataSimpanan->simpanan;

            
            switch ($jenisTransaksi) {

                case 'pokok':

                    $musawamah = Musawamah::where('id_member',$idMember)->first();
                                        
                    $resturktur = new Restruktur;
                    $resturktur->buss_date = $musawamah->buss_date;
                    $resturktur->code_kel = $musawamah->code_kel;
                    $resturktur->no_anggota = $musawamah->no_anggota;
                    $resturktur->id_member = $idMember;
                    $resturktur->Cust_Short_name = $musawamah->Cust_Short_name;
                    $resturktur->DEAL_TYPE = $musawamah->DEAL_TYPE;
                    $resturktur->suffix = $musawamah->suffix;
                    $resturktur->bagi_hasil = $musawamah->bagi_hasil;
                    $resturktur->Tenor = $musawamah->Tenor;
                    $resturktur->Plafond = $musawamah->Plafond;
                    $resturktur->os = $musawamah->os;
                    $resturktur->saldo_margin = $musawamah->saldo_margin;
                    $resturktur->angsuran = $musawamah->angsuran;
                    $resturktur->pokok = $musawamah->pokok;
                    $resturktur->ijaroh = $musawamah->ijaroh;
                    $resturktur->bulat = $musawamah->bulat;
                    $resturktur->run_tenor = $musawamah->run_tenor;
                    $resturktur->ke = $musawamah->ke;
                    $resturktur->usaha = $musawamah->usaha;
                    $resturktur->nama_usaha = $musawamah->nama_usaha;
                    $resturktur->unit = $musawamah->unit;
                    $resturktur->tgl_wakalah = $musawamah->tgl_wakalah;
                    $resturktur->tgl_akad = $musawamah->tgl_akad;
                    $resturktur->tgl_murab = $musawamah->tgl_murab;
                    $resturktur->next_schedule = $musawamah->next_schedule;
                    $resturktur->maturity_date = $musawamah->maturity_date;
                    $resturktur->last_payment = $musawamah->last_payment;
                    $resturktur->hari = $musawamah->hari;
                    $resturktur->cao = $musawamah->cao;
                    $resturktur->USERID = $musawamah->USERID;
                    $resturktur->status = $musawamah->status;
                    $resturktur->status_usia = $musawamah->status_usia;
                    $resturktur->status_app = $musawamah->status_app;
                    $resturktur->gol = $musawamah->gol;
                    $resturktur->id_outlet = $musawamah->id_outlet;
                    $resturktur->code_musa = $musawamah->code_musa;
                    $resturktur->save();

                    $musawamah = Musawamah::where('id_member',$idMember)->first();
                    $musawamah->os = $musawamah->os - $musawamah->saldo_margin;
                    $musawamah->saldo_margin = 0;
                    $musawamah->ijaroh = 0;
                    $musawamah->update();

                    $kode=Uuid::uuid4()->getHex();
                    $kode_t=substr($kode,25);
                    $unit=Auth::user()->unit;
                    $kode_t="BU/-".$unit.$kode_t;

                    // Piutang
                    $jurnal = new TabelTransaksi;
                    $jurnal->unit =  $musawamah->unit; 
                    $jurnal->kode_transaksi = $kode_t;
                    $jurnal->kode_rekening = 1412000;
                    $jurnal->tanggal_transaksi  = $tanggal;
                    $jurnal->jenis_transaksi  = 'Jurnal System';
                    $jurnal->keterangan_transaksi = 'Restrukturisasi Pokok' . ' ' . $id . ' an/ ' . $nama;
                    $jurnal->debet =0;
                    $jurnal->kredit = $simpanan;
                    $jurnal->tanggal_posting = '';
                    $jurnal->keterangan_posting = '0';
                    $jurnal->id_admin = Auth::user()->id; 
                    $jurnal->save();

                    //ESCROW/Titipan selisih lebihnya
                    $jurnal = new TabelTransaksi;
                    $jurnal->unit =  $musawamah->unit; 
                    $jurnal->kode_transaksi = $kode_t;
                    $jurnal->kode_rekening = 1422000;
                    $jurnal->tanggal_transaksi  = $tanggal;
                    $jurnal->jenis_transaksi  = 'Jurnal System';
                    $jurnal->keterangan_transaksi = 'Restrukturisasi Pokok' . ' ' . $id . ' an/ ' . $nama;
                    $jurnal->debet =0;
                    $jurnal->kredit = $simpanan;
                    $jurnal->tanggal_posting = '';
                    $jurnal->keterangan_posting = '0';
                    $jurnal->id_admin = Auth::user()->id; 
                    $jurnal->save();   

                    $musawamah = Musawamah::where('id_member',$idMember)->first();
                    
                    $angsuran = $musawamah->os/$tenor;
                    $angsuranBaru = roundUpToAny($angsuran,5000);

                    $ijaroh = round($musawamah->saldo_margin/$tenor,-3);
                    $ijarohBaru = roundUpToAny($ijaroh,5000);
                    
                    $musawamah->tenor = $tenor;
                    $musawamah->angsuran = $angsuranBaru;
                    $musawamah->ijaroh =$ijarohBaru;
                    $musawamah->status_app = 'RES POKOK';
                    $musawamah->update();


                break;
                
                case 'pokokMargin':

                        $musawamah = Musawamah::where('id_member',$idMember)->first();
                            
                        if ($simpanan != null || $simpanan > 0) {
                            
                            $resturktur = new Restruktur;
                            $resturktur->buss_date = $musawamah->buss_date;
                            $resturktur->code_kel = $musawamah->code_kel;
                            $resturktur->no_anggota = $musawamah->no_anggota;
                            $resturktur->id_member = $idMember;
                            $resturktur->Cust_Short_name = $musawamah->Cust_Short_name;
                            $resturktur->DEAL_TYPE = $musawamah->DEAL_TYPE;
                            $resturktur->suffix = $musawamah->suffix;
                            $resturktur->bagi_hasil = $musawamah->bagi_hasil;
                            $resturktur->Tenor = $musawamah->Tenor;
                            $resturktur->Plafond = $musawamah->Plafond;
                            $resturktur->os = $musawamah->os;
                            $resturktur->saldo_margin = $musawamah->saldo_margin;
                            $resturktur->angsuran = $musawamah->angsuran;
                            $resturktur->pokok = $musawamah->pokok;
                            $resturktur->ijaroh = $musawamah->ijaroh;
                            $resturktur->bulat = $musawamah->bulat;
                            $resturktur->run_tenor = $musawamah->run_tenor;
                            $resturktur->ke = $musawamah->ke;
                            $resturktur->usaha = $musawamah->usaha;
                            $resturktur->nama_usaha = $musawamah->nama_usaha;
                            $resturktur->unit = $musawamah->unit;
                            $resturktur->tgl_wakalah = $musawamah->tgl_wakalah;
                            $resturktur->tgl_akad = $musawamah->tgl_akad;
                            $resturktur->tgl_murab = $musawamah->tgl_murab;
                            $resturktur->next_schedule = $musawamah->next_schedule;
                            $resturktur->maturity_date = $musawamah->maturity_date;
                            $resturktur->last_payment = $musawamah->last_payment;
                            $resturktur->hari = $musawamah->hari;
                            $resturktur->cao = $musawamah->cao;
                            $resturktur->USERID = $musawamah->USERID;
                            $resturktur->status = $musawamah->status;
                            $resturktur->status_usia = $musawamah->status_usia;
                            $resturktur->status_app = $musawamah->status_app;
                            $resturktur->gol = $musawamah->gol;
                            $resturktur->id_outlet = $musawamah->id_outlet;
                            $resturktur->code_musa = $musawamah->code_musa;
                            $resturktur->save();
                            
                            $musawamah = Musawamah::where('id_member',$idMember)->first();
                            $musawamah->update();

                            $angsuran = $musawamah->os/$tenor;
                            $angsuranBaru = roundUpToAny($angsuran,5000);

                            $ijaroh = round($musawamah->saldo_margin/$tenor,-3);
                            $ijarohBaru = roundUpToAny($ijaroh,5000);

                            $musawamah->os = $musawamah->os - $simpanan;    
                            $musawamah->tenor = $tenor;
                            $musawamah->angsuran = $angsuranBaru;
                            $musawamah->ijaroh =$ijarohBaru;
                            $musawamah->status_app = 'RES POKOK MARGIN';
                            $musawamah->update();

                            $kode=Uuid::uuid4()->getHex();
                            $kode_t=substr($kode,25);
                            $unit=Auth::user()->unit;
                            $kode_t="BU/-".$unit.$kode_t;

                            $simpanan = new ListToko;
                            $simpanan->buss_date = $tanggal;
                            $simpanan->norek   = $idMember;
                            $simpanan->unit = $musawamah->unit;
                            $simpanan->id_member =$idMember;
                            $simpanan->code_kel =$musawamah->code_kel;
                            $simpanan->DEBIT = 0;
                            $simpanan->type ='02';
                            $simpanan->KREDIT = $simpanan;
                            $simpanan->userid =Auth::user()->id;
                            $simpanan->ket = 'Restrukturisasi Pokok Dari Titipan';
                            $simpanan->kode_transaksi = $kode_t;
                            $simpanan->tgl_input = $tanggal;
                            $simpanan->cao =$musawamah->cao;
                            $simpanan->save(); 
                            
                            // Piutang
                            $jurnal = new TabelTransaksi;
                            $jurnal->unit =  $musawamah->unit; 
                            $jurnal->kode_transaksi = $kode_t;
                            $jurnal->kode_rekening = 1412000;
                            $jurnal->tanggal_transaksi  = $tanggal;
                            $jurnal->jenis_transaksi  = 'Jurnal System';
                            $jurnal->keterangan_transaksi = 'Restrukturisasi Pokok Dari Titipan' . ' ' . $id . ' an/ ' . $nama;
                            $jurnal->debet =0;
                            $jurnal->kredit = $simpanan;
                            $jurnal->tanggal_posting = '';
                            $jurnal->keterangan_posting = '0';
                            $jurnal->id_admin = Auth::user()->id; 
                            $jurnal->save();

                            //ESCROW/Titipan selisih lebihnya
                            $jurnal = new TabelTransaksi;
                            $jurnal->unit =  $musawamah->unit; 
                            $jurnal->kode_transaksi = $kode_t;
                            $jurnal->kode_rekening = 2891000;
                            $jurnal->tanggal_transaksi  = $tanggal;
                            $jurnal->jenis_transaksi  = 'Jurnal System';
                            $jurnal->keterangan_transaksi = 'Restrukturisasi Pokok Dari Titipan' . ' ' . $id . ' an/ ' . $nama;
                            $jurnal->debet =0;
                            $jurnal->kredit = $simpanan;
                            $jurnal->tanggal_posting = '';
                            $jurnal->keterangan_posting = '0';
                            $jurnal->id_admin = Auth::user()->id; 
                            $jurnal->save();          

                        }else {

                            $musawamah = Musawamah::where('id_member',$idMember)->first();
                            
                            $resturktur = new Restruktur;
                            $resturktur->buss_date = $musawamah->buss_date;
                            $resturktur->code_kel = $musawamah->code_kel;
                            $resturktur->no_anggota = $musawamah->no_anggota;
                            $resturktur->id_member = $idMember;
                            $resturktur->Cust_Short_name = $musawamah->Cust_Short_name;
                            $resturktur->DEAL_TYPE = $musawamah->DEAL_TYPE;
                            $resturktur->suffix = $musawamah->suffix;
                            $resturktur->bagi_hasil = $musawamah->bagi_hasil;
                            $resturktur->Tenor = $musawamah->Tenor;
                            $resturktur->Plafond = $musawamah->Plafond;
                            $resturktur->os = $musawamah->os;
                            $resturktur->saldo_margin = $musawamah->saldo_margin;
                            $resturktur->angsuran = $musawamah->angsuran;
                            $resturktur->pokok = $musawamah->pokok;
                            $resturktur->ijaroh = $musawamah->ijaroh;
                            $resturktur->bulat = $musawamah->bulat;
                            $resturktur->run_tenor = $musawamah->run_tenor;
                            $resturktur->ke = $musawamah->ke;
                            $resturktur->usaha = $musawamah->usaha;
                            $resturktur->nama_usaha = $musawamah->nama_usaha;
                            $resturktur->unit = $musawamah->unit;
                            $resturktur->tgl_wakalah = $musawamah->tgl_wakalah;
                            $resturktur->tgl_akad = $musawamah->tgl_akad;
                            $resturktur->tgl_murab = $musawamah->tgl_murab;
                            $resturktur->next_schedule = $musawamah->next_schedule;
                            $resturktur->maturity_date = $musawamah->maturity_date;
                            $resturktur->last_payment = $musawamah->last_payment;
                            $resturktur->hari = $musawamah->hari;
                            $resturktur->cao = $musawamah->cao;
                            $resturktur->USERID = $musawamah->USERID;
                            $resturktur->status = $musawamah->status;
                            $resturktur->status_usia = $musawamah->status_usia;
                            $resturktur->status_app = $musawamah->status_app;
                            $resturktur->gol = $musawamah->gol;
                            $resturktur->id_outlet = $musawamah->id_outlet;
                            $resturktur->code_musa = $musawamah->code_musa;
                            $resturktur->save();
                            
                            $musawamah = Musawamah::where('id_member',$idMember)->first();
                            
                            $angsuran = $musawamah->os/$tenor;
                            $angsuranBaru = roundUpToAny($angsuran,5000);

                            $ijaroh = round($musawamah->saldo_margin/$tenor,-3);
                            $ijarohBaru = roundUpToAny($ijaroh,5000);
                            
                            $musawamah->angsuran = $angsuranBaru;
                            $musawamah->ijaroh = $ijarohBaru;
                            $musawamah->tenor = $tenor;
                            $musawamah->status_app = 'RES POKOK MARGIN';
                            $musawamah->update();
                            
                        }

                break;
            }

            DB::commit();
            
        }catch(\Exception $e){
         
            DB::rollback();
            $data = array(
                    "alert" => $e->getmessage() . " : " . $e->getLine(),
                );
            return response()->json($data);
   
        }


    }
}
