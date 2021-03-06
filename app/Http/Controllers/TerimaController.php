<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Pembelian;
use App\PembelianDetail;
use App\ProdukDetail;
use App\Produk;
use App\TabelTransaksi;
use Auth;
use Yajra\Datatables\Datatables;
use Carbon\Carbon;


class TerimaController extends Controller
{
    public function index(){

    $pembelian = Pembelian::leftJoin('supplier','pembelian.id_supplier','=','supplier.id_supplier')
                            ->where('kode_gudang',Auth::user()->unit)
                            ->get();
    $no = 1;
    return view ('terima.index',['pembelian'=>$pembelian,'no'=>$no]);

    }

    public function detail($id){

        $detail = PembelianDetail::leftJoin('produk','pembelian_detail.kode_produk','=','produk.kode_produk')
                                    ->where('id_pembelian',$id)
                                    ->where('unit',Auth::user()->unit)
                                    ->get();
        $nopo = Pembelian::where('id_pembelian',$id)->get();
        $nomer = 1;
        return view('terima.detail',['pembelian'=>$detail,'nomer'=>$nomer,'nopo'=>$nopo]);
    }
    
    public function update_jumlah_terima(Request $request,$id){

        $detail = PembelianDetail::where('id_pembelian_detail',$id);
        $detail->update(['jumlah_terima'=>$request->value]);
                

    }
    
    public function update_expired_date(Request $request,$id){

        $detail = PembelianDetail::where('id_pembelian_detail',$id);
        $detail->update(['expired_date'=>$request->value]);

    }

    public function update_status(Request $request){
        
        $data = $request->check;
        
        // cek expired
        foreach($data as $d){

            // cek kadaluarsa
            $produk = DB::table('pembelian_detail','produk')
                        ->select('pembelian_detail.*','produk.kode_produk','produk.nama_produk','produk.id_kategori','produk.id_kategori','produk.unit')
                        ->leftJoin('produk','pembelian_detail.kode_produk','=','produk.kode_produk')
                        ->where('unit',Auth::user()->unit)
                        ->where('id_pembelian',$d)
                        ->get();
            
            
            foreach ($produk as $p ) {
                $exp_date_food = Carbon::now()->addMonths(6);
                $exp_date_non_food = Carbon::now()->addMonths(12);

                if ($p->id_kategori == 2 || $p->id_kategori == 3 ) {
                    if ($p->expired_date < $exp_date_food) {
                        return redirect('terima/index')->with(['error' => 'Item food kadaluarsa < 6 bulan']);
                    }
                }else {
                    if ($p->expired_date < $exp_date_non_food) {
                        return redirect('terima/index')->with(['error' => 'Item kadaluarsa < 1 Tahun']);
                    }
                }
            }
        }


        // looping id yang diceklis
        foreach($data as $d){

            // input ke gudang
            $produk = DB::table('pembelian_detail','produk')
                        ->select('pembelian_detail.*','produk.kode_produk','produk.nama_produk','produk.id_kategori','produk.unit')
                        ->leftJoin('produk','pembelian_detail.kode_produk','=','produk.kode_produk')
                        ->where('unit',Auth::user()->unit)
                        ->where('id_pembelian',$d)
                        ->get();
            
            
            foreach ($produk as $p ) {
                
                // update table produk
                $produk_main = Produk::where('kode_produk',$p->kode_produk)
                                    ->where('unit',Auth::user()->unit)
                                    ->first();
                $new_stok = $produk_main->stok + $p->jumlah_terima;
                $produk_main->update(['stok'=>$new_stok]);
                
                //insert to produk_detail 
                $insert_produk = new ProdukDetail;
                $insert_produk->kode_produk = $p->kode_produk;
                $insert_produk->id_kategori = $p->id_kategori;
                $insert_produk->nama_produk = $p->nama_produk;
                $insert_produk->stok_detail = $p->jumlah_terima;
                $insert_produk->harga_beli = $p->harga_beli;
                $insert_produk->expired_date = $p->expired_date;
                $insert_produk->unit = $p->unit;
                $insert_produk->save();

            }

            // update status_app menjadi 1
            $pembelian=Pembelian::where('id_pembelian',$d);
            $pembelian->update(['status'=>1]);
        }
        
        return redirect('terima/index');
    }
    


