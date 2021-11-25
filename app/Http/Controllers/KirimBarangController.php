<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Redirect;
use DB;
use App\Kirim;
use Auth;
use PDF;
use App\Supplier;
use App\KartuStok;
use App\KirimDetail;
use App\KirimDetailTemporary;
use App\Produk;
use App\ProdukDetail;
use App\Branch;
use App\TabelTransaksi;
use Carbon\Carbon;

class KirimBarangController extends Controller{

  public function index(){
      
    $supplier = Supplier::all();
    $branch = Branch::where('kode_gudang',Auth::user()->unit)->get();
    $no = 1;
    
    $kirim = Kirim::leftJoin('branch', 'branch.kode_toko', '=', 'kirim_barang.id_supplier')
    ->where('status','hold')
    ->where('kirim_barang.kode_gudang',Auth::user()->unit)
    ->orderBy('kirim_barang.updated_at', 'desc')
    ->get();

    $cek = Kirim::where('status','hold')->first();

    if ($cek == null) {
      $data = 0;
    }else{
      $data = 1;
    }

    return view('kirim_barang.index', compact('supplier','branch','kirim','no','data')); 
  }        


    public function listData(){
 
      if (Auth::user()->level==5){
        $pembelian = Kirim::leftJoin('branch', 'branch.kode_toko', '=', 'kirim_barang.id_supplier')
        ->where('status',1)
        ->orderBy('kirim_barang.id_pembelian', 'desc')
        ->get();
      }elseif(Auth::user()->level==4){
        $pembelian = Kirim::leftJoin('branch', 'branch.kode_toko', '=', 'kirim_barang.id_supplier')
        ->where('kirim_barang.kode_gudang',Auth::user()->unit)
        ->where('tujuan','toko')
        ->where('status',1)
        ->orderBy('kirim_barang.id_pembelian', 'desc')
        ->get();
      }
      
      $no = 0;
      $data = array();
      foreach($pembelian as $list){
        $no ++;
        $row = array();
        $row[] = $no;
        $row[] = $list->id_pembelian;
        $row[] = tanggal_indonesia(substr($list->created_at, 0, 10), false);
        $row[] = $list->nama_toko;
        $row[] = $list->total_item;
        $row[] = "Rp. ".format_uang($list->total_harga);
        $row[] = '<div class="btn-group">
                <a onclick="showDetail('.$list->id_pembelian.')" class="btn btn-primary btn-sm"><i class="fa fa-eye"></i></a>
                <a href="/toko/kirim_barang/'.$list->id_pembelian.'/poPDF" class="btn btn-print btn-sm" target="_blank"><i class="fa fa-print"></i></a>
              </div>';
        $data[] = $row;
      }

      $output = array("data" => $data);
      return response()->json($output);
    }

    public function show($id){

      $detail = KirimDetail::leftJoin('produk', 'produk.kode_produk', '=', 'kirim_barang_detail.kode_produk')
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
    
    session()->forget('cetak');

    $data['produk'] = KirimDetail::leftJoin('produk','kirim_barang_detail.kode_produk','=','produk.kode_produk')
                                      ->where('id_pembelian',$id)
                                      ->where('produk.unit',Auth::user()->unit)
                                      ->get();

    $data['alamat']= Kirim::leftJoin('branch','kirim_barang.id_supplier','=','branch.kode_toko')
                            ->where('id_pembelian',$id)
                            ->first();

    $kirim = Kirim::find($id);
    $pengirim = $kirim->kode_gudang;
    $penerima = $kirim->id_supplier;
    $tanggal_kirim = Carbon::createFromFormat ( "Y-m-d H:i:s", $kirim->updated_at );
    $data['alamat_pengirim'] = Branch::where('kode_toko',$kirim->kode_gudang)->first();
    $data['alamat_penerima'] = Branch::where('kode_toko',$kirim->id_supplier)->first();

    $data['nomer_surat'] = 'TRF/'.$id.'/'.$pengirim.'/'.$penerima.'/'.$tanggal_kirim->format('m/Y');
    $data['data_surat'] = Kirim::where('id_pembelian',$id)->get();
    $data['no'] =1;
    $pdf = PDF::loadView('kirim_barang.cetak_sj', $data);
    return $pdf->stream('TRF-'.$id.'-'.$pengirim.'-'.$penerima.'-'.$tanggal_kirim->format('m/Y').'.pdf');

  }

  public function create($id){

    $pembelian = new Kirim;
    $pembelian->id_supplier = $id;     
    $pembelian->total_item = 0;     
    $pembelian->total_harga = 0;     
    $pembelian->total_margin = 0;
    $pembelian->total_terima = 0;
    $pembelian->total_harga_terima = 0;
    $pembelian->total_margin_terima = 0;
    $pembelian->kode_gudang = 0;
    $pembelian->status = 'hold';
    $pembelian->id_user = Auth::user()->id;
    $pembelian->kode_gudang = Auth::user()->unit;
    $pembelian->tujuan = 'toko';
    $pembelian->status_kirim = 'transfer'; 
    $pembelian->save();    

    session(['idpembelian' => $pembelian->id_pembelian]);
    session(['idsupplier' => $id]);
    session(['kode_toko' => $id]);

    return Redirect::route('kirim_barang_detail.index');      
  }

  public function hold(Request $request){
      
    $pembelian = Kirim::find($request['idpembelian']);
    $pembelian->total_item = $request['totalitem'];
    $pembelian->total_harga = $request['total'];
    $pembelian->diskon = $request['diskon'];
    $pembelian->bayar = $request['bayar'];
    $pembelian->update();
    
    return view('kirim_barang.index', compact('supplier','branch')); 
      
  }

  public function store(Request $request){

    $id_pembelian = $request['idpembelian'];

    $total_item = KirimDetailTemporary::where('id_pembelian',$id_pembelian)->sum('jumlah');
    $total_harga = KirimDetailTemporary::where('id_pembelian',$id_pembelian)->sum('sub_total');
    $total_margin = KirimDetailTemporary::where('id_pembelian',$id_pembelian)->sum('sub_total_margin');

    $kirim_barang = Kirim::where('id_pembelian',$id_pembelian)->first();
    $kirim_barang->total_item = $total_item;
    $kirim_barang->total_margin = $total_margin;
    $kirim_barang->total_harga = $total_harga;
    $kirim_barang->update();

    $pembelian = Kirim::find($request['idpembelian']);
    $pembelian->status = 'approval';
    $pembelian->update();
    $request->session()->forget('idpembelian');
    session(['cetak'=>$request['idpembelian']]);
  
    return Redirect::route('kirim_barang.index')->with(['success' => 'Surat Jalan Berhasil Di Buat !']); 
      
  }

    public function destroy($id){
      $pembelian = Kirim::find($id);
      $pembelian->delete();

      $detail = KirimDetail::where('id_pembelian', '=', $id)->get();
      foreach($detail as $data){
        $produk = ProdukDetail::where('kode_produk', '=', $data->kode_produk)
                                ->where('expired_date',$data->expired_date)                      
                                ->first();
        $produk->stok += $data->jumlah;
        $produk->update();
        $data->delete();
      }
    }
}


