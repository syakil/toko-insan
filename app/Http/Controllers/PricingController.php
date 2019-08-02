<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Produk;
use App\ProdukDetail;
use App\ParamProduk;
use App\ParamStatus;
use App\TabelTransaksi;
use Auth;
use DB;

class PricingController extends Controller
{
    public function index(){
        $produk = Produk::leftJoin('param_status','param_status.id_status','=','produk.param_status')
                        ->where('unit','WH01')->get();
        $fast = ParamStatus::where('keterangan','Fast Moving')->first();
        $medium = ParamStatus::where('keterangan','Medium Moving')->first();
        $slow = ParamStatus::where('keterangan','Slow Moving')->first();
        $no = 1;
        // dd($produk);
        return view('pricing.index',['produk'=>$produk,'no'=>$no,'fast'=>$fast,'medium'=>$medium,'slow'=>$slow]);
    }

    public function edit($id)
    {
        $produk = Produk::leftJoin('param_status','param_status.id_status','=','produk.param_status')->where('kode_produk',$id)->first();
        $param = ParamStatus::all();
        $no=1;
        return view('pricing.edit',['param'=>$param,'produk'=>$produk,'no'=>$no]);
    }

    public function update(Request $request, $id){
        // dd($request->status);
        $produk = Produk::where('kode_produk',$id)->first();

        
        if($produk->harga_jual == 0){
            
            $margin = $request['harga_jual_pabrik'] - $produk->harga_beli;

            $jurnal = new TabelTransaksi;
            $jurnal->unit = Auth::user()->unit; 
            $jurnal->kode_transaksi = $id;
            $jurnal->kode_rekening = 1482000;
            $jurnal->tanggal_transaksi  = date('Y-m-d');
            $jurnal->jenis_transaksi  = 'Jurnal System';
            $jurnal->keterangan_transaksi = 'Harga Baru' . ' ' . $id;
            $jurnal->debet = $margin;
            $jurnal->kredit = 0;
            $jurnal->tanggal_posting = '';
            $jurnal->keterangan_posting = '0';
            $jurnal->id_admin = Auth::user()->id; 
            $jurnal->save();

            $jurnal = new TabelTransaksi;
            $jurnal->unit =  Auth::user()->unit; 
            $jurnal->kode_transaksi = $id;
            $jurnal->kode_rekening = 1422000;
            $jurnal->tanggal_transaksi  = date('Y-m-d');
            $jurnal->jenis_transaksi  = 'Jurnal System';
            $jurnal->keterangan_transaksi = 'Harga Baru' . ' ' . $id;
            $jurnal->debet =0;
            $jurnal->kredit =$margin;
            $jurnal->tanggal_posting = '';
            $jurnal->keterangan_posting = '0';
            $jurnal->id_admin = Auth::user()->id; 
            $jurnal->save();

        }elseif ($produk->harga_jual < $request['harga_jual']) {
            
            $margin = $request['harga_jual_pabrik'] - $produk->harga_jual;

            $jurnal = new TabelTransaksi;
            $jurnal->unit = Auth::user()->unit; 
            $jurnal->kode_transaksi = $id;
            $jurnal->kode_rekening = 1482000;
            $jurnal->tanggal_transaksi  = date('Y-m-d');
            $jurnal->jenis_transaksi  = 'Jurnal System';
            $jurnal->keterangan_transaksi = 'Perubahan Harga' . ' ' . $id;
            $jurnal->debet = $margin;
            $jurnal->kredit = 0;
            $jurnal->tanggal_posting = '';
            $jurnal->keterangan_posting = '0';
            $jurnal->id_admin = Auth::user()->id; 
            $jurnal->save();

            $jurnal = new TabelTransaksi;
            $jurnal->unit =  Auth::user()->unit; 
            $jurnal->kode_transaksi = $id;
            $jurnal->kode_rekening = 1422000;
            $jurnal->tanggal_transaksi  = date('Y-m-d');
            $jurnal->jenis_transaksi  = 'Jurnal System';
            $jurnal->keterangan_transaksi = 'Perubahan Harga' . ' ' . $id;
            $jurnal->debet =0;
            $jurnal->kredit =$margin;
            $jurnal->tanggal_posting = '';
            $jurnal->keterangan_posting = '0';
            $jurnal->id_admin = Auth::user()->id; 
            $jurnal->save();

        }elseif ($produk->harga_jual > $request['harga_jual']) {
            $margin = $request['harga_jual_pabrik'] - $produk->harga_jual;
            
            $jurnal = new TabelTransaksi;
            $jurnal->unit = Auth::user()->unit; 
            $jurnal->kode_transaksi = $id;
            $jurnal->kode_rekening = 1422000;
            $jurnal->tanggal_transaksi  = date('Y-m-d');
            $jurnal->jenis_transaksi  = 'Jurnal System';
            $jurnal->keterangan_transaksi = 'Perubahan Harga' . ' ' . $id;
            $jurnal->debet = abs($margin);
            $jurnal->kredit = 0;
            $jurnal->tanggal_posting = '';
            $jurnal->keterangan_posting = '0';
            $jurnal->id_admin = Auth::user()->id; 
            $jurnal->save();

            $jurnal = new TabelTransaksi;
            $jurnal->unit =  Auth::user()->unit; 
            $jurnal->kode_transaksi = $id;
            $jurnal->kode_rekening = 1482000;
            $jurnal->tanggal_transaksi  = date('Y-m-d');
            $jurnal->jenis_transaksi  = 'Jurnal System';
            $jurnal->keterangan_transaksi = 'Perubahan Harga' . ' ' . $id;
            $jurnal->debet =0;
            $jurnal->kredit =abs($margin);
            $jurnal->tanggal_posting = '';
            $jurnal->keterangan_posting = '0';
            $jurnal->id_admin = Auth::user()->id; 
            $jurnal->save();
        }
        
            
        // dd($produk->nama_produk);
        $produk = Produk::where('kode_produk',$id)->get();
        foreach($produk as $produk_all){
        $produk_all->param_status = $request['status'];
        $produk_all->harga_jual = $request['harga_jual_pabrik'];   
        $produk_all->harga_jual_member_insan= $request['harga_jual_insan'];
        $produk_all->harga_jual_insan= $request['harga_jual_insan'];
        $produk_all->harga_jual_pabrik       = $request['harga_jual_pabrik']; 
        $produk_all->update();
        }
        return redirect('pricing/index');
    }

    public function show(Request $request){
        
        $param = ParamStatus::where('keterangan','Fast Moving')->first();
        $param->margin = $request->fast;
        $param->update();
        
        $param = ParamStatus::where('keterangan','Medium Moving')->first();
        $param->margin = $request->medium;
        $param->update();

        $param = ParamStatus::where('keterangan','Slow Moving')->first();
        $param->margin = $request->slow;
        $param->update();

        return redirect('pricing/index');
    
    }
}
