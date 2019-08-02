<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Redirect;
use App\Pembelian;
use App\PembelianDetail;
use App\PembelianTemporaryDetail;
use App\Supplier;
use Auth;
use App\Produk;
use App\PembelianTemporary;
use Carbon\Carbon;

class TerimaPoDetailController extends Controller
{
    public function  index(){
        // dd(session('idpembelian'));
        $produk = PembelianTemporaryDetail::leftJoin('produk','pembelian_temporary_detail.kode_produk','=','produk.kode_produk')
                                    ->where('produk.unit', '=', Auth::user()->unit)
                                    ->where('id_pembelian',session('idtemporary'))
                                    ->get();
        $idpembelian = session('idpembelian');
        $supplier = Supplier::find(session('idsupplier'));
        return view('terima_po_detail.index', compact('produk', 'idpembelian', 'supplier'));
    }
    public function listData($id){
        $detail = PembelianDetail::leftJoin('produk', 'produk.kode_produk', '=', 'pembelian_detail.kode_produk')
                                    ->where('id_pembelian', '=', $id)
                                    ->where('unit', '=',  Auth::user()->unit)
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
        $row[] = $list->jumlah;
        $row[] = "<input type='number' class='form-control' name='jumlah_$list->id_pembelian_detail' value='$list->jumlah_terima' onChange='changeCount($list->id_pembelian_detail)'>";
        $row[] = "<input type='date' class='form-control' name='expired_$list->id_pembelian_detail' value='$list->expired_date' onChange='changeCount($list->id_pembelian_detail)'>";
        $row[] = '<a onclick="deleteItem('.$list->id_pembelian_detail.')" class="btn btn-danger btn-sm"><i class="fa fa-trash"></i></a>';
        $data[] = $row;
        $total += $list->harga_beli * $list->jumlah_terima;
        $total_item += $list->jumlah_terima;
        }
        $data[] = array("<span class='hide total'>$total</span><span class='hide totalitem'>$total_item</span>", "", "", "", "", "", "");
        
        $output = array("data" => $data);
        return response()->json($output);
    }

    public function store(Request $request)
    {
        $produk = Produk::where('kode_produk', '=', $request['kode'])
                        ->where('unit', '=',  Auth::user()->unit)
                        ->first();

        $pembelian = PembelianTemporaryDetail::where('id_pembelian', '=', session('idtemporary'))
                                            ->where('kode_produk', '=', $request['kode'])
                                            ->first();

        // dd(session('idtemporary'));
        $detail = new PembelianDetail;
        $detail->id_pembelian = $request['idpembelian'];
        $detail->id_kategori = $produk->id_kategori;
        $detail->kode_produk = $request['kode'];
        $detail->harga_beli = $produk->harga_beli;
        $detail->jumlah = $pembelian->jumlah;
        $detail->expired_date = date('Y-m-d');
        $detail->jumlah_terima = 0;
        $detail->jumlah_selisih = 0;
        $detail->sub_total = $produk->harga_beli * $pembelian->jumlah;
        $detail->sub_total_terima = 0;
        $detail->sub_total_selisih = 0;
        $detail->jurnal_status = 0;
        $detail->save();
    }
    public function update(Request $request, $id)
    {
        $nama_input = "jumlah_".$id;
        $exp_input = "expired_".$id;

        $detail = PembelianDetail::find($id);
        
        $detail->jumlah_terima = $request[$nama_input];
        $detail->expired_date = $request[$exp_input];
        $detail->jumlah_selisih = $detail->jumlah - $request[$nama_input];
        $detail->sub_total_terima = $detail->harga_beli * $request[$nama_input];
        $detail->update();

        // $temporary = PembelianTemporaryDetail::where('id_pembelian',$detail->id_pembelian)
        //                                 ->where('kode_produk',$detail->kode_produk)
        //                                 ->first();
        // $temporary->jumlah = $detail->jumlah_selisih;
        // $temporary->update();

    }
    public function destroy($id)
    {
        $detail = PembelianDetail::find($id);
        // $temporary = PembelianTemporaryDetail::where('id_pembelian',$detail->id_pembelian)
        //                                 ->where('kode_produk',$detail->kode_produk)
        //                                ->first();
        // $temporary->jumlah += $detail->jumlah_terima;
        // $temporary->update();
        $detail->delete();
    }
    public function loadForm($diskon, $total){
        // dd($total);
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