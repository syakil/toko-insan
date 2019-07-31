<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Pembelian;
use App\PembelianDetail;
use App\TabelTransaksi;
use App\ProdukDetail;
use App\Produk;
use Auth;
use Illuminate\Support\Facades\DB;
use PDF;


class PembelianAdminController extends Controller
{
    public function index(){
        $pembelian = Pembelian::leftJoin('supplier','pembelian.id_supplier','=','supplier.id_supplier')
                            ->where('status','1')
                            ->where('kode_gudang',Auth::user()->unit)
                            ->get();
        $no = 1;
        return view ('pembelian_admin.index',['pembelian'=>$pembelian,'no'=>$no]);
    }

    public function detail($id){

        $detail = DB::table('pembelian_detail','produk')
                    ->select('pembelian_detail.*','produk.kode_produk','produk.nama_produk')
                    ->leftJoin('produk','pembelian_detail.kode_produk','=','produk.kode_produk')
                    ->where('unit',Auth::user()->unit)
                    ->where('id_pembelian',$id)
                    ->get();

        $nopo = Pembelian::where('id_pembelian',$id)->get();
        $nomer = 1;
        return view('pembelian_admin.detail',['pembelian'=>$detail,'nomer'=>$nomer,'nopo'=>$nopo,'id_pembelian'=>$id]);
    }

    public function ubah_harga(Request $request,$id){

        $harga_beli = PembelianDetail::where('id_pembelian_detail',$id)
                                        ->get();
        // ubah harga beli
        foreach($harga_beli as $detail){
            $produk_inti = Produk::where('kode_produk',$detail->kode_produk);
            $produk_inti->update(['harga_beli'=>$request->value]);

            $produk_detail = ProdukDetail::where('kode_produk',$detail->kode_produk)
                                        ->where('expired_date',$detail->expired_date)
                                        ->where('stok_detail',$detail->jumlah_terima);
            $produk_detail->update(['harga_beli'=>$request->value]);
            // harga sub total pembelian_detail
            $sub_total = $detail->jumlah_terima * $request->value;
            $produk_sub_total = PembelianDetail::where('id_pembelian_detail',$id);
            $produk_sub_total->update(['sub_total'=>$sub_total]);
        }
        $detail = PembelianDetail::where('id_pembelian_detail',$id);
        $detail->update(['harga_beli'=>$request->value]);
    }

    public function ubah_jatuh_tempo(Request $request,$id){
        $detail = Pembelian::where('id_pembelian',$id);
        $detail->update(['jatuh_tempo'=>$request->value]);
    }

    public function ubah_tipe_bayar(Request $request,$id){
        $detail = Pembelian::where('id_pembelian',$id);
        $detail->update(['tipe_bayar'=>$request->value]);
    }

    public function cetak_fpd($id){
        $data['produk'] = PembelianDetail::leftJoin('produk','pembelian_detail.kode_produk','=','produk.kode_produk')
                        ->where('id_pembelian',$id)
                        ->where('produk.unit',Auth::user()->unit)
                        ->get();
        $data['alamat'] = Pembelian::leftJoin('supplier','pembelian.id_supplier','=','supplier.id_supplier')
                        ->leftJoin('branch','pembelian.kode_gudang','=','branch.kode_gudang')
                        ->where('id_pembelian',$id)
                        ->get();
        $data['nosurat'] = Pembelian::where('id_pembelian',$id)->get();
        $data['no'] =1;
        $pdf = PDF::loadView('pembelian_admin.cetak_fpd', $data);
        return $pdf->stream('surat_jalan.pdf');
    }
    
    
    public function cetak_po($id){
        $data['produk'] = PembelianDetail::leftJoin('produk','pembelian_detail.kode_produk','=','produk.kode_produk')
                        ->where('id_pembelian',$id)
                        ->where('produk.unit',Auth::user()->unit)
                        ->get();
        $data['alamat'] = Pembelian::leftJoin('supplier','pembelian.id_supplier','=','supplier.id_supplier')
                        ->leftJoin('branch','pembelian.kode_gudang','=','branch.kode_gudang')
                        ->where('id_pembelian',$id)
                        ->first();
        $data['nosurat'] = Pembelian::where('id_pembelian',$id)->get();
        $data['no'] =1;
        $pdf = PDF::loadView('pembelian_admin.cetak_po', $data);
        return $pdf->stream('surat_jalan.pdf');
    }

