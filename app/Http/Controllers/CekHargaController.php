<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
use Auth;
use App\Produk;
use App\ParamKenaikan;


class CekHargaController extends Controller
{
    public function index(){

        $tenor = ParamKenaikan::orderBy('pekan','asc')->get();
        return view('cek_harga/index',compact('tenor'));

    }

    public function listData(){

        $produk = Produk::where('unit',Auth::user()->unit)->get();
        $tenor = ParamKenaikan::orderBy('pekan','asc')->get();
        $data = array();
        

        foreach($produk as $list){
            
            $row = array();
            
            $row[] = $list->kode_produk;
            $row[] = $list->nama_produk;
            $row[] = $list->stok;
                
            foreach ($tenor as $value) {
                
                if ($value->kenaikan > 0) {
                    
                    $kenaikan = $list->harga_jual * $value->kenaikan / 100;
                    
                }else {
                    
                    $kenaikan = 0;
                
                }
                
                $harga = $list->harga_jual + $kenaikan;

                $row[] = number_format($harga);
            }
            
            $row[] = number_format($list->harga_jual_insan);
          
            $data[] = $row;
        
        }
   
        $output = array("data" => $data);
        return response()->json($output);

    }
}
