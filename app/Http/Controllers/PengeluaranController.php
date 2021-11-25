<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Pengeluaran;
use App\Coa;
use App\Branch;
use Auth;
use App\TabelTransaksi;
use Yajra\Datatables\Datatables;
use Ramsey\Uuid\Uuid;

class PengeluaranController extends Controller
{
    public function index()
    {
      $coa = Coa::all()->where('gr_head', '=', '1300000');
    
       return view('pengeluaran.index', compact('coa')); 

    }

    public function listData()
    {
    
      $pengeluaran = Pengeluaran::orderBy('id_pengeluaran', 'desc')->get();
      $no = 0;
      $data = array();
      foreach($pengeluaran as $list){
         $no ++;
         $row = array();
         $row[] = $no;
         $row[] = tanggal_indonesia(substr($list->created_at, 0, 10), false);
         $row[] = $list->jenis_pengeluaran;
         $row[] = $list->jenis_transaksi;         
         $row[] = "Rp. ".format_uang($list->nominal);
         $row[] = '<div class="btn-group">
                    <a onclick="editForm('.$list->id_pengeluaran.')" class="btn btn-primary btn-sm"><i class="fa fa-pencil"></i></a>
                    <a onclick="deleteData('.$list->id_pengeluaran.')" class="btn btn-danger btn-sm"><i class="fa fa-trash"></i></a></div>';
         $data[] = $row;
      }

      return Datatables::of($data)->escapeColumns([])->make(true);
    }

    public function store(Request $request)
    {
      //$now = \Carbon\Carbon::now();
$param_tgl = \App\ParamTgl::where('nama_param_tgl','tanggal_transaksi')->where('unit',Auth::user()->id)->first();
      $now = $param_tgl->param_tgl;

      $kode=Uuid::uuid4()->getHex();
      $kode_t=substr($kode,25);
      $unit=Auth::user()->unit;
      $kode_t="BU/-".$unit.$kode_t;
      $branch_coa_aktiva_user = Branch::find(Auth::user()->unit);
      $coa_aktiva_user=$branch_coa_aktiva_user->aktiva;

        $pengeluaran = new Pengeluaran;
        $pengeluaran->jenis_pengeluaran   = $request['ket'];
$pengeluaran->jenis_pengeluaran   = $unit;
        $pengeluaran->jenis_transaksi   = $request['trns'];        
        $pengeluaran->nominal = $request['nominal'];
        $pengeluaran->save();
        
        $jurnal = new TabelTransaksi;
         $jurnal->unit =  Auth::user()->unit; 
         $jurnal->kode_transaksi = $kode_t;
         $jurnal->kode_rekening = 2500000;
         $jurnal->tanggal_transaksi = $now;
         $jurnal->jenis_transaksi  = 'Jurnal System';
         $jurnal->keterangan_transaksi = 'Setoran Toko ';
         $jurnal->debet = $request['nominal'];
         $jurnal->kredit = 0;
         $jurnal->tanggal_posting = ' ';
         $jurnal->keterangan_posting = '0';
         $jurnal->id_admin = Auth::user()->id; 
         $jurnal->save();

         $jurnal = new TabelTransaksi;
         $jurnal->unit =  Auth::user()->unit; 
         $jurnal->kode_transaksi =$kode_t;
         $jurnal->kode_rekening = 1120000;
         $jurnal->tanggal_transaksi = $now;
         $jurnal->jenis_transaksi  = 'Jurnal System';
         $jurnal->keterangan_transaksi = 'Setoran Toko ';
         $jurnal->debet =0;
         $jurnal->kredit = $request['nominal'];
         $jurnal->tanggal_posting = ' ';
         $jurnal->keterangan_posting = '0';
         $jurnal->id_admin = Auth::user()->id; 
         $jurnal->save();

         $jurnal = new TabelTransaksi;
         $jurnal->unit =  Auth::user()->unit; 
         $jurnal->kode_transaksi = $kode_t;
         $jurnal->kode_rekening =("1010-".$request['coa']);
         $jurnal->tanggal_transaksi = $now;
         $jurnal->jenis_transaksi  = 'Jurnal System';
         $jurnal->keterangan_transaksi = 'Setoran Toko ';
         $jurnal->debet = $request['nominal'];
         $jurnal->kredit = 0;
         $jurnal->tanggal_posting = ' ';
         $jurnal->keterangan_posting = '0';
         $jurnal->id_admin = Auth::user()->id; 
         $jurnal->save();

         $jurnal = new TabelTransaksi;
         $jurnal->unit =  Auth::user()->unit; 
         $jurnal->kode_transaksi =$kode_t;
         $jurnal->kode_rekening = $coa_aktiva_user;
         $jurnal->tanggal_transaksi = $now;
         $jurnal->jenis_transaksi  = 'Jurnal System';
         $jurnal->keterangan_transaksi = 'Setoran Toko';
         $jurnal->debet =0;
         $jurnal->kredit = $request['nominal'];
         $jurnal->tanggal_posting = ' ';
         $jurnal->keterangan_posting = '0';
         $jurnal->id_admin = Auth::user()->id; 
         $jurnal->save();       
        
        return view('pengeluaran.index');


    }

    public function edit($id)
    {
      $pengeluaran = Pengeluaran::find($id);
      echo json_encode($pengeluaran);
    }

    public function update(Request $request, $id)
    {
        $pengeluaran = Pengeluaran::find($id);
        $pengeluaran->jenis_pengeluaran   = $request['ket'];
        $pengeluaran->jenis_transaksi   = $request['trns'];      
        $pengeluaran->nominal = $request['nominal'];
        $pengeluaran->update();
    }

    public function destroy($id)
    {
        $pengeluaran = Pengeluaran::find($id);
        $pengeluaran->delete();
    }

}
