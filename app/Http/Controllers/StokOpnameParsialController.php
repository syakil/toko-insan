<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Produk;
use App\ProdukSelisih;
use App\Branch;
use App\StokOpnameParsial;
use Auth;
use DB;
use Redirect;

class StokOpnameParsialController extends Controller{

    public function index(){

        $data_produk = StokOpnameParsial::leftJoin('produk','produk.kode_produk','stok_opname_parsial.kode_produk')->where('produk.unit',Auth::user()->unit)->where('stok_opname_parsial.unit',Auth::user()->unit)->where('stok_opname_parsial.status',1)->get();
        $nomer = 1;
        return view('stok_opname_parsial.index',compact('data_produk','nomer'));

    }


    public function update(Request $request,$id){

        $data = StokOpnameParsial::where('id_produk_so',$id)->first();
        $data->qty = $request->value;
        $data->update();

    }

    public function store(Request $request){

        $data = DB::table('stok_opname_parsial')->where('unit',Auth::user()->unit)->where('status',1)->update(['status' => 2,'tanggal_so' => date('Y-m-d')]);
        return redirect()->route('stok_opname_parsial.index')->with(['success' => 'Selamat Stok Opname Parsial Berhasil !']);

    }



}
