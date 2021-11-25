<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\PembelianTemporary;
use App\PembelianTemporaryDetail;
use App\Pembelian;
use App\PembelianDetail;
use App\TabelTransaksi;
use App\Produk;
use App\ProdukDetail;
use Auth;
use DB;

class PricingKPController extends Controller
{
    public function index(){
        return view('pricing_kp/index');
    }

    public function listData(){
        
        $unit = Auth::user()->unit;
        $pembelian = PembelianTemporary::leftJoin('supplier','pembelian_temporary.id_supplier','=','supplier.id_supplier')
                                        ->leftJoin('branch','branch.kode_toko','=','pembelian_temporary.kode_gudang')
                                        ->select('pembelian_temporary.*','supplier.nama','supplier.id_supplier','branch.nama_toko')
                                        ->where('pembelian_temporary.kode_gudang',$unit)
                                        ->where('pembelian_temporary.status','3')
                                        ->get();

        $no = 1;
        $data = array();
        foreach($pembelian as $list){
            $row = array();
            $row [] = $no++;
            $row [] = $list->id_pembelian ;
            $row [] = tanggal_indonesia($list->created_at);
            $row [] = $list->nama_toko;
            $row [] = $list->nama ;
            $row [] = 'Rp '.number_format($list->total_harga_terima) ;
            $row [] = '<a href="'. route('pricing_kp.detail',$list->id_pembelian).'" class="btn btn-sm btn-warning"><i class="fa fa-pencil"></i></a>';
            $data [] = $row; 
        }

        $output = array("data" => $data);
        return response()->json($output);
        
    }

    public function detail($id){
        $id_pembelian = $id;
        return view('pricing_kp/detail',compact('id_pembelian'));
    }

    public function listDetail($id){
        
        $pembelian_detail = PembelianTemporaryDetail::where('id_pembelian', $id)
            ->leftJoin('produk','pembelian_temporary_detail.kode_produk','produk.kode_produk')
            ->select('pembelian_temporary_detail.*','produk.harga_beli','produk.harga_jual_insan','produk.harga_indo','produk.harga_alfa','produk.harga_olshop','produk.harga_grosir','produk.nama_produk')
            ->where('pembelian_temporary_detail.status',1)
            ->groupBy('produk.kode_produk')
            ->get();

        $no = 1;
        $data = array();
        
        foreach($pembelian_detail as $list){
            
            $row = array();
            
            $hpp_baru = round($list->total/$list->jumlah_terima);
                
            $harga_pasar = array($list->harga_indo,$list->harga_alfa,$list->harga_grosir,$list->harga_olshop);

            $row [] = $no++;
            $row [] = $list->kode_produk;
            $row [] = $list->nama_produk ;
            $row [] = number_format($hpp_baru);
            $row [] = number_format($list->harga_jual_insan);
            $row [] = number_format($list->harga_jual_insan - $hpp_baru);
            $row[] = number_format(min($harga_pasar));
            $row [] = "<input type='number' onChange='harga_jual_ni(".$list->id_pembelian_detail.")' name='harga-jual-ni-".$list->id_pembelian_detail."' value='".$list->harga_jual_ni."' style='border:none; background:transparent;'>";
            $row [] = "<input type='number' onChange='harga_jual(".$list->id_pembelian_detail.")' name='harga-jual-".$list->id_pembelian_detail."' value='".$list->harga_jual_pabrik."' style='border:none; background:transparent;'>";
            $data [] = $row; 

        }

        $output = array("data" => $data);
        return response()->json($output);
        
    }

    public function update_harga_jual(Request $request,$id){
        
        $nama_barang = 'harga-jual-'.$id;
        $harga_jual = $request[$nama_barang];

        $pembelian_detail = PembelianTemporaryDetail::where('id_pembelian_detail',$id)->first();
        
        $pembelian_detail->harga_jual_pabrik = $harga_jual;
        $pembelian_detail->update();

    }

    public function update_harga_jual_ni(Request $request,$id){
        
        $nama_barang = 'harga-jual-ni-'.$id;
        $harga_jual = $request[$nama_barang];
        
        $pembelian_detail = PembelianTemporaryDetail::where('id_pembelian_detail',$id)->first();
        
        $pembelian_detail->harga_jual_ni = $harga_jual;
        $pembelian_detail->update();

    }

    public function update_invoice(Request $request,$id){

        $nama_barang = 'harga-invoice-'.$id;
        $harga_invoice = $request[$nama_barang];

        $pembelian_detail = PembelianTemporaryDetail::where('id_pembelian_detail',$id)->first();

        $jumlah_barang = $pembelian_detail->jumlah_terima;
        $harga = round($harga_invoice/$jumlah_barang);
        $harga_beli = $harga;

        $pembelian_detail->harga_beli = $harga_beli;
        $pembelian_detail->total = $harga_invoice;
        $pembelian_detail->update();

    }

    public function simpan(Request $request,$id){

        $id_pembelian = $id;

        $param_tgl = \App\ParamTgl::where('nama_param_tgl','tanggal_transaksi')->where('unit',Auth::user()->id)->first();
        $tanggal = $param_tgl->param_tgl;

        $pembelian_detail = PembelianTemporaryDetail::where('id_pembelian',$id_pembelian)->where('status',1)->get();
            
        foreach ($pembelian_detail as $list ) {
            
            $list->status_eod = 2;
            $list->update();

            $produk = Produk::where('kode_produk',$list->kode_produk)->where('unit',Auth::user()->unit)->first();

            $margin = DB::table('param_status')->where('id_status',$produk->param_status)->first();

            $margin_baru = ($list->harga_jual_ni-$list->harga_beli)/$list->harga_jual_ni*100;

            $produk_detail = ProdukDetail::where('no_faktur',$id)->where('kode_produk',$list->kode_produk)->first();
            $produk_detail->harga_beli = $list->harga_beli;
            $produk_detail->harga_jual_insan = $list->harga_jual_ni;
            $produk_detail->harga_jual_umum = $list->harga_jual_pabrik;
            
            if($produk->harga_jual == $list->harga_jual_ni){
                $produk_detail->status = null;
            }else {
                $produk_detail->status = 3;
            }
            
            $produk_detail->update();
        
        }

        $pembelian = PembelianTemporary::where('id_pembelian',$id)->first();
        $pembelian->status = 4;
        $pembelian->update();

        $pembelian_detail_tempo = DB::table('pembelian_temporary_detail')->where('id_pembelian',$id)->where('status',1)->update(['status' => 2]);

        return redirect()->route('pricing_kp.index')->with(['berhasil' => 'PO '. $id_pembelian . ' Berhasil Disimpan']);

    }
}

