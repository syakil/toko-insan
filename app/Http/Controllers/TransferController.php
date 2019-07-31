<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Kirim;
use App\KirimDetail;
use PDF;
use Auth;

class TransferController extends Controller
{
    public function gudang(){
        // menampilkan data transfer where status app = gudang
        // barang dari gudang ke toko
        $dataTransfer = Transfer::leftJoin('branch','transfer.kode_toko','=','branch.kode_toko')
                        ->where('status_app','gudang')
                        ->get();
        $no = 1;
        return view('transfer/gudang/index',['dataTransfer'=>$dataTransfer,'no'=>$no]);
    }

    public function detail($id){
        // mengambil data detail_transfer berdasar id_transfer yang ingin dilihat
        $detail = TransferDetail::leftJoin('branch','transfer_detail.kode_toko','=','branch.kode_toko')
                                    ->leftJoin('produk','transfer_detail.id_produk','=','produk.kode_produk')
                                    ->where('id_transfer',$id)
                                    ->get();
        // mengambil no surat jalan berdsar id_transfer yang dipilih
        $no_surat = Kirim::where('id_transfer',$id)->get();
        $nomer = 1;
        return view('transfer/detail',['transfer'=>$detail,'nomer'=>$nomer,'no_surat'=>$no_surat]);
    }

    public function store(Request $request,$id){
        // mengambil data berdasar id
        $jumlah_kirim = TransferDetail::where('id_transfer_detail',$id)->get();
        // mengambil field jumlah_kirim
        foreach ($jumlah_kirim as $jumlah ) {
            $jumlah_kirim = $jumlah->jumlah_kirim;
        }
        // membuat kondisi lengkap/tidak lengkap/lebih berdasar jumlah yang dikirim
        if ($request->value == $jumlah_kirim) {
            $status = 'Lengkap';
        }elseif ($request->value > $jumlah_kirim) {
            $status = 'Lebih';
        }else {
            $status = 'Tidak Lengkap';
        }

        // mengupdate data ke database
        $detail = KirimDetail::where('id_pembelian_detail',$id);
        $detail->update(['jumlah_terima'=>$request->value,'status_lengkap'=>$status]);
    }

    public function print_gudang($id){
        $data['produk'] = KirimDetail::leftJoin('produk','kirim_barang_detail.kode_produk','=','produk.kode_produk')
                        ->where('id_pembelian',$id)
                        ->where('produk.unit',Auth::user()->unit)
                        ->get();
        $data['alamat'] = kirim::leftJoin('branch','kirim_barang.id_user','=','branch.kode_toko')
                        ->where('id_pembelian',$id)
                        ->get();
        $data['nosurat'] = Kirim::where('id_pembelian',$id)->get();
        $data['no'] =1;
        $pdf = PDF::loadView('transfer/gudang', $data);
        return $pdf->stream('surat_jalan.pdf');
    }

    public function toko(){
        // menampilkan data transfer where status app = toko
        // retur barang dari toko ke gudang
        $dataTransfer = Kirim::leftJoin('branch','transfer.kode_toko','=','branch.kode_toko')
                    ->where('status_app','toko')
                    ->get();
        $no = 1;
        return view('transfer/toko/index',['dataTransfer'=>$dataTransfer,'no'=>$no]);
    }

    public function print_toko($id){
        $data['surat'] = KirimDetail::leftJoin('branch','transfer_detail.kode_toko','=','branch.kode_toko')
                        ->leftJoin('produk','transfer_detail.id_produk','=','produk.kode_produk')
                        ->where('id_transfer',$id)
                        ->get();
        $data['alamat'] = Kirim::leftJoin('branch','transfer.kode_toko','=','branch.kode_toko')
                        ->where('id_transfer',$id)
                        ->get();
        $data['nosurat'] = Kirim::where('id_transfer',$id)->get();
        $data['no'] =1;
        $pdf = PDF::loadView('transfer/toko', $data);
        return $pdf->stream('surat_jalan.pdf');
    }

    public function supplier(){
        // menampilkan data transfer where status app = supplier
        // retur barang dari gudang ke supplier
        $dataTransfer = Kirim::leftJoin('supplier','transfer.kode_supplier','=','supplier.id_supplier')
                    ->leftJoin('branch','transfer.kode_toko','=','branch.kode_toko')
                    ->where('status_app','supplier')
                    ->get();
        $no = 1;
        return view('transfer/supplier/index',['dataTransfer'=>$dataTransfer,'no'=>$no]);
    }

    public function print_supplier($id){
        $data['surat'] = KirimDetail::leftJoin('branch','transfer_detail.kode_toko','=','branch.kode_toko')
                        ->leftJoin('produk','transfer_detail.id_produk','=','produk.kode_produk')
                        ->where('id_transfer',$id)
                        ->get();
        $data['alamat'] = Kirim::leftJoin('branch','transfer.kode_toko','=','branch.kode_toko')
                        ->leftJoin('supplier','transfer.kode_supplier','=','supplier.id_supplier')
                        ->where('id_transfer',$id)
                        ->get();
        $data['nosurat'] = Kirim::where('id_transfer',$id)->get();
        $data['no'] =1;
        $pdf = PDF::loadView('transfer/supplier', $data);
        return $pdf->stream('surat_jalan.pdf');
    }
}
