<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Transfer;
use App\TransferDetail;
use PDF;

class KirimTokoController extends Controller
{
    public function kirim_view(){
        $dataTransfer = Transfer::leftJoin('branch','transfer.id_user','=','branch.kode_toko')
                    ->where('status_app','gudang')
                    ->get();
        $no = 1;
        return view('transfer/toko/kirim',['dataTransfer'=>$dataTransfer,'no'=>$no]);
    }
    public function kirim_detail($id){
            // mengambil data detail_transfer berdasar id_transfer yang ingin dilihat
            $detail = TransferDetail::leftJoin('produk','transfer_detail.kode_produk','=','produk.kode_produk')
                                        ->where('id_transfer',$id)
                                        ->get();
            // mengambil no surat jalan berdsar id_transfer yang dipilih
            $no_surat = Transfer::where('id_transfer',$id)->get();
            $nomer = 1;
            return view('transfer/toko/kirim_detail',['transfer'=>$detail,'nomer'=>$nomer,'no_surat'=>$no_surat]);
    }
}
