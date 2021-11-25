<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Kirim;
use App\KirimDetailTemporary;
use App\ProdukWriteOff;
use App\Produk;
use App\Supplier;
use Auth;
use DB;
use Redirect;

class ReturTukarBarangDetailController extends Controller{
    
    public function  index(){
    
        $produk = ProdukWriteOff::where('unit', '=', Auth::user()->unit)->where('stok','>',0)->where('param_status',1)->get();
        $idpembelian = session('idpembelian');
        $supplier = Supplier::find(session('idsupplier'));

        return view('retur_tukar_barang_detail.index', compact('produk', 'idpembelian', 'supplier'));
    
    }


    public function continued_hold($id){
        
        $produk = ProdukWriteOff::where('unit', '=', Auth::user()->unit)->where('stok','>',0)->get();
        $kirim = Kirim::where('id_pembelian',$id)->first();
        $idpembelian = $kirim->id_pembelian;
        $supplier = Supplier::find(session('idsupplier'));

        session(['idpembelian' => $id]);
        session(['idsupplier' => $supplier]);
        session(['kode_toko' => $kirim->kode_gudang]);

        return view('retur_tukar_barang_detail.index', compact('produk', 'idpembelian', 'supplier'));
    
    }

    public function listData($id){
    
        $detail = KirimDetailTemporary::leftJoin('produk', 'produk.kode_produk', '=', 'kirim_barang_detail_temporary.kode_produk')
                                        ->where('id_pembelian', '=', $id)
                                        ->where('unit', '=', Auth::user()->unit)
                                        ->select('kirim_barang_detail_temporary.*','produk.kode_produk','produk.nama_produk','produk.stok')
                                        ->orderBy('updated_at','desc')        
                                        ->get();
            
        $no = 0;
        $data = array();
        $total = 0;
        $total_item = 0;
        $id_toko = Kirim::where('id_pembelian',$id)->first();
       
        foreach($detail as $list){
            $no ++;
            $stok_toko = ProdukWriteOff::where('kode_produk',$list->kode_produk)->where('unit',Auth::user()->unit)->where('param_status',1)->first();

            $row = array();
            $row[] = $no;
            $row[] = $list->kode_produk;
            $row[] = $list->nama_produk;
            $row[] = $stok_toko->stok;         
            $row[] = "<input type='text' class='form-control' name='jumlah_$list->id_pembelian_detail' value='$list->jumlah' onChange='changeCount($list->id_pembelian_detail)'>";
            $row[] = '<a onclick="deleteItem('.$list->id_pembelian_detail.')" class="btn btn-danger btn-sm"><i class="fa fa-trash"></i></a>';
            $data[] = $row;
            $total += $list->harga_beli * $list->jumlah;
            $total_item += $list->jumlah_terima;
        }
    
        $data[] = array("<span class='hide total'>$total</span><span class='hide totalitem'>$total_item</span>", "", "","", "", "", "", "");
            
        $output = array("data" => $data);
        return response()->json($output);
 
    }


    
    public function store(Request $request){
        
        $produk = ProdukWriteOff::where('kode_produk',$request['kode'])->where('unit',Auth::user()->unit)->first();

        $detail = new KirimDetailTemporary;
        $detail->id_pembelian = $request['idpembelian'];
        $detail->kode_produk = $request['kode'];
        $detail->harga_jual = $produk->harga_jual;
        $detail->jumlah = 1;
        $detail->expired_date = date('Y-m-d');
        $detail->jumlah_terima = 0;
        $detail->sub_total = $produk->harga_jual;
        $detail->sub_total_terima = 0;
        $detail->jurnal_status = 0;
        $detail->save();

    }

    
    public function update(Request $request, $id){

        $nama_input = "jumlah_".$id;
        $exp_input = "expired_".$id;

        $detail = KirimDetailTemporary::find($id);
        $detail->jumlah = $request[$nama_input];
        $detail->expired_date = $request[$exp_input];
        $detail->sub_total = $detail->harga_jual * $request[$nama_input];
        $detail->update();
    }

    public function destroy($id){

        $detail = KirimDetailTemporary::find($id);
        $detail->delete();
    }

    public function loadForm($id){

        $total = KirimDetailTemporary::where('id_pembelian',$id)->sum('jumlah');

        $data = array(
            "totalitem" => format_uang($total),
            );
        return response()->json($data);

    }

}
