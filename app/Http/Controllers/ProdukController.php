<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Produk;
use App\Branch;
use App\ParamStatus;
use App\Supplier;
use App\ProdukDetail;
use App\TabelTransaksi;
use Auth;
use App\Kategori;
use Yajra\Datatables\Datatables;
use PDF;
use DB;

class ProdukController extends Controller{

    public function index(){

       $kategori = Kategori::all();
       $status = ParamStatus::all();     
       $supplier = Supplier::orderBy('nama','asc')->get();
        
       return view('produk.index', compact('kategori','status','supplier'));
    
    }

    public function listData(){
    
        $produk = Produk::where('unit',Auth::user()->unit)->leftJoin('param_status','param_status.id_status','produk.param_status')
        ->leftJoin('supplier','supplier.id_supplier','produk.id_supplier')
        ->get();

        $no = 0;
        $data = array();
        
        foreach($produk as $list){

            $kategori = Kategori::where('id_kategori','like',$list->id_kategori . '%')->first();
            
            if ($kategori) {

                $nama_kategori = $kategori->nama_kategori;

            }else{

                $nama_kategori =' ';

            }


            $no ++;
            $row = array();
            $row[] = $no;
            $row[] = $list->kode_produk;
            $row[] = $list->nama_produk;            
            $row[] = $list->nama_struk;
            $row[] = $nama_kategori;
            $row[] = $list->satuan;
            $row[] = $list->keterangan;
            $row[] = $list->nama;
            $row[] = "<div class='btn-group'>
                    <a href='".route('produk.edit',$list->id_produk)."' class='btn btn-primary btn-sm'><i class='fa fa-pencil'></i></a>";
            $data[] = $row;
        }
        
        $output = array("data" => $data);
        return response()->json($output);    
    }

    public function store(Request $request){
        
        $kode_produk = $request->kode;
        $unit = Auth::user()->unit;
        $cek = Produk::where('kode_produk',$kode_produk)->where('unit',$unit)->first();

        if ($cek) {
        
            return back()->with(['error' => 'Kode Produk Sudah Ada '.$cek->kode_produk .' '. $cek->nama_produk]);
        
        }else {

            try{

                DB::beginTransaction();

                $allunit = Branch::where('kode_gudang',Auth::user()->unit)->get();

                foreach ($allunit as $value) {
                    
                    $produk = new Produk;
                    $produk->kode_produk = $request->kode;
                    $produk->nama_produk = $request->nama;
                    $produk->nama_struk = $request->nama_struk;
                    $produk->merk = '';
                    $produk->id_kategori = '0' . $request->kategori;
                    $produk->diskon = 0;
                    $produk->harga_beli = 0;
                    $produk->harga_jual = 0;
                    $produk->stok = 0;
                    $produk->isi_satuan = 0;                    
                    $produk->satuan = $request->satuan;
                    $produk->param_status= $request->status;
                    $produk->stok_mak = 0; 
                    $produk->stok_min = 0;
                    $produk->unit = $value->kode_toko;
                    $produk->harga_jual_member_insan = 0;
                    $produk->harga_jual_insan = 0;
                    $produk->harga_jual_pabrik = 0;
                    $produk->id_supplier = $request->supplier;
                    $produk->status = 1;
                    $produk->save();

                }

                DB::commit();
                return back()->with(['success' => 'Produk Berhasil Ditambahkan']);

            }catch(\Exception $e){
         
                DB::rollback();
                return back()->with(['error' => $e->getmessage()]);
        
            }
        }
    }

    public function edit($id_produk){

        $produk = Produk::find($id_produk);    
        $kategori = Kategori::all();
        $status = ParamStatus::all();     
        $supplier = Supplier::orderBy('nama','asc')->get();    

        return view('produk.edit',compact('produk','kategori','status','supplier'));

    }

    public function update(Request $request){

        $produk = Produk::find($request->id);
        
        if ($produk) {
            
            try{

                DB::beginTransaction();

                $allunit = array();
                $kode_toko = Branch::where('kode_gudang',Auth::user()->unit)->get();
                
                foreach ($kode_toko as $key) {
                    $allunit[]=$key->kode_toko;
                }
                
                $master_produk = Produk::whereIn('unit',$allunit)->where('kode_produk',$produk->kode_produk)->get();

                foreach ($master_produk as $value) {
                    
                    $value->nama_produk = $request->nama;
                    $value->nama_struk = $request->nama_struk;
                    $value->merk = '';
                    $value->id_kategori = '0' . $request->kategori;
                    $value->satuan = $request->satuan;
                    $value->param_status= $request->status;
                    $value->id_supplier = $request->supplier;
                    $value->status = 1;
                    $value->update();

                }

                DB::commit();
                return redirect()->route('produk.index')->with(['success' => 'Produk Berhasil Diubah']);

            }catch(\Exception $e){
         
                DB::rollback();
                return back()->with(['error' => $e->getmessage()]);
        
            }

        }else {
            
            return back()->with(['error' => 'Stok Tidak Ada !']);

        }

    }

}
