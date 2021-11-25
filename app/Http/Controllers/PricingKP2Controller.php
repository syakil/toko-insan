<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\PembelianTemporary;
use App\PembelianTemporaryDetail;
use App\Pembelian;
use App\PembelianDetail;
use App\TabelTransaksi;
use App\Produk;
use Auth;


class PricingKPController extends Controller
{
    public function index(){
        return view('pricing_kp/index');
    }

    public function listData(){
        
        $pembelian = PembelianTemporary::leftJoin('supplier','pembelian_temporary.id_supplier','=','supplier.id_supplier')
                                        ->leftJoin('branch','branch.kode_toko','=','pembelian_temporary.kode_gudang')
                                        ->select('pembelian_temporary.*','supplier.nama','supplier.id_supplier','branch.nama_toko')
                                        ->where('status','2')
                                        ->get();
// dd($pembelian);
        $no = 1;
        $data = array();
        foreach($pembelian as $list){
            $row = array();
            $row [] = $no++;
            $row [] = $list->id_pembelian ;
            $row [] = tanggal_indonesia($list->created_at);
            $row [] = $list->nama_toko;
            $row [] = $list->nama ;
            $row [] = 'Rp '.number_format($list->total_harga_terima) ;
            $row [] = '<a href="'. route('pricing_kp.detail',$list->id_pembelian).'" class="btn btn-sm btn-warning"><i class="fa fa-pencil"></i></a>';
            $data [] = $row; 
        }

        $output = array("data" => $data);
        return response()->json($output);
        
    }

    public function detail($id){
        $id_pembelian = $id;
        return view('pricing_kp/detail',compact('id_pembelian'));
    }

    public function listDetail($id){
        
        $pembelian_detail = PembelianTemporaryDetail::where('id_pembelian', $id)
            ->leftJoin('produk','pembelian_temporary_detail.kode_produk','produk.kode_produk')
            ->select('pembelian_temporary_detail.*','produk.harga_beli','produk.nama_produk')
            ->groupBy('produk.kode_produk')
            ->get();

        $no = 1;
        $data = array();
        
        foreach($pembelian_detail as $list){
            
            $row = array();
            
            $ptsri = (($list->total/$list->jumlah_terima)*5)/100;
            $hpp_baru = round($list->total/$list->jumlah_terima);
                
            $row [] = $no++;
            $row [] = $list->kode_produk;
            $row [] = $list->nama_produk ;
            $row [] = number_format($list->harga_beli);
            $row [] = number_format($list->jumlah_terima);
            $row [] = number_format($hpp_baru);
            $row [] = "<input type='number' class='sub_total' onChange='invoice(".$list->id_pembelian_detail.")' name='harga-invoice-".$list->id_pembelian_detail."' value='".$list->total."' style='border:none; background:transparent;'>";
            $row [] = "<input type='number' onChange='harga_jual_ni(".$list->id_pembelian_detail.")' name='harga-jual-ni-".$list->id_pembelian_detail."' value='".$list->harga_jual_ni."' style='border:none; background:transparent;'>";
            $row [] = "<input type='number' onChange='harga_jual(".$list->id_pembelian_detail.")' name='harga-jual-".$list->id_pembelian_detail."' value='".$list->harga_jual_pabrik."' style='border:none; background:transparent;'>";
            $data [] = $row; 

        }

        $output = array("data" => $data);
        return response()->json($output);
        
    }

    public function update_harga_jual(Request $request,$id){
        
        $nama_barang = 'harga-jual-'.$id;
        $harga_jual = $request[$nama_barang];

        $pembelian_detail = PembelianTemporaryDetail::where('id_pembelian_detail',$id)->first();
        
        $pembelian_detail->harga_jual_pabrik = $harga_jual;
        $pembelian_detail->update();

    }

    public function update_harga_jual_ni(Request $request,$id){
        
        $nama_barang = 'harga-jual-ni-'.$id;
        $harga_jual = $request[$nama_barang];
        
        $pembelian_detail = PembelianTemporaryDetail::where('id_pembelian_detail',$id)->first();
        
        $pembelian_detail->harga_jual_ni = $harga_jual;
        $pembelian_detail->update();

    }

