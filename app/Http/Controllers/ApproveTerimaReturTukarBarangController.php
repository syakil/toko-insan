<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Redirect;
use App\Pembelian;
use Auth;
use PDF;
use App\Supplier;
use App\Kategori;
use App\Kirim;
use App\KirimDetail;
use App\Produk;
use App\ProdukDetail;
use DB;
use App\TabelTransaksi;
use Session;
use App\KartuStok;
use App\PembelianDetail;



class ApproveTerimaReturTukarBarangController extends Controller{

    public function index(){

        return view('approve_terima_retur_tukar_barang.index');

    }


    public function listData(){

        $retur = Kirim::leftJoin('supplier', 'supplier.id_supplier', '=', 'kirim_barang.id_supplier')
                        ->select('kirim_barang.*','supplier.nama')
                        ->where('status_kirim','tukar_barang')
                        ->where('tujuan','gudang')
                        ->where('kode_gudang',Auth::user()->unit)
                        ->where('kirim_barang.status','4')
                        ->get();
        
        $no = 0;
        $data = array();
        foreach($retur as $list){

            $no ++;
            $row = array();
            $row[] = $no;
            $row[] = tanggal_indonesia($list->created_at);
            $row[] = $list->nama;
            $row[] = $list->total_item;
            $row[] = "Rp. ".format_uang($list->total_harga);
            $row[] = '<div class="btn-group">
                        <a onclick="showDetail('.$list->id_pembelian.')" class="btn btn-primary btn-sm"><i class="fa fa-eye"></i></a>
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
            $row[] = $list->jumlah;
            $row[] = $list->jumlah_terima;
            $data[] = $row;
        
        }

        $output = array("data" => $data);
        return response()->json($output);

    }

    public function reject($id){

        try {

            DB::beginTransaction();

            Kirim::where('id_pembelian',$id)->update(['status' => 'hold']);

            DB::commit();
            return back()->with(['success' => 'Transaksi Berhasil Di Reject!']);

        }catch(\Exception $e){
         
            DB::rollback();
            return back()->with(['error' => $e->getmessage()]);
   
        }
   

    }

    public function approve($id){

        
        $id_pembelian = $id;


        try {
                
            DB::beginTransaction();

            $param_tgl = \App\ParamTgl::where('nama_param_tgl','tanggal_transaksi')->where('unit',Auth::user()->id)->first();

            $tanggal = $param_tgl->param_tgl;        
            

            $kirim = Kirim::where('id_pembelian',$id)->first();
            $kirim_detail = KirimDetail::where('id_pembelian',$id)->get();
                            
            foreach ($kirim_detail as $p ) {
                    
                $produk_main = Produk::where('kode_produk',$p->kode_produk)
                ->where('unit', Auth::user()->unit)
                ->first();
                
                $produk_main->stok += $p->jumlah_terima;
                $produk_main->update();

                $produk_detail = new ProdukDetail;
                $produk_detail->kode_produk = $p->kode_produk;
                $produk_detail->nama_produk = $produk_main->nama_produk;
                $produk_detail->stok_detail = $p->jumlah_terima;
                $produk_detail->harga_beli = $p->harga_beli;
                $produk_detail->harga_jual_umum = 0;
                $produk_detail->harga_jual_insan = 0;
                $produk_detail->expired_date = $p->expired_date;
                $produk_detail->promo = 0;
                $produk_detail->tanggal_masuk = date('Y-m-d');
                $produk_detail->no_faktur = $p->id_pembelian;
                $produk_detail->unit = Auth::user()->unit;
                $produk_detail->status = '1';
                $produk_detail->promo = 0;
                $produk_detail->save();
                
                $kartu_stok = new KartuStok;
                $kartu_stok->buss_date = date('Y-m-d');
                $kartu_stok->kode_produk = $p->kode_produk;
                $kartu_stok->masuk = $p->jumlah_terima;
                $kartu_stok->keluar = 0;
                $kartu_stok->status = 'terima_tukar_barang';
                $kartu_stok->kode_transaksi = $p->id_pembelian;
                $kartu_stok->unit = Auth::user()->unit;
                $kartu_stok->save();
            }
            
            // Persediaan Barang Dagang
            $jurnal = new TabelTransaksi;
            $jurnal->unit =  Auth::user()->unit; 
            $jurnal->kode_transaksi = $id;
            $jurnal->kode_rekening = 1482000;
            $jurnal->tanggal_transaksi  = $tanggal;
            $jurnal->jenis_transaksi  = 'Jurnal System';
            $jurnal->keterangan_transaksi = 'Terima Retur Tukar Barang ' . $kirim->id_pembelian;
            $jurnal->debet = $kirim->total_harga_terima;
            $jurnal->kredit = 0;
            $jurnal->tanggal_posting = '';
            $jurnal->keterangan_posting = '0';
            $jurnal->id_admin = Auth::user()->id; 
            $jurnal->save();

            // RAK Pasiva
            $jurnal = new TabelTransaksi;
            $jurnal->unit =  Auth::user()->unit; 
            $jurnal->kode_transaksi = $id;
            $jurnal->kode_rekening = 1962000;
            $jurnal->tanggal_transaksi  = $tanggal;
            $jurnal->jenis_transaksi  = 'Jurnal System';
            $jurnal->keterangan_transaksi = 'Terima Retur Tukar Barang ' .  $kirim->id_pembelian;
            $jurnal->debet = 0;
            $jurnal->kredit = $kirim->total_harga_terima;
            $jurnal->tanggal_posting = '';
            $jurnal->keterangan_posting = '0';
            $jurnal->id_admin = Auth::user()->id; 
            $jurnal->save();


            DB::commit();
            return Redirect::route('approve_terima_po.index')->with(['success' => 'Surat Jalan Berhasil Diterima !']);
      
        }catch(\Exception $e){
           
           DB::rollback();
           return back()->with(['error' => $e->getmessage()]);
   
        }


    }

}