    // simpan ke tabel transaksi jika di pilih beberapa PO
    public function store_jurnal(Request $request){
        $data = $request->check;    
        foreach ($data as $d) {    
            //insert jurnal 
            $jurnal = Pembelian::leftJoin('supplier','supplier.id_supplier','=','pembelian.id_supplier')
            ->where('id_pembelian',$d)
            ->get();

            foreach($jurnal as $d){
                if ($d->tipe_bayar == 1) {
                    $jurnal = new TabelTransaksi;
                    $jurnal->unit =  Auth::user()->unit; 
                    $jurnal->kode_transaksi = $d->id_pembelian;
                    $jurnal->kode_rekening = 1482000;
                    $jurnal->tanggal_transaksi  = date('Y-m-d');
                    $jurnal->jenis_transaksi  = 'Jurnal System';
                    $jurnal->keterangan_transaksi = 'Pembelian' . ' ' . $d->id_pembelian . ' ' . $d->nama;
                    $jurnal->debet =$d->total_harga + ($d->total_harga*5/100);
                    $jurnal->kredit = 0;
                    $jurnal->tanggal_posting = '';
                    $jurnal->keterangan_posting = '0';
                    $jurnal->id_admin = Auth::user()->id; 
                    $jurnal->save();

                    $jurnal = new TabelTransaksi;
                    $jurnal->unit =  Auth::user()->unit; 
                    $jurnal->kode_transaksi = $d->id_pembelian;
                    $jurnal->kode_rekening = 2473000;
                    $jurnal->tanggal_transaksi  = date('Y-m-d');
                    $jurnal->jenis_transaksi  = 'Jurnal System';
                    $jurnal->keterangan_transaksi = 'Pembelian' . ' ' . $d->id_pembelian . ' ' . $d->nama;
                    $jurnal->debet =0;
                    $jurnal->kredit =$d->total_harga + ($d->total_harga*5/100);
                    $jurnal->tanggal_posting = '';
                    $jurnal->keterangan_posting = '0';
                    $jurnal->id_admin = Auth::user()->id; 
                    $jurnal->save();
                }else{
                $jurnal = new TabelTransaksi;
                $jurnal->unit =  Auth::user()->unit; 
                $jurnal->kode_transaksi = $d->id_pembelian;
                $jurnal->kode_rekening = 1482000;
                $jurnal->tanggal_transaksi  = date('Y-m-d');
                $jurnal->jenis_transaksi  = 'Jurnal System';
                $jurnal->keterangan_transaksi = 'Pembelian' . ' ' . $d->id_pembelian . ' ' . $d->nama;
                $jurnal->debet =$d->total_harga;
                $jurnal->kredit = 0;
                $jurnal->tanggal_posting = '';
                $jurnal->keterangan_posting = '0';
                $jurnal->id_admin = Auth::user()->id; 
                $jurnal->save();
                
                $jurnal = new TabelTransaksi;
                $jurnal->unit =  Auth::user()->unit; 
                $jurnal->kode_transaksi = $d->id_pembelian;
                $jurnal->kode_rekening = 2500000;
                $jurnal->tanggal_transaksi  = date('Y-m-d');
                $jurnal->jenis_transaksi  = 'Jurnal System';
                $jurnal->keterangan_transaksi = 'Pembelian' . ' ' . $d->id_pembelian . ' ' . $d->nama;
                $jurnal->debet =0;
                $jurnal->kredit =$d->total_harga + ($d->total_harga*5/100);
                $jurnal->tanggal_posting = '';
                $jurnal->keterangan_posting = '0';
                $jurnal->id_admin = Auth::user()->id; 
                $jurnal->save();
                }
            }
            // updates status menjadi 2
            $data = Pembelian::where('id_pembelian',$d)->first();
            $data->update(['status'=>2]);
        }
        
        return redirect('pembelian_admin/index');
    }

