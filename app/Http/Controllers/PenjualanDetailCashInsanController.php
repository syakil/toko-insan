<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Redirect;
use Auth;
use PDF;
use App\Penjualan;
use App\Produk;
use App\Member;
use App\Setting;
use App\TabelTransaksi;
use App\PenjualanDetail;   


class PenjualanDetailCashInsanController extends Controller
{
   public function index(){
      $produk = Produk::all() -> where('stok', '>', '0');
      $member = Member:: all() -> where('jenis', '=', '1');
      $setting = Setting::first();
     
     if(!empty(session('idpenjualan'))){
       $idpenjualan = session('idpenjualan');
       return view('penjualan_detail_member_insan.index', compact('produk', 'member', 'setting', 'idpenjualan'));
     }else{
       return Redirect::route('home');  
     }
   }



   public function listData($id)
   {

      
     $detail = PenjualanDetail::leftJoin('produk', 'produk.kode_produk', '=', 'penjualan_detail.kode_produk')
        ->where('id_penjualan', '=', $id)
        ->get();
        $no = 0;
        $data = array();
        $total = 0;
        $total_item = 0;
        $stok=0;
        $in=0;
        foreach($detail as $list){
          $no ++;
          $row = array();
          $row[] = $no;
          $row[] = $list->kode_produk;
          $row[] = $list->nama_produk;
          $row[] = "Rp. ".format_uang($list->harga_jual_member_insan);
          $row[] = "<input type='number' class='form-control' name='jumlah_$list->id_penjualan_detail' value='$list->jumlah' onChange='changeCount($list->id_penjualan_detail)'>";
          $row[] = $list->diskon;
          $row[] = "Rp. ".format_uang($list->sub_total);
          $row[] = '<div class="btn-group">
                  <a onclick="deleteItem('.$list->id_penjualan_detail.')" class="btn btn-danger btn-sm"><i class="fa fa-trash"></i></a>';
          $data[] = $row;
   
          $total += $list->harga_jual_member_insan * $list->jumlah;
          $total_item += $list->jumlah;
          $stok= $list->stok;
          $in=$list->jumlah;

        }
        if($stok<$in){
         $data[] = array("Stok kurang");
       
         $output = array("data" => $data);
         return response()->json($output);
        }else{
   
        $data[] = array("<span class='hide total'>$total</span><span class='hide totalitem'>$total_item</span>", "", "", "", "", "", "", "");
       
        $output = array("data" => $data);
        return response()->json($output);
        }
      }
   

   public function store(Request $request)
   {
        $produk = Produk::where('kode_produk', '=', $request['kode'])->first();

        $detail = new PenjualanDetail;
        $detail->id_penjualan = $request['idpenjualan'];
        $detail->kode_produk = $request['kode'];
        $detail->harga_jual = $produk->harga_jual;
        $detail->jumlah = 1;
        $detail->diskon = $produk->diskon;
        $detail->sub_total = $produk->harga_jual_member_insan - ($produk->diskon);
        $detail->save();

   }

   public function update(Request $request, $id)
   {
      $nama_input = "jumlah_".$id;
      $detail = PenjualanDetail::find($id);
      $total_harga = $request[$nama_input] * $detail->harga_jual;

      $detail->jumlah = $request[$nama_input];
      $detail->sub_total = $total_harga - ($detail->diskon/100 * $total_harga);
      $detail->update();
   }

   public function destroy($id)
   {
      $detail = PenjualanDetail::find($id);
      $detail->delete();
   }

   public function newSession()
   {
      $penjualan = new Penjualan; 
      $penjualan->kode_member = 0;    
      $penjualan->total_item = 0;    
      $penjualan->total_harga = 0;    
      $penjualan->diskon = 0;    
      $penjualan->bayar = 0;    
      $penjualan->diterima = 0;    
      $penjualan->id_user = Auth::user()->id;    
      $penjualan->save();
      
      session(['idpenjualan' => $penjualan->id_penjualan]);

      return Redirect::route('memberinsan.index');    
   }

 

