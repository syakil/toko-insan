<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Redirect;
use App\PembelianTemporaryDetail;
use DB;
use App\Supplier;
use Auth;
use App\Produk;
use App\PembelianTemporary;

class PembelianDetailController extends Controller
{
   public function  index(){
      $produk = DB::table('produk')->where('unit',Auth::user()->unit)->where('produk.status',0)->get();
      $idpembelian = session('idpembelian');
      // dd($idpembelian);
      $supplier = Supplier::find(session('idsupplier'));
      return view('pembelian_detail.index', compact('produk', 'idpembelian', 'supplier'));
   }
    public function listData($id)
   {
   // dd($id);
     $detail = PembelianTemporaryDetail::leftJoin('produk', 'produk.kode_produk', '=', 'pembelian_temporary_detail.kode_produk')
         ->select('pembelian_temporary_detail.*','produk.nama_produk')
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
       $row[] = "<input type='number' class='form-control' name='harga_$list->id_pembelian_detail' value='$list->harga_beli' onChange='changeHarga($list->id_pembelian_detail)'>";
       $row[] = "<input type='number' class='form-control' name='jumlah_$list->id_pembelian_detail' value='$list->jumlah' onChange='changeCount($list->id_pembelian_detail)'>";
       $row[] = "Rp. ".format_uang($list->harga_beli * $list->jumlah);
       $row[] = '<a onclick="deleteItem('.$list->id_pembelian_detail.')" class="btn btn-danger btn-sm"><i class="fa fa-trash"></i></a>';
       $data[] = $row;
       $total += $list->harga_beli * $list->jumlah;
       $total_item += $list->jumlah;
     }
     $data[] = array("<span class='hide total'>$total</span><span class='hide totalitem'>$total_item</span>", "", "", "", "", "", "");
    
     $output = array("data" => $data);
     return response()->json($output);
   }
   public function store(Request $request)
   {
      $produk = DB::select('select kode_produk,id_kategori,harga_jual_member_insan,harga_beli,promo,diskon from produk where kode_produk = '.$request["kode"] .' and unit = '.Auth::user()->unit);
      // dd($produk);
      // $new_produk = new stdClass;
      // $new_produk->harga_beli = $produk->

      // dd($produk);
      $detail = new PembelianTemporaryDetail;
      $detail->id_pembelian = $request['idpembelian'];
      $detail->kode_produk = $produk[0]->kode_produk;
      $detail->harga_beli = $produk[0]->harga_beli;
      $detail->id_kategori =$produk[0]->id_kategori;
      $detail->jumlah = 1;
      $detail->expired_date =date('Y-m-d');
      $detail->jumlah_terima = 0;
      $detail->sub_total = $produk[0]->harga_beli;
      $detail->jurnal_status = 0;
      $detail->save();
   }
   public function update(Request $request, $id)
   {
      $nama_input = "jumlah_".$id;
      $detail = PembelianTemporaryDetail::find($id);
      $detail->jumlah = $request[$nama_input];
      $detail->sub_total = $detail->harga_beli * $request[$nama_input];
      $detail->update();
   }
public function update_harga(Request $request, $id)
   {
      $nama_input = "harga_".$id;
   
      $detail = PembelianTemporaryDetail::find($id);
      
      $detail->harga_beli = $request[$nama_input];
      $detail->sub_total = $detail->jumlah * $request[$nama_input];
      $detail->update();
   }
   public function destroy($id)
   {
      $detail = PembelianTemporaryDetail::find($id);
      $detail->delete();
   }
   public function loadForm($diskon, $total){
     $bayar = $total - ($diskon / 100 * $total);
     $data = array(
        "totalrp" => format_uang($total),
        "bayar" => $bayar,
        "bayarrp" => format_uang($bayar),
        "terbilang" => ucwords(terbilang($bayar))." Rupiah"
      );
      return response()->json($data);
   }
}
