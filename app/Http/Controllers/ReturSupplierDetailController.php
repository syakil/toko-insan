<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Redirect;
use App\Kirim;
use App\Supplier;
use Auth;
use App\ProdukWriteOff;
use App\Produk;
use App\ProdukDetail;
use App\KirimDetail;
use App\KirimDetailTemporary;
use App\Branch;
use DB;

class ReturSupplierDetailController extends Controller
{
   public function  index(){
      
      $produk = ProdukWriteOff::where('unit',Auth::user()->unit)
      ->where('stok','>',0)
      ->where('param_status',1)
      ->get();
      
      $idpembelian = session('idpembelian');
      $supplier = Supplier::find(session('idsupplier'));
      $branch = Branch::find(session('kode_gudang'));

      return view('retur_supplier_detail.itndex', compact('produk', 'idpembelian', 'supplier','branch'));
   }

   public function listData($id){
      
     $detail = KirimDetailTemporary::leftJoin('produk', 'produk.kode_produk', '=', 'kirim_barang_detail_temporary.kode_produk')
     ->where('id_pembelian', '=', $id)
     ->where('unit', '=', Auth::user()->unit)
     ->select('kirim_barang_detail_temporary.*','produk.kode_produk','produk.nama_produk','produk.stok')
     ->orderBy('updated_at','desc')        
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
         $row[] = "<input type='text' class='form-control' name='jumlah_$list->id_pembelian_detail' value='$list->jumlah' onChange='changeCount($list->id_pembelian_detail)'>";
         $row[] = "<input type='date' class='form-control' name='expired_$list->id_pembelian_detail' value='$list->expired_date' onChange='changeCount($list->id_pembelian_detail)'>";
         $row[] = '<a onclick="deleteItem('.$list->id_pembelian_detail.')" class="btn btn-danger btn-sm"><i class="fa fa-trash"></i></a>';
         $data[] = $row;
         $total += $list->harga_beli * $list->jumlah;
         $total_item += $list->jumlah;
      
      }

      $data[] = array("<span class='hide total'>$total</span><span class='hide totalitem'>$total_item</span>", "", "","", "", "", "", "");

      $output = array("data" => $data);
      return response()->json($output);
   
   }
   
   public function store(Request $request){
    
      $produk = ProdukWriteOff::where('kode_produk',$request['kode'])->where('unit',Auth::user()->unit)->first();

      $detail = new KirimDetailTemporary;
      $detail->id_pembelian = $request['idpembelian'];
      $detail->kode_produk = $request['kode'];
      $detail->harga_jual = $produk->harga_jual;
      $detail->jumlah = 1;
      $detail->expired_date = date('Y-m-d');
      $detail->jumlah_terima = 0;
      $detail->sub_total = $produk->harga_jual;
      $detail->sub_total_terima = 0;
      $detail->jurnal_status = 0;
      $detail->save();
   }
   
   public function update(Request $request, $id){

      $nama_input = "jumlah_".$id;
      $exp_input = "expired_".$id;

      $detail = KirimDetailTemporary::find($id);
      $detail->jumlah = $request[$nama_input];
      $detail->expired_date = $request[$exp_input];
      $detail->sub_total = $detail->harga_jual * $request[$nama_input];
      $detail->update();
   }

   public function destroy($id){

      $detail = KirimDetailTemporary::find($id);
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