    public function update_invoice(Request $request,$id){

        $nama_barang = 'harga-invoice-'.$id;
        $harga_invoice = $request[$nama_barang];

        $pembelian_detail = PembelianTemporaryDetail::where('id_pembelian_detail',$id)->first();

        $jumlah_barang = $pembelian_detail->jumlah_terima;
        $margin = round((($harga_invoice/$jumlah_barang)*5)/100);
        $harga = round($harga_invoice/$jumlah_barang);
        $harga_beli = $harga+$margin;

        $pembelian_detail->harga_beli = $harga_beli;
        $pembelian_detail->total = $harga_invoice;
        $pembelian_detail->update();

    }

    public function simpan(Request $request,$id){

        $id_pembelian = $id;

        $param_tgl = \App\ParamTgl::where('nama_param_tgl','tanggal_transaksi')->where('unit',Auth::user()->id)->first();
        $tanggal = $param_tgl->param_tgl;

        $pembelian = PembelianTemporary::where('id_pembelian',$id)
                    ->leftJoin('supplier','supplier.id_supplier','pembelian_temporary.id_supplier')
                    ->first();

        $sum_pembelian_detail = PembelianTemporaryDetail::where('id_pembelian',$id)->sum('total');

        // cek jumlah yang sudah di jurnal
        $harga_lama = $pembelian->total_harga_terima;
        $harga_baru = $sum_pembelian_detail;


        if ($harga_baru == $harga_lama) {
            
            $detail = PembelianTemporaryDetail::where('id_pembelian',$id)->get();

            // jurnal pricing
            foreach ($detail as $list ) {

                $produk_lama = Produk::where('kode_produk',$list->kode_produk)->where('unit',3000)->first();

                $jumlah_barang = $list->jumlah;

                $harga_beli_produk_lama = $produk_lama->harga_beli*$jumlah_barang;
                $harga_jual_produk_lama = $produk_lama->harga_jual*$jumlah_barang;
                
                $harga_beli_produk_baru = $list->harga_beli*$jumlah_barang;
                $harga_jual_produk_baru = $list->harga_jual_pabrik*$jumlah_barang;
                
                $margin_lama = $harga_jual_produk_lama-$harga_beli_produk_lama;
                $margin_baru = $harga_jual_produk_baru-$harga_beli_produk_baru; 


                if ($margin_lama <= 0) {
                    
                    $jurnal = new TabelTransaksi;
                    $jurnal->unit =  $pembelian->kode_gudang; 
                    $jurnal->kode_transaksi = $pembelian->id_pembelian;
                    $jurnal->kode_rekening = 1482000;
                    $jurnal->tanggal_transaksi  = $tanggal;
                    $jurnal->jenis_transaksi  = 'Jurnal System';
                    $jurnal->keterangan_transaksi = 'Pricing-' . ' ' . $list->kode_produk;
                    $jurnal->debet = $margin_baru;
                    $jurnal->kredit = 0;
                    $jurnal->tanggal_posting = '';
                    $jurnal->keterangan_posting = '0';
                    $jurnal->id_admin = Auth::user()->id; 
                    $jurnal->save();

                    $jurnal = new TabelTransaksi;
                    $jurnal->unit =  $pembelian->kode_gudang; 
                    $jurnal->kode_transaksi = $pembelian->id_pembelian;
                    $jurnal->kode_rekening = 1422000;
                    $jurnal->tanggal_transaksi  = $tanggal;
                    $jurnal->jenis_transaksi  = 'Jurnal System';
                    $jurnal->keterangan_transaksi = 'Pricing-' . ' ' . $list->kode_produk;
                    $jurnal->debet =0;
                    $jurnal->kredit = $margin_baru;
                    $jurnal->tanggal_posting = '';
                    $jurnal->keterangan_posting = '0';
                    $jurnal->id_admin = Auth::user()->id; 
                    $jurnal->save();
                
                }elseif ($margin_baru < $margin_lama) {
                    
                    $selisih = abs($margin_baru - $margin_lama);
                    
                    $jurnal = new TabelTransaksi;
                    $jurnal->unit =  $pembelian->kode_gudang; 
                    $jurnal->kode_transaksi = $pembelian->id_pembelian;
                    $jurnal->kode_rekening = 1422000;
                    $jurnal->tanggal_transaksi  = $tanggal;
                    $jurnal->jenis_transaksi  = 'Jurnal System';
                    $jurnal->keterangan_transaksi = 'Pricing-Harga-Turun' . ' ' . $list->kode_produk;
                    $jurnal->debet = $selisih;
                    $jurnal->kredit = 0;
                    $jurnal->tanggal_posting = '';
                    $jurnal->keterangan_posting = '0';
                    $jurnal->id_admin = Auth::user()->id; 
                    $jurnal->save();

                    $jurnal = new TabelTransaksi;
                    $jurnal->unit =  $pembelian->kode_gudang; 
                    $jurnal->kode_transaksi = $pembelian->id_pembelian;
                    $jurnal->kode_rekening = 1482000;
                    $jurnal->tanggal_transaksi  = $tanggal;
                    $jurnal->jenis_transaksi  = 'Jurnal System';
                    $jurnal->keterangan_transaksi = 'Pricing-Harga-Turun' . ' ' . $list->kode_produk;
                    $jurnal->debet =0;
                    $jurnal->kredit = $selisih;
                    $jurnal->tanggal_posting = '';
                    $jurnal->keterangan_posting = '0';
                    $jurnal->id_admin = Auth::user()->id; 
                    $jurnal->save();

                }elseif ($margin_baru>$margin_lama) {
                                        
                    $selisih = $margin_baru - $margin_lama;
                    
                    $jurnal = new TabelTransaksi;
                    $jurnal->unit =  $pembelian->kode_gudang; 
                    $jurnal->kode_transaksi = $pembelian->id_pembelian;
                    $jurnal->kode_rekening = 1422000;
                    $jurnal->tanggal_transaksi  = $tanggal;
                    $jurnal->jenis_transaksi  = 'Jurnal System';
                    $jurnal->keterangan_transaksi = 'Pricing-Harga-Naik' . ' ' . $list->kode_produk;
                    $jurnal->debet = $selisih;
                    $jurnal->kredit = 0;
                    $jurnal->tanggal_posting = '';
                    $jurnal->keterangan_posting = '0';
                    $jurnal->id_admin = Auth::user()->id; 
                    $jurnal->save();

                    $jurnal = new TabelTransaksi;
                    $jurnal->unit =  $pembelian->kode_gudang; 
                    $jurnal->kode_transaksi = $pembelian->id_pembelian;
                    $jurnal->kode_rekening = 1482000;
                    $jurnal->tanggal_transaksi  = $tanggal;
                    $jurnal->jenis_transaksi  = 'Jurnal System';
                    $jurnal->keterangan_transaksi = 'Pricing-Harga-Naik' . ' ' . $list->kode_produk;
                    $jurnal->debet =0;
                    $jurnal->kredit = $selisih;
                    $jurnal->tanggal_posting = '';
                    $jurnal->keterangan_posting = '0';
                    $jurnal->id_admin = Auth::user()->id; 
                    $jurnal->save();

                }
            }

        }else{

            //jurnal reverse 
            $tipe_pembayaran = $pembelian->tipe_bayar;
            
            // harga_lama
            $ptsri = round(($harga_lama*5)/100);
            $harga_gabungan_lama = $harga_lama+$ptsri;

            // harga_baru
            $ptsri_baru = round(($harga_baru*5)/100);
            $harga_gabungan_baru = $ptsri_baru+$harga_baru;

            if ($tipe_pembayaran == 1) {
                
                // reverse
                $jurnal = new TabelTransaksi;
                $jurnal->unit =  $pembelian->kode_gudang; 
                $jurnal->kode_transaksi = $pembelian->id_pembelian;
                $jurnal->kode_rekening = 2473000;
                $jurnal->tanggal_transaksi  = $tanggal;
                $jurnal->jenis_transaksi  = 'Jurnal System';
                $jurnal->keterangan_transaksi = 'Reverse Pembelian' . ' ' . $pembelian->id_pembelian . ' ' . $pembelian->nama;
                $jurnal->debet = $harga_gabungan_lama;
                $jurnal->kredit = 0;
                $jurnal->tanggal_posting = '';
                $jurnal->keterangan_posting = '0';
                $jurnal->id_admin = Auth::user()->id; 
                $jurnal->save();
                
                $jurnal = new TabelTransaksi;
                $jurnal->unit =  $pembelian->kode_gudang; 
                $jurnal->kode_transaksi = $pembelian->id_pembelian;
                $jurnal->kode_rekening = 1482000;
                $jurnal->tanggal_transaksi  = $tanggal;
                $jurnal->jenis_transaksi  = 'Jurnal System';
                $jurnal->keterangan_transaksi = 'Reverse Pembelian' . ' ' . $pembelian->id_pembelian . ' ' . $pembelian->nama;
                $jurnal->debet =0;
                $jurnal->kredit = $harga_gabungan_lama;
                $jurnal->tanggal_posting = '';
                $jurnal->keterangan_posting = '0';
                $jurnal->id_admin = Auth::user()->id; 
                $jurnal->save();
                
                // jurnal baru
                $jurnal = new TabelTransaksi;
                $jurnal->unit =  $pembelian->kode_gudang; 
                $jurnal->kode_transaksi = $pembelian->id_pembelian;
                $jurnal->kode_rekening = 1482000;
                $jurnal->tanggal_transaksi  = $tanggal;
                $jurnal->jenis_transaksi  = 'Jurnal System';
                $jurnal->keterangan_transaksi = 'Pembelian' . ' ' . $pembelian->id_pembelian . ' ' . $pembelian->nama;
                $jurnal->debet = $harga_gabungan_baru;
                $jurnal->kredit = 0;
                $jurnal->tanggal_posting = '';
                $jurnal->keterangan_posting = '0';
                $jurnal->id_admin = Auth::user()->id; 
                $jurnal->save();
                
                $jurnal = new TabelTransaksi;
                $jurnal->unit =  $pembelian->kode_gudang; 
                $jurnal->kode_transaksi = $pembelian->id_pembelian;
                $jurnal->kode_rekening = 2473000;
                $jurnal->tanggal_transaksi  = $tanggal;
                $jurnal->jenis_transaksi  = 'Jurnal System';
                $jurnal->keterangan_transaksi = 'Pembelian' . ' ' . $pembelian->id_pembelian . ' ' . $pembelian->nama;
                $jurnal->debet =0;
                $jurnal->kredit = $harga_gabungan_baru;
                $jurnal->tanggal_posting = '';
                $jurnal->keterangan_posting = '0';
                $jurnal->id_admin = Auth::user()->id; 
                $jurnal->save();
                

            }else {
                
                // reverse jurnal
                $jurnal = new TabelTransaksi;
                $jurnal->unit =  $pembelian->kode_gudang; 
                $jurnal->kode_transaksi = $pembelian->id_pembelian;
                $jurnal->kode_rekening = 2500000;
                $jurnal->tanggal_transaksi  = $tanggal;
                $jurnal->jenis_transaksi  = 'Jurnal System';
                $jurnal->keterangan_transaksi = 'Reverse Pembelian' . ' ' . $pembelian->id_pembelian . ' ' . $pembelian->nama;
                $jurnal->debet = $harga_gabungan_lama;
                $jurnal->kredit = 0;
                $jurnal->tanggal_posting = '';
                $jurnal->keterangan_posting = '0';
                $jurnal->id_admin = Auth::user()->id; 
                $jurnal->save();
                
                $jurnal = new TabelTransaksi;
                $jurnal->unit =  $pembelian->kode_gudang; 
                $jurnal->kode_transaksi = $pembelian->id_pembelian;
                $jurnal->kode_rekening = 1482000;
                $jurnal->tanggal_transaksi  = $tanggal;
                $jurnal->jenis_transaksi  = 'Jurnal System';
                $jurnal->keterangan_transaksi = 'Reverse Pembelian' . ' ' . $pembelian->id_pembelian . ' ' . $pembelian->nama;
                $jurnal->debet =0;
                $jurnal->kredit = $harga_gabungan_lama;
                $jurnal->tanggal_posting = '';
                $jurnal->keterangan_posting = '0';
                $jurnal->id_admin = Auth::user()->id; 
                $jurnal->save();

                // jurnal baru
                
                $jurnal = new TabelTransaksi;
                $jurnal->unit =  $pembelian->kode_gudang; 
                $jurnal->kode_transaksi = $pembelian->id_pembelian;
                $jurnal->kode_rekening = 1482000;
                $jurnal->tanggal_transaksi  = $tanggal;
                $jurnal->jenis_transaksi  = 'Jurnal System';
                $jurnal->keterangan_transaksi = 'Pembelian' . ' ' . $pembelian->id_pembelian . ' ' . $pembelian->nama;
                $jurnal->debet = $harga_gabungan_baru;
                $jurnal->kredit = 0;
                $jurnal->tanggal_posting = '';
                $jurnal->keterangan_posting = '0';
                $jurnal->id_admin = Auth::user()->id; 
                $jurnal->save();
                
                $jurnal = new TabelTransaksi;
                $jurnal->unit =  $pembelian->kode_gudang; 
                $jurnal->kode_transaksi = $pembelian->id_pembelian;
                $jurnal->kode_rekening = 2500000;
                $jurnal->tanggal_transaksi  = $tanggal;
                $jurnal->jenis_transaksi  = 'Jurnal System';
                $jurnal->keterangan_transaksi = 'Pembelian' . ' ' . $pembelian->id_pembelian . ' ' . $pembelian->nama;
                $jurnal->debet =0;
                $jurnal->kredit = $harga_gabungan_baru;
                $jurnal->tanggal_posting = '';
                $jurnal->keterangan_posting = '0';
                $jurnal->id_admin = Auth::user()->id; 
                $jurnal->save();
                
            }

            $detail = PembelianTemporaryDetail::where('id_pembelian',$id)->get();

            // jurnal pricing
            foreach ($detail as $list ) {

                $produk_lama = Produk::where('kode_produk',$list->kode_produk)->where('unit',3000)->first();

                $jumlah_barang = $list->jumlah;

                $harga_beli_produk_lama = $produk_lama->harga_beli*$jumlah_barang;
                $harga_jual_produk_lama = $produk_lama->harga_jual*$jumlah_barang;
                
                $harga_beli_produk_baru = $list->harga_beli*$jumlah_barang;
                $harga_jual_produk_baru = $list->harga_jual_pabrik*$jumlah_barang;
                
                $margin_lama = $harga_jual_produk_lama-$harga_beli_produk_lama;
                $margin_baru = $harga_jual_produk_baru-$harga_beli_produk_baru; 


                if ($margin_lama <= 0) {
                    
                    $jurnal = new TabelTransaksi;
                    $jurnal->unit =  $pembelian->kode_gudang; 
                    $jurnal->kode_transaksi = $pembelian->id_pembelian;
                    $jurnal->kode_rekening = 1482000;
                    $jurnal->tanggal_transaksi  = $tanggal;
                    $jurnal->jenis_transaksi  = 'Jurnal System';
                    $jurnal->keterangan_transaksi = 'Pricing-' . ' ' . $list->kode_produk;
                    $jurnal->debet = $margin_baru;
                    $jurnal->kredit = 0;
                    $jurnal->tanggal_posting = '';
                    $jurnal->keterangan_posting = '0';
                    $jurnal->id_admin = Auth::user()->id; 
                    $jurnal->save();

                    $jurnal = new TabelTransaksi;
                    $jurnal->unit =  $pembelian->kode_gudang; 
                    $jurnal->kode_transaksi = $pembelian->id_pembelian;
                    $jurnal->kode_rekening = 1422000;
                    $jurnal->tanggal_transaksi  = $tanggal;
                    $jurnal->jenis_transaksi  = 'Jurnal System';
                    $jurnal->keterangan_transaksi = 'Pricing-' . ' ' . $list->kode_produk;
                    $jurnal->debet =0;
                    $jurnal->kredit = $margin_baru;
                    $jurnal->tanggal_posting = '';
                    $jurnal->keterangan_posting = '0';
                    $jurnal->id_admin = Auth::user()->id; 
                    $jurnal->save();
                
                }elseif ($margin_baru < $margin_lama) {
                    
                    $selisih = abs($margin_baru - $margin_lama);
                    
                    $jurnal = new TabelTransaksi;
                    $jurnal->unit =  $pembelian->kode_gudang; 
                    $jurnal->kode_transaksi = $pembelian->id_pembelian;
                    $jurnal->kode_rekening = 1422000;
                    $jurnal->tanggal_transaksi  = $tanggal;
                    $jurnal->jenis_transaksi  = 'Jurnal System';
                    $jurnal->keterangan_transaksi = 'Pricing-Harga-Turun' . ' ' . $list->kode_produk;
                    $jurnal->debet = $selisih;
                    $jurnal->kredit = 0;
                    $jurnal->tanggal_posting = '';
                    $jurnal->keterangan_posting = '0';
                    $jurnal->id_admin = Auth::user()->id; 
                    $jurnal->save();

                    $jurnal = new TabelTransaksi;
                    $jurnal->unit =  $pembelian->kode_gudang; 
                    $jurnal->kode_transaksi = $pembelian->id_pembelian;
                    $jurnal->kode_rekening = 1482000;
                    $jurnal->tanggal_transaksi  = $tanggal;
                    $jurnal->jenis_transaksi  = 'Jurnal System';
                    $jurnal->keterangan_transaksi = 'Pricing-Harga-Turun' . ' ' . $list->kode_produk;
                    $jurnal->debet =0;
                    $jurnal->kredit = $selisih;
                    $jurnal->tanggal_posting = '';
                    $jurnal->keterangan_posting = '0';
                    $jurnal->id_admin = Auth::user()->id; 
                    $jurnal->save();

                }elseif ($margin_baru>$margin_lama) {
                                        
                    $selisih = $margin_baru - $margin_lama;
                    
                    $jurnal = new TabelTransaksi;
                    $jurnal->unit =  $pembelian->kode_gudang; 
                    $jurnal->kode_transaksi = $pembelian->id_pembelian;
                    $jurnal->kode_rekening = 1422000;
                    $jurnal->tanggal_transaksi  = $tanggal;
                    $jurnal->jenis_transaksi  = 'Jurnal System';
                    $jurnal->keterangan_transaksi = 'Pricing-Harga-Naik' . ' ' . $list->kode_produk;
                    $jurnal->debet = $selisih;
                    $jurnal->kredit = 0;
                    $jurnal->tanggal_posting = '';
                    $jurnal->keterangan_posting = '0';
                    $jurnal->id_admin = Auth::user()->id; 
                    $jurnal->save();

                    $jurnal = new TabelTransaksi;
                    $jurnal->unit =  $pembelian->kode_gudang; 
                    $jurnal->kode_transaksi = $pembelian->id_pembelian;
                    $jurnal->kode_rekening = 1482000;
                    $jurnal->tanggal_transaksi  = $tanggal;
                    $jurnal->jenis_transaksi  = 'Jurnal System';
                    $jurnal->keterangan_transaksi = 'Pricing-Harga-Naik' . ' ' . $list->kode_produk;
                    $jurnal->debet =0;
                    $jurnal->kredit = $selisih;
                    $jurnal->tanggal_posting = '';
                    $jurnal->keterangan_posting = '0';
                    $jurnal->id_admin = Auth::user()->id; 
                    $jurnal->save();

                }
            }
        }

        // update harga beli
        $pembelian->total_harga_terima = $sum_pembelian_detail;
        $pembelian->status = 3; 
        $pembelian->update();

        return redirect()->route('pricing_kp.index')->with(['berhasil' => 'PO '. $id_pembelian . ' Berhasil Disimpan']);

    }
}