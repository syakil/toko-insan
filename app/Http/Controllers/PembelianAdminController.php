<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Pembelian;
use App\PembelianDetail;
use App\PembelianTemporary;
use App\PembelianTemporaryDetail;
use App\TabelTransaksi;
use App\ProdukDetail;
use App\Produk;
use Auth;
use Illuminate\Support\Facades\DB;
use PDF;


class PembelianAdminController extends Controller
{
    public function index(){
        $pembelian = PembelianTemporary::leftJoin('supplier','pembelian_temporary.id_supplier','=','supplier.id_supplier')
                            ->where('status','1')
                            ->where('kode_gudang',Auth::user()->unit)
                            ->get();
        $no = 1;
        return view ('pembelian_admin.index',['pembelian'=>$pembelian,'no'=>$no]);
    }

    public function detail($id){

        $detail = DB::table('pembelian_temporary_detail','produk')
                    ->select('pembelian_temporary_detail.*','produk.kode_produk','produk.nama_produk','produk.satuan','produk.isi_satuan')
                    ->leftJoin('produk','pembelian_temporary_detail.kode_produk','=','produk.kode_produk')
                    ->where('unit',Auth::user()->unit)
                    ->where('id_pembelian',$id)
                    ->get();

        $nopo = PembelianTemporary::where('id_pembelian',$id)->get();
        // dd($detail);
        $nomer = 1;
        return view('pembelian_admin.detail',['pembelian_detail'=>$detail,'nomer'=>$nomer,'nopo'=>$nopo]);
    }


    public function ubah_harga(Request $request,$id){
        // pembelian detail
        $pembelian = PembelianTemporaryDetail::where('id_pembelian_detail',$id)->first();
        $pembelian->harga_beli = $request->value/$pembelian->jumlah_terima;
        $pembelian->sub_total_terima = $request->value;
        $pembelian->update();

        // pembelian
        $total_harga = PembelianTemporaryDetail::where('id_pembelian',$pembelian->id_pembelian)->sum('sub_total_terima');
        $total = PembelianTemporary::where('id_pembelian',$pembelian->id_pembelian)->first();
        $total->total_harga_terima = $total_harga;
        $total->total_harga_selisih = $total->total_harga - $total_harga;
        $total->update();

        // produk
        $produks = Produk::where('kode_produk',$pembelian->kode_produk)
                        ->get();
                        
        foreach ($produks as $produk ) {
            $produk->harga_beli = round($pembelian->harga_beli);
            $produk->update();
        }

        // detail-produk
        $produks_detail = ProdukDetail::where('kode_produk',$pembelian->kode_produk)
                                        ->where('expired_date',$pembelian->expired_date)
                                        ->get();

        foreach ($produks_detail as $produk ) {
            $produk->harga_beli = round($pembelian->harga_beli);
            $produk->update();
        }

    }

    public function ubah_harga2(Request $request,$id){
      
        $detail = PembelianTemporaryDetail::where('id_pembelian_detail',$id)->first();
        // dd($detail);
        $detail->sub_total_terima = $request->value;
        // $detail->harga_beli = round($request->value/$detail->jumlah_terima);
        $detail->update();

        
        $pembelian = PembelianTemporaryDetail::where('id_pembelian_detail',$id)
                                            ->where('kode_produk',$detail->kode_produk)
                                            ->get();
        

        foreach($pembelian as $param){
            // dd($param);
            $param->harga_beli = $request->value/$param->jumlah_terima;
            $param->update();
            $param->sub_total_terima = $request->value;
            $param->update();
            
            $produks_detail = ProdukDetail::where('kode_produk',$param->kode_produk)
                                            ->where('expired_date',$param->expired_date)
                                            ->get();
            foreach ($produks_detail as $produk ) {
                $produk->harga_beli = round($param->harga_beli);
                $produk->update();
            }

            $produks = Produk::where('kode_produk',$param->kode_produk)
                            ->get();
            foreach ($produks as $produk ) {
                // dd($param->harga_beli);
                $produk->harga_beli = round($param->harga_beli);
                $produk->update();
            }
        }
        
        $total_harga = PembelianTemporaryDetail::where('id_pembelian',$detail->id_pembelian)->sum('sub_total_terima');
        $total = PembelianTemporary::where('id_pembelian',$detail->id_pembelian)->first();
        $total->total_harga_terima = $total_harga;
        $total->update();
        

    }

    public function ubah_jatuh_tempo(Request $request,$id){
        $detail = PembelianTemporary::where('id_pembelian',$id);
        $detail->update(['jatuh_tempo'=>$request->value]);
    }

