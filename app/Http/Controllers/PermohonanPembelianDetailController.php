<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
use Auth;
use App\Produk;
use App\Supplier;

class PermohonanPembelianDetailController extends Controller
{
    
    public function index(){
        
        $produk = Produk::where('unit',Auth::user()->unit)->get();
        $supplier = Supplier::all();

        return view('permohonan_pembelian_detail.index',compact('produk','supplier'));

    }
}
