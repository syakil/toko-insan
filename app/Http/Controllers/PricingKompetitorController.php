<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Produk;
use App\Branch;
use DB;
use Auth;


class PricingKompetitorController extends Controller{
    
    public function index(){

        return view('pricing_kompetitor.index');

    }


    public function listData(){

        $produk = Produk::where('produk.unit',auth::user()->unit)
                        ->get();

        $no = 0;
        $data = array();
        foreach($produk as $list){
            $no ++;
            $row = array();
            $row[] = $no;
            $row[] = $list->kode_produk;
            $row[] = $list->nama_produk;
            $row[] = $list->harga_jual;
            $row[] = $list->harga_indo;
            $row[] = $list->harga_alfa;
            $row[] = $list->harga_olshop;
            $row[] = $list->harga_grosir;
            $row[] = '
            <a href="'. route('pricing_kompetitor.edit', $list->id_produk).'" class="btn btn-sm btn-warning"><i class="fa fa-pencil"></i></a>';
            $data[] = $row;
        }
    
        $output = array("data" => $data);
        return response()->json($output);

    }

    public function edit($id){

        $produk = Produk::find($id);

        return view('pricing_kompetitor.edit',compact('produk'));

    }


    public function update(Request $request){

        try {
        
            DB::beginTransaction();
         
            $id = $request->id;

            $produk = Produk::find($id);
            $unit = array();
            $kode_toko = Branch::where('kode_gudang',Auth::user()->unit)->get();
            
            foreach ($kode_toko as $key) {
                $unit[]=$key->kode_toko;
            }

            $all_produk = Produk::where('kode_produk',$produk->kode_produk)->whereIn('unit',$unit)->get();

            foreach ($all_produk as $value) {
                
                $value->harga_indo = $request->harga_indo;
                $value->harga_alfa = $request->harga_alfa;
                $value->harga_olshop = $request->harga_olshop;
                $value->harga_grosir = $request->harga_grosir;
                $value->update();

            }

            DB::commit();

            return redirect()->route('pricing_kompetitor.index')->with(['success' => 'Produk Berhasil di Update']);
        
        }catch(\Exception $e) {

            DB::rollback();
            return back()->with(['error' => $e->getmessage()]);
        
        }
    }


}