   public function saveData(Request $request)
   {

      $now = \Carbon\Carbon::now();
      $penjualan_dis= PenjualanDetail::groupBy('id_penjualan')
   ->select('id_penjualan', \DB::raw('sum(diskon) as diskon'))
   ->where('id_penjualan', '=', $request['idpenjualan'])
   ->first();

    $penjualan = Penjualan::find($request['idpenjualan']);
      $penjualan->kode_member = $request['member'];
      $penjualan->total_item = $request['totalitem'];
      $penjualan->total_harga = $request['total'];
      $penjualan->diskon = $penjualan_dis->diskon;
      $penjualan->bayar = $request['bayar'];
      $penjualan->diterima = $request['diterima'];
      $penjualan->update();
      
      if($penjualan_dis->diskon == 0){
      $jurnal = new TabelTransaksi;
        $jurnal->unit =  Auth::user()->unit; 
        $jurnal->kode_transaksi = $request['idpenjualan'];
        $jurnal->kode_rekening = 1120000;
        $jurnal->tanggal_transaksi  = $now;
        $jurnal->jenis_transaksi  = 'Jurnal Umum';
        $jurnal->keterangan_transaksi = 'Penjualan';
        $jurnal->debet =$request['bayar'];
        $jurnal->kredit = 0;
        $jurnal->tanggal_posting = '';
        $jurnal->keterangan_posting = '0';
        $jurnal->id_admin = Auth::user()->id; 
        $jurnal->save();
  
        $jurnal = new TabelTransaksi;
        $jurnal->unit =  Auth::user()->unit; 
        $jurnal->kode_transaksi = $request['idpenjualan'];
        $jurnal->kode_rekening = 1482000;
        $jurnal->tanggal_transaksi  = $now;
        $jurnal->jenis_transaksi  = 'Jurnal Umum';
        $jurnal->keterangan_transaksi = 'Penjualan';
        $jurnal->debet =0;
        $jurnal->kredit =$request['bayar'];
        $jurnal->tanggal_posting = '';
        $jurnal->keterangan_posting = '0';
        $jurnal->id_admin = Auth::user()->id; 
        $jurnal->save();
      }
      else{
         $jurnal = new TabelTransaksi;
        $jurnal->unit =  Auth::user()->unit; 
        $jurnal->kode_transaksi = $request['idpenjualan'];
        $jurnal->kode_rekening = 1120000;
        $jurnal->tanggal_transaksi  = $now;
        $jurnal->jenis_transaksi  = 'Jurnal Umum';
        $jurnal->keterangan_transaksi = 'Penjualan';
        $jurnal->debet =$request['bayar'] - $penjualan_dis->diskon;
        $jurnal->kredit = 0;
        $jurnal->tanggal_posting = '';
        $jurnal->keterangan_posting = '0';
        $jurnal->id_admin = Auth::user()->id; 
        $jurnal->save();

        $jurnal = new TabelTransaksi;
        $jurnal->unit =  Auth::user()->unit; 
        $jurnal->kode_transaksi = $request['idpenjualan'];
        $jurnal->kode_rekening = 56412;
        $jurnal->tanggal_transaksi  = $now;
        $jurnal->jenis_transaksi  = 'Jurnal Umum';
        $jurnal->keterangan_transaksi = 'Penjualan';
        $jurnal->debet =$penjualan_dis->diskon;
        $jurnal->kredit = 0;
        $jurnal->tanggal_posting = '';
        $jurnal->keterangan_posting = '0';
        $jurnal->id_admin = Auth::user()->id; 
        $jurnal->save();
  
        $jurnal = new TabelTransaksi;
        $jurnal->unit =  Auth::user()->unit; 
        $jurnal->kode_transaksi = $request['idpenjualan'];
        $jurnal->kode_rekening = 1482000;
        $jurnal->tanggal_transaksi  = $now;
        $jurnal->jenis_transaksi  = 'Jurnal Umum';
        $jurnal->keterangan_transaksi = 'Penjualan';
        $jurnal->debet =0;
        $jurnal->kredit =$request['bayar'];
        $jurnal->tanggal_posting = '';
        $jurnal->keterangan_posting = '0';
        $jurnal->id_admin = Auth::user()->id; 
        $jurnal->save();
      }

      $detail = PenjualanDetail::where('id_penjualan', '=', $request['idpenjualan'])->get();
      foreach($detail as $data){
        $produk = Produk::where('kode_produk', '=', $data->kode_produk)->first();
        $produk->stok -= $data->jumlah;
        $produk->update();
      }
      return Redirect::route('memberinsan.cetak');
   }
   
   public function loadForm($diskon, $total, $diterima){
     $bayar = $total - ($diskon);
     $kembali = ($diterima != 0) ? $diterima - $bayar : 0;

     $data = array(
        "totalrp" => format_uang($total),
        "bayar" => $bayar,
        "bayarrp" => format_uang($bayar),
        "terbilang" => ucwords(terbilang($bayar))." Rupiah",
        "kembalirp" => format_uang($kembali),
        "kembaliterbilang" => ucwords(terbilang($kembali))." Rupiah"
      );
     return response()->json($data);
   }

