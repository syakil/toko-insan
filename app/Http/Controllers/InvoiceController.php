<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\PembelianTemporary;
use Auth;
use DB;
use App\PembelianTemporaryDetail;
use App\DiskonPembelian;
use App\TabelTransaksi;
use App\Pembelian;

class InvoiceController extends Controller
{
    public function index(){
    
        return view('invoice/index');
    
    }

    public function listData(){
        
        $pembelian = PembelianTemporary::leftJoin('supplier','pembelian_temporary.id_supplier','=','supplier.id_supplier')
                                        ->leftJoin('branch','pembelian_temporary.kode_gudang','branch.kode_toko')
                                        ->where('pembelian_temporary.status','2')
                                        ->select('pembelian_temporary.*','supplier.nama','branch.nama_toko')
                                        ->where('pembelian_temporary.kode_gudang',Auth::user()->unit)
                                        ->get();

        $no = 0;
        $data = array();
        foreach($pembelian as $list){
            $no ++;
            $row = array();
            $row[] = $no;
            $row[] = tanggal_indonesia($list->created_at);
            $row[] = $list->id_pembelian;
            $row[] = $list->nama;
            $row[] = $list->total_terima;
            $row[] = number_format($list->total_harga_terima);
            $row[] = $list->nama_toko;
            $row[] = '<a href="'.route('invoice.detail',$list->id_pembelian).'" class="btn btn-sm btn-warning opsi" ><i class="fa fa-pencil"></i></a>';
            $data[] = $row;
        }
    
        $output = array("data" => $data);
        return response()->json($output);
                    
    }

    public function detail($id){
        
        $nopo = PembelianTemporary::leftJoin('supplier','pembelian_temporary.id_supplier','supplier.id_supplier')->where('id_pembelian',$id)->first();
        

        $produk = PembelianTemporaryDetail::leftJoin('produk','produk.kode_produk','pembelian_temporary_detail.kode_produk')
                                            ->where('produk.unit',Auth::user()->unit)
                                            ->where('id_pembelian',$id)
                                            ->groupBy('produk.kode_produk')
                                            ->get();

        return view('invoice/detail',compact('nopo','produk','id'));
    }

    public function listDetail($id){

        $pembelian_detail = DB::table('pembelian_temporary_detail','produk')
                            ->select('pembelian_temporary_detail.*','produk.kode_produk','produk.nama_produk','produk.satuan','produk.isi_satuan')
                            ->leftJoin('produk','pembelian_temporary_detail.kode_produk','=','produk.kode_produk')
                            ->where('unit',Auth::user()->unit)
                            ->where('id_pembelian',$id)
                            ->get();
                
        $data = array();
        $no = 0;
        foreach ($pembelian_detail as $p){
            $no ++;
            $row = array();
            $row[] = $no;
            $row[] = $p->kode_produk;
            $row[] = $p->nama_produk;
            $row[] = $p->jumlah_terima;
            $row[] = "<input type='number' onChange='invoice(".$p->id_pembelian_detail.")' class='harga-invoice-".$p->id_pembelian_detail."' name='harga-invoice-".$p->id_pembelian_detail."' value='".$p->sub_total_terima."' style='border:none; background:transparent;'>";
            $row[] = "<input type='number' onChange='spesial_diskon(".$p->id_pembelian_detail.")' name='spesial-diskon-".$p->id_pembelian_detail."' value='".$p->spesial_diskon."' style='border:none; background:transparent;'>";
            $row[] = "<input readonly type='number' name='diskon-lainya-".$p->id_pembelian_detail."' value='".$p->diskon_lainya."' style='border:none; background:transparent;'>";
            $row[] = "<input type='number' onChange='regular_diskon(".$p->id_pembelian_detail.")' name='regular-diskon-".$p->id_pembelian_detail."' style='border:none; background:transparent;' value='".$p->regular_diskon."'>";
            $row[] = "<input value='".$p->total."' type='number' name='total-".$p->id_pembelian_detail."' style='border:none; background:transparent;'>";
            $data[] = $row;
        }
        
        $output = array("data" => $data);
        return response()->json($output);
        
    }