    public function input_stok(Request $request){
        // dd($request->check);
        $data = $request->check;
        
        foreach($data as $d){
            // input ke gudang
            
            $produk = DB::table('pembelian_detail','produk')
                        ->select('pembelian_detail.*','produk.kode_produk','produk.nama_produk','produk.id_kategori','produk.id_kategori','produk.unit')
                        ->leftJoin('produk','pembelian_detail.kode_produk','=','produk.kode_produk')
                        ->where('unit',Auth::user()->unit)
                        ->where('id_pembelian_detail',$d)
                        ->get();
            
            // dd($produk);

            foreach ($produk as $p ) {
                $exp_date_food = Carbon::now()->addMonths(6);
                $exp_date_non_food = Carbon::now()->addMonths(12);
                
                // dd($exp_date_food);
                switch ($p->id_kategori) {
                    case '1':
                        if ($p->expired_date < $exp_date_food) {
                                return redirect('terima/detail/'. $p->id_pembelian)->with(['error' => 'Item food kadaluarsa < 6 bulan']);
                            }
                        break;
                    
                    case'2':
                    if ($p->expired_date < $exp_date_food) {
                            return redirect('terima/detail/'. $p->id_pembelian)->with(['error' => 'Item food kadaluarsa < 6 bulan']);
                        }
                    break;

                    default:
                        if ($p->expired_date < $exp_date_non_food) {
                                return redirect('terima/detail/'. $p->id_pembelian)->with(['error' => 'Item kadaluarsa < 1 Tahun']);
                            }
                    break;
                }

                    // if ($p->id_kategori == 2 || $p->id_kategori == 3 ) {
                    //     if ($p->expired_date < $exp_date_food) {
                    //         return redirect('terima/detail/'. $p->id_pembelian)->with(['error' => 'Item food kadaluarsa < 6 bulan']);
                    //     }
                    // }else {
                    //     if ($p->expired_date < $exp_date_non_food) {
                    //         return redirect('terima/detail/'. $p->id_pembelian)->with(['error' => 'Item kadaluarsa < 1 Tahun']);
                    //     }
                    // }
            }
        }


        foreach($data as $d){
            
            // input ke gudang
            
            $produk = DB::table('pembelian_detail','produk')
                        ->select('pembelian_detail.*','produk.kode_produk','produk.nama_produk','produk.id_kategori','produk.id_kategori','produk.unit')
                        ->leftJoin('produk','pembelian_detail.kode_produk','=','produk.kode_produk')
                        ->where('unit',Auth::user()->unit)
                        ->where('id_pembelian_detail',$d)
                        ->get();
        
                        
            foreach ($produk as $p ) {
            
                // update status_app menjadi 1
                $pembelian=Pembelian::where('id_pembelian',$p->id_pembelian);
                $pembelian->update(['status'=>1]);
                
                // update table produk
                $produk_main = Produk::where('kode_produk',$p->kode_produk)
                                    ->where('unit',$p->unit)
                                    ->first();
                $new_stok = $produk_main->stok + $p->jumlah_terima;
                $produk_main->update(['stok'=>$new_stok]);
                

                //insert to produk_detail 
                $insert_produk = new ProdukDetail;
                $insert_produk->kode_produk = $p->kode_produk;
                $insert_produk->id_kategori = $p->id_kategori;
                $insert_produk->nama_produk = $p->nama_produk;
                $insert_produk->stok_detail = $p->jumlah_terima;
                $insert_produk->harga_beli = $p->harga_beli;
                $insert_produk->expired_date = $p->expired_date;
                $insert_produk->unit = $p->unit;
                $insert_produk->save();

            }
        }
        
        return redirect('terima/index');
    }

    
}
