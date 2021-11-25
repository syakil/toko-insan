<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
use Auth;
use App\Kirim;
use App\KirimDetail;
use App\Branch;
use App\Supplier;
use App\Produk;
use App\ProdukWriteOff;
use App\KirimDetailTemporary;
use Redirect;


class ReturTukarBarangController extends Controller{
    
    public function index(){

        $supplier = Supplier::all();
        $no = 1;
        
        $kirim = Kirim::leftJoin('branch', 'branch.kode_toko', '=', 'kirim_barang.id_supplier')
                        ->where('status_kirim','tukar_barang')
                        ->where('tujuan','supplier')
                        ->where('kirim_barang.status','hold')
                        ->where('kirim_barang.kode_gudang',Auth::user()->unit)
                        ->orderBy('kirim_barang.updated_at', 'desc')
                        ->get();

        $cek = Kirim::where('status','hold')->where('status_kirim','tukar_barang')->first();

        if ($cek == null) {
            $data = 0;
        }else{
            $data = 1;
        }

        return view('retur_tukar_barang.index', compact('supplier','kirim','no','data')); 

    }


    public function listData(){

        $pembelian = Kirim::select('kirim_barang.*','supplier.nama')
        ->leftJoin('supplier', 'supplier.id_supplier', '=', 'kirim_barang.id_supplier')
        ->where('kirim_barang.kode_gudang',Auth::user()->unit)
        ->where('tujuan','supplier')
        ->where('kirim_barang.status',1)
        ->where('status_kirim','tukar_barang')
        ->orderBy('kirim_barang.id_pembelian', 'desc')
        ->get();
      
      
        $no = 0;
        $data = array();
        foreach($pembelian as $list){
            $no ++;
            $row = array();
            $row[] = $list->id_pembelian;
            $row[] = $list->nama;
            $row[] = tanggal_indonesia(substr($list->created_at, 0, 10), false);
            $row[] = $list->total_item;
            $row[] = "Rp. ".format_uang($list->total_harga);
            $row[] = '<div class="btn-group">
                    <a onclick="showDetail('.$list->id_pembelian.')"  class="btn btn-warning btn-sm" target="_blank"><i class="fa fa-eye"></i></a>
                    <a href="'.route("retur_tukar_barang.cetak",$list->id_pembelian).'"  class="btn btn-danger btn-sm" target="_blank"><i class="fa fa-print"></i></a>
                </div>';
            $data[] = $row;
        }

        $output = array("data" => $data);
        return response()->json($output);

    }    

    public function show($id){

        $detail = KirimDetail::leftJoin('produk', 'produk.kode_produk', '=', 'kirim_barang_detail.kode_produk')
          ->where('id_pembelian', '=', $id)
          ->where('unit',Auth::user()->unit)
          ->get();
          
  
        $no = 0;
        $data = array();
        foreach($detail as $list){
          $no ++;
          $row = array();
          $row[] = $no;
          $row[] = $list->kode_produk;
          $row[] = $list->nama_produk;
          $row[] = $list->jumlah;
          $row[] = $list->jumlah_terima;
          $data[] = $row;
        }
  
        $output = array("data" => $data);
        return response()->json($output);
    }
    
    public function create($id){

        $pembelian = new Kirim;
        $pembelian->id_supplier = $id;     
        $pembelian->total_item = 0;     
        $pembelian->total_harga = 0;     
        $pembelian->total_margin = 0;
        $pembelian->total_terima = 0;
        $pembelian->total_harga_terima = 0;
        $pembelian->total_margin_terima = 0;
        $pembelian->kode_gudang = 0;
        $pembelian->status = 'hold';
        $pembelian->id_user = Auth::user()->id;
        $pembelian->kode_gudang = Auth::user()->unit;
        $pembelian->tujuan = 'supplier';
        $pembelian->status_kirim = 'tukar_barang'; 
        $pembelian->save();    

        session(['idpembelian' => $pembelian->id_pembelian]);
        session(['idsupplier' => $id]);
        session(['kode_toko' => $id]);

        return Redirect::route('retur_tukar_barang_detail.index');      
    
    }

    
    public function hold($id){
        
        $pembelian = Kirim::find($id);
        $total_item = KirimDetailTemporary::where('id_pembelian',$id)->sum('jumlah');
        $total_harga = KirimDetailTemporary::where('id_pembelian',$id)->sum('sub_total');
        $total_harga_jual = KirimDetailTemporary::where('id_pembelian',$id)->sum('sub_total_margin');

        $pembelian->total_item = $total_item;
        $pembelian->total_harga = $total_harga;
        $pembelian->total_margin = $total_harga_jual;
        $pembelian->update();
        session()->forget('idpembelian');

        return Redirect::route('retur_tukar_barang.index');  
        
    }


        
    public function store(Request $request){

        $id_pembelian = $request['idpembelian'];

        $total_item = KirimDetailTemporary::where('id_pembelian',$id_pembelian)->sum('jumlah');
        $total_harga = KirimDetailTemporary::where('id_pembelian',$id_pembelian)->sum('sub_total');
        $total_margin = KirimDetailTemporary::where('id_pembelian',$id_pembelian)->sum('sub_total_margin');

        $kirim_barang = Kirim::where('id_pembelian',$id_pembelian)->first();
        $kirim_barang->total_item = $total_item;
        $kirim_barang->total_margin = $total_margin;
        $kirim_barang->total_harga = $total_harga;
        $kirim_barang->update();

        $pembelian = Kirim::find($request['idpembelian']);
        $pembelian->status = 'approval';
        $pembelian->update();
        $request->session()->forget('idpembelian');
        session(['cetak'=>$request['idpembelian']]);
        
        return redirect()->route('retur_tukar_barang.index')->with(['success' => 'Surat Jalan Berhasil Di Buat !']); ;

    }


    
    public function delete($id){
     
        $pembelian = Kirim::find($id);
        $pembelian->delete();
        $detail = KirimDetailTemporary::where('id_pembelian', '=', $id)->delete();
        
        return redirect()->route('retur_tukar_barang.index')->with(['success'=>'Surat Jalan Berhasil Di Hapus']);
    }

}
