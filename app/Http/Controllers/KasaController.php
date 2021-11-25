<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Kasa;
use App\TabelTransaksi;
use App\Member;
use Redirect;
use PDF;
use Auth;
use App\Kirim;
use App\KirimDetail;
use App\SaldoToko;
use App\Pengeluaran;
use App\Setting;
use App\Penjualan;
use App\PembelianTemporary;
use App\PembelianTemporaryDetail;
use App\Produk;
use App\StokOpnameParsial;
use App\ProdukDetail;
use DB;
use Ramsey\Uuid\Uuid;
use App\TunggakanToko;
use App\Branch;
use App\Musawamah;
use App\PenjualanDetail;

class KasaController extends Controller{

   public function index(){
   
      $param_tgl = \App\ParamTgl::where('nama_param_tgl','tanggal_transaksi')->where('unit',Auth::user()->id)->first();
      $tanggal = $param_tgl->param_tgl;

      $penjualan_cash = Penjualan::
                  select(\DB::raw('sum(total_harga) as cash'))
                  ->where('created_at', 'like', $tanggal.'%')
                  ->where('unit', '=', Auth::user()->unit)
                  ->where('type_transaksi','cash')
                  ->first();

      $cash = $penjualan_cash->cash;

      $penjualan_musawamah = Penjualan::
                           select(\DB::raw('sum(total_harga) as musawamah'))
                           ->where('created_at', 'like', $tanggal.'%')
                           ->where('unit', '=', Auth::user()->unit)
                           ->where('type_transaksi','credit')
                           ->first();

      $id_penjualan = array();

      $transaksi = Penjualan::where('created_at', 'like', $tanggal.'%')
                           ->where('unit', '=', Auth::user()->unit)
                           ->where('type_transaksi','credit')
                           ->get();

      foreach ($transaksi as $key) {
         $id_penjualan[] = $key->id_penjualan;
      }

      $cash_lebih_plafond = TabelTransaksi::select(\DB::raw('sum(debet-kredit) as cash_lebih'))
      ->where('kode_rekening',1120000)
      ->where('tanggal_transaksi',$tanggal)
      ->where('unit',Auth::user()->unit)
      ->whereIn('kode_transaksi',$id_penjualan)
      ->first();

      $setoran = TabelTransaksi::select(\DB::raw('sum(debet) as setoran'))
      ->where('kode_rekening',1120000)
      ->where('tanggal_transaksi',$tanggal)
      ->where('unit',Auth::user()->unit)
      ->whereIn('keterangan_transaksi',['Setoran angsuran Musawamah'])
      ->first();

      $musawamah = $penjualan_musawamah->musawamah;

      return view('kasa.index',compact('cash','musawamah','setoran','cash_lebih_plafond')); 
   
   }

   public function listData(){
   
      $kasa = Kasa::orderBy('id_kasa', 'desc')->where('kode_toko', '=' , Auth::user()->unit)
                  ->get();
      $no = 0;
      $data = array();
      foreach($kasa as $list){

         $no ++;
         $row = array();
         $row[] = "<input type='checkbox' name='id[]'' value='".$list->id_kasa."'>";
         $row[] = $no;
         $row[] = $list->tgl;
         $row[] = $list->kode_kasir;
         $row[] = $list->seratus_ribu;
         $row[] = $list->limapuluh_ribu;
         $row[] = $list->duapuluh;
         $row[] = $list->sepuluh;
         $row[] = $list->limaribu;
         $row[] = $list->duaribu;
         $row[] = $list->seribu;
         $row[] = $list->seratus;
         $row[] = number_format($list->jumlah);
         $row[] = number_format($list->cash);
         $row[] = number_format($list->musawamah);
         $row[] = number_format($list->total);
         $row[] = number_format($list->selisih);
         $data[] = $row;
      }

      $output = array("data" => $data);
      return response()->json($output);
   }

