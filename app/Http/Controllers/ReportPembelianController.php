<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\PembelianTemporary;
use App\PembelianTemporaryDetail;
use App\Produk;
use Auth;
use DB;

class ReportPembelianController extends Controller
{
    public function index(){

        return view('report_pembelian/index');

    }

    public function listData(){

        
      $pembelian = PembelianTemporary::
                    leftJoin('branch','pembelian_temporary.kode_gudang','=','branch.kode_toko')
                    ->select('pembelian_temporary.*','supplier.nama','branch.nama_toko')
                    ->leftJoin('supplier', 'supplier.id_supplier', '=', 'pembelian_temporary.id_supplier')
                    ->whereIn('pembelian_temporary.status',[1,2,3,4])
->where('pembelian_temporary.kode_gudang',Auth::user()->unit)
                    ->orderBy('pembelian_temporary.id_pembelian','desc')
                    ->get();

        $no = 0;
        $data = array();
        foreach($pembelian as $list){
        $no ++;
        $row = array();
        $row[] = $no;
        $row[] = tanggal_indonesia($list->created_at);
        $row[] = $list->id_pembelian;
        $row[] = $list->no_invoice;
        $row[] = $list->nama;
        $row[] = $list->total_terima;
        $row[] = "Rp. ".format_uang($list->total_harga_terima);
        $row[] = $list->nama_toko;
        $row[] = '<div class="btn-group">
        <a href="'.route('report_pembelian.detail',$list->id_pembelian).'" class="btn btn-primary btn-sm"><i class="fa fa-eye"></i></a>
        </div>';
        $data[] = $row;
        }

     $output = array("data" => $data);
     return response()->json($output);

    }

    public function detail($id){

        return view('report_pembelian/detail',compact('id'));

    }


    public function listDetail($id){
        
        $detail = PembelianTemporaryDetail::leftJoin('produk', 'produk.kode_produk', '=', 'pembelian_temporary_detail.kode_produk')
->select('produk.nama_produk','pembelian_temporary_detail.*')
                ->where('id_pembelian', '=', $id)
                ->where('unit', '=',  Auth::user()->unit)
                ->orderBy('id_pembelian_detail','desc')
                ->get();
        
        $no = 0;
        $data = array();
        $total = 0;
        $total_item = 0;
        
        foreach($detail as $list){
            $no ++;
            $row = array();
            $row[] = $no;
            $row[] = $list->kode_produk;
            $row[] = $list->nama_produk;
            $row[] = "Rp. ".format_uang($list->harga_beli);
            $row[] = number_format($list->jumlah_terima);
            $row[] = $list->total;
            $row[] = $data[] = $row;
        }
        
        
        $output = array("data" => $data);
        return response()->json($output);

    }
}