    public function listSpesialDiskon($id){
        
        $spesial_diskon = PembelianTemporaryDetail::leftJoin('produk','produk.kode_produk','pembelian_temporary_detail.kode_produk')
                                                    ->where('produk.unit',Auth::user()->unit)
                                                    ->where('id_pembelian',$id)
                                                    ->where('spesial_diskon','!=',null)
                                                    ->groupBy('produk.kode_produk')
                                                    ->get();

        $data = array();
        $no = 0;
        foreach ($spesial_diskon as $p){
            $no ++;
            $row = array();
            $row[] = $no;
            $row[] = $p->kode_produk;
            $row[] = $p->nama_produk;
            $row[] = "<input type='number' onChange='spesial_diskon_2(".$p->id_pembelian_detail.")' name='spesial-diskon-".$p->id_pembelian_detail."' value='".$p->spesial_diskon."' style='border:none; background:transparent;'>";
            $row[] = '<div class="btn-group">
               <a onclick="deleteItem('.$p->id_pembelian_detail.')" class="btn btn-danger btn-sm"><i class="fa fa-trash"></i></a>';
            $data[] = $row;
        }
        
        $output = array("data" => $data);
        return response()->json($output);
                                            
    }

    public function listDiskonLainya($id){
        $diskon = DiskonPembelian::where('id_pembelian',$id)->get();

        
        $data = array();
        $no = 0;
        foreach ($diskon as $p){
            $no ++;
            $row = array();
            $row[] = $no;
            $row[] = $p->keterangan;
            $row[] = $p->nominal;
            $row[] = '<a onclick="deleteDiskon('.$p->id_diskon.')" class="btn btn-danger btn-sm"><i class="fa fa-trash"></i></a>';
            
            $data[] = $row;
        }
        
        $output = array("data" => $data);
        return response()->json($output);
    }

    public function add_spesial_diskon(Request $request){
        $id = $request->id;
        $kode = $request->kode;
        
        $pembelian_detail = PembelianTemporaryDetail::where('id_pembelian',$id)
                                                    ->where('kode_produk','like', '%'.$kode)
                                                    ->get();
        foreach($pembelian_detail as $list){
            $list->spesial_diskon = 0;
            $list->update();
        }
        
    }

    public function delete_spesial_diskon($id){

        $pembelian_detail = PembelianTemporaryDetail::where('id_pembelian_detail',$id)->first();

        $detail = PembelianTemporaryDetail::where('kode_produk',$pembelian_detail->kode_produk)->get();

        foreach($detail as $list){
            $list->spesial_diskon = null;
            $list->update();
        }
        
    }

    public function update_invoice(Request $request,$id){
        
        $pembelian_detail = PembelianTemporaryDetail::where('id_pembelian_detail',$id)->first();
        
        $invoice = "harga-invoice-".$id;
        $harga_invoice = $request[$invoice];

        $pembelian_detail->sub_total_terima = $harga_invoice;
        $pembelian_detail->update();
    }