   public function printNota()
   {
      $detail = PenjualanDetail::leftJoin('produk', 'produk.kode_produk', '=', 'penjualan_detail.kode_produk')
        ->where('id_penjualan', '=', session('idpenjualan'))
        ->where('produk.unit', '=', Auth::user()->id) 
         ->get();

      $penjualan = Penjualan::find(session('idpenjualan'));
      $setting = Setting::find(1);
      
      if($setting->tipe_nota == 0){
        $handle = printer_open(); 
        printer_start_doc($handle, "Nota");
        printer_start_page($handle);

        $font = printer_create_font("Arial", 10, 11, 10, false, false, false, 0);
        printer_select_font($handle, $font);
        printer_draw_text($handle, $setting->logo, 0, 80);
        printer_draw_text($handle, $setting->nama_perusahaan, 0, 90);

        $font = printer_create_font("Arial", 10, 11, 10, false, false, false, 0);
        printer_select_font($handle, $font);
        printer_draw_text($handle, $setting->alamat, 0, 100);

        printer_draw_text($handle, date('Y-m-d'), 0, 120);
        printer_draw_text($handle, substr("".Auth::user()->name, 0), 0, 130);

        printer_draw_text($handle, "No : ".substr("00000000".$penjualan->id_penjualan, 0), 0, 140);

        printer_draw_text($handle, "============================", 0, 150);
        
        $y = 170;
        
        foreach($detail as $list){           
           printer_draw_text($handle, $list->kode_produk." ".$list->nama_produk, 0,$y+=15);
           printer_draw_text($handle, $list->jumlah." x ".format_uang($list->harga_jual), 0,$y+=15);
           printer_draw_text($handle, substr("".format_uang($list->harga_jual*$list->jumlah), -10), 250, $y);

           if($list->diskon != 0){
              printer_draw_text($handle, "Diskon", 0,$y+= 140);
              printer_draw_text($handle, substr("-".format_uang($list->diskon/100*$list->sub_total),-10),850,$y);
           }
        }
        
        printer_draw_text($handle, "------------------------------------", 0, $y+=15);

        printer_draw_text($handle, "Total Harga     : ", 0, $y+=15);
        printer_draw_text($handle, substr("          ".format_uang($penjualan->total_harga), 0),155,$y);

        printer_draw_text($handle, "Total Item      : ", 0, $y+=15);
        printer_draw_text($handle, substr("          ".$penjualan->total_item, 0),155,$y);

        printer_draw_text($handle, "Diskon Member : ", 0, $y+=15);
        printer_draw_text($handle, substr("           ".$penjualan->diskon."%",0),155,$y);

        printer_draw_text($handle, "Total Bayar   : ", 0, $y+=15);
        printer_draw_text($handle, substr("            ".format_uang($penjualan->bayar),0), 155, $y);

        printer_draw_text($handle, "Diterima      : ", 0, $y+=15);
        printer_draw_text($handle, substr("            ".format_uang($penjualan->diterima), 0), 155,$y);

        printer_draw_text($handle, "Kembali        : ", 0, $y+=15);
        printer_draw_text($handle, substr("            ".format_uang($penjualan->diterima-$penjualan->bayar),0), 155,$y);
        printer_draw_text($handle, "============================", 0, $y+=15);
        printer_draw_text($handle, "Id Member     : ", 0, $y+=15);
        printer_draw_text($handle, substr("          ", 0),155,$y);
        printer_draw_text($handle, "Nama     : ", 0, $y+=15);
        printer_draw_text($handle, substr("          ", 0),155,$y);
        printer_draw_text($handle, "Maxsimal Belanja     : ", 0, $y+=15);
        printer_draw_text($handle, substr("          ", 0),155,$y);

        printer_draw_text($handle, "Belanja Sekarang     : ", 0, $y+=15);
        printer_draw_text($handle, substr("          ".format_uang($penjualan->total_harga), 0),155,$y);


        printer_draw_text($handle, "============================", 0, $y+=15);
        printer_draw_text($handle, "-= TERIMA KASIH =-", 10, $y+=15);
        printer_draw_text($handle, "-Barang Yang sdh dibeli tidak dpt ditukar/kembali-", 10, $y+=15);
        
       


        
        printer_delete_font($font);
        
        printer_end_page($handle);
        printer_end_doc($handle);
        printer_close($handle);
      }
       
      return view('penjualan_detail.selesai', compact('setting'));
   }

   public function notaPDF(){
     $detail = PenjualanDetail::leftJoin('produk', 'produk.kode_produk', '=', 'penjualan_detail.kode_produk')
        ->where('id_penjualan', '=', session('idpenjualan'))
        ->where('unit', '=',Auth::user()->unit)
         ->get();

      $penjualan = Penjualan::find(session('idpenjualan'));
      $setting = Setting::find(1);
      $no = 0;
     
     $pdf = PDF::loadView('penjualan_detail.notapdf', compact('detail', 'penjualan', 'setting', 'no'));
     $pdf->setPaper(array(0,0,550,440), 'potrait');      
      return $pdf->stream();
   }
}