<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;
// buat table jurnal_umum untuk mendapatkan kode_jurnal
use App\JurnalUmum;
use App\TabelTransaksi;
use Auth;
use Session;
use Illuminate\Support\Facades\Redirect;
// buat table COA untuk mendapatkan kode_coa/rekening
use App\Coa;
use DB;

class JurnalUmumPoController extends Controller
{
    public function index(){
        if (Session::has('kode_transaksi')) {
            $kode_transaksi = Session::get('kode_transaksi');            
            
        }else{
            $getid = JurnalUmum::getId();
            foreach($getid as $value)
            $kode_lama = $value->id_jurnal;
            $kode_baru = $kode_lama + 1;
            $kode = sprintf("%03s", $kode_baru);
            $tggl = date('Ymd');
            
            $kode_transaksi = 'BU/'. '-' . Auth::user()->unit . '-' . $tggl . '-' . $kode;

            
            $insert_kode = new JurnalUmum;
            $insert_kode->kode_jurnal = $kode_transaksi;
            $insert_kode->save();

            session(['kode_transaksi' => $kode_transaksi]);    
        }
        $list_jurnal = TabelTransaksi::where('kode_transaksi',$kode_transaksi)
                                        ->get();
        $nomer = 1;

        $debet = TabelTransaksi::where('kode_transaksi',$kode_transaksi)->sum('debet');
            
        $kredit = TabelTransaksi::where('kode_transaksi',$kode_transaksi)->sum('kredit');
        
        $rekening = Coa::all();


        return view('jurnal_umum_po.index',['kode_transaksi'=>$kode_transaksi,'list_jurnal'=>$list_jurnal,'nomer'=>$nomer,'debet'=>$debet,'kredit'=>$kredit,'rekening'=>$rekening]);
    }

    public function autocomplete(Request $request){
        if ($request->has('q')) {
            $cari = $request->q;
            $data = Coa::where('kode_coa', 'LIKE', '%$cari%')->first();
            return response()->json($data);
        }
    }

    public function create(Request $request){
        
        $this->validate($request,[
            'keterangan' => 'required',
            'tanggal_jurnal' => 'required',
            'nominal' => 'required|numeric'
         ]);


        if ($request->transaksi == "debit") {

            $debet_count = DB::table('tabel_transaksi')
                        ->where('debet', '!=', 0)
                        ->where('kode_transaksi',$request->kode_transaksi)
                        ->count();
                // dd($debet_count);
            if ($debet_count >= 1) {            
                return Redirect::route('jurnal_umum_po.index')->with(['error' => 'Transaksi Debit Hanya 1x']);
            }
            
            $insert_tabel_transaksi = new TabelTransaksi;
            $insert_tabel_transaksi->unit = Auth::user()->unit;
            $insert_tabel_transaksi->kode_transaksi = $request->kode_transaksi;
            $insert_tabel_transaksi->kode_rekening = $request->rekening;
            $insert_tabel_transaksi->tanggal_transaksi = $request->tanggal_jurnal;
            $insert_tabel_transaksi->jenis_transaksi = "Jurnal Manual";
            $insert_tabel_transaksi->keterangan_transaksi = $request->keterangan;
            $insert_tabel_transaksi->debet = $request->nominal;
            $insert_tabel_transaksi->kredit = 0;
            $insert_tabel_transaksi->tanggal_posting = date('Y-m-d');
            $insert_tabel_transaksi->keterangan_posting = 0;
            $insert_tabel_transaksi->id_admin = Auth::user()->id;
            $insert_tabel_transaksi->save();

            }else {
                
                $insert_tabel_transaksi = new TabelTransaksi;
                $insert_tabel_transaksi->unit = Auth::user()->unit;
                $insert_tabel_transaksi->kode_transaksi = $request->kode_transaksi;
                $insert_tabel_transaksi->kode_rekening = $request->rekening;
                $insert_tabel_transaksi->tanggal_transaksi = $request->tanggal_jurnal;
                $insert_tabel_transaksi->jenis_transaksi = "Jurnal Manual";
                $insert_tabel_transaksi->keterangan_transaksi = $request->keterangan;
                $insert_tabel_transaksi->debet = 0;
                $insert_tabel_transaksi->kredit = $request->nominal;
                $insert_tabel_transaksi->tanggal_posting = date('Y-m-d');
                $insert_tabel_transaksi->keterangan_posting = 0;
                $insert_tabel_transaksi->id_admin = Auth::user()->id;
                $insert_tabel_transaksi->id_admin = Auth::user()->id;
                $insert_tabel_transaksi->save();
            }

            return Redirect::route('jurnal_umum_po.index');
        }
        
        public function update_debet(Request $request,$id){    
    
            $detail = TabelTransaksi::where('id_transaksi',$id);
            $detail->update(['debet'=>$request->value]);
        }

        public function update_kredit(Request $request,$id){    
    
            $detail = TabelTransaksi::where('id_transaksi',$id);
            $detail->update(['kredit'=>$request->value]);
        }
        
        public function destroy($id){
            $detail = TabelTransaksi::find($id);
            $detail->delete();
            return Redirect::route('jurnal_umum_po.index');
        }

        public function approve(){
            Session::forget('kode_transaksi');
            return Redirect::route('jurnal_umum_po.index');
        }

}
