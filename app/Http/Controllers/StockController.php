<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Auth;
use App\ProdukDetail;
use App\Produk;
use App\Kategori;
use Yajra\Datatables\Datatables;
use PDF;
use DB;
use App\Branch;

class StockController extends Controller
{
    public function index(){
        $branch = Branch::where('kode_gudang',Auth::user()->unit)->orderBy('kode_toko','ASC')->get();
        return view('gudang/stock',compact('branch'));
    }

    public function listData(){

        $branch = Branch::where('kode_gudang',Auth::user()->unit)->get();
        $kode_toko = array();
        foreach ($branch as $list ) {
            $kode_toko[] = $list->kode_toko;
        }


        $produk = Produk::where('unit', '=',  Auth::user()->unit)->get();

        $no = 0;
        $data = array();
        foreach($produk as $list){
            
            $produk_unit = Produk::where("kode_produk",$list->kode_produk)->whereIn("unit",$kode_toko)->groupBy("unit")->orderBy("unit","ASC")->get();
            
            $no ++;
            $row = array();
            $row[] = $no;
            $row[] = $list->kode_produk;
            $row[] = $list->nama_produk;  
            foreach ($produk_unit as $stok) {
                $row[] = $stok->stok;
            }
            $data[] = $row;
        }
        //   dd($data);
        $output = array("data" => $data);
        return response()->json($output);
    }

    public function detail($id){
        $produk = DB::table('produk_detail')
                    ->select('*')
                    ->where('kode_produk',$id)
                    ->where('unit', '=',  Auth::user()->unit)
                    ->get();
                        
        $nama = Produk::where('kode_produk',$id)->first();
        return view('gudang/detail_stock',['produk'=>$produk,'nama'=>$nama]);

        
    }

    public function update_stock(Request $request,$id){

        $detail = ProdukDetail::where('id_produk_detail',$id)->first();
        $detail->stok_detail = $request->value;
        $detail->update();
                
        $stok = ProdukDetail::where('kode_produk',$detail->kode_produk)
                            ->where('unit',$detail->unit)
                            ->sum('stok_detail');
        $produk = Produk::where('kode_produk',$detail->kode_produk)
                        ->where('unit',$detail->unit)->first();
        $produk->stok = $stok;
        $produk->update();

    }

    public function delete($id){
        
        // dd($id);
        $detail = ProdukDetail::where('id_produk_detail',$id)->first();
        
        
        $produk = Produk::where('kode_produk',$detail->kode_produk)
        ->where('unit',Auth::user()->unit)
        ->first();
        
        
        $detail->delete();
        
        $stok = ProdukDetail::where('kode_produk',$detail->kode_produk)
                            ->where('unit',Auth::user()->unit)
                            ->sum('stok_detail');
        // dd($produk);
        $produk->stok = $stok;
        $produk->update(); 

        return back();

    }


    
    public function update_expired_stock(Request $request,$id){

        $detail = ProdukDetail::where('id_produk_detail',$id)->first();
        $detail->expired_date = $request->value;
        $detail->update();

    }

    public function store(Request $request){
        $unit = Auth::user()->unit;
        // dd($unit);
        $produk_detail = new ProdukDetail;
        $produk_detail->kode_produk = $request->barcode;
        $produk_detail->nama_produk = $request->nama;
        $produk_detail->unit = Auth::user()->unit;
        $produk_detail->stok_detail = $request->stok;
        $produk_detail->expired_date = $request->tanggal;
        $produk_detail->save();

        $stok = ProdukDetail::where('kode_produk',$request->barcode)
                        ->where('unit',Auth::user()->unit)
                        ->sum('stok_detail');

        $update_stok = Produk::where('kode_produk',$request->barcode)
                            ->where('unit',Auth::user()->unit)
                            ->first();
        $update_stok->stok= $stok;
        $update_stok->update();
    
        return back();
    }

}

