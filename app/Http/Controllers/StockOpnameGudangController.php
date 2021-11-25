<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Produk;
use App\ProdukDetail;
use Illuminate\Support\Facades\Validator;
use Auth;
use DB;
use Session;
use Redirect;
use App\ProdukSo;
use App\ParamTgl;
use App\Exports\ProdukSoExport;
use App\Imports\ProdukSoImport;
use Maatwebsite\Excel\Facades\Excel;


class StockOpnameGudangController extends Controller{


    public function index(){
        
        $param = ParamTgl::where('nama_param_tgl','STOK_OPNAME')->first();
        $tanggalSo = $param->param_tgl;
        $param_tgl_unit = ParamTgl::where('unit',Auth::user()->unit)->first();
        $tanggalNow = $param_tgl_unit->param_tgl;

        if ($tanggalSo == $tanggalNow) {
            
        $produk = Produk::where('unit',Auth::user()->unit)
                        ->orderBy('stok','desc')
                        ->get();

        return view('stock_opname/index',compact('produk'));

        }else {
            
            return Redirect::route()->withErrors(['Belum Waktunya Stok Opname']);;
        
        }
        
    }

	public function export_excel(){

		Excel::create('File Upload', function($excel) {

            $excel->sheet('Excel sheet', function($sheet) {

                $data = ProdukSo::where('unit',Auth::user()->unit)->first();
                $sheet->loadView('export.upload_so')->withKey($data);
    
            });
    
        })->export('csv');
    }
    
    public function import_excel(Request $request){

        if($request->hasFile('import_excel')){
            
            $rules = 'csv';

            $input =  $request['import_excel']->getClientOriginalExtension();

            if ($rules !== $input) {
                return back()->with(['error' => 'Extension harus .csv !']);
            }

            $path = $request->file('import_excel')->getRealPath();
                
            $data = \Excel::load($path)->get();
            
            if($data->count()){

                foreach ($data as $key => $value) {
                    $arr[] = ['kode_produk' => $value->kode_produk, 'qty' => $value->qty,'unit' => Auth::user()->unit];
                }

                if(!empty($arr)){
                    \DB::table('produk_so')->insert($arr);
                    return back()->with(['success' => 'Upload Berhasil !']);
                }else {
                    return back()->with(['error' => 'Upload Gagal !']);
                }
            }
        }

    }

    public function listData(){
        
        $produk_so = ProdukSo:: select('produk_so.*','produk.nama_produk')
                                ->leftJoin('produk','produk.kode_produk','produk_so.kode_produk')
                                ->where('produk_so.unit',Auth::user()->unit)
                                ->where('produk.unit',Auth::user()->unit)
                                ->where('status',null)
                                ->get();
        
        $no = 0;
        $data = array();
        foreach ($produk_so as $list) {
            $no++;
            $row = array();
            $row[] = $no;
            $row[] = $list->kode_produk;
            $row[] = $list->nama_produk;
            $row[] = $list->qty;
            $data[] = $row;
        }
        
        $output = array("data" => $data);
        return response()->json($output);
    }


    public function proses(){
        
        try {

            DB::beginTransaction();

            $produk_so = ProdukSo::where('unit',Auth::user()->unit)->where('status',null)->get();
             
            foreach ($produk_so as $value) {
                
                $master_produk = Produk::where('kode_produk',$value->kode_produk)->where('unit',$value->unit)->first();

                if ($master_produk->stok == $value->qty) {
                    
                    $data = ProdukSo::where('kode_produk',$value->kode_produk)->where('unit',Auth::user()->unit)->where('status',null)->first();
                    $data->stok_system = $master_produk->stok;
                    $data->status = 2;
                    $data->update();

                }else {
                    
                    $data = ProdukSo::where('kode_produk',$value->kode_produk)->where('unit',Auth::user()->unit)->where('status',null)->first();
                    $data->stok_system = $master_produk->stok;
                    $data->status = 1;
                    $data->update();
                }

            }
            
            DB::commit();
            return back()->with(['success' => 'Proses SO Berhasil !']);

        }catch(\Exception $e){
           
            DB::rollback();
            
            return back()->with(['error' => $e->getmessage()]);
        }

    }
}