    public function update_spesial_diskon(Request $request,$id){
        
        $pembelian_detail = PembelianTemporaryDetail::where('id_pembelian_detail',$id)->first();
        
        $spesial = "spesial-diskon-".$id;
        $spesial_diskon = $request[$spesial];

        $produk = PembelianTemporaryDetail::where('id_pembelian',$pembelian_detail->id_pembelian)->where('kode_produk',$pembelian_detail->kode_produk)->get();
        
        foreach ($produk as $list ) {   
            $list->spesial_diskon = $spesial_diskon;
            $list->update();
        }
    }
    
    
    public function update_regular_diskon_ppn(Request $request,$id){

        $pembelian_detail = PembelianTemporaryDetail::where('id_pembelian_detail',$id)->first();

        $regular = "regular-diskon-".$id;
        
        $regular_diskon = $request[$regular];
        $invoice = $pembelian_detail->sub_total_terima;
        $spesial_diskon = $pembelian_detail->spesial_diskon;
        $dikon_lainya = $pembelian_detail->diskon_lainya;

        $sub_total = $invoice-$spesial_diskon-$dikon_lainya-$regular_diskon;
        $ppn = $sub_total+($sub_total*10)/100;
        
        // update
        $pembelian_detail->regular_diskon = $regular_diskon;
        $pembelian_detail->total = round($ppn);
        $pembelian_detail->update();


    }

    
    public function update_regular_diskon(Request $request,$id){

        $pembelian_detail = PembelianTemporaryDetail::where('id_pembelian_detail',$id)->first();

        $regular = "regular-diskon-".$id;
        
        $regular_diskon = $request[$regular];
        $invoice = $pembelian_detail->sub_total_terima;
        $spesial_diskon = $pembelian_detail->spesial_diskon;
        $dikon_lainya = $pembelian_detail->diskon_lainya;

        $sub_total = $invoice-$spesial_diskon-$dikon_lainya-$regular_diskon;
        
        // update
        $pembelian_detail->regular_diskon = $regular_diskon;
        $pembelian_detail->total = round($sub_total);
        $pembelian_detail->update();


    }

    public function add_diskon_lainya(Request $request){

        $id_pembelian = $request['id'];
        $keterangan = $request['keterangan'];
        $nominal = $request['nominal'];

        $cek_data_invoice = PembelianTemporaryDetail::where('id_pembelian',$id_pembelian)->where('sub_total_terima',null)->count();

        if($cek_data_invoice > 0){
            return back()->with('alert','Harga Invoice Masih Ada yang Kosong');
        }

        $cek_spesial_diskon = DiskonPembelian::where('id_pembelian',$id_pembelian)->where('keterangan',$keterangan)->count();

        if($cek_spesial_diskon > 0){
            return back()->with('alert','Spesial Diskon Sudah Ada');
        }


        if($request->keterangan == 'spesial diskon'){
            
            $spesial_diskon = PembelianTemporaryDetail::where('id_pembelian',$id_pembelian)->where('spesial_diskon','!=',null)->count();
            
            $total = PembelianTemporaryDetail::where('id_pembelian',$id_pembelian)->count();
            
            $belum_spesial_diskon = $total-$spesial_diskon;
            
            if($belum_spesial_diskon == 0){
                return back()->with('alert','Spesial Diskon Sudah Terisi Semua');
            }

            $spesial_diskon_lainya = $nominal/$belum_spesial_diskon;

            $data_spesial_diskon = PembelianTemporaryDetail::where('id_pembelian',$id_pembelian)->where('spesial_diskon',null)->get();

            foreach ($data_spesial_diskon as $list ) {
                $list->spesial_diskon = $spesial_diskon_lainya;
                $list->update();
            }


        }

        $diskon_lainya = new DiskonPembelian;
        $diskon_lainya->id_pembelian = $id_pembelian;
        $diskon_lainya->keterangan = $keterangan;
        $diskon_lainya->nominal = $nominal;
        $diskon_lainya->save();

        return back();
    }

    public function delete_diskon_lainya($id){

        $diskon = DiskonPembelian::where('id_diskon',$id)->first();
        
        if ($diskon->keterangan == 'spesial diskon') {
            
            
            $get_spesial_diskon = PembelianTemporaryDetail::where('id_pembelian',$diskon->id_pembelian)
            ->groupBy('spesial_diskon')
            ->havingRaw('COUNT("spesial_diskon")>1')
            ->get();
            
            foreach ($get_spesial_diskon as $list ) {
            
                $pembelian_detail = PembelianTemporaryDetail::where('id_pembelian',$diskon->id_pembelian)->where('spesial_diskon',$list->spesial_diskon)->get();

                foreach ($pembelian_detail as $detail ) {
                    $detail->spesial_diskon = null;
                    $detail->update();
                }
            
            }
        }

        $diskon->delete();

    }


