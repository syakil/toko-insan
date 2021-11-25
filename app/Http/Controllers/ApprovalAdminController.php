<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Produk;
use App\ProdukDetail;
use App\ProdukSo;
use Auth;
use App\Branch;
use App\ParamTgl;

class ApprovalAdminController extends Controller
{
    public function index(){
        return view('approve_admin/index');
    }


    public function listData(){
        // $data_gudang = Branch::where('nama_toko','like','%GUDANG%')->select('kode_toko')->get();
        // // dd($data_gudang);
        
        // foreach ($data_gudang as $gudang) {
        //     dd($gudang->kode_toko);
        //     printf("%s",$gudang->kode_toko);die;
        // }

        $produk = ProdukSo::where('produk_so.unit',Auth::user()->unit)
                        ->where('status','selisih')
                        ->where('produk.unit',Auth::user()->unit)
                        ->where('approval',null)
                        ->where('produk_so.keterangan','!=','dihapus')
                        ->leftJoin('produk','produk.kode_produk','produk_so.kode_produk')
                        ->leftJoin('branch','branch.kode_toko','produk_so.unit')
                        ->select(\DB::raw('SUM(stok_opname) as so,produk_so.*,branch.nama_toko,produk.kode_produk,produk.nama_produk'))
                        ->groupBy('produk_so.kode_produk')
                        ->groupBy('tanggal_so')
                        ->get();

        // dd($produk);
        $data = array();
        $no = 0;
        foreach ($produk as $detail ) {
            $row = array();
            $no++;
            $row [] = '<input type="checkbox" name="kode[]" id="kode" class="kode" onclick="check()" value="'.$detail->id_produk_so.'">';
            $row [] = $no;
            $row [] = $detail->kode_produk;
            $row [] = $detail->nama_produk;
            $row [] = $detail->tanggal_so;
            $row [] = $detail->stok;
            $row [] = $detail->so;
            $row [] = $detail->nama_toko;
            $data [] = $row; 
        }

        $output = array("data" => $data);
        return response()->json($output);
    }


    public function store(Request $request){
        
        $data = $request->kode;
        $param_tgl = ParamTgl::where('nama_param_tgl','STOK_OPNAME')->first();
        $now = $param_tgl->param_tgl;

        foreach ($data as $id ) {

            $data_produk = ProdukSo::find($id);
            $get_produk_so = ProdukSo::where('kode_produk',$data_produk->kode_produk)->where('unit',$data_produk->unit)->where('tanggal_so',$now)->get();
                        
            $master_produk = Produk::where('kode_produk',$data_produk->kode_produk)->where('unit',$data_produk->unit)->first();
            $sum_detail = ProdukDetail::where('kode_produk',$data_produk->kode_produk)->where('unit',$data_produk->unit)->sum('stok_detail');
            
            $master_produk->stok = $sum_detail;
            $master_produk->update();

            foreach ($get_produk_so as $produk_so ) {
                
                $produk_so->approval = 'A';
                $produk_so->update();

            }

            $get_produk_detail = ProdukDetail::where('kode_produk',$data_produk->kode_produk)->where('unit',$data_produk->unit)->get();

            foreach ($get_produk_detail as $produk_detail ) {
                
                $produk_detail->status = null;
                $produk_detail->update();

            }
            
        }


        return back();
    }
}

