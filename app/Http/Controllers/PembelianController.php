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
      $supplier = Supplier::where('status',0)->get();
      return view('pembelian.index', compact('supplier')); 
   }

   public function listData(){
   
      $pembelian = PembelianTemporary::leftJoin('supplier', 'supplier.id_supplier', '=', 'pembelian_temporary.id_supplier')
      ->leftJoin('pembelian_status','pembelian_status.id_status','pembelian_temporary.status')
      ->where('kode_gudang',Auth::user()->unit)
      ->where('pembelian_temporary.status','!=',null)
      ->select('pembelian_temporary.*','supplier.nama','pembelian_status.keterangan')
      ->orderBy('pembelian_temporary.id_pembelian','desc')
      ->get();

      $no = 0;
      $data = array();
      foreach($pembelian as $list){
         $no ++;
         $row = array();
         $row[] = $no;
         $row[] = $list->id_pembelian;
         $row[] = tanggal_indonesia($list->created_at);
         $row[] = $list->nama;
         $row[] = $list->total_item;
         $row[] = "Rp. ".format_uang($list->total_harga);
         
         switch ($list->status) {
            case '1':
               $row[] = '<span class="label label-info">'.$list->keterangan.'</span>';
               break;
            
            case '2':
               $row[] = '<span class="label label-primary">'.$list->keterangan.'</span>';
               break;
               
            case '2':
               $row[] = '<span class="label label-warning">'.$list->keterangan.'</span>';
               break;

            default:
               $row[] = '<span class="label label-warning">'.$list->keterangan.'</span>';
               break;
         }

         $row[] = '<div class="btn-group">
                  <a onclick="showDetail('.$list->id_pembelian.')" class="btn btn-primary btn-sm"><i class="fa fa-eye"></i></a>
                  <a href="/toko/pembelian/'.$list->id_pembelian.'/poPDF" class="btn btn-print btn-sm" target="_blank"><i class="fa fa-print"></i></a>
               </div>';
         $data[] = $row;
      }

      $output = array("data" => $data);
      return response()->json($output);
   }

   public function show($id){
   
      $detail = PembelianTemporaryDetail::
         select('pembelian_temporary_detail.*','produk.nama_produk')
         ->leftJoin('produk', 'produk.kode_produk', '=', 'pembelian_temporary_detail.kode_produk')
         ->where('id_pembelian', '=', $id)
         ->where('unit',Auth::user()->unit)

         ->get();
    
      $no = 0;
      $data = array();
      foreach($detail as $list){ 
         $no ++;
         $row = array();
         if ($list->status == null) {
            $row[] = '<input type="checkbox" class="check_koreksi" name="id_detail[]" value="'.$list->id_pembelian_detail.'">';
         }else {
            $row[] = '<input type="checkbox" disabled>';
         }
         $row[] = $no;
         $row[] = $list->kode_produk;
         $row[] = $list->nama_produk;
         $row[] = "Rp. ".format_uang($list->harga_beli);
         $row[] = $list->jumlah;
         $row[] = $list->jumlah_terima;
            
         if ($list->jumlah_terima == 0) {
            $row[] = '<span class="label label-warning">Belum Diterima</span>';
         }elseif($list->jumlah > $list->jumlah_terima){
            $row[] = '<span class="label label-danger">Kurang</span>';
         }else {
            $row[] = '<span class="label label-success">Lengkap</span>';
         }

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

   public function create($id){

      $pembelian = new PembelianTemporary;
      $pembelian->id_supplier = $id;     
      $pembelian->total_item = 0;     
      $pembelian->total_harga = 0;
      $pembelian->total_terima = 0;     
      $pembelian->diskon = 0;     
      $pembelian->bayar = 0;      
      $pembelian->jatuh_tempo = date('Y-m-d');
      $pembelian->kode_gudang = 0;    
      $pembelian->tipe_bayar = 0;    
      $pembelian->status = null;
      $pembelian->id_user = Auth::user()->id;
      $pembelian->kode_gudang = Auth::user()->unit;
          
      $pembelian->save();
      // dd($pembelian);
      session(['idpembelian' => $pembelian->id_pembelian]);
      session(['idsupplier' => $id]);

      return Redirect::route('pembelian_detail.index');      
   }

   public function store(Request $request){

      $pembelian = PembelianTemporary::find($request['idpembelian']);
      $pembelian->total_item = $request['totalitem'];
      $pembelian->total_harga = $request['total'];
      $pembelian->diskon = $request['diskon'];
      $pembelian->bayar = $request['bayar'];
      $pembelian->status = 1;
      $pembelian->update();
      
      $request->session()->forget('idpembelian');
      $request->session()->forget('idsupplier');
      return Redirect::route('pembelian.index');

   
   }
   
   public function destroy($id){

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