    public function perhitungan_diskon($id){

        $cek = $diskon = DiskonPembelian::where('id_pembelian',$id)->count();
     
        if ($cek < 1) {
            $detail_pembelian = PembelianTemporaryDetail::where('id_pembelian',$id)->get();
            
            foreach ($detail_pembelian as $detail ) {
             
                $detail->diskon_lainya = null;
                $detail->update();
    
            }
     
        }else{
            
            $diskon_pembelian = DiskonPembelian::where('id_pembelian',$id)->where('keterangan','!=','spesial diskon')->get();
            
            $diskon = 0;
            $persentase = 0;

            foreach ($diskon_pembelian as $list ) {

                if ( strpos( $list->nominal, "." ) !== false ) {
                    $persentase += $list->nominal;
                }else{
                    $diskon += $list->nominal;
                }

            }


            $total_sementara = 0;
            
            $detail_pembelian = PembelianTemporaryDetail::where('id_pembelian',$id)->get();
            
            foreach ($detail_pembelian as $detail ) {
                
                $total_sementara += $detail->sub_total_terima;
                
            }
            
            $persentase += $diskon/$total_sementara;
            

            foreach ($detail_pembelian as $detail ) {
                
                $pengurangan_spesial = $detail->sub_total_terima - $detail->spesial_diskon;
                
                $detail->diskon_lainya = round($pengurangan_spesial * $persentase);
                $detail->update();
                
            }
            
        }
    
    }