    // simpan ke tabel transaksi pda edit satu po
    public function jurnal($id){
        // updates status menjadi 2
        $data = Pembelian::where('id_pembelian',$id)->first();
        $data->update(['status'=>2]);
        //insert jurnal 
        $jurnal = Pembelian::leftJoin('supplier','supplier.id_supplier','=','pembelian.id_supplier')
        ->where('id_pembelian',$id)
        ->get();

        foreach($jurnal as $d){

            if ($d->tipe_bayar == 1) {
                $jurnal = new TabelTransaksi;
                $jurnal->unit =  Auth::user()->unit; 
                $jurnal->kode_transaksi = $d->id_pembelian;
                $jurnal->kode_rekening = 1482000;
                $jurnal->tanggal_transaksi  = date('Y-m-d');
                $jurnal->jenis_transaksi  = 'Jurnal System';
                $jurnal->keterangan_transaksi = 'Pembelian' . ' ' . $d->id_pembelian . ' ' . $d->nama;
                $jurnal->debet =$d->total_harga + ($d->total_harga*5/100);
                $jurnal->kredit = 0;
                $jurnal->tanggal_posting = '';
                $jurnal->keterangan_posting = '0';
                $jurnal->id_admin = Auth::user()->id; 
                $jurnal->save();

                $jurnal = new TabelTransaksi;
                $jurnal->unit =  Auth::user()->unit; 
                $jurnal->kode_transaksi = $d->id_pembelian;
                $jurnal->kode_rekening = 2473000;
                $jurnal->tanggal_transaksi  = date('Y-m-d');
                $jurnal->jenis_transaksi  = 'Jurnal System';
                $jurnal->keterangan_transaksi = 'Pembelian' . ' ' . $d->id_pembelian . ' ' . $d->nama;
                $jurnal->debet =0;
                $jurnal->kredit =$d->total_harga + ($d->total_harga*5/100);
                $jurnal->tanggal_posting = '';
                $jurnal->keterangan_posting = '0';
                $jurnal->id_admin = Auth::user()->id; 
                $jurnal->save();
                }else{
                    $jurnal = new TabelTransaksi;
                    $jurnal->unit =  Auth::user()->unit; 
                    $jurnal->kode_transaksi = $d->id_pembelian;
                    $jurnal->kode_rekening = 1482000;
                    $jurnal->tanggal_transaksi  = date('Y-m-d');
                    $jurnal->jenis_transaksi  = 'Jurnal System';
                    $jurnal->keterangan_transaksi = 'Pembelian' . ' ' . $d->id_pembelian . ' ' . $d->nama;
                    $jurnal->debet =$d->total_harga + ($d->total_harga*5/100);
                    $jurnal->kredit = 0;
                    $jurnal->tanggal_posting = '';
                    $jurnal->keterangan_posting = '0';
                    $jurnal->id_admin = Auth::user()->id; 
                    $jurnal->save();

                    $jurnal = new TabelTransaksi;
                    $jurnal->unit =  Auth::user()->unit; 
                    $jurnal->kode_transaksi = $d->id_pembelian;
                    $jurnal->kode_rekening = 2500000;
                    $jurnal->tanggal_transaksi  = date('Y-m-d');
                    $jurnal->jenis_transaksi  = 'Jurnal System';
                    $jurnal->keterangan_transaksi = 'Pembelian' . ' ' . $d->id_pembelian . ' ' . $d->nama;
                    $jurnal->debet =0;
                    $jurnal->kredit =$d->total_harga + ($d->total_harga*5/100);
                    $jurnal->tanggal_posting = '';
                    $jurnal->keterangan_posting = '0';
                    $jurnal->id_admin = Auth::user()->id; 
                    $jurnal->save();
            }
        }
        return redirect('pembelian_admin/index');
    }
}
