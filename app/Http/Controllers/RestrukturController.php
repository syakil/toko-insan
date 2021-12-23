<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;
use App\TabelTransaksi;
use PDF;
use Ramsey\Uuid\Uuid;
use DB;
use App\Musawamah;
use App\Member;
use App\Restruktur;
use App\ListToko;
use App\Branch;
use App\TunggakanToko;

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
        
        $data = DB::table('musawamah')
        ->select('id_member', 'Cust_Short_name')
        ->where('id_member', 'LIKE', '%'.$cari.'%')
        ->whereIn('gol',[3,4])
        ->where('os','>',0)
        ->limit('5')
        ->get();

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
            $jumlahHari = 7 * $tenor;
            $date = strtotime($tanggal);
            $next_jatpo = strtotime("+".$jumlahHari." day", $date);
            $maturity_date = date('Y-m-d',$next_jatpo);
            $id = $idMember;
            $nama = $musawamah->Cust_Short_name;

            $DataSimpanan = DB::table('list_toko')->select(DB::raw('SUM(kredit-debit) as simpanan'))->where('id_member',$idMember)->first();
            $simpanan = $DataSimpanan->simpanan;

            if ($musawamah->bulat > 0) {

                $tunggakan = new TunggakanToko;
                $tunggakan->tgl_tunggak = $tanggal;
                $tunggakan->NOREK = $id;
                $tunggakan->unit = $musawamah->unit;
                $tunggakan->CIF = $id;
                $tunggakan->CODE_KEL = $musawamah->code_kel;
                $tunggakan->DEBIT = 0;
                $tunggakan->type = "01";
                $tunggakan->USERID = $musawamah->unit;
                $tunggakan->KREDIT = $musawamah->bulat;
                $tunggakan->KET = 'Restruktur' . ' ' . $id . ' an/ ' . $nama;
                $tunggakan->cao = $musawamah->cao;
                $tunggakan->blok = 1;
                $tunggakan->save();

            }

            
            switch ($jenisTransaksi) {

                case 'pokok':

                    if ($request->simpanan) {
                        
                        if ($simpanan != null || $simpanan > 0) {

                            $saldo_margin = $musawamah->saldo_margin;

                            $musawamah = Musawamah::where('id_member',$idMember)->first();
                                
                            // restruktur
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
                            //--end restruktur

                            $kode=Uuid::uuid4()->getHex();
                            $kode_t=substr($kode,25);
                            $unit=Auth::user()->unit;
                            $kode_t="BU/-".$unit.$kode_t;

                            $jurnal = new TabelTransaksi;
                            $jurnal->unit =  $musawamah->unit; 
                            $jurnal->kode_transaksi = $kode_t;
                            $jurnal->kode_rekening = 1412000;
                            $jurnal->tanggal_transaksi  = $tanggal;
                            $jurnal->jenis_transaksi  = 'Jurnal System';
                            $jurnal->keterangan_transaksi = 'Restrukturisasi Pokok' . ' ' . $id . ' an/ ' . $nama;
                            $jurnal->debet =0;
                            $jurnal->kredit = $musawamah->os;
                            $jurnal->tanggal_posting = '';
                            $jurnal->keterangan_posting = '0';
                            $jurnal->id_admin = Auth::user()->id; 
                            $jurnal->save();
                        
                            $jurnal = new TabelTransaksi;
                            $jurnal->unit =  $musawamah->unit; 
                            $jurnal->kode_transaksi = $kode_t;
                            $jurnal->kode_rekening = 1422000;
                            $jurnal->tanggal_transaksi  = $tanggal;
                            $jurnal->jenis_transaksi  = 'Jurnal System';
                            $jurnal->keterangan_transaksi = 'Restrukturisasi Pokok' . ' ' . $id . ' an/ ' . $nama;
                            $jurnal->debet = $musawamah->saldo_margin;
                            $jurnal->kredit = 0;
                            $jurnal->tanggal_posting = '';
                            $jurnal->keterangan_posting = '0';
                            $jurnal->id_admin = Auth::user()->id; 
                            $jurnal->save();   

                            $jurnal = new TabelTransaksi;
                            $jurnal->unit =  $musawamah->unit; 
                            $jurnal->kode_transaksi = $kode_t;
                            $jurnal->kode_rekening = 1415000;
                            $jurnal->tanggal_transaksi  = $tanggal;
                            $jurnal->jenis_transaksi  = 'Jurnal System';
                            $jurnal->keterangan_transaksi = 'Restrukturisasi Pokok' . ' ' . $id . ' an/ ' . $nama;
                            $jurnal->debet = $musawamah->os - $musawamah->saldo_margin;
                            $jurnal->kredit =0;
                            $jurnal->tanggal_posting = '';
                            $jurnal->keterangan_posting = '0';
                            $jurnal->id_admin = Auth::user()->id; 
                            $jurnal->save();
                        

                            $musawamah = Musawamah::where('id_member',$idMember)->first();
                            $musawamah->os = $musawamah->os - $musawamah->saldo_margin;
                            $musawamah->saldo_margin = 0;
                            $musawamah->ijaroh = 0;
                            $musawamah->update();

                            $angsuran = $musawamah->os/$tenor;
                            $angsuranBaru = roundUpToAny($angsuran,5000);

                            $musawamah->tenor = $tenor;
                            $musawamah->angsuran = $angsuranBaru;
                            // TODO : Run Tenor ,Bulat, Ke , Gol, Akad sesuai tgl res
                            $musawamah->gol = 1;
                            $musawamah->bulat = 0;
                            $musawamah->maturity_date = $maturity_date;
                            $musawamah->run_tenor = 0;
                            $musawamah->ke = 1;
                            $musawamah->tgl_akad = $tanggal;
                            $musawamah->status_app = 'REST_POKOK';
                            $musawamah->update();
                            
                        }else { 

                            $musawamah = Musawamah::where('id_member',$idMember)->first();

                            $saldo_margin = $musawamah->saldo_margin;
                                             
                            // restruktur
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
                            //restrutktur

                            $kode=Uuid::uuid4()->getHex();
                            $kode_t=substr($kode,25);
                            $unit = Auth::user()->unit;
                            $kode_t="BU/-".$unit.$kode_t;
                            
                            $list_simpanan = new ListToko;
                            $list_simpanan->buss_date = $tanggal;
                            $list_simpanan->norek   = $idMember;
                            $list_simpanan->unit = $musawamah->unit;
                            $list_simpanan->id_member =$idMember;
                            $list_simpanan->code_kel =$musawamah->code_kel;
                            $list_simpanan->kredit = 0;
                            $list_simpanan->type ='02';
                            $list_simpanan->DEBIT = $simpanan;
                            $list_simpanan->userid =Auth::user()->id;
                            $list_simpanan->ket = 'Restrukturisasi Pokok Dari Titipan';
                            $list_simpanan->kode_transaksi = $kode_t;
                            $list_simpanan->tgl_input = $tanggal;
                            $list_simpanan->cao =$musawamah->cao;
                            $list_simpanan->save();    

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
                            
                            $jurnal = new TabelTransaksi;
                            $jurnal->unit =  $musawamah->unit; 
                            $jurnal->kode_transaksi = $kode_t;
                            $jurnal->kode_rekening = 1412000;
                            $jurnal->tanggal_transaksi  = $tanggal;
                            $jurnal->jenis_transaksi  = 'Jurnal System';
                            $jurnal->keterangan_transaksi = 'Restrukturisasi Pokok' . ' ' . $id . ' an/ ' . $nama;
                            $jurnal->debet  = 0;
                            $jurnal->kredit = $musawamah->os - $simpanan;
                            $jurnal->tanggal_posting = '';
                            $jurnal->keterangan_posting = '0';
                            $jurnal->id_admin = Auth::user()->id; 
                            $jurnal->save();   
                        
                            $jurnal = new TabelTransaksi;
                            $jurnal->unit =  $musawamah->unit; 
                            $jurnal->kode_transaksi = $kode_t;
                            $jurnal->kode_rekening = 1422000;
                            $jurnal->tanggal_transaksi  = $tanggal;
                            $jurnal->jenis_transaksi  = 'Jurnal System';
                            $jurnal->keterangan_transaksi = 'Restrukturisasi Pokok' . ' ' . $id . ' an/ ' . $nama;
                            $jurnal->debet = $musawamah->saldo_margin;
                            $jurnal->kredit = 0;
                            $jurnal->tanggal_posting = '';
                            $jurnal->keterangan_posting = '0';
                            $jurnal->id_admin = Auth::user()->id; 
                            $jurnal->save();   
                            
                            $jurnal = new TabelTransaksi;
                            $jurnal->unit =  $musawamah->unit; 
                            $jurnal->kode_transaksi = $kode_t;
                            $jurnal->kode_rekening = 1415000;
                            $jurnal->tanggal_transaksi  = $tanggal;
                            $jurnal->jenis_transaksi  = 'Jurnal System';
                            $jurnal->keterangan_transaksi = 'Restrukturisasi Pokok' . ' ' . $id . ' an/ ' . $nama;
                            $jurnal->debet = $musawamah->os-$musawamah->saldo_margin-$simpanan;
                            $jurnal->kredit = 0;
                            $jurnal->tanggal_posting = '';
                            $jurnal->keterangan_posting = '0';
                            $jurnal->id_admin = Auth::user()->id; 
                            $jurnal->save();   

                            $musawamah = Musawamah::where('id_member',$idMember)->first();
                            $musawamah->os = $musawamah->os - $musawamah->saldo_margin - $simpanan;
                            $musawamah->saldo_margin = 0;
                            $musawamah->ijaroh = 0;
                            $musawamah->update();

                            $angsuran = $musawamah->os/$tenor;
                            $angsuranBaru = roundUpToAny($angsuran,5000);


                            $musawamah->gol = 1;
                            $musawamah->bulat = 0;
                            $musawamah->run_tenor = 0;
                            $musawamah->ke = 1;
                            $musawamah->maturity_date = $maturity_date;
                            $musawamah->tgl_akad = $tanggal;
                            $musawamah->tenor = $tenor;
                            $musawamah->angsuran = $angsuranBaru;
                            $musawamah->status_app = 'REST_POKOK';
                            $musawamah->update();  

                        }

                    }else{
         
                        $saldo_margin = $musawamah->saldo_margin;

                        $musawamah = Musawamah::where('id_member',$idMember)->first();
                            
                        // Restruktur
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
                        // restruktur

                        $kode=Uuid::uuid4()->getHex();
                        $kode_t=substr($kode,25);
                        $unit=Auth::user()->unit;
                        $kode_t="BU/-".$unit.$kode_t;

                        $jurnal = new TabelTransaksi;
                        $jurnal->unit =  $musawamah->unit; 
                        $jurnal->kode_transaksi = $kode_t;
                        $jurnal->kode_rekening = 1412000;
                        $jurnal->tanggal_transaksi  = $tanggal;
                        $jurnal->jenis_transaksi  = 'Jurnal System';
                        $jurnal->keterangan_transaksi = 'Restrukturisasi Pokok' . ' ' . $id . ' an/ ' . $nama;
                        $jurnal->debet =0;
                        $jurnal->kredit = $musawamah->os;
                        $jurnal->tanggal_posting = '';
                        $jurnal->keterangan_posting = '0';
                        $jurnal->id_admin = Auth::user()->id; 
                        $jurnal->save();
                    
                        $jurnal = new TabelTransaksi;
                        $jurnal->unit =  $musawamah->unit; 
                        $jurnal->kode_transaksi = $kode_t;
                        $jurnal->kode_rekening = 1422000;
                        $jurnal->tanggal_transaksi  = $tanggal;
                        $jurnal->jenis_transaksi  = 'Jurnal System';
                        $jurnal->keterangan_transaksi = 'Restrukturisasi Pokok' . ' ' . $id . ' an/ ' . $nama;
                        $jurnal->debet = $musawamah->saldo_margin;
                        $jurnal->kredit = 0;
                        $jurnal->tanggal_posting = '';
                        $jurnal->keterangan_posting = '0';
                        $jurnal->id_admin = Auth::user()->id; 
                        $jurnal->save();   

                        $jurnal = new TabelTransaksi;
                        $jurnal->unit =  $musawamah->unit; 
                        $jurnal->kode_transaksi = $kode_t;
                        $jurnal->kode_rekening = 1415000;
                        $jurnal->tanggal_transaksi  = $tanggal;
                        $jurnal->jenis_transaksi  = 'Jurnal System';
                        $jurnal->keterangan_transaksi = 'Restrukturisasi Pokok' . ' ' . $id . ' an/ ' . $nama;
                        $jurnal->debet = $musawamah->os - $musawamah->saldo_margin;
                        $jurnal->kredit =0;
                        $jurnal->tanggal_posting = '';
                        $jurnal->keterangan_posting = '0';
                        $jurnal->id_admin = Auth::user()->id; 
                        $jurnal->save();

                        $musawamah = Musawamah::where('id_member',$idMember)->first();
                        $musawamah->os = $musawamah->os - $musawamah->saldo_margin;
                        $musawamah->saldo_margin = 0;
                        $musawamah->ijaroh = 0;
                        $musawamah->update();

                        $angsuran = $musawamah->os/$tenor;
                        $angsuranBaru = roundUpToAny($angsuran,5000);

                        $musawamah->gol = 1;
                        $musawamah->bulat = 0;
                        $musawamah->run_tenor = 0;
                        $musawamah->ke = 1;
                        $musawamah->tgl_akad = $tanggal;
                        $musawamah->tenor = $tenor;
                        $musawamah->maturity_date = $maturity_date;
                        $musawamah->angsuran = $angsuranBaru;
                        $musawamah->status_app = 'REST_POKOK';
                        $musawamah->update();
                    
                    }

                break;
                
                case 'pokokMargin':

                    $musawamah = Musawamah::where('id_member',$idMember)->first();
                    
                    if ($request->simpanan) {
        
                        if ($simpanan != null || $simpanan > 0) {
                            
                            // Restruktur
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
                            // Restruktur
                            
                            $jurnal = new TabelTransaksi;
                            $jurnal->unit =  $musawamah->unit; 
                            $jurnal->kode_transaksi = $kode_t;
                            $jurnal->kode_rekening = 1412000;
                            $jurnal->tanggal_transaksi  = $tanggal;
                            $jurnal->jenis_transaksi  = 'Jurnal System';
                            $jurnal->keterangan_transaksi = 'Restrukturisasi Pokok Margin' . ' ' . $id . ' an/ ' . $nama;
                            $jurnal->debet =0;
                            $jurnal->kredit = $musawamah->os;
                            $jurnal->tanggal_posting = '';
                            $jurnal->keterangan_posting = '0';
                            $jurnal->id_admin = Auth::user()->id; 
                            $jurnal->save();
                        
                            $jurnal = new TabelTransaksi;
                            $jurnal->unit =  $musawamah->unit; 
                            $jurnal->kode_transaksi = $kode_t;
                            $jurnal->kode_rekening = 1422000;
                            $jurnal->tanggal_transaksi  = $tanggal;
                            $jurnal->jenis_transaksi  = 'Jurnal System';
                            $jurnal->keterangan_transaksi = 'Restrukturisasi Pokok Margin' . ' ' . $id . ' an/ ' . $nama;
                            $jurnal->debet = $musawamah->saldo_margin;
                            $jurnal->kredit = 0;
                            $jurnal->tanggal_posting = '';
                            $jurnal->keterangan_posting = '0';
                            $jurnal->id_admin = Auth::user()->id; 
                            $jurnal->save();   
                            
                            $jurnal = new TabelTransaksi;
                            $jurnal->unit =  $musawamah->unit; 
                            $jurnal->kode_transaksi = $kode_t;
                            $jurnal->kode_rekening = 2891000;
                            $jurnal->tanggal_transaksi  = $tanggal;
                            $jurnal->jenis_transaksi  = 'Jurnal System';
                            $jurnal->keterangan_transaksi = 'Restrukturisasi Pokok Margin' . ' ' . $id . ' an/ ' . $nama;
                            $jurnal->debet = $simpanan;
                            $jurnal->kredit = 0;
                            $jurnal->tanggal_posting = '';
                            $jurnal->keterangan_posting = '0';
                            $jurnal->id_admin = Auth::user()->id; 
                            $jurnal->save();   

                            $jurnal = new TabelTransaksi;
                            $jurnal->unit =  $musawamah->unit; 
                            $jurnal->kode_transaksi = $kode_t;
                            $jurnal->kode_rekening = 1415000;
                            $jurnal->tanggal_transaksi  = $tanggal;
                            $jurnal->jenis_transaksi  = 'Jurnal System';
                            $jurnal->keterangan_transaksi = 'Restrukturisasi Pokok Margin' . ' ' . $id . ' an/ ' . $nama;
                            $jurnal->debet = $musawamah->os-$simpanan;
                            $jurnal->kredit =0;
                            $jurnal->tanggal_posting = '';
                            $jurnal->keterangan_posting = '0';
                            $jurnal->id_admin = Auth::user()->id; 
                            $jurnal->save();

                            $jurnal = new TabelTransaksi;
                            $jurnal->unit =  $musawamah->unit; 
                            $jurnal->kode_transaksi = $kode_t;
                            $jurnal->kode_rekening = 1425000;
                            $jurnal->tanggal_transaksi  = $tanggal;
                            $jurnal->jenis_transaksi  = 'Jurnal System';
                            $jurnal->keterangan_transaksi = 'Restrukturisasi Pokok Margin' . ' ' . $id . ' an/ ' . $nama;
                            $jurnal->debet =0;
                            $jurnal->kredit = $musawamah->saldo_margin;
                            $jurnal->tanggal_posting = '';
                            $jurnal->keterangan_posting = '0';
                            $jurnal->id_admin = Auth::user()->id; 
                            $jurnal->save();                            

                            $musawamah = Musawamah::where('id_member',$idMember)->first();

                            $angsuran = $musawamah->os/$tenor;
                            $angsuranBaru = roundUpToAny($angsuran,5000);

                            $ijaroh = round($musawamah->saldo_margin/$tenor);
    
                            $musawamah->gol = 1;
                            $musawamah->bulat = 0;
                            $musawamah->run_tenor = 0;
                            $musawamah->ke = 1;
                            $musawamah->tgl_akad = $tanggal;
                            $musawamah->tenor = $tenor;
                            $musawamah->maturity_date = $maturity_date;
                            $musawamah->angsuran = $angsuranBaru;
                            $musawamah->ijaroh =$ijaroh;
                            $musawamah->status_app = 'REST_POKOK_MARGIN';
                            $musawamah->update();

                        }else {

                            $musawamah = Musawamah::where('id_member',$idMember)->first();
                            
                            // Restrktur
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
                            // Restruktur
                            
                            $jurnal = new TabelTransaksi;
                            $jurnal->unit =  $musawamah->unit; 
                            $jurnal->kode_transaksi = $kode_t;
                            $jurnal->kode_rekening = 1412000;
                            $jurnal->tanggal_transaksi  = $tanggal;
                            $jurnal->jenis_transaksi  = 'Jurnal System';
                            $jurnal->keterangan_transaksi = 'Restrukturisasi Pokok Margin' . ' ' . $id . ' an/ ' . $nama;
                            $jurnal->debet =0;
                            $jurnal->kredit = $musawamah->os;
                            $jurnal->tanggal_posting = '';
                            $jurnal->keterangan_posting = '0';
                            $jurnal->id_admin = Auth::user()->id; 
                            $jurnal->save();
                        
                            $jurnal = new TabelTransaksi;
                            $jurnal->unit =  $musawamah->unit; 
                            $jurnal->kode_transaksi = $kode_t;
                            $jurnal->kode_rekening = 1422000;
                            $jurnal->tanggal_transaksi  = $tanggal;
                            $jurnal->jenis_transaksi  = 'Jurnal System';
                            $jurnal->keterangan_transaksi = 'Restrukturisasi Pokok Margin' . ' ' . $id . ' an/ ' . $nama;
                            $jurnal->debet = $musawamah->saldo_margin;
                            $jurnal->kredit = 0;
                            $jurnal->tanggal_posting = '';
                            $jurnal->keterangan_posting = '0';
                            $jurnal->id_admin = Auth::user()->id; 
                            $jurnal->save();   
                            
                            $jurnal = new TabelTransaksi;
                            $jurnal->unit =  $musawamah->unit; 
                            $jurnal->kode_transaksi = $kode_t;
                            $jurnal->kode_rekening = 1415000;
                            $jurnal->tanggal_transaksi  = $tanggal;
                            $jurnal->jenis_transaksi  = 'Jurnal System';
                            $jurnal->keterangan_transaksi = 'Restrukturisasi Pokok Margin' . ' ' . $id . ' an/ ' . $nama;
                            $jurnal->debet = $musawamah->os-$simpanan;
                            $jurnal->kredit =0;
                            $jurnal->tanggal_posting = '';
                            $jurnal->keterangan_posting = '0';
                            $jurnal->id_admin = Auth::user()->id; 
                            $jurnal->save();

                            $jurnal = new TabelTransaksi;
                            $jurnal->unit =  $musawamah->unit; 
                            $jurnal->kode_transaksi = $kode_t;
                            $jurnal->kode_rekening = 1425000;
                            $jurnal->tanggal_transaksi  = $tanggal;
                            $jurnal->jenis_transaksi  = 'Jurnal System';
                            $jurnal->keterangan_transaksi = 'Restrukturisasi Pokok Margin' . ' ' . $id . ' an/ ' . $nama;
                            $jurnal->debet =0;
                            $jurnal->kredit = $musawamah->saldo_margin;
                            $jurnal->tanggal_posting = '';
                            $jurnal->keterangan_posting = '0';
                            $jurnal->id_admin = Auth::user()->id; 
                            $jurnal->save();   

                            $musawamah = Musawamah::where('id_member',$idMember)->first();
                            $musawamah->os = $musawamah->os - $simpanan;
                            $musawamah->update();
                            
                            $angsuran = $musawamah->os/$tenor;
                            $angsuranBaru = roundUpToAny($angsuran,5000);

                            $ijaroh = round($musawamah->saldo_margin/$tenor);
                            
                            $musawamah->gol = 1;
                            $musawamah->bulat = 0;
                            $musawamah->run_tenor = 0;
                            $musawamah->ke = 1;
                            $musawamah->maturity_date = $maturity_date;
                            $musawamah->tgl_akad = $tanggal;
                            $musawamah->angsuran = $angsuranBaru;
                            $musawamah->ijaroh = $ijaroh;
                            $musawamah->tenor = $tenor;
                            $musawamah->status_app = 'REST_POKOK_MARGIN';
                            $musawamah->update();
                            
                            
                            $kode=Uuid::uuid4()->getHex();
                            $kode_t=substr($kode,25);
                            $unit=Auth::user()->unit;
                            $kode_t="BU/-".$unit.$kode_t;

                            $list_simpanan = new ListToko;
                            $list_simpanan->buss_date = $tanggal;
                            $list_simpanan->norek   = $idMember;
                            $list_simpanan->unit = $musawamah->unit;
                            $list_simpanan->id_member =$idMember;
                            $list_simpanan->code_kel =$musawamah->code_kel;
                            $list_simpanan->kredit = 0;
                            $list_simpanan->type ='02';
                            $list_simpanan->DEBIT = $simpanan;
                            $list_simpanan->userid =Auth::user()->id;
                            $list_simpanan->ket = 'Restrukturisasi Pokok Dari Titipan';
                            $list_simpanan->kode_transaksi = $kode_t;
                            $list_simpanan->tgl_input = $tanggal;
                            $list_simpanan->cao =$musawamah->cao;
                            $list_simpanan->save();     
                            
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
                            
                            $jurnal = new TabelTransaksi;
                            $jurnal->unit =  $musawamah->unit; 
                            $jurnal->kode_transaksi = $kode_t;
                            $jurnal->kode_rekening = 2891000;
                            $jurnal->tanggal_transaksi  = $tanggal;
                            $jurnal->jenis_transaksi  = 'Jurnal System';
                            $jurnal->keterangan_transaksi = 'Restrukturisasi Pokok Dari Titipan' . ' ' . $id . ' an/ ' . $nama;
                            $jurnal->debet = $simpanan;
                            $jurnal->kredit =0;
                            $jurnal->tanggal_posting = '';
                            $jurnal->keterangan_posting = '0';
                            $jurnal->id_admin = Auth::user()->id; 
                            $jurnal->save();  
                        }

                    }else{

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

                        $kode=Uuid::uuid4()->getHex();
                        $kode_t=substr($kode,25);
                        $unit=Auth::user()->unit;
                        $kode_t="BU/-".$unit.$kode_t;
                        
                        $jurnal = new TabelTransaksi;
                        $jurnal->unit =  $musawamah->unit; 
                        $jurnal->kode_transaksi = $kode_t;
                        $jurnal->kode_rekening = 1412000;
                        $jurnal->tanggal_transaksi  = $tanggal;
                        $jurnal->jenis_transaksi  = 'Jurnal System';
                        $jurnal->keterangan_transaksi = 'Restrukturisasi Pokok Margin' . ' ' . $id . ' an/ ' . $nama;
                        $jurnal->debet =0;
                        $jurnal->kredit = $musawamah->os;
                        $jurnal->tanggal_posting = '';
                        $jurnal->keterangan_posting = '0';
                        $jurnal->id_admin = Auth::user()->id; 
                        $jurnal->save();
                    
                        $jurnal = new TabelTransaksi;
                        $jurnal->unit =  $musawamah->unit; 
                        $jurnal->kode_transaksi = $kode_t;
                        $jurnal->kode_rekening = 1422000;
                        $jurnal->tanggal_transaksi  = $tanggal;
                        $jurnal->jenis_transaksi  = 'Jurnal System';
                        $jurnal->keterangan_transaksi = 'Restrukturisasi Pokok Margin' . ' ' . $id . ' an/ ' . $nama;
                        $jurnal->debet = $musawamah->saldo_margin;
                        $jurnal->kredit = 0;
                        $jurnal->tanggal_posting = '';
                        $jurnal->keterangan_posting = '0';
                        $jurnal->id_admin = Auth::user()->id; 
                        $jurnal->save();   
                        
                        $jurnal = new TabelTransaksi;
                        $jurnal->unit =  $musawamah->unit; 
                        $jurnal->kode_transaksi = $kode_t;
                        $jurnal->kode_rekening = 1415000;
                        $jurnal->tanggal_transaksi  = $tanggal;
                        $jurnal->jenis_transaksi  = 'Jurnal System';
                        $jurnal->keterangan_transaksi = 'Restrukturisasi Pokok Margin' . ' ' . $id . ' an/ ' . $nama;
                        $jurnal->debet = $musawamah->os-$simpanan;
                        $jurnal->kredit =0;
                        $jurnal->tanggal_posting = '';
                        $jurnal->keterangan_posting = '0';
                        $jurnal->id_admin = Auth::user()->id; 
                        $jurnal->save();

                        $jurnal = new TabelTransaksi;
                        $jurnal->unit =  $musawamah->unit; 
                        $jurnal->kode_transaksi = $kode_t;
                        $jurnal->kode_rekening = 1425000;
                        $jurnal->tanggal_transaksi  = $tanggal;
                        $jurnal->jenis_transaksi  = 'Jurnal System';
                        $jurnal->keterangan_transaksi = 'Restrukturisasi Pokok Margin' . ' ' . $id . ' an/ ' . $nama;
                        $jurnal->debet =0;
                        $jurnal->kredit = $musawamah->saldo_margin;
                        $jurnal->tanggal_posting = '';
                        $jurnal->keterangan_posting = '0';
                        $jurnal->id_admin = Auth::user()->id; 
                        $jurnal->save();   

                        $musawamah = Musawamah::where('id_member',$idMember)->first();
                        
                        $angsuran = $musawamah->os/$tenor;
                        $angsuranBaru = roundUpToAny($angsuran,5000);

                        $ijaroh = round($musawamah->saldo_margin/$tenor);
                        
                        $musawamah->gol = 1;
                        $musawamah->bulat = 0;
                        $musawamah->run_tenor = 0;
                        $musawamah->maturity_date = $maturity_date;
                        $musawamah->ke = 1;
                        $musawamah->tgl_akad = $tanggal;
                        $musawamah->angsuran = $angsuranBaru;
                        $musawamah->ijaroh = $ijaroh;
                        $musawamah->tenor = $tenor;
                        $musawamah->status_app = 'REST_POKOK_MARGIN';
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

        $data = array(
            "idMember" => $idMember,
        );
        return response()->json($data);

    }

    public function print($id){

        $musawamah = Musawamah::findOrFail($id);
        $noAkad = $musawamah->unit.'-'.$id;
        $pdf = PDF::loadView('restruktur.adendum',compact('id','noAkad'));  
        return $pdf->stream();

    }
}
