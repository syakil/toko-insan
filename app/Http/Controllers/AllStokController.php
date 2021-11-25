<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Produk;
use App\ProdukDetail;
use App\Branch;
use Auth;
use DB;

class AllStokController extends Controller
{
    public function index(){

        $branch = Branch::get();

        return view('all_stok.index',compact('branch'));

    }


    public function listData($unit){

        session(['unit'=>$unit]);
        
        $produk = Produk::where('unit', '=',  $unit)
        ->get();

        $no = 0;
        $data = array();
        foreach($produk as $list){
        $no ++;
        $row = array();
        $row[] = $no;
        $row[] = $list->kode_produk;
        $row[] = '<a href="'. route('all_stok.detail',$list->kode_produk) .'">'.$list->nama_produk.'</a>';
        $row[] = $list->stok;
        $data[] = $row;
        }
        //   dd($data);
        $output = array("data" => $data);
        return response()->json($output);
    
    }

    public function detail($id){

        $unit = session('unit');

        $produk = ProdukDetail::where('kode_produk',$id)
                        ->where('unit', '=',  $unit)
                        ->get();
                        
        $nama = Produk::where('kode_produk',$id)->first();
        return view('all_stok/detail_stock',['produk'=>$produk,'nama'=>$nama]);
    
    }


    public function delete($id){
        
        $unit = session('unit');

        // dd($id);
        $detail = ProdukDetail::where('id_produk_detail',$id)->first();
        $produk = Produk::where('kode_produk',$detail->kode_produk)
        ->where('unit',$unit)
        ->first();
        // dd($produk);
        $produk->stok = $produk->stok - $detail->stok_detail;
        $produk->update(); 
        
        
        $detail->delete();

        return back();

    }



    public function store(Request $request){
        
        $unit = session('unit');
        // dd($unit);
        $produk_detail = new ProdukDetail;
        $produk_detail->kode_produk = $request->barcode;
        $produk_detail->nama_produk = $request->nama;
        $produk_detail->unit = $unit;
        $produk_detail->stok_detail = $request->stok;
        $produk_detail->expired_date = $request->tanggal;
        $produk_detail->save();

        $stok = ProdukDetail::where('kode_produk',$request->barcode)
                        ->where('unit',$unit)
                        ->sum('stok_detail');

        $update_stok = Produk::where('kode_produk',$request->barcode)
                            ->where('unit',$unit)
                            ->first();

        $update_stok->stok= $stok;
        $update_stok->update();
    
        return back();
    }
}
