<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
use Auth;
use App\Produk;
use App\ProdukDetail;
use App\Kategori;
use App\ParamStatus;
use App\Branch;

class ApproveProdukController extends Controller{
    
    public function index(){

        return view('approve_produk.index');

    }


    public function listData(){
        
        $produk = Produk::where('unit',Auth::user()->unit)->leftJoin('param_status','param_status.id_status','produk.param_status')
                        ->where('produk.status',1)
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
                    <a href='".route('approve_produk.approve',$list->id_produk)."' class='btn btn-danger btn-sm'><i class='fa fa-gavel'></i> Approve</a>
                    </div>";
            $data[] = $row;
        }
        
        $output = array("data" => $data);
        return response()->json($output);    

    }

    public function approve($id){


        try{
            
            DB::beginTransaction();
            
            $produk = Produk::find($id);

            $kode_gudang = Branch::where('kode_gudang',Auth::user()->unit)->get();

            $kode_toko = array();

            foreach ($kode_gudang as $key) {
                $kode_toko[]= $key->kode_toko;
            }

            Produk::where('kode_produk',$produk->kode_produk)->whereIn('unit',$kode_toko)->update(['status' => 0]);

            DB::commit();
            return back()->with(['success' => 'Produk Berhasil Ditambahkan']);

        }catch(\Exception $e){
     
            DB::rollback();
            return back()->with(['error' => $e->getmessage()]);
    
        }
    }


}
