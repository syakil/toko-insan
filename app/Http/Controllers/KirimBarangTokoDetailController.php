<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Redirect;
use App\Kirim;
use App\Supplier;
use Auth;
use App\Produk;
use App\ProdukDetail;
use App\KirimDetailTemporary;
use App\Branch;
use DB;

class KirimBarangTokoDetailController extends Controller
{
   public function  index(){
      $produk = Produk::where('unit', '=', Auth::user()->unit)->where('stok','>',0)->get();
      $idpembelian = session('idpembelian');
      $supplier = Supplier::find(session('idsupplier'));
      $branch = Branch::find(session('kode_toko'));

      return view('kirim_barang_toko_detail.index', compact('produk', 'idpembelian', 'supplier','branch'));
   }


   public function continued_hold($id){
      
      $produk = Produk::where('unit', '=', Auth::user()->unit)->where('stok','>',0)->get();
      $kirim = Kirim::where('id_pembelian',$id)->first();
      $idpembelian = $kirim->id_pembelian;
      $supplier = $kirim->id_supplier;
      $branch = Branch::find($kirim->id_supplier);

      return view('kirim_barang_toko_detail.index', compact('produk', 'idpembelian', 'supplier','branch'));
   }


   public function listData($id){
   
      $detail = KirimDetailTemporary::leftJoin('produk', 'produk.kode_produk', '=', 'kirim_barang_detail_temporary.kode_produk')
         ->where('id_pembelian', '=', $id)
         ->where('unit', '=', Auth::user()->unit)
         ->select('kirim_barang_detail_temporary.*','produk.kode_produk','produk.nama_produk','produk.stok')
         ->orderBy('updated_at','desc')        
         ->get();

      $status = DB::table('status_kirim')->get();

      $no = 0;
      $data = array();
      $total = 0;
      $total_item = 0;
      foreach($detail as $list){
         
         $option = "";
         
         foreach($status as $v){

            if ($v->id_status == $list->keterangan) {
               $option .= "<option value='$v->id_status' selected >".$v->status."</option>";
            }else {
               $option .= "<option value='$v->id_status' >".$v->status."</option>";
            }
         
         }

         // dd($option);
         $no ++;
         $row = array();
         $row[] = $no;
         $row[] = $list->kode_produk;
         $row[] = $list->nama_produk;
         $row[] = $list->stok;
         $row[] = "<input type='text' class='form-control' name='jumlah_$list->id_pembelian_detail' value='$list->jumlah' onChange='changeCount($list->id_pembelian_detail)'>";
         // $row[] = "<input type='text' class='form-control' id='exp_$list->id_pembelian_detail' name='expired_$list->id_pembelian_detail' value='$list->expired_date' onChange='changeExpired($list->id_pembelian_detail)'>";
         $row[] = "
            <select class='form-control' id='keterangan' name='keterangan_$list->id_pembelian_detail' onChange='changeKeterangan($list->id_pembelian_detail)'>
            <option selected disabled> Pilih Keterangan </option>".   
            $option.
            "</select>";
         $row[] = '<a onclick="deleteItem('.$list->id_pembelian_detail.')" class="btn btn-danger btn-sm"><i class="fa fa-trash"></i></a>';
         $data[] = $row;
         $total += $list->harga_beli * $list->jumlah_terima;
         $total_item += $list->jumlah_terima;
      }
   
      $data[] = array("<span class='hide total'>$total</span><span class='hide totalitem'>$total_item</span>", "", "", "", "", "", "");
      
      $output = array("data" => $data);
      return response()->json($output);
   
   }

   public function store(Request $request){

      $unit = Auth::user()->unit;
      $produk = DB::table('produk')
      ->where('produk.kode_produk','like','%'.$request['kode'])
      ->where('produk.unit',Auth::user()->unit)
      ->first();
              
      $detail = new KirimDetailTemporary;
      $detail->id_pembelian = $request['idpembelian'];
      $detail->kode_produk = $produk->kode_produk;
      $detail->harga_jual = $produk->harga_jual_member_insan;
      $detail->harga_beli = $produk->harga_beli;
      $detail->jumlah = '';
      $detail->jumlah_terima = 0;
      $detail->sub_total = $produk->harga_beli;
      $detail->sub_total_terima = $produk->harga_beli;
      $detail->sub_total_margin = $produk->harga_jual_member_insan;
      $detail->sub_total_margin_terima = 0;
      $detail->expired_date = '';
      $detail->jurnal_status = 0;
      $detail->save();
      
      $total = KirimDetailTemporary::where('id_pembelian',$request['idpembelian'])->sum('jumlah');
      
      $data = array(
         "tota" => $total,
         );
      return response()->json($data);
   }

   public function update(Request $request, $id)
   {
      $detail = KirimDetailTemporary::find($id);
      
      $unit = Auth::user()->unit;

      $nama_input = "jumlah_".$id;
      
      $produk = Produk::where('kode_produk',$detail->kode_produk)->where('unit',$unit)->first();

      // jika yang di input menggandung alpahabet
      if(is_numeric($request[$nama_input]) == false){
         
         $data = array(
            "alert" => "Masukan Nominal Jumlah dengan Ankga",
            );
         return response()->json($data);
      
      }

      // jika stok kurang
      if ($produk->stok < $request[$nama_input]) {

         $data = array(
            "alert" => "Stok Kurang",
            );
         return response()->json($data);

      }else {
         // jika stok mencukupi
         $detail->jumlah = $request[$nama_input];
         $detail->sub_total = $detail->harga_beli * $request[$nama_input];
         $detail->sub_total_margin = $detail->harga_jual * $request[$nama_input];
         $detail->update();            
      }      


   }

   
   public function keterangan(Request $request, $id){
      
      $keterangan = "keterangan_".$id;
      
      $detail = KirimDetailTemporary::find($id);
      $detail->keterangan = $request[$keterangan];
      $detail->update();
   }

   
   public function destroy($id)
   {
      $detail = KirimDetailTemporary::find($id);
      $detail->delete();
   }

   public function loadForm($id){

      $total = KirimDetailTemporary::where('id_pembelian',$id)->sum('jumlah');

      $data = array(
         "totalitem" => format_uang($total),
         );
      return response()->json($data);
   }
}