    public function simpan(Request $request){

        $id_pembelian = $request['id'];
        $tipe_bayar = $request['tipe_bayar'];
        $noinvoice = $request['no_invoice']; 
        
        if($tipe_bayar == 0 || $tipe_bayar == null || $noinvoice == null) {
        
            return back()->with('alert','Pilih Tipe Bayar');
        
        }else {
            
            $update_tipe_bayar = PembelianTemporary::where('id_pembelian',$id_pembelian)->first();
            $update_tipe_bayar->tipe_bayar = $tipe_bayar;
            $update_tipe_bayar->update();
                    
        }


        $param_tgl = \App\ParamTgl::where('nama_param_tgl','tanggal_transaksi')->where('unit',Auth::user()->id)->first();
        $tanggal = $param_tgl->param_tgl;
        
        $cek = PembelianTemporaryDetail::where('id_pembelian',$id_pembelian)->get();
        
        foreach ($cek as $list ) {
            
            $jumlah_terima = $list->jumlah_terima;
            $harga_invoice = $list->total;
            
            if ($jumlah_terima = 0 || $harga_invoice == null || $jumlah_terima = null || $harga_invoice == 0 ) {
            
                return back()->with('alert','Kolom Invoice/Jumlah Item'.$list->kode_produk.' Masih Ada yang Kosong');

            }

        }


        $pembelian_temporary  = PembelianTemporaryDetail::where('id_pembelian',$id_pembelian)->sum('total');
        
        $pembelian = PembelianTemporary::where('id_pembelian',$id_pembelian)
        ->leftJoin('supplier','supplier.id_supplier','pembelian_temporary.id_supplier')
        ->first();

        $pembelian->total_harga_terima = $pembelian_temporary;
        $pembelian->update();
        
        $total_jurnal = $pembelian_temporary;

        $get_pembelian = PembelianTemporaryDetail::where('id_pembelian',$id_pembelian)->get();
        
        // input HPP
        //HPP = harga beli supplier + 5%
        foreach ($get_pembelian as $list ) {
            
            $jumlah_terima = $list->jumlah_terima;
            $harga_invoice = $list->total;
        
            $harga = round($harga_invoice/$jumlah_terima);
            //$margin = round(($harga*5)/100);
            $harga_beli = $harga;

            $list->harga_beli = $harga_beli;
            $list->update();

        }
        
        if($tipe_bayar == 0){

            return back()->with('alert','Pilih Tipe Bayar');    
        
        }elseif ($tipe_bayar == 1) {
            
                $jurnal = new TabelTransaksi;
                $jurnal->unit =  Auth::user()->unit; 
                $jurnal->kode_transaksi = $pembelian->id_pembelian;
                $jurnal->kode_rekening = 1482000;
                $jurnal->tanggal_transaksi  = $tanggal;
                $jurnal->jenis_transaksi  = 'Jurnal System';
                $jurnal->keterangan_transaksi = 'Pembelian' . ' ' . $pembelian->id_pembelian . ' ' . $pembelian->nama;
                $jurnal->debet = $total_jurnal;
                $jurnal->kredit = 0;
                $jurnal->tanggal_posting = '';
                $jurnal->keterangan_posting = '0';
                $jurnal->id_admin = Auth::user()->id; 
                $jurnal->save();

                $jurnal = new TabelTransaksi;
                $jurnal->unit =  Auth::user()->unit; 
                $jurnal->kode_transaksi = $pembelian->id_pembelian;
                $jurnal->kode_rekening = 2473000;
                $jurnal->tanggal_transaksi  = $tanggal;
                $jurnal->jenis_transaksi  = 'Jurnal System';
                $jurnal->keterangan_transaksi = 'Pembelian' . ' ' . $pembelian->id_pembelian . ' ' . $pembelian->nama;
                $jurnal->debet =0;
                $jurnal->kredit = $total_jurnal;
                $jurnal->tanggal_posting = '';
                $jurnal->keterangan_posting = '0';
                $jurnal->id_admin = Auth::user()->id; 
                $jurnal->save();
        
            }else{
            
                $jurnal = new TabelTransaksi;
                $jurnal->unit =  Auth::user()->unit; 
                $jurnal->kode_transaksi = $pembelian->id_pembelian;
                $jurnal->kode_rekening = 1482000;
                $jurnal->tanggal_transaksi  = $tanggal;
                $jurnal->jenis_transaksi  = 'Jurnal System';
                $jurnal->keterangan_transaksi = 'Pembelian' . ' ' . $pembelian->id_pembelian . ' ' . $pembelian->nama;
                $jurnal->debet = $total_jurnal;
                $jurnal->kredit = 0;
                $jurnal->tanggal_posting = '';
                $jurnal->keterangan_posting = '0';
                $jurnal->id_admin = Auth::user()->id; 
                $jurnal->save();
                
                $jurnal = new TabelTransaksi;
                $jurnal->unit =  Auth::user()->unit; 
                $jurnal->kode_transaksi = $pembelian->id_pembelian;
                $jurnal->kode_rekening = 2500000;
                $jurnal->tanggal_transaksi  = $tanggal;
                $jurnal->jenis_transaksi  = 'Jurnal System';
                $jurnal->keterangan_transaksi = 'Pembelian' . ' ' . $pembelian->id_pembelian . ' ' . $pembelian->nama;
                $jurnal->debet =0;
                $jurnal->kredit = $total_jurnal;
                $jurnal->tanggal_posting = '';
                $jurnal->keterangan_posting = '0';
                $jurnal->id_admin = Auth::user()->id; 
                $jurnal->save();
                
            }
          
            $data = PembelianTemporary::where('id_pembelian',$id_pembelian)->first();
            $data->status = 3;
            $data->no_invoice = $noinvoice;
            $data->update();

            // 
            $data = Pembelian::where('id_pembelian_t',$id_pembelian)->first();
            $data->status = 2;
            $data->update();

            return redirect()->route('invoice.index')->with(['berhasil' => 'PO '. $id_pembelian . ' Berhasil Disimpan']);;

    }