    public function ubah_tipe_bayar(Request $request,$id){
        $detail = PembelianTemporary::where('id_pembelian',$id);
        $detail->update(['tipe_bayar'=>$request->value]);
    }

    public function cetak_fpd($id){
        $data['produk'] = PembelianTemporary::where('id_pembelian',$id)->first();
        $data['alamat'] = PembelianTemporary::leftJoin('supplier','pembelian_temporary.id_supplier','=','supplier.id_supplier')
                        ->leftJoin('branch','pembelian_temporary.kode_gudang','=','branch.kode_gudang')
                        ->where('id_pembelian',$id)
                        ->first();
        $data['nosurat'] = PembelianTemporary::where('id_pembelian',$id)->get();
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
        foreach ($data as $id) {    

            //insert jurnal 
            $jurnal = PembelianTemporary::leftJoin('supplier','supplier.id_supplier','=','pembelian_temporary.id_supplier')
            ->where('id_pembelian',$id)
            ->get();
$param_tgl = \App\ParamTgl::where('nama_param_tgl','tanggal_transaksi')->where('unit',Auth::user()->id)->first();
      $tanggal = $param_tgl->param_tgl;

            foreach($jurnal as $d){
if($d->tipe_bayar == 0){
  return back()->with(['error' => 'Pilih Tipe Pembayaran PO '. $d->id_pembelian]);
}elseif ($d->tipe_bayar == 1) {

                    $margin = ($d->total_harga_terima * 5)/100;
                    $jurnal = new TabelTransaksi;
                    $jurnal->unit =  Auth::user()->unit; 
                    $jurnal->kode_transaksi = $d->id_pembelian;
                    $jurnal->kode_rekening = 1482000;
                    $jurnal->tanggal_transaksi  = $tanggal;
                    $jurnal->jenis_transaksi  = 'Jurnal System';
                    $jurnal->keterangan_transaksi = 'Pembelian' . ' ' . $d->id_pembelian . ' ' . $d->nama;
                    $jurnal->debet = $d->total_harga_terima + $margin;
                    $jurnal->kredit = 0;
                    $jurnal->tanggal_posting = '';
                    $jurnal->keterangan_posting = '0';
                    $jurnal->id_admin = Auth::user()->id; 
                    $jurnal->save();

                    $jurnal = new TabelTransaksi;
                    $jurnal->unit =  Auth::user()->unit; 
                    $jurnal->kode_transaksi = $d->id_pembelian;
                    $jurnal->kode_rekening = 2473000;
                    $jurnal->tanggal_transaksi  = $tanggal;
                    $jurnal->jenis_transaksi  = 'Jurnal System';
                    $jurnal->keterangan_transaksi = 'Pembelian' . ' ' . $d->id_pembelian . ' ' . $d->nama;
                    $jurnal->debet =0;
                    $jurnal->kredit = $d->total_harga_terima + $margin;
                    $jurnal->tanggal_posting = '';
                    $jurnal->keterangan_posting = '0';
                    $jurnal->id_admin = Auth::user()->id; 
                    $jurnal->save();
                }else{

                $margin = ($d->total_harga_terima * 5)/100;
                $jurnal = new TabelTransaksi;
                $jurnal->unit =  Auth::user()->unit; 
                $jurnal->kode_transaksi = $d->id_pembelian;
                $jurnal->kode_rekening = 1482000;
                $jurnal->tanggal_transaksi  = $tanggal;
                $jurnal->jenis_transaksi  = 'Jurnal System';
                $jurnal->keterangan_transaksi = 'Pembelian' . ' ' . $d->id_pembelian . ' ' . $d->nama;
                $jurnal->debet = $d->total_harga_terima + $margin;
                $jurnal->kredit = 0;
                $jurnal->tanggal_posting = '';
                $jurnal->keterangan_posting = '0';
                $jurnal->id_admin = Auth::user()->id; 
                $jurnal->save();
                
                $jurnal = new TabelTransaksi;
                $jurnal->unit =  Auth::user()->unit; 
                $jurnal->kode_transaksi = $d->id_pembelian;
                $jurnal->kode_rekening = 2500000;
                $jurnal->tanggal_transaksi  = $tanggal;
                $jurnal->jenis_transaksi  = 'Jurnal System';
                $jurnal->keterangan_transaksi = 'Pembelian' . ' ' . $d->id_pembelian . ' ' . $d->nama;
                $jurnal->debet =0;
                $jurnal->kredit = $d->total_harga_terima + $margin;
                $jurnal->tanggal_posting = '';
                $jurnal->keterangan_posting = '0';
                $jurnal->id_admin = Auth::user()->id; 
                $jurnal->save();

                }
            }
            // updates status menjadi 2
            $data = PembelianTemporary::where('id_pembelian',$id)->first();
            $data->status = 2;
            $data->update();

            // 
            $data = Pembelian::where('id_pembelian_t',$id)->first();
            $data->status = 2;
            $data->update();
        }
        return redirect('pembelian_admin/index');
    }

