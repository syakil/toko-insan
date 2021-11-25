<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\PembelianTemporary; 


class ReportJatuhTempoController extends Controller
{
    public function index(){

        return view('report_jatpo/index');

    }

    public function listData(){

        $date = \Carbon\Carbon::now()->addDays(7);
        $now = $date->toDateString();
        // dd($now);
        $pembelian = PembelianTemporary::where('tipe_bayar','1')
        ->leftJoin('supplier','pembelian_temporary.id_supplier','=','supplier.id_supplier')
        ->get();

        $no = 0;
        $data = array();
        foreach($pembelian as $list){
        $no ++;
        $row = array();
        $row[] = $no;
        $row[] = $list->jatuh_tempo;
        $row[] = $list->nama;
        $row[] = $list->no_rek;
        $row[] = $list->nama_rek;
        $row[] = $list->bank;
        $row[] = $list->total_item;
        $row[] = "Rp. ".format_uang($list->total_harga_terima);

        if($list->status_bayar == 'L'){
            $row[] = '<button class="btn btn-sm btn-success">Lunas</button>';
        }else {
            $row[] = '<div class="btn-group">
                    <a href="'.$list->id_pembelian.'/update" class="btn btn-danger btn-sm" onclick="return confirm('.' \'Anda Akan Membayar Tagihan ini ? \' '.');"><i class="fa fa-dollar"></i> Bayar</a>
                    </div>';
            }
        $data[] = $row;
        }

        $output = array("data" => $data);
        return response()->json($output);

    }

    public function show($id){
        $pembelian = PembelianTemporary::where('id_pembelian',$id)->first();
        
        $no = 0;
        $data = array();
        $data[] = $no++;
        $data[] =  $pembelian->jatuh_tempo;
        $data[] = $pembelian->nama;
        $data[] = $pembelian->no_rek;
        $data[] = $pembelian->nama_rek;
        $data[] = $pembelian->bank;
        $data[] = $pembelian->total_item;
        $data[] = "Rp. ".format_uang($pembelian->total_harga_terima);

        $output = array("data" => $data);
        return response()->json($output);
    }

    public function cetak_fpd($id){

        $data['pembelian'] = PembelianTemporary::where('id_pembelian',$id)
                                        ->leftJoin('supplier','pembelian_temporary.id_supplier','=','supplier.id_supplier')
                                        ->first();
        $pdf = PDF::loadView('report_tempo.cetak_fpd', $data);
        return $pdf->stream('fpd.pdf');
    }

    public function update($id){
        $pembelian = PembelianTemporary::where('id_pembelian',$id)->first();
        $pembelian->status_bayar = 'L';
        $pembelian->update();

        return back();
    }
}
