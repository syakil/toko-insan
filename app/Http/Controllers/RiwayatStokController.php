<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Produk;
use App\ProdukDetail;
use App\PenjualanDetail;
use App\PembelianTemporaryDetail;
use DB;
use Auth;
use App\Branch;
use App\User;

class RiwayatStokController extends Controller
{
    public function index(){
        return view('riwayat_stok/index');
    }

    public function listData(){

        $sekarang = \Carbon\Carbon::now()->toDateString();
        $now = \Carbon\Carbon::now();
        $add_month = $now->addMonths(-3)->toDateString();
        
        $branch = Branch::where('kode_gudang',Auth::user()->unit)->where('kode_toko','!=',Auth::user()->unit)->get();
        $unit = array();

        foreach($branch as $list){
            $unit[] = $list->kode_toko;
        }

        $user = DB::table('users')->whereIn('unit',$unit)->where('level',2)->get();

        $id_user = array();

        foreach ($user as $list ) {
            $id_user [] = $list->id; 
        }


        $penjualan = DB::table('penjualan')->whereIn('id_user',$id_user)->get();

        $id_penjualan = array();

        foreach($penjualan as $list){
            $id_penjualan[] = $list->id_penjualan;
        }

        $produk = DB::table('produk','penjualan_detail','pembelian_temporary_detail')
                    ->select(DB::raw(
                        'produk.kode_produk,stok_min,produk.nama_produk,produk.stok , sum(penjualan_detail.jumlah) as penjualan'
                    ))
                    ->leftJoin('penjualan_detail','penjualan_detail.kode_produk','produk.kode_produk')
                    ->whereBetween('penjualan_detail.created_at',[$add_month.'%',$sekarang.'%'])
                    ->whereIn('id_penjualan',$id_penjualan)
                    ->where('produk.unit',Auth::user()->unit)
                    ->groupBy(DB::raw('produk.kode_produk, produk.unit'))
                    ->get();

        $no = 1;
        $data = array();
        
        foreach($produk->sortBy('stok') as $list){
            
            $rata_rata = ceil($list->penjualan/3);
            $stok = $list->stok;
            $stok_min = $list->stok_min;
$stok_toko = Produk::where('kode_produk',$list->kode_produk)->whereIn('unit',$unit)->sum('stok');
            
            if($list->stok_min < $rata_rata){    
                $row = array();           

                $row [] = $no++;
                $row [] = $list->kode_produk;
                $row [] = $list->nama_produk;
                $row [] = $list->stok_min;
                $row [] = $stok_toko;
                $row [] = $list->stok;
                $row [] = $list->penjualan;
                $row [] = $rata_rata;

                if($rata_rata >= 100 && $stok >= $rata_rata){
                $row [] = '<div>
                <span class="label label-success">Stok Aman</span>
                </div>';    
                }else if($rata_rata >= 100 && $stok <= $rata_rata){
                $row [] = '<div>
                <span class="label label-danger">Harus diBeli</span>
                </div>';
                }else if($stok >= $rata_rata){
                $row [] = '<div>
                <span class="label label-success">Stok Aman</span>
                </div>';    
                }else{
                $row [] = '<div>
                <span class="label label-warning">Di Pertimbangkan</span>
                </div>';    
                }
                $data [] = $row; 
            }

        }

        $output = array("data" => $data);
        return response()->json($output);
    
    }

}

