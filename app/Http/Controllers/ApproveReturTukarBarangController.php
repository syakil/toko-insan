<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
use Auth;
use App\Produk;
use App\ProdukWriteOff;
use App\Kirim;
use App\KirimDetail;
use App\KartuStok;
use App\KirimDetailTemporary;
use App\TabelTransaksi;
use App\Branch;
use Redirect;



class ApproveReturTukarBarangController extends Controller{
    
    public function index(){

        return view('approve_retur_tukar_barang.index');

    }

    public function listData(){

        $retur = Kirim::leftJoin('supplier', 'supplier.id_supplier', '=', 'kirim_barang.id_supplier')
                        ->select('kirim_barang.*','supplier.nama')
                        ->where('status_kirim','tukar_barang')
                        ->where('tujuan','supplier')
                        ->where('kode_gudang',Auth::user()->unit)
                        ->where('kirim_barang.status','approval')
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

        $detail = KirimDetailTemporary::leftJoin('produk', 'produk.kode_produk', '=', 'kirim_barang_detail_temporary.kode_produk')
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
            $row[] = "Rp. ".format_uang($list->harga_beli * $list->jumlah);
            $data[] = $row;
        
        }

        $output = array("data" => $data);
        return response()->json($output);

    }


    public function reject($id){

        try {

            DB::beginTransaction();

            Kirim::where('id_pembelian',$id)->update(['status' => 1]);

            DB::commit();
            return back()->with(['success' => 'Transaksi Berhasil Di Reject!']);

        }catch(\Exception $e){
         
            DB::rollback();
            return back()->with(['error' => $e->getmessage()]);
   
        }
   

    }


    public function approve($id){

        try {

            DB::beginTransaction();
                
                $id_pembelian = $id;
            
                $details = KirimDetailTemporary::where('id_pembelian', '=', $id)->orderBy('id_pembelian_detail','desc')->get();
        
                // --- //
                foreach($details as $list){
                
                    $cek_sum_kirim= KirimDetailTemporary::where('id_pembelian', $id)->where('kode_produk',$list->kode_produk)->sum('jumlah');
                    $produk_wo = ProdukWriteOff::where('kode_produk',$list->kode_produk)
                    ->where('unit',Auth::user()->unit)
                    ->where('param_status',1)
                    ->sum('stok');
            
                    if($cek_sum_kirim > $produk_wo){
                        return back()->with(['error' => 'Stock '. $list->kode_produk . ' Kurang']);
                    }      
                            
                }
        
                foreach($details as $d){
        
                    $kode = $d->kode_produk;
                    $jumlah_penjualan = $d->jumlah;
                    
                    // mengaambil stok di produk_wo berdasar barcode dan harga beli lebih rendah (stok yang tesedria) yang terdapat di kirim_detail_temporary
                    produk:
                    $produk_detail = ProdukWriteOff::where('kode_produk',$kode)
                    ->where('unit',Auth::user()->unit)
                    ->where('stok','>','0')
                    ->where('param_status',1)
                    ->orderBy('tanggal_input','ASC')
                    ->first();
                    
                    // buat variable stok toko dari column stok dari table produk_wo
                    $stok_toko = $produk_detail->stok;
               
                    // jika qty penjualan == jumlah stok yang tersedia di gudang
                    if ($jumlah_penjualan == $stok_toko) {
                        
                        $detail = new KirimDetail;
                        $detail->id_pembelian = $id;
                        $detail->kode_produk = $kode;
                        $detail->harga_jual = $produk_detail->harga_jual;
                        $detail->harga_beli = $produk_detail->harga_beli;
                        $detail->jumlah = $jumlah_penjualan;
                        $detail->jumlah_terima = 0;
                        $detail->sub_total = $produk_detail->harga_beli * $jumlah_penjualan;
                        $detail->sub_total_terima = 0;
                        $detail->sub_total_margin = $produk_detail->harga_jual * $jumlah_penjualan;
                        $detail->sub_total_margin_terima = 0;
                        $detail->expired_date = $d->expired_date;
                        $detail->jurnal_status = 0;
                        $detail->no_faktur = $produk_detail->no_faktur;
                        $detail->save();
        
                        $kartu_stok = new KartuStok;
                        $kartu_stok->buss_date = date('Y-m-d');
                        $kartu_stok->kode_produk = $produk_detail->kode_produk;
                        $kartu_stok->masuk = 0;
                        $kartu_stok->keluar = $jumlah_penjualan;
                        $kartu_stok->status = 'kirim_barang_retur';
                        $kartu_stok->kode_transaksi = $id;
                        $kartu_stok->unit = Auth::user()->unit;
                        $kartu_stok->save();
        
                        $produk_detail->stok = 0;
                        $produk_detail->update();
                        // jika selisih qty retur dengan jumlah stok yang tersedia
                    
                    }else {
                        
                        // mengurangi qty retur dengan stok unit berdasarkan stok(table produk_wo)
                        $stok = $jumlah_penjualan - $stok_toko;
            
                        // jika hasilnya lebih dari nol atau tidak minus, stok tsb tidak memenuhi qty penjualan dan harus ambil lagi record pada produk detail~
                        // ~ yang stok nya lebih dari nol
            
                        if ($stok >= 0) {
            
                            // update produk_detail->stok menjadi nol berdasarkan $produk_detail                             
                            $detail = new KirimDetail;
                            $detail->id_pembelian = $id;
                            $detail->kode_produk = $kode;
                            $detail->harga_jual = $produk_detail->harga_jual;
                            $detail->harga_beli = $produk_detail->harga_beli;
                            $detail->jumlah = $stok_toko;
                            $detail->jumlah_terima = 0;
                            $detail->sub_total = $produk_detail->harga_beli * $stok_toko;
                            $detail->sub_total_terima = 0;
                            $detail->sub_total_margin = $produk_detail->harga_jual * $stok_toko;
                            $detail->sub_total_margin_terima = 0;
                            $detail->expired_date = $d->expired_date;
                            $detail->jurnal_status = 0;
                            $detail->no_faktur = $produk_detail->no_faktur;
                            $detail->save();
        
                            $kartu_stok = new KartuStok;
                            $kartu_stok->buss_date = date('Y-m-d');
                            $kartu_stok->kode_produk = $produk_detail->kode_produk;
                            $kartu_stok->masuk = 0;
                            $kartu_stok->keluar = $stok_toko;
                            $kartu_stok->status = 'kirim_barang_retur';
                            $kartu_stok->kode_transaksi = $id;
                            $kartu_stok->unit = Auth::user()->unit;
                            $kartu_stok->save();
                            
                            $produk_detail->stok = 0;
                            $produk_detail->update();
                            // sisa qty penjualan yang dikurangi stok toko yang harganya paling rendah
                            $jumlah_penjualan = $stok;
            
                            // mengulangi looping untuk mencari harga yang paling rendah
                            goto produk;
                            
                        // jika pengurangan qty penjualan dengan stok toko hasilnya kurang dari 0 atau minus
                        }else if($stok < 0){
        
                            // update stok berdasar sisa pengurangan qty penjualan dengan stok toko hasilnya kurang dari 0 atau minus
                            
                            $detail = new KirimDetail;
                            $detail->id_pembelian = $id;
                            $detail->kode_produk = $kode;
                            $detail->harga_jual = $produk_detail->harga_jual;
                            $detail->harga_beli = $produk_detail->harga_beli;
                            $detail->jumlah = $jumlah_penjualan;
                            $detail->jumlah_terima = 0;
                            $detail->sub_total = $produk_detail->harga_beli * $jumlah_penjualan;
                            $detail->sub_total_terima = 0;
                            $detail->sub_total_margin = $produk_detail->harga_jual * $jumlah_penjualan;
                            $detail->sub_total_margin_terima = 0;
                            $detail->expired_date = $d->expired_date;
                            $detail->jurnal_status = 0;
                            $detail->no_faktur = $produk_detail->no_faktur;
                            $detail->save();
                            
                            $kartu_stok = new KartuStok;
                            $kartu_stok->buss_date = date('Y-m-d');
                            $kartu_stok->kode_produk = $produk_detail->kode_produk;
                            $kartu_stok->masuk = 0;
                            $kartu_stok->keluar = $jumlah_penjualan;
                            $kartu_stok->status = 'kirim_barang_retur';
                            $kartu_stok->kode_transaksi = $id;
                            $kartu_stok->unit = Auth::user()->unit;
                            $kartu_stok->save();
                            
                            $produk_detail->stok = abs($stok);
                            $produk_detail->update();
                    
                        }    
                    }   
                }
                
        
                //  update table kirim_barang
                $total_item = KirimDetail::where('id_pembelian',$id_pembelian)->sum('jumlah');
                $total_harga = KirimDetail::where('id_pembelian',$id_pembelian)->sum('sub_total');
                $total_margin = KirimDetail::where('id_pembelian',$id_pembelian)->sum('sub_total_margin');
        
                $kirim_barang = Kirim::where('id_pembelian',$id_pembelian)->first();
                $kirim_barang->total_item = $total_item;
                $kirim_barang->total_margin = $total_margin;
                $kirim_barang->total_harga = $total_harga;
                $kirim_barang->status = 1;
                $kirim_barang->tujuan = 'gudang';
                $kirim_barang->update();
        
                //insert jurnal 
                $d = Kirim::leftJoin('supplier','kirim_barang.id_supplier','=','supplier.id_supplier')
                            ->where('id_pembelian',$id)
                            ->first();
                
                $param_tgl = \App\ParamTgl::where('nama_param_tgl','tanggal_transaksi')->where('unit',Auth::user()->id)->first();
                $tanggal = $param_tgl->param_tgl;
                    
            
                $jurnal = new TabelTransaksi;
                $jurnal->unit =  Auth::user()->unit; 
                $jurnal->kode_transaksi = $d->id_pembelian;
                $jurnal->kode_rekening = 1962000;
                $jurnal->tanggal_transaksi  = $tanggal;
                $jurnal->jenis_transaksi  = 'Jurnal System';
                $jurnal->keterangan_transaksi = 'Retur Supplier' . ' ' . $d->id_pembelian . ' ' . $d->nama;
                $jurnal->debet =$d->total_harga;
                $jurnal->kredit = 0;
                $jurnal->tanggal_posting = '';
                $jurnal->keterangan_posting = '0';
                $jurnal->id_admin = Auth::user()->id; 
                $jurnal->save();
    
                $jurnal = new TabelTransaksi;
                $jurnal->unit =  Auth::user()->unit; 
                $jurnal->kode_transaksi = $d->id_pembelian;
                $jurnal->kode_rekening = 1484000;
                $jurnal->tanggal_transaksi  = $tanggal;
                $jurnal->jenis_transaksi  = 'Jurnal System';
                $jurnal->keterangan_transaksi = 'Retur Supplier' . ' ' . $d->id_pembelian . ' ' . $d->nama;
                $jurnal->debet =0;
                $jurnal->kredit =$d->total_harga;
                $jurnal->tanggal_posting = '';
                $jurnal->keterangan_posting = '0';
                $jurnal->id_admin = Auth::user()->id; 
                $jurnal->save();
                
                // --- /

            DB::commit();
            return back()->with(['success' => 'Transaksi Berhasil Di Approve!']);

        }catch(\Exception $e){
         
            DB::rollback();
            return back()->with(['error' => $e->getmessage()]);
   
        }
    }
}
