<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Redirect;
use App\Pembelian;
use Auth;
use PDF;
use App\Supplier;
use App\PembelianTemporary;
use App\PembelianTemporaryDetail;
use App\Produk;
use DB;

class PembelianController extends Controller
{
   public function index()
   {
      $supplier = Supplier::all();
      return view('pembelian.index', compact('supplier')); 
   }

   public function listData()
   {
   
      $pembelian = PembelianTemporary::leftJoin('supplier', 'supplier.id_supplier', '=', 'pembelian_temporary.id_supplier')
      
     ->orderBy('pembelian_temporary.id_pembelian', 'desc')
     ->get();
     $no = 0;
     $data = array();
     foreach($pembelian as $list){
       $no ++;
       $row = array();
       $row[] = $no;
       $row[] = tanggal_indonesia(substr($list->created_at, 0, 10), false);
       $row[] = $list->nama;
       $row[] = $list->total_item;
       $row[] = "Rp. ".format_uang($list->total_harga);
       $row[] = $list->diskon."%";
       $row[] = "Rp. ".format_uang($list->bayar);
       $row[] = '<div class="btn-group">
               <a onclick="showDetail('.$list->id_pembelian.')" class="btn btn-primary btn-sm"><i class="fa fa-eye"></i></a>
               <a href="/toko-master/pembelian/'.$list->id_pembelian.'/poPDF" class="btn btn-print btn-sm" target="_blank"><i class="fa fa-print"></i></a>
              </div>';
       $data[] = $row;
     }

     $output = array("data" => $data);
     return response()->json($output);
   }

   public function show($id)
   {
   
     $detail = PembelianTemporaryDetail::leftJoin('produk', 'produk.kode_produk', '=', 'pembelian_temporary_detail.kode_produk')
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
       $row[] = "Rp. ".format_uang($list->harga_beli);
       $row[] = $list->jumlah;
       $row[] = $list->jumlah_terima;
       $row[] = $list->status_jurnal;
       $row[] = "Rp. ".format_uang($list->harga_beli * $list->jumlah);
       $data[] = $row;
     }

     $output = array("data" => $data);
     return response()->json($output);
   }




  public function cetak($id){
      $data['produk'] = DB::table('pembelian_temporary_detail','produk')
                           ->select('pembelian_temporary_detail.*','produk.kode_produk','produk.nama_produk')
                           ->leftJoin('produk','pembelian_temporary_detail.kode_produk','=','produk.kode_produk')
                           ->where('unit',Auth::user()->unit)
                           ->where('id_pembelian',$id)
                           ->get();

      $data['alamat'] = PembelianTemporary::leftJoin('supplier','pembelian_temporary.id_supplier','=','supplier.id_supplier')
                                          ->leftJoin('branch','pembelian_temporary.kode_gudang','=','branch.kode_gudang')
                                          ->where('id_pembelian',$id)
                                          ->first();
      // dd($data['alamat']);
      $data['nosurat'] = PembelianTemporary::where('id_pembelian',$id)->get();
      $data['no'] =1;
      $pdf = PDF::loadView('pembelian.cetak_po', $data);
      return $pdf->stream('surat_jalan.pdf');
    }

   public function create($id)
   {
      $pembelian = new PembelianTemporary;
      $pembelian->id_supplier = $id;     
      $pembelian->total_item = 0;     
      $pembelian->total_harga = 0;
      $pembelian->total_terima = 0;     
      $pembelian->diskon = 0;     
      $pembelian->bayar = 0;      
      $pembelian->jatuh_tempo = date('Y-m-d');
      $pembelian->kode_gudang = 0;    
      $pembelian->tipe_bayar = 2;    
      $pembelian->id_user = Auth::user()->id;
      $pembelian->kode_gudang = Auth::user()->unit;
          
      $pembelian->save();
      // dd($pembelian);
      session(['idpembelian' => $pembelian->id_pembelian]);
      session(['idsupplier' => $id]);

      return Redirect::route('pembelian_detail.index');      
   }

   public function store(Request $request)
   {
      $pembelian = PembelianTemporary::find($request['idpembelian']);
      $pembelian->total_item = $request['totalitem'];
      $pembelian->total_harga = $request['total'];
      $pembelian->diskon = $request['diskon'];
      $pembelian->bayar = $request['bayar'];
      $pembelian->update();

      // $detail = PembelianDetail::where('id_pembelian', '=', $request['idpembelian'])->get();
      // foreach($detail as $data){
      //   $produk = Produk::where('kode_produk', '=', $data->kode_produk)->first();
      //   $produk->stok += $data->jumlah;
      //   $produk->update();
      // }
      return Redirect::route('pembelian.index');
   }
   
   public function destroy($id)
   {
      $pembelian = PembelianTemporary::find($id);
      $pembelian->delete();

      $detail = PembelianTemporaryDetail::where('id_pembelian', '=', $id)->get();
      foreach($detail as $data){
        $produk = Produk::where('kode_produk', '=', $data->kode_produk)->first();
        $produk->stok -= $data->jumlah;
        $produk->update();
        $data->delete();
      }
   }
}
