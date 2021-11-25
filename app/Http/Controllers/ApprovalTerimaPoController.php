<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Redirect;
use App\Pembelian;
use Auth;
use PDF;
use App\Supplier;
use App\Kategori;
use App\PembelianTemporary;
use App\PembelianTemporaryDetail;
use App\Produk;
use App\ProdukDetail;
use DB;
use Session;
use App\KartuStok;
use App\PembelianDetail;



class ApprovalTerimaPoController extends Controller{

    public function index(){

        return view('approve_terima_po.index');

    }


    public function listData(){

        $pembelian = Pembelian::select('pembelian.*','supplier.nama')->leftJoin('supplier', 'supplier.id_supplier', '=', 'pembelian.id_supplier')
                                ->where('kode_gudang',Auth::user()->unit)
                                ->where('pembelian.status',1)
                                ->orderBy('pembelian.id_pembelian', 'desc')
                                ->get();
        $no = 0;
        $data = array();
        foreach($pembelian as $list){

            $no ++;
            $row = array();
            $row[] = $no;
            $row[] = $list->id_pembelian_t;
            $row[] = tanggal_indonesia($list->created_at);
            $row[] = $list->nama;
            $row[] = $list->total_item;
            $row[] = $list->total_terima;
            $row[] = "<div class='btn-group'>
            <a href='".route('approve_terima_po.detail',$list->id_pembelian)."' class='btn btn-primary btn-sm'><i class='fa fa-eye'></i></a>
            </div>";

            $data[] = $row;
            
        }

        $output = array("data" => $data);
        return response()->json($output);

    }

    public function listDetail($id){

        $detail = PembelianDetail::leftJoin('produk', 'produk.kode_produk', '=', 'pembelian_detail.kode_produk')
        ->select('pembelian_detail.*','produk.nama_produk')                                    
        ->where('id_pembelian', '=', $id)
        ->where('unit', '=',  Auth::user()->unit)
        ->orderBy('pembelian_detail.id_pembelian_detail', 'desc')
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
            $row[] = $list->expired_date;

            $data[] = $row;
        }

        $output = array("data" => $data);
        return response()->json($output);

    }


    public function detail($id){

        return view('approve_terima_po.detail',compact('id'));

    }


    public function approve(Request $request){

        $pembelian = Pembelian::find($request->id);
        
        $id = $request->id;

        $cek_expired = PembelianDetail::where('id_pembelian',$id)->get();
        $tanggal_sekarang = new \DateTime();

        foreach ($cek_expired as $key) {
            
            $tanggal_expired = new \DateTime($key['expired_date']); 
            $perbedaan = $tanggal_sekarang->diff($tanggal_expired)->format("%a");

            $produk = DB::table('produk')->where('kode_produk',$key->kode_produk)->where('unit',Auth::user()->unit)->first();
            
            $kategori_produk = explode(".",$produk->id_kategori);

            
            $kategori = Kategori::where('id_kategori','like', '0'.$kategori_produk[0].'%')->first();
if($kategori == null){
            $kategori = Kategori::where('id_kategori', $produk->id_kategori)->first();
}
            
            $param_expired = $kategori->expired;

            if ($perbedaan < $param_expired) {
                return back()->with(['error' => 'Expired Produk '.$produk->kode_produk . ' ' . $produk->nama_produk . ' Kurang Dari ' . $param_expired . ' Hari !!']);
            }

        }


        try {
                
            DB::beginTransaction();
            
            $pembelian = Pembelian::find($id);
            $pembelian_temporary = PembelianTemporary::find($pembelian->id_pembelian_t);
            
            $produk = DB::table('pembelian_detail','produk')
                        ->select('pembelian_detail.*','produk.kode_produk','produk.nama_produk','produk.id_kategori','produk.id_kategori','produk.unit')
                        ->leftJoin('produk','pembelian_detail.kode_produk','=','produk.kode_produk')
                        ->where('unit',Auth::user()->unit)
                        ->where('id_pembelian',$id)
                        ->get();
        
                            
            foreach ($produk as $p ) {
                    
                $produk_main = Produk::where('kode_produk',$p->kode_produk)
                ->where('unit', Auth::user()->unit)
                ->first();
                
                $produk_main->stok += $p->jumlah_terima;
                $produk_main->update();

                $produk_detail = new ProdukDetail;
                $produk_detail->kode_produk = $p->kode_produk;
                $produk_detail->nama_produk = $produk_main->nama_produk;
                $produk_detail->stok_detail = $p->jumlah_terima;
                $produk_detail->harga_beli = $p->harga_beli;
                $produk_detail->harga_jual_umum = 0;
                $produk_detail->harga_jual_insan = 0;
                $produk_detail->expired_date = $p->expired_date;
                $produk_detail->promo = 0;
                $produk_detail->tanggal_masuk = date('Y-m-d');
                $produk_detail->no_faktur = $pembelian->id_pembelian_t;
                $produk_detail->unit = Auth::user()->unit;
                $produk_detail->status = '1';
                $produk_detail->promo = 0;
                $produk_detail->save();
                
                $kartu_stok = new KartuStok;
                $kartu_stok->buss_date = date('Y-m-d');
                $kartu_stok->kode_produk = $p->kode_produk;
                $kartu_stok->masuk = $p->jumlah_terima;
                $kartu_stok->keluar = 0;
                $kartu_stok->status = 'pembelian';
                $kartu_stok->kode_transaksi = $pembelian->id_pembelian_t;
                $kartu_stok->unit = Auth::user()->unit;
                $kartu_stok->save();
            }
            
            $pembelianT=PembelianTemporary::where('id_pembelian',$pembelian->id_pembelian_t);
            $pembelianT->update(['status'=>2]);
            
            Pembelian::where('id_pembelian',$id)->update(['status'=>2]);
            PembelianTemporaryDetail::where('id_pembelian',$pembelian->id_pembelian_t)->where('jumlah_terima',0)->delete();

            DB::commit();
            return Redirect::route('approve_terima_po.index')->with(['success' => 'PO Berhasil Di Terima !']);
      
        }catch(\Exception $e){
           
           DB::rollback();
           return back()->with(['error' => $e->getmessage()]);
   
        }


    }

}
