<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Redirect;
use App\Kirim;
use Auth;
use PDF;
use App\Supplier;
use App\KirimDetail;
use App\Produk;
use App\ProdukDetail;
use App\Branch;
use App\TabelTransaksi;
use Carbon\Carbon;

class KirimBarangTokoController extends Controller{

  public function index(){
    
    $supplier = Supplier::all();
    $branch = Branch::where('kode_toko',Auth::user()->unit)
                    ->get();
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

    return view('kirim_barang_toko.index', compact('supplier','branch','kirim','no','data')); 
  }        


  public function listData(){

    if (Auth::user()->level==5){
      $pembelian = Kirim::leftJoin('branch', 'branch.kode_toko', '=', 'kirim_barang.id_supplier')
      ->where('tujuan','gudang')
      ->where('kirim_barang.kode_gudang',Auth::user()->unit)
      ->where('status',1)
      ->orderBy('kirim_barang.id_pembelian', 'desc')
      ->get();
    }elseif(Auth::user()->level==4){
      $pembelian = Kirim::leftJoin('branch', 'branch.kode_toko', '=', 'kirim_barang.id_supplier')
      ->where('tujuan','toko')
      ->where('kirim_barang.kode_gudang',Auth::user()->unit)
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
      $row[] = $list->diskon."%";
      $row[] = "Rp. ".format_uang($list->bayar);
      $row[] = '<div class="btn-group">
              <a onclick="showDetail('.$list->id_pembelian.')" class="btn btn-primary btn-sm"><i class="fa fa-eye"></i></a>
              <a href="/toko/kirim_barang_toko/'.$list->id_pembelian.'/poPDF" class="btn btn-print btn-sm" target="_blank"><i class="fa fa-print"></i></a>
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

    $data['alamat']= Kirim::leftJoin('branch','kirim_barang.kode_gudang','=','branch.kode_toko')
                          ->where('id_pembelian',$id)
                          ->first();

    $kirim = Kirim::find($id);
    $pengirim = $kirim->kode_gudang;
    $penerima = $kirim->id_supplier;

    $tanggal_kirim = Carbon::createFromFormat ( "Y-m-d H:i:s", $kirim->updated_at );

    $data['alamat_pengirim'] = Branch::where('kode_toko',$kirim->kode_gudang)->first();
    $data['alamat_penerima'] = Branch::where('kode_toko',$kirim->id_supplier)->first();

    $data['nomer_surat'] = 'RTR/'.$id.'/'.$pengirim.'/'.$penerima.'/'.$tanggal_kirim->format('m/Y');
    $data['data_surat'] = Kirim::where('id_pembelian',$id)->get();
    $data['no'] =1;

    $pdf = PDF::loadView('kirim_barang_toko.cetak_sj', $data);
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
    $pembelian->tujuan = 'gudang';
    $pembelian->status_kirim = 'retur'; 
    $pembelian->save();    

    session(['idpembelian' => $pembelian->id_pembelian]);
    session(['idsupplier' => $id]);
    session(['kode_toko' => $id]);

    return Redirect::route('kirim_barang_toko_detail.index');      
  }

  public function hold(Request $request){
    
    $pembelian = Kirim::find($request['idpembelian']);
    $pembelian->total_item = $request['totalitem'];
    $pembelian->total_harga = $request['total'];
    $pembelian->diskon = $request['diskon'];
    $pembelian->bayar = $request['bayar'];
    $pembelian->update();
    
    return view('kirim_barang_toko.index', compact('supplier','branch')); 
    
  }

  public function store(Request $request){

    $id_pembelian = $request['idpembelian'];
    
    $pembelian = Kirim::find($request['idpembelian']);
    //kode syakil
    $details = KirimDetail::where('id_pembelian', '=', $request['idpembelian'])->orderBy('id_pembelian_detail','desc')->get();

      // --- //
    foreach($details as $list){
      
      $cek_sum_kirim= KirimDetail::where('id_pembelian', $request['idpembelian'])->where('kode_produk',$list->kode_produk)->sum('jumlah');
      $produk = Produk::where('kode_produk',$list->kode_produk)->where('unit',Auth::user()->unit)->first();
              
      if($cek_sum_kirim > $produk->stok){
        return back()->with(['error' => 'Stock '. $list->kode_produk . ' Kurang']);
      }
      
    }


    foreach($details as $list){

      $produk = Produk::where('kode_produk',$list->kode_produk)->where('unit',Auth::user()->unit)->first();
      $produk->stok -= $list->jumlah;
      $produk->update();

    }

    //  update table kirim_barang
    $total_item = KirimDetail::where('id_pembelian',$id_pembelian)->sum('jumlah');
    $total_harga = KirimDetail::where('id_pembelian',$id_pembelian)->sum('sub_total');
    $total_margin = KirimDetail::where('id_pembelian',$id_pembelian)->sum('sub_total_margin');

    $kirim_barang = Kirim::where('id_pembelian',$id_pembelian)->first();
    $kirim_barang->total_item = $total_item;
    $kirim_barang->total_margin = $total_margin;
    $kirim_barang->total_harga = $total_harga;
    $kirim_barang->update();

    //insert jurnal 
    $data = Kirim::leftJoin('branch','kirim_barang.id_supplier','=','branch.kode_toko')
                ->where('id_pembelian',$request['idpembelian'])
                ->get();
    
    $param_tgl = \App\ParamTgl::where('nama_param_tgl','tanggal_transaksi')->where('unit',Auth::user()->id)->first();
    $tanggal = $param_tgl->param_tgl;

    
    foreach($data as $d){

      $jurnal = new TabelTransaksi;
      $jurnal->unit =  Auth::user()->unit; 
      $jurnal->kode_transaksi = $d->id_pembelian;
      $jurnal->kode_rekening = 2500000;
      $jurnal->tanggal_transaksi  = $tanggal;
      $jurnal->jenis_transaksi  = 'Jurnal System';
      $jurnal->keterangan_transaksi = 'Kirim Gudang ' . $d->id_pembelian . ' ' . $d->nama_toko;
      $jurnal->debet = $total_harga;
      $jurnal->kredit = 0;
      $jurnal->tanggal_posting = '';
      $jurnal->keterangan_posting = '0';
      $jurnal->id_admin = Auth::user()->id; 
      $jurnal->save();

      $jurnal = new TabelTransaksi;
      $jurnal->unit =  Auth::user()->unit; 
      $jurnal->kode_transaksi = $d->id_pembelian;
      $jurnal->kode_rekening = 1422000;
      $jurnal->tanggal_transaksi  = $tanggal;
      $jurnal->jenis_transaksi  = 'Jurnal System';
      $jurnal->keterangan_transaksi = 'Kirim Gudang ' . $d->id_pembelian . ' ' . $d->nama_toko;
      $jurnal->debet = $total_margin - $total_harga;
      $jurnal->kredit =0;
      $jurnal->tanggal_posting = '';
      $jurnal->keterangan_posting = '0';
      $jurnal->id_admin = Auth::user()->id; 
      $jurnal->save();

      $jurnal = new TabelTransaksi;
      $jurnal->unit =  Auth::user()->unit; 
      $jurnal->kode_transaksi = $d->id_pembelian;
      $jurnal->kode_rekening = 1482000;
      $jurnal->tanggal_transaksi  = $tanggal;
      $jurnal->jenis_transaksi  = 'Jurnal System';
      $jurnal->keterangan_transaksi = 'Kirim Gudang ' . $d->id_pembelian . ' ' . $d->nama_toko;
      $jurnal->debet =0;
      $jurnal->kredit = $total_margin;
      $jurnal->tanggal_posting = '';
      $jurnal->keterangan_posting = '0';
      $jurnal->id_admin = Auth::user()->id; 
      $jurnal->save();
    }
    // --- /kode syakil ---
    
    $kirim_status = Kirim::where('id_pembelian',$request['idpembelian'])->update(['status'=>1]);
    
    $request->session()->forget('idpembelian');

    $id = $request['idpembelian'];
    session(['cetak'=>$id]);
    return Redirect::route('kirim_barang_toko.index'); 
    
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