   Public function eod(){

      try {
         
         DB::beginTransaction();
         
         $branch = Branch::where('kode_gudang',Auth::user()->unit)->get();
         $kode_toko = array();


         foreach ($branch as $data ) {
            $kode_toko[] = $data->kode_toko;
         }

         $so_parsial = StokOpnameParsial::where("unit",Auth::user()->unit)->where('status',1)->count();

         if ($so_parsial > 0) {
            return back()->with(["error" => "SO Parsial Belum Di Lakukan!"]);
         }

         
         $so_parsial_approve = StokOpnameParsial::where("unit",Auth::user()->unit)->where('status',2)->count();

         if ($so_parsial_approve > 0) {
            return back()->with(["error" => "SO Parsial Belum Di Approve!"]);
         }

         $produk = Produk::where("unit",Auth::user()->unit)->get();

         foreach ($produk as $list ) {

            $produk_detail = ProdukDetail::where("kode_produk",$list->kode_produk)->where("unit",Auth::user()->unit)->where("stok_detail", ">","0")->where('status',2)->orderBy("no_faktur","DESC")->first();

            
            if ($produk_detail) {

               $produk_detail->status = null;
               $produk_detail->update();

               $data_master_produk = Produk::where("kode_produk",$list->kode_produk)->whereIn("unit",$kode_toko)->update([
                     "harga_beli" => $produk_detail->harga_beli,
                     "harga_jual" => $produk_detail->harga_jual_umum,
                     "harga_jual_member_insan" => $produk_detail->harga_jual_insan,
                     "harga_jual_insan" => $produk_detail->harga_jual_insan,
                     "harga_jual_pabrik" => $produk_detail->harga_jual_umum,
                     
                  ]);  

               $produk_ubah = ProdukDetail::where("kode_produk",$list->kode_produk)->whereIn("unit",$kode_toko)->where("status",null)->get();
               
               foreach($produk_ubah as $ubah) {

                  $kode_produk = $ubah->kode_produk;
                  $harga_lama = $ubah->harga_jual_umum * $ubah->stok_detail;
                  $harga_baru = $produk_detail->harga_jual_umum * $ubah->stok_detail;
                  $selisih = abs($harga_baru - $harga_lama);
                  $unit = $ubah->unit;

                  $kode = Uuid::uuid4()->getHex();
                  $kode_t = substr($kode,25);
                  $kode_t ="EOD/-".$unit.$kode_t;
                  $now = date('Y-m-d');

                  if ($unit != Auth::user()->unit) {                        
                     if ($harga_baru > $harga_lama) {
                        $jurnal = new TabelTransaksi;
                        $jurnal->unit =  $unit; 
                        $jurnal->kode_transaksi = $kode_t;
                        $jurnal->kode_rekening = 1482000;
                        $jurnal->tanggal_transaksi  = $now;
                        $jurnal->jenis_transaksi  = "Jurnal System";
                        $jurnal->keterangan_transaksi = "Kenaikan Harga ". $kode_produk;
                        $jurnal->debet = $selisih;
                        $jurnal->kredit = 0;
                        $jurnal->tanggal_posting = "";
                        $jurnal->keterangan_posting = "0";
                        $jurnal->id_admin = $unit; 
                        $jurnal->save();
                        
                        $jurnal = new TabelTransaksi;
                        $jurnal->unit =  $unit; 
                        $jurnal->kode_transaksi = $kode_t;
                        $jurnal->kode_rekening = 1483000;
                        $jurnal->tanggal_transaksi  = $now;
                        $jurnal->jenis_transaksi  = "Jurnal System";
                        $jurnal->keterangan_transaksi =  "Kenaikan Harga ". $kode_produk;
                        $jurnal->debet = 0;
                        $jurnal->kredit = $selisih;
                        $jurnal->tanggal_posting = "";
                        $jurnal->keterangan_posting = "0";
                        $jurnal->id_admin = $unit; 
                        $jurnal->save();

                     }elseif ($harga_baru < $harga_lama){
                        
                        $jurnal = new TabelTransaksi;
                        $jurnal->unit =  $unit; 
                        $jurnal->kode_transaksi = $kode_t;
                        $jurnal->kode_rekening = 1483000;
                        $jurnal->tanggal_transaksi  = $now;
                        $jurnal->jenis_transaksi  = "Jurnal System";
                        $jurnal->keterangan_transaksi =  "Penurunan Harga ". $kode_produk;
                        $jurnal->debet = $selisih;
                        $jurnal->kredit = 0;
                        $jurnal->tanggal_posting = "";
                        $jurnal->keterangan_posting = "0";
                        $jurnal->id_admin = $unit; 
                        $jurnal->save();
                        
                        $jurnal = new TabelTransaksi;
                        $jurnal->unit =  $unit; 
                        $jurnal->kode_transaksi = $kode_t;
                        $jurnal->kode_rekening = 1482000;
                        $jurnal->tanggal_transaksi  = $now;
                        $jurnal->jenis_transaksi  = "Jurnal System";
                        $jurnal->keterangan_transaksi = "Penurunan Harga ". $kode_produk;
                        $jurnal->debet = 0;
                        $jurnal->kredit = $selisih;
                        $jurnal->tanggal_posting = "";
                        $jurnal->keterangan_posting = "0";
                        $jurnal->id_admin = $unit; 
                        $jurnal->save();
                     }
                  }

                  $ubah->harga_jual_umum = $produk_detail->harga_jual_umum;
                  $ubah->harga_jual_insan = $produk_detail->harga_jual_insan;
                  $ubah->status = null;
                  $ubah->update();
                  
               }  

                             
		
               
            }

         }
         
         $produk = Produk::where('unit',Auth::user()->unit)->get();
         $bulan_sekarang = date("m");
         $kirim_barang = Kirim::where("kode_gudang",Auth::user()->unit)->whereMonth('created_at',$bulan_sekarang)->get();
         $terima_barang = Kirim::where("id_supplier",Auth::user()->unit)->whereMonth('created_at',$bulan_sekarang)->get();
         $pembelian = PembelianTemporary::where("kode_gudang",Auth::user()->unit)->whereMonth('updated_at',$bulan_sekarang)->get();
         $id_kirim = array();
         $id_pembelian = array();
         $id_terima = array();
         

         foreach ($kirim_barang as $value) {
            $id_kirim[] = $value->id_pembelian;
         }
         
         foreach ($terima_barang as $value) {
            $id_terima[] = $value->id_pembelian;
         }
         
         foreach ($pembelian as $value) {
            $id_pembelian[] = $value->id_pembelian;
         }

         $jumlah = StokOpnameParsial::where("unit",Auth::user()->unit)->where("status",1)->count();
            
         foreach ($produk as $value) {
            
            
            $rata_rata_kirim = KirimDetail::whereIn("id_pembelian",$id_kirim)->where("kode_produk",$value->kode_produk)->avg("jumlah");
            $rata_rata_terima = KirimDetail::whereIn("id_pembelian",$id_terima)->where("kode_produk",$value->kode_produk)->avg("jumlah_terima");
            $rata_rata_pembelian = PembelianTemporaryDetail::whereIn("id_pembelian",$id_pembelian)->where("kode_produk",$value->kode_produk)->avg("jumlah_terima");
            
            $rata_rata = $rata_rata_kirim + $rata_rata_terima + $rata_rata_pembelian;
            

            if($rata_rata) {
               
               $cek = StokOpnameParsial::where("kode_produk",$value->kode_produk)->where("unit",Auth::user()->unit)->whereMonth("tanggal_so",$bulan_sekarang)->first();
               
               if ($cek == null) {
                  
                  if ($rata_rata > $value->stok) {

                     if ($jumlah < 5) {
                     
                        $stok_opname_parsial = new StokOpnameParsial;
                        $stok_opname_parsial->stok_system = $value->stok;
                        $stok_opname_parsial->kode_produk = $value->kode_produk;
                        $stok_opname_parsial->unit = Auth::user()->unit;
                        $stok_opname_parsial->status = 1;
                        $stok_opname_parsial->save();
                        
                        $jumlah++;
                     }   
                  }  
               }
            }
            
         }

         DB::commit();
      
      }catch(\Exception $e){
         
         DB::rollback();
         return back()->with(["error" => $e->getmessage() . ' ' . $e->getLine() ]);

      }

      return back()->with(["success" => "Eod Berhasil"]);

   }