    public function hitung(Request $request,$id){
    
        //cek diskon
        $cek = $diskon = DiskonPembelian::where('id_pembelian',$id)->count();
     
        if ($cek < 1) {

            $detail_pembelian = PembelianTemporaryDetail::where('id_pembelian',$id)->get();
            
            foreach ($detail_pembelian as $detail ) {
             
                $detail->diskon_lainya = null;
                $detail->update();
    
            }
     
        }else{
            
            $diskon_pembelian = DiskonPembelian::where('id_pembelian',$id)->where('keterangan','!=','spesial diskon')->get();
            
            $diskon = 0;
            $persentase = 0;

            foreach ($diskon_pembelian as $list ) {

                if ( strpos( $list->nominal, "." ) !== false ) {
                    $persentase += $list->nominal;
                }else{
                    $diskon += $list->nominal;
                }

                
            }


            $total_sementara = 0;
            
            $detail_pembelian = PembelianTemporaryDetail::where('id_pembelian',$id)->get();
            
            foreach ($detail_pembelian as $detail ) {
                
                $total_sementara += $detail->sub_total_terima - $detail->spesial_diskon;
                
            }
            
            $persentase += $diskon/$total_sementara;
            
            foreach ($detail_pembelian as $detail ) {
                
                $pengurangan_spesial = $detail->sub_total_terima - $detail->spesial_diskon;
                
                $detail->diskon_lainya = round($pengurangan_spesial * $persentase);
                $detail->update();
                
            }
            
        }
        
        if( $request->has('ppn') ){
        
                    //hitung kesluruhan + ppn
                    $pembelian_detail = PembelianTemporaryDetail::where('id_pembelian',$id)->get();
            
                    foreach ($pembelian_detail as $list ) {
                        
                        $invoice = $list->sub_total_terima;
                        
                        if ($invoice = 0 || $invoice == null) {
                            $data = array(
                                "alert" => "Kolom Invoice ".$list->kode_produk." Kosong",
                                );
                            return response()->json($data);
                        }
                    }
        
                    foreach ($pembelian_detail as $list ) {
                        
                        $regular_diskon = $list->regular_diskon;
                        $invoice = $list->sub_total_terima;
                        $spesial_diskon = $list->spesial_diskon;
                        $dikon_lainya = $list->diskon_lainya;
                        
                        $sub_total = $invoice - $spesial_diskon - $dikon_lainya - $regular_diskon;
        
                        // update
                        $list->total = $sub_total;
                        $list->update();
                        
                    }
                            
                        $total = PembelianTemporaryDetail::where('id_pembelian',$id)->sum('total');
        
                        $data = array(
                            "bayarrp" => format_uang($total),
                            "terbilang" => ucwords(terbilang($total))." Rupiah",
                            "pesan" => "Harga Invoice Termasuk PPN 10%"
                            );
        
                    return response()->json($data);
            
            
        }else {
    

            //hitung kesluruhan tanpa ppn
            $pembelian_detail = PembelianTemporaryDetail::where('id_pembelian',$id)->get();
        
            foreach ($pembelian_detail as $list ) {
                
                $invoice = $list->sub_total_terima;
                
                if ($invoice = 0 || $invoice == null) {
                    $data = array(
                        "alert" => "Kolom Invoice ".$list->kode_produk." Kosong",
                        );
                    return response()->json($data);
                }
            }

            foreach ($pembelian_detail as $list ) {
                
                $regular_diskon = $list->regular_diskon;
                $invoice = $list->sub_total_terima;
                $spesial_diskon = $list->spesial_diskon;
                $dikon_lainya = $list->diskon_lainya;
                
                $sub_total = $invoice-$spesial_diskon-$dikon_lainya-$regular_diskon;
                $ppn = $sub_total+($sub_total*10)/100;
                
                // update
                $list->total = $ppn;
                $list->update();
                
            }
                
            $total = PembelianTemporaryDetail::where('id_pembelian',$id)->sum('total');

            $data = array(
                "bayarrp" => format_uang($total),
                "terbilang" => ucwords(terbilang($total))." Rupiah",
                "pesan" => "Harga Invoice Tidak Termasuk PPN 10%"
                );
        
            return response()->json($data);
           
    
            
        }  
  
        
    } 


}

