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


class KirimBarangTokoController extends Controller{

    public function index(){
      $supplier = Supplier::all();
      $branch = Branch::where('kode_toko',Auth::user()->unit)
                      ->get();
      return view('kirim_barang_toko.index', compact('supplier','branch')); 
    }

    public function listData(){
      if (Auth::user()->level==5){
        $pembelian = Kirim::leftJoin('branch', 'branch.kode_toko', '=', 'kirim_barang.id_supplier')
        ->where('kirim_barang.kode_gudang',Auth::user()->unit)
        ->orderBy('kirim_barang.id_pembelian', 'desc')
        ->get();
      }elseif(Auth::user()->level==4){
        $pembelian = Kirim::leftJoin('branch', 'branch.kode_gudang', '=', 'kirim_barang.id_supplier')
        ->where('kirim_barang.kode_gudang',Auth::user()->unit)  
        ->orderBy('kirim_barang.id_pembelian', 'desc')
        ->get();
      }
      
      $no = 0;
      $data = array();
      foreach($pembelian as $list){
        $no ++;
        $row = array();
        $row[] = $no;
        $row[] = tanggal_indonesia(substr($list->created_at, 0, 10), false);
        $row[] = $list->nama_gudang;
        $row[] = $list->total_item;
        $row[] = "Rp. ".format_uang($list->total_harga);
        $row[] = $list->diskon."%";
        $row[] = "Rp. ".format_uang($list->bayar);
        $row[] = '<div class="btn-group">
                <a onclick="showDetail('.$list->id_pembelian.')" class="btn btn-primary btn-sm"><i class="fa fa-eye"></i></a>
                <a onclick="deleteData('.$list->id_pembelian.')" class="btn btn-danger btn-sm"><i class="fa fa-trash"></i></a>
                <a href="/toko-master/kirim_barang_toko/'.$list->id_pembelian.'/poPDF" class="btn btn-print btn-sm" target="_blank"><i class="fa fa-print"></i></a>
              </div>';
        $data[] = $row;
      }

      $output = array("data" => $data);
      return response()->json($output);
    }

    public function show($id){

      $detail = KirimDetail::leftJoin('produk_detail', 'produk_detail.kode_produk', '=', 'kirim_barang_detail.kode_produk')
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
    $data['produk'] = KirimDetail::leftJoin('produk_detail','kirim_barang_detail.kode_produk','=','produk_detail.kode_produk')
                                      ->where('id_pembelian',$id)
                                      ->where('produk_detail.unit',Auth::user()->unit)
                                      ->get();

    $data['alamat'] = Kirim::leftJoin('branch','kirim_barang.id_supplier','=','branch.kode_gudang')
                            ->where('id_pembelian',$id)
                            ->get();
                                
    $data['nosurat'] = Kirim::where('id_pembelian',$id)->get();
    $data['no'] =1;
    $pdf = PDF::loadView('kirim_barang_toko.cetak_sj', $data);
    return $pdf->stream('surat_jalan.pdf');
    }