   public function store(Request $request){

      try {
         
         DB::beginTransaction();

         $param_tgl = \App\ParamTgl::where("nama_param_tgl","tanggal_transaksi")->where('unit',Auth::user()->id)->first();   
         $tanggal = $param_tgl->param_tgl;
         $tanggal_esok_carbon = new \Carbon\Carbon($param_tgl->param_tgl);
         $cek_hari = new \Carbon\Carbon($param_tgl->param_tgl);
         $tanggal_carbon = new \Carbon\Carbon($param_tgl->param_tgl);
         $tanggal_tunggak_carbon = new \Carbon\Carbon($param_tgl->param_tgl);
         
         
         $so_parsial = StokOpnameParsial::where("unit",Auth::user()->unit)->where('status',1)->count();

         if ($so_parsial > 0) {
            return back()->with(["error" => "SO Parsial Belum Di Lakukan!"]);
         }

         
         $so_parsial_approve = StokOpnameParsial::where("unit",Auth::user()->unit)->where('status',2)->count();

         if ($so_parsial_approve>0) {
            return back()->with(["error" => "SO Parsial Belum Di Approve!"]);
         }

         
         $produk = Produk::where('unit',Auth::user()->unit)->get();
         $bulan_sekarang = date("m");
         $kirim_barang = Kirim::where("kode_gudang",Auth::user()->unit)->whereMonth('created_at',$bulan_sekarang)->get();
         $terima_barang = Kirim::where("id_supplier",Auth::user()->unit)->whereMonth('created_at',$bulan_sekarang)->get();
         $penjualan = Penjualan::where("id_user",Auth::user()->id)->whereMonth('updated_at',$bulan_sekarang)->get();
         $id_kirim = array();
         $id_pejualan = array();
         $id_terima = array();
         

         if ($kirim_barang != null) {
            
            foreach ($kirim_barang as $value) {
               $id_kirim[] = $value->id_pembelian;
            }
         }
         
         if ($terima_barang != null) {
            foreach ($terima_barang as $value) {
               
               $id_terima[] = $value->id_pembelian;
            }
         }
         
         if ($penjualan !=null) {
            
            foreach ($penjualan as $value) {
               $id_penjualan[] = $value->id_penjualan;
            }
         }

         $jumlah = StokOpnameParsial::where("unit",Auth::user()->unit)->where("status",1)->count();
            
         foreach ($produk as $value) {
            
            
            $rata_rata_kirim = KirimDetail::whereIn("id_pembelian",$id_kirim)->where("kode_produk",$value->kode_produk)->avg("jumlah");
            $rata_rata_terima = KirimDetail::whereIn("id_pembelian",$id_terima)->where("kode_produk",$value->kode_produk)->avg("jumlah_terima");
            $rata_rata_pembelian = PenjualanDetail::where("kode_produk",$value->kode_produk)->leftJoin("penjualan","penjualan.id_penjualan","penjualan_detail.id_penjualan")->where("id_user",Auth::user()->id)->whereMonth('penjualan_detail.updated_at',$bulan_sekarang)->avg("jumlah");
            
            $rata_rata = $rata_rata_kirim + $rata_rata_terima + $rata_rata_pembelian;
            

            if($rata_rata) {
               
               $cek = StokOpnameParsial::where("kode_produk",$value->kode_produk)->where("unit",Auth::user()->unit)->whereMonth("tanggal_so",$bulan_sekarang)->first();
               
               if ($cek == null) {
                  
                  if ($rata_rata > $value->stok) {

                     if ($jumlah < 5) {
                     
                        $stok_opname_parsial = new StokOpnameParsial;
                        $stok_opname_parsial->stok_system = $value->stok;
                        $stok_opname_parsial->kode_produk = $value->kode_produk;
                        $stok_opname_parsial->unit = Auth::user()->unit;
                        $stok_opname_parsial->status = 1;
                        $stok_opname_parsial->save();
                        
                        $jumlah++;
                     }   
                  }  
               }
            }
            
         }
         
         
         $daftar_hari = array(
            "Sunday" => "Minggu",
            "Monday" => "Senin",
            "Tuesday" => "Selasa",
            "Wednesday" => "Rabu",
            "Thursday" => "Kamis",
            "Friday" => "Jumat",
            "Saturday" => "Sabtu"
         );
         
         $namahari = date('l', strtotime($tanggal_esok_carbon->addDays(1)->toDateString()));
         
         if ($namahari == "Saturday") {
            
            $tgl_esok = $tanggal_carbon->addDays(3);
            $namahari = date('l', strtotime($cek_hari->addDays(3)->toDateString()));
            $tgl_tunggakan = $tanggal_tunggak_carbon->subDays(11)->toDateString();
            
         }else {
            
            $tgl_esok = $tanggal_carbon->addDays(1);
            $namahari = date("l", strtotime($cek_hari->addDays(1)->toDateString()));
            $tgl_tunggakan = $tanggal_tunggak_carbon->subDays(14)->toDateString();
         }

         
         $hari = $daftar_hari[$namahari];
         $unit = Auth::user()->unit;
         

         $cash = TabelTransaksi::where("unit",auth::user()->unit)->where("kode_rekening","1120000")->where("tanggal_transaksi",$tanggal)->where("id_admin",Auth::user()->id)->sum("debet");    
         $musawamah = TabelTransaksi::where("unit",auth::user()->unit)->where("keterangan_transaksi","musawamah")->where("kode_rekening","1482000")->where("tanggal_transaksi",$tanggal)->where("id_admin",Auth::user()->id)->sum("kredit");

         $jumlah_cash = $request['jumlah'];
         $total_belanja = $cash+$musawamah;
         $selisih = abs($cash - $jumlah_cash);

         $kasa = new Kasa;
         $kasa->seratus_ribu = $request["seratus_ribu"];
         $kasa->limapuluh_ribu  = $request["limapuluh_ribu"];
         $kasa->duapuluh = $request["duapuluh"];
         $kasa->sepuluh = $request["sepuluh"];
         $kasa->limaribu = $request["limaribu"];
         $kasa->duaribu = $request["duaribu"];
         $kasa->seribu = $request["seribu"];
         $kasa->limaratus = $request["limaratus"];
         $kasa->seratus = $request["seratus"];
         $kasa->jumlah = $request["jumlah"];
         $kasa->cash =$cash;
         $kasa->total = $total_belanja;
         $kasa->selisih = $selisih;
         $kasa->musawamah = $musawamah;
         $kasa->tgl =$tanggal;
         $kasa->kode_kasir = Auth::user()->id;
         $kasa->kode_toko = Auth::user()->unit;
         $kasa->save();

               
         $pendapatan = TabelTransaksi::groupBy('kode_rekening')
                                    ->select("kode_rekening", \DB::raw("sum(debet) as debet"))
                                    ->where("tanggal_transaksi", "=", $tanggal)
                                    ->where("kode_rekening", "=", "1120000")
                                    ->where("unit", "=", Auth::user()->unit)
                                    ->first();

               
         $pengeluaran = TabelTransaksi::groupBy("kode_rekening")
                                    ->select("kode_rekening", \DB::raw("sum(kredit) as kredit"))
                                    ->where("tanggal_transaksi", "=", $tanggal)
                                    ->where("kode_rekening", "=", "1120000")
                                    ->where("unit", "=", Auth::user()->unit)
                                    ->first();

         $saldo_id = SaldoToko::where("tanggal",$tanggal)
                              ->where("unit",Auth::user()->unit)
                              ->first();
            
         $saldo_toko = SaldoToko::where("id_saldo",$saldo_id->id_saldo)->first();
         $saldo_akhir = $pengeluaran->debet + $saldo_toko->saldo_akhir - $pengeluaran->kredit;

         $saldo_toko->pemasukan = $pendapatan->debet;
         $saldo_toko->pengeluaran = $pengeluaran->kredir;
         $saldo_toko->saldo_akhir = $saldo_akhir;
         $saldo_toko->update();

         $now = new \Carbon\Carbon($tanggal);
         $saldo_awal = new SaldoToko;
         $saldo_awal->tanggal = $now->addDays(1);
               
         $saldo_awal->saldo_awal = $saldo_toko->saldo_akhir;
         $saldo_awal->unit = Auth::user()->unit;
         $saldo_awal->save();


         $get_tunggakan = DB::table("tunggakan_toko")->where("tgl_tunggak",$tgl_esok)->where("KREDIT",">",0)->where("unit",$unit)->get();
       
         if($get_tunggakan){
            foreach ($get_tunggakan as $data) {
               $member_tunggak = Musawamah::where('id_member',$data->NOREK)->first();
               $hasil_pengurangan_tunggakan = $member_tunggak->bulat - $member_tunggak->angsuran;

               if ($hasil_pengurangan_tunggakan < 0) {
                  $member_tunggak->bulat = 0;
                  $member_tunggak->update();   
               }else {
                  $member_tunggak->bulat -= $member_tunggak->angsuran;
                  $member_tunggak->update();
               }
            }
         }

         $delete_tunggakan = DB::table("tunggakan_toko")->where("tgl_tunggak",$tgl_esok)->where("KREDIT",">",0)->where("unit",$unit)->delete();
         // tunggakan
         $member_tunggakan = DB::table("musawamah")->where("unit",$unit)->where("os",">",0)->where("hari",$hari)->where("tgl_wakalah","<=",$tgl_tunggakan)->orderBy("tgl_wakalah","desc")->get();
         $param_libur = \App\ParamLibur::where("tgl_libur",$tgl_esok)->first(); 
         
         if (empty($param_libur)) {
            
            foreach ($member_tunggakan as $data){
            
               $param_tgl = \App\ParamTgl::where("nama_param_tgl","tanggal_transaksi")->where("unit",Auth::user()->id)->first();   
               $tanggal = $param_tgl->param_tgl;
               $kode_kelompok = $data->code_kel;
               $angsuran = $data->angsuran;
               $nama = $data->Cust_Short_name;
               $cao = $data->cao;
               $id = $data->id_member;

               $tunggakan_data = Musawamah::where("id_member",$data->id_member)->first();
               $tunggakan_selanjutnya = $tunggakan_data->bulat + $tunggakan_data->angsuran;

               if ($tunggakan_data->bulat < $tunggakan_data->os) {

                  if ($tunggakan_selanjutnya > $tunggakan_data->os) {
                  
                     $nominal_tunggakan = $tunggakan_data->os - $tunggakan_data->bulat;
                     // dd($tunggakan_selanjutnya)
                     $tunggakan = new TunggakanToko;
                     $tunggakan->tgl_tunggak = $tgl_esok;
                     $tunggakan->NOREK = $id;
                     $tunggakan->unit = $unit;
                     $tunggakan->CIF = $id;
                     $tunggakan->CODE_KEL = $kode_kelompok;
                     $tunggakan->DEBIT = 0;
                     $tunggakan->type = "01";
                     $tunggakan->KREDIT = $nominal_tunggakan;
                     $tunggakan->USERID = $unit;
                     $tunggakan->KET = "Tunggakan" . " " . $id . " an/ " . $nama;
                     $tunggakan->cao = $cao;
                     $tunggakan->blok = 1;
                     $tunggakan->save();
                     
                     $tunggakan_data->bulat = $tunggakan_data->os;
                     $tunggakan_data->update();
                  
                  }else {

                     $tunggakan = new TunggakanToko;
                     $tunggakan->tgl_tunggak = $tgl_esok;
                     $tunggakan->NOREK = $id;
                     $tunggakan->unit = $unit;
                     $tunggakan->CIF = $id;
                     $tunggakan->CODE_KEL = $kode_kelompok;
                     $tunggakan->DEBIT = 0;
                     $tunggakan->type = "01";
                     $tunggakan->KREDIT = $angsuran;
                     $tunggakan->USERID = $unit;
                     $tunggakan->KET = "Tunggakan" . " " . $id . " an/ " . $nama;
                     $tunggakan->cao = $cao;
                     $tunggakan->blok = 1;
                     $tunggakan->save();
                     
                     $tunggakan_data->bulat += $angsuran;
                     $tunggakan_data->update();
         
      
                  }  
      
               }
            
               $member_status = Member::where('kode_member',$data->id_member)->first();
               $member_status->status_member ="Blok";
               $member_status->update();            
            } 
         }
            
         DB::commit();
      
      }catch(\Exception $e){
         
         DB::rollback();
         return redirect()->route("kasa.index")->with(['error' => $e->getmessage(). ' ' . $e->getLine()]);

      }
               
         return redirect()->action("KasaController@printKasa");
      
   }

