<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Redirect;
use App\Kirim;
use App\Supplier;
use Auth;
use App\Produk;
use App\ProdukDetail;
use App\KirimDetail;
use App\Branch;
use DB;

class KirimBarangDetailController extends Controller
{
   public function  index(){
      $produk = ProdukDetail:: all() 
      ->where('unit', '=', Auth::user()->unit);
      $idpembelian = session('idpembelian');
      $supplier = Supplier::find(session('idsupplier'));
      $branch = Branch::find(session('kode_toko'));

      return view('kirim_barang_detail.index', compact('produk', 'idpembelian', 'supplier','branch'));
   }
    public function listData($id)
   {
   
     $detail = KirimDetail::leftJoin('produk_detail', 'produk_detail.kode_produk', '=', 'kirim_barang_detail.kode_produk')
        ->where('id_pembelian', '=', $id)
        ->where('unit', '=', Auth::user()->unit)        
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
      $produk = DB::table('produk_detail','produk')
                  ->leftJoin('produk','produk_detail.kode_produk','=','produk.kode_produk')
                  ->select('produk_detail.*','produk.kode_produk','produk.harga_jual')
                  ->where('produk_detail.kode_produk',$request['kode'])
                  ->where('produk_detail.unit',Auth::user()->unit)
                  ->first();
      $detail = new KirimDetail;
      $detail->id_pembelian = $request['idpembelian'];
      $detail->kode_produk = $request['kode'];
      $detail->harga_jual = $produk->harga_jual;
      $detail->jumlah = 1;
      $detail->expired_date =$produk->expired_date;
      $detail->jumlah_terima = 0;
      $detail->sub_total = $produk->harga_beli;
      $detail->sub_total_terima = 0;
      $detail->jurnal_status = 0;
      $detail->save();
   }
   public function update(Request $request, $id)
   {
      $nama_input = "jumlah_".$id;
      $detail = KirimDetail::find($id);
      $detail->jumlah = $request[$nama_input];
      $detail->sub_total = $detail->harga_beli * $request[$nama_input];
      $detail->update();
   }
   public function destroy($id)
   {
      $detail = KirimDetail::find($id);
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