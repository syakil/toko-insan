<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\PembelianTemporaryDetail;
use DB;
use Auth;
use App\Produk;
use App\PembelianTemporary;
use App\Branch;
use Redirect;


class KoreksiPembelianController extends Controller
{
    
    public function index(){

        return view('koreksi_pembelian/index');

    }


    public function listData(){

        $po = PembelianTemporary::where('kode_gudang',Auth::user()->unit)->where('status',null)->get();

        $id_pembel = array();

        foreach ($po as $list ) {
            $id_pembel [] = $list->id_pembelian;
        }

        $detail = PembelianTemporaryDetail::leftJoin('produk', 'produk.kode_produk', '=', 'pembelian_temporary_detail.kode_produk')
        ->where('pembelian_temporary_detail.status','edit')
        ->whereIn('id_pembelian',$id_pembel)
        ->where('produk.unit',Auth::user()->unit)
        ->get();

    
        $no = 0;
        $data = array();        
        foreach($detail as $list){ 

            $region = Branch::where('kode_gudang',Auth::user()->unit)->get();

            $unit = array();

            foreach ($region as $data_unit ) {
                $unit[] = $data_unit->kode_toko;
            }

            $stok = Produk::where('kode_produk',$list->kode_produk)->whereIn('unit',$unit)->sum('stok');

            $no ++;
            $row = array();
            $row[] = $no;
            $row[] = $list->id_pembelian;
            $row[] = $list->kode_produk;
            $row[] = $list->nama_produk;
            $row[] = $list->jumlah;
            $row[] = $stok;
            $row[] = '<div class="btn-group">
            <a onclick="showDetail('.$list->id_pembelian_detail.')" class="btn btn-primary btn-sm"><i class="fa fa-eye"></i></a>
            <a onclick="deleteId('.$list->id_pembelian_detail.')" class="btn btn-danger btn-sm"><i class="fa fa-trash"></i></a>
            </div>';
            $data[] = $row;
        }

        $output = array("data" => $data);
        return response()->json($output);

    }

    public function show($id){

        $detail = PembelianTemporaryDetail::where('id_pembelian_detail',$id)->first();

        $data = new \stdClass();
        $data->kode_produk = strval($detail->kode_produk);
        $data->qty = $detail->jumlah;
        $data->id = $id;
        
        echo json_encode($data);

    }
    
    
    public function store(Request $request){

        $data = $request->id_detail;

        foreach ($data as $list ) {
            // dd($list);
            $detail = PembelianTemporaryDetail::where('id_pembelian_detail',$list)->first();
            $detail->keterangan = "koreksi produk";
            $detail->status = "edit";
            $detail->update();
        }

        return back();

    }

    public function update(Request $request){

        $kode_baru = $request->kode_baru;
        $id = $request->id; 
        $qty = $request->qty;

        if($qty == 0){

            return Redirect::back()->withErrors(['Jumlah Pembelian Harus Lebih Dari 0']);

        }

        $detail = PembelianTemporaryDetail::where('id_pembelian_detail',$id)->first();

        $kode_lama = $detail->kode_produk;

        $region = Branch::where('kode_gudang',Auth::user()->unit)->get();

        $unit = array();

        foreach ($region as $data ) {
            $unit[] = $data->kode_toko;
        }

        $stok = Produk::where('kode_produk','like','%'.$detail->kode_produk)->whereIn('unit',$unit)->sum('stok');

        
        if($kode_baru != $kode_lama){
            
            $check = Produk::where('kode_produk','like','%'.$kode_baru)->first();

            if ($check) {
                return Redirect::back()->withErrors(['kode sudah terpakai '.$check->nama_produk]);
            }else {
                

                if ($stok == 0) {
                    
                    $produk  = Produk::where('kode_produk','like','%'.$detail->kode_produk)->whereIn('unit',$unit)->get();
                    
                    foreach ($produk as $data ) {
                        $detail_produk = Produk::where('id_produk',$data->id_produk)->first();
                        $detail_produk->kode_produk = $kode_baru;
                        $detail_produk->update();
                    }
                    
                }else {
                    
                    
                    $detail_produk = Produk::where('kode_produk','like','%'.$detail->kode_produk)->first();
                    
                    foreach ($unit as $list ) {
                        
                        $produk = new Produk;
                        $produk->kode_produk = $kode_baru;
                        $produk->nama_produk = $detail_produk->nama_produk;
                        $produk->nama_struk = $detail_produk->nama_struk;
                        $produk->merk = '';
                        $produk->id_kategori = 0;
                        $produk->diskon = 0;
                        $produk->harga_beli = $detail_produk->harga_beli;
                        $produk->harga_jual = $detail_produk->harga_jual;
                        $produk->stok = 0;
                        $produk->isi_satuan = 0;
                        $produk->satuan = '';
                        $produk->param_status= 0;
                        $produk->stok_mak = 0; 
                        $produk->stok_min = 0;
                        $produk->unit = $list;
                        $produk->harga_jual_member_insan = $detail_produk->harga_jual_insan;
                        $produk->harga_jual_insan = $detail_produk->harga_jual_insan;
                        $produk->harga_jual_pabrik = $detail_produk->harga_jual;
                        $produk->save();
                        
                    }               
                }
            }

            
            $detail = PembelianTemporaryDetail::where('id_pembelian_detail',$id)->first();
            $detail->kode_produk = $kode_baru;
            $detail->jumlah = $qty;
            $detail->status = null;
            $detail->update();

        }else {
            
            $detail = PembelianTemporaryDetail::where('id_pembelian_detail',$id)->first();
            $detail->jumlah = $qty;
            $detail->status = null;
            $detail->keterangan= "merubah qty";
            $detail->update();

        }

    
        return back();
    }

    public function delete($id){

        $detail = PembelianTemporaryDetail::where('id_pembelian_detail',$id)->first();
        $detail->status = null;
        $detail->keterangan = null;
        $detail->update();

    }
}