   public function edit($id){
      $member = Member::find($id);
      echo json_encode($member);
   }
           
   public function destroy($id){

      $member = Kasa::find($id);
      $member->delete();
   
   }

   public function show(){
     
      $param_tgl = \App\ParamTgl::where("nama_param_tgl","tanggal_transaksi")->where("unit",Auth::user()->id)->first();
      $tanggal = $param_tgl->param_tgl;

      $saldo_id = SaldoToko::where("tanggal",$tanggal)
                                    ->where("unit",Auth::user()->unit)
                                    ->first();

      $saldo_toko = SaldoToko::where("id_saldo",$saldo_id->id_saldo)->first();
   

      $setting=Setting::find(1);
      $no=0;

      $cash = TabelTransaksi::where('unit',auth::user()->unit)->where("kode_rekening","1120000")->where("tanggal_transaksi",$tanggal)->where("id_admin",Auth::user()->id)->sum("debet");    
      $musawamah = TabelTransaksi::where("unit",auth::user()->unit)->where("kode_rekening","1482000")->where("tanggal_transaksi",$tanggal)->where("id_admin",Auth::user()->id)->sum("kredit");
  
      $pdf = PDF::loadView("kasa.printpembayaran", compact("saldo_id","saldo_toko","no","setting","cash","musawamah"));
      $pdf->setPaper(array(0,0,700,600), "potrait");      
      return $pdf->stream();
   }

   
   public function printKasa(){
     
      $param_tgl = \App\ParamTgl::where("nama_param_tgl","tanggal_transaksi")->where("unit",Auth::user()->id)->first();
      $tanggal = $param_tgl->param_tgl;

      $saldo_id = SaldoToko::where("tanggal",$tanggal)
                           ->where("unit",Auth::user()->unit)
                           ->first();

      $saldo_toko = SaldoToko::where("id_saldo",$saldo_id->id_saldo)->first();
   

      $setting=Setting::find(1);
      $no=0;

      $penjualan_cash = TabelTransaksi::groupBy("kode_rekening")
                  ->select("kode_rekening", \DB::raw("sum(debet) as cash"))
                  ->where("tanggal_transaksi", "=", $tanggal)
                  ->where("kode_rekening", "=", "1120000")
                  ->where("unit", "=", Auth::user()->unit)
                  ->whereIn("keterangan_transaksi",["musawamah","penjualan","setoran angsuran musawamah"] )
                  ->first();
      $cash = $penjualan_cash->cash;
      $penjualan_musawamah = TabelTransaksi::groupBy("kode_rekening")
                  ->select("kode_rekening", \DB::raw("sum(kredit) as musawamah"))
                  ->where("tanggal_transaksi", "=", $tanggal)
                  ->where("kode_rekening", "=", "1482000")
                  ->whereIn("keterangan_transaksi",["musawamah","penjualan","setoran angsuran musawamah"] )
                  ->where("unit", "=", Auth::user()->unit)        
                  ->whereIn("keterangan_transaksi",["musawamah","penjualan","setoran angsuran musawamah"] )
          
                  ->first();
      $musawamah = $penjualan_musawamah->musawamah;
      $pdf = PDF::loadView('kasa.printpembayaran', compact('saldo_id','saldo_toko','no','setting','cash','musawamah'));
      $pdf->setPaper(array(0,0,700,600), "potrait");      
         return $pdf->stream();
   }

}




   