    public function create($id){
      $pembelian = new Kirim;
      $pembelian->id_supplier = $id;     
      $pembelian->total_item = 0;     
      $pembelian->total_harga = 0;     
      $pembelian->diskon = 0;     
      $pembelian->bayar = 0;  
      $pembelian->kode_gudang = 0;
      //total_terima = 0;
      $pembelian->total_terima = 0;
      // tambah field total_harga_terima di table kirim_barang not null
      $pembelian->total_harga_terima = 0;
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

    public function store(Request $request){
      $pembelian = Kirim::find($request['idpembelian']);
      $pembelian->total_item = $request['totalitem'];
      $pembelian->total_harga = $request['total'];
      $pembelian->diskon = $request['diskon'];
      $pembelian->bayar = $request['bayar'];
      $pembelian->update();

      $detail = KirimDetail::where('id_pembelian', '=', $request['idpembelian'])->get();
      
      // delete
      // foreach($detail as $data){
      //   $produk = Produk::where('kode_produk', '=', $data->kode_produk)->first();
      //   $produk->stok += $data->jumlah;
      //   $produk->update();
      // }
      
      //code syakil
      
      // code mengurangi produk
      foreach($detail as $d){
        // kode produk
        $kode = $d->kode_produk;
        
        // buat variable stok_dikirim dari field jumlah dari table kirim_detail
        $stok_dikirim = $d->jumlah;
        $stok_dikirim2 = $d->jumlah;
        $now = \Carbon\Carbon::now();
        
        // mengaambil stok di produk_detail berdasar barcode dan expired date lebih awal yang terdapat di kirim_detail
        produk:
        $produk_detail = ProdukDetail::where('kode_produk',$kode)
        ->where('unit',Auth::user()->unit)
        ->where('expired_date','>',$now)
        ->where('stok_detail','>','0')
        ->orderBy('expired_date','ASC')
        ->first();
        // jika produk ybs kosong krim pesan eror
        // dd($produk_detail);
        if ($produk_detail == null) {
          return view('kirim_barang_toko.index')->with(['error' => 'Stock Kosong/Kadaluarsa']);
        }
        // else

        // buat variable stok gudan dari field stok_detail dari table produk_detail
        $stok_gudang = $produk_detail->stok_detail;
        
        // mengurangi stok kirim dengan stok gudang
        $stok = $stok_dikirim - $stok_gudang;
        // sisa pengurangan diatas menjadi stok yang dkirim
        $stok_dikirim = $stok;
        
        // jika hasilnya lebih dari nol
        if ($stok >= 0) {
            // update produk_detail->stok_detail menjadi nol berdasar barcode dan tgl expired
            $produk_detail->update(['stok_detail'=>0]);

            // mengulangi looping
            goto produk;
          }else if($stok < 0){
            // else
            // update stok berdasar sisa pengurangan
            $produk_detail->update(['stok_detail'=>abs($stok)]);
        }
        
      }

      foreach($detail as $d){
        // kode produk
        $kode = $d->kode_produk;
        
        // buat variable stok_dikirim dari field jumlah dari table kirim_detail
        $stok_dikirim = $d->jumlah;
        
        // update stok-> produk
        $produk_inti = Produk::where('kode_produk',$kode)
        ->where('unit',Auth::user()->unit)->get();
        // dd($stok_baru);
        foreach ($produk_inti as $prod) {
          $update = Produk::where('kode_produk',$kode)
                                ->where('unit',Auth::user()->unit);
          $stok_baru = $prod->stok - $stok_dikirim;
          $update->update(['stok'=> $stok_baru]);
        }
      }
      //insert jurnal 
      $data = Kirim::leftJoin('branch','kirim_barang.id_supplier','=','branch.kode_toko')
                  ->where('id_pembelian',$request['idpembelian'])
                  ->get();
                  
      foreach($data as $d){
        $jurnal = new TabelTransaksi;
        $jurnal->unit =  Auth::user()->unit; 
        $jurnal->kode_transaksi = $d->id_pembelian;
        $jurnal->kode_rekening = 1482000;
        $jurnal->tanggal_transaksi  = date('Y-m-d');
        $jurnal->jenis_transaksi  = 'Jurnal System';
        $jurnal->keterangan_transaksi = 'ReturGudang' . ' ' . $d->id_pembelian . ' ' . $d->nama_toko;
        $jurnal->debet =$d->total_harga;
        $jurnal->kredit = 0;
        $jurnal->tanggal_posting = '';
        $jurnal->keterangan_posting = '0';
        $jurnal->id_admin = Auth::user()->id; 
        $jurnal->save();

        $jurnal = new TabelTransaksi;
        $jurnal->unit =  Auth::user()->unit; 
        $jurnal->kode_transaksi = $d->id_pembelian;
        $jurnal->kode_rekening = 1482000;
        $jurnal->tanggal_transaksi  = date('Y-m-d');
        $jurnal->jenis_transaksi  = 'Jurnal System';
        $jurnal->keterangan_transaksi = 'ReturGudang' . ' ' . $d->id_pembelian . ' ' . $d->nama_toko;
        $jurnal->debet =0;
        $jurnal->kredit =$d->total_harga;
        $jurnal->tanggal_posting = '';
        $jurnal->keterangan_posting = '0';
        $jurnal->id_admin = Auth::user()->id; 
        $jurnal->save();
      }
      // --- /kode syakil ---
      $branch = Branch::where('kode_toko',Auth::user()->unit)
                      ->get();
      return view('/kirim_barang_toko/index', compact('supplier','branch'));
      
    }

    public function destroy($id){
      $pembelian = Kirim::find($id);
      $pembelian->delete();

      $detail = KirimDetail::where('id_pembelian', '=', $id)->get();
      foreach($detail as $data){
        $produk = ProdukDetail::where('kode_produk', '=', $data->kode_produk)->first();
        $produk->stok -= $data->jumlah;
        $produk->update();
        $data->delete();
      }
    }
}