    // simpan ke tabel transaksi pda edit satu po
    public function simpan(Request $request){
        
        // updates status menjadi 2
        // $data = Pembelian::where('id_pembelian',$request->id)->first();
        // $data->update(['status'=>2]);
        //insert jurnal 

        $jurnal = PembelianTemporary::leftJoin('supplier','supplier.id_supplier','=','pembelian_temporary.id_supplier')
        ->where('id_pembelian',$request->id)
        ->get();
$param_tgl = \App\ParamTgl::where('nama_param_tgl','tanggal_transaksi')->where('unit',Auth::user()->id)->first();
      $tanggal = $param_tgl->param_tgl;

        foreach($jurnal as $d){
if($d->tipe_bayar == 0){
  return redirect('pembelian_admin/index')->with(['error' => 'Pilih Tipe Pembayaran PO '. $d->id_pembelian]);
}elseif ($d->tipe_bayar == 1) {
                $margin = ($d->total_harga_terima * 5)/100;
                $jurnal = new TabelTransaksi;
                $jurnal->unit =  Auth::user()->unit; 
                $jurnal->kode_transaksi = $d->id_pembelian;
                $jurnal->kode_rekening = 1482000;
                $jurnal->tanggal_transaksi  = $tanggal;
                $jurnal->jenis_transaksi  = 'Jurnal System';
                $jurnal->keterangan_transaksi = 'Pembelian' . ' ' . $d->id_pembelian . ' ' . $d->nama;
                $jurnal->debet = $d->total_harga_terima + $margin;
                $jurnal->kredit = 0;
                $jurnal->tanggal_posting = '';
                $jurnal->keterangan_posting = '0';
                $jurnal->id_admin = Auth::user()->id; 
                $jurnal->save();
                $jurnal = new TabelTransaksi;
                $jurnal->unit =  Auth::user()->unit; 
                $jurnal->kode_transaksi = $d->id_pembelian;
                $jurnal->kode_rekening = 2473000;
                $jurnal->tanggal_transaksi  = $tanggal;
                $jurnal->jenis_transaksi  = 'Jurnal System';
                $jurnal->keterangan_transaksi = 'Pembelian' . ' ' . $d->id_pembelian . ' ' . $d->nama;
                $jurnal->debet =0;
                $jurnal->kredit = $d->total_harga_terima + $margin;
                $jurnal->tanggal_posting = '';
                $jurnal->keterangan_posting = '0';
                $jurnal->id_admin = Auth::user()->id; 
                $jurnal->save();
                }else{
                                        $margin = ($d->total_harga_terima * 5)/100;
                    $jurnal = new TabelTransaksi;
                    $jurnal->unit =  Auth::user()->unit; 
                    $jurnal->kode_transaksi = $d->id_pembelian;
                    $jurnal->kode_rekening = 1482000;
                    $jurnal->tanggal_transaksi  = $tanggal;
                    $jurnal->jenis_transaksi  = 'Jurnal System';
                    $jurnal->keterangan_transaksi = 'Pembelian' . ' ' . $d->id_pembelian . ' ' . $d->nama;
                    $jurnal->debet = $d->total_harga_terima + $margin;
                    $jurnal->kredit = 0;
                    $jurnal->tanggal_posting = '';
                    $jurnal->keterangan_posting = '0';
                    $jurnal->id_admin = Auth::user()->id; 
                    $jurnal->save();
                    $jurnal = new TabelTransaksi;
                    $jurnal->unit =  Auth::user()->unit; 
                    $jurnal->kode_transaksi = $d->id_pembelian;
                    $jurnal->kode_rekening = 2500000;
                    $jurnal->tanggal_transaksi  = $tanggal;
                    $jurnal->jenis_transaksi  = 'Jurnal System';
                    $jurnal->keterangan_transaksi = 'Pembelian' . ' ' . $d->id_pembelian . ' ' . $d->nama;
                    $jurnal->debet =0;
                    $jurnal->kredit = $d->total_harga_terima + $margin;
                    $jurnal->tanggal_posting = '';
                    $jurnal->keterangan_posting = '0';
                    $jurnal->id_admin = Auth::user()->id; 
                    $jurnal->save();
            }
        }

        $data = PembelianTemporary::where('id_pembelian',$request->id)->first();
        $data->update(['status'=>2]);

        return redirect('pembelian_admin/index');
    }
}
