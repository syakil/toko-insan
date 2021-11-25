<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Produk;
use App\ProdukSelisih;
use App\ProdukDetail;
use App\ProdukSo;
use DB;
use App\TabelTransaksi;
use Auth;
use Ramsey\Uuid\Uuid;
use App\Branch;
use App\ParamTgl;

class ApprovalKpController extends Controller
{
    public function index(){
        $unit = Branch::groupBy('kode_gudang')
                        ->get();

        
        return view('approve_kp/index',compact('unit'));
    }


    public function listData($unit){

        $produk_so = ProdukSo:: select('produk_so.*','produk.nama_produk','produk.stok')
                                ->leftJoin('produk','produk.kode_produk','produk_so.kode_produk')
                                ->where('produk_so.unit',$unit)
                                ->where('produk.unit',$unit)
                                ->where('status',1)
                                ->get();
        
        $no = 0;
        $data = array();
        foreach ($produk_so as $list) {
            $no++;
            $row = array();
            $row[] = $no;
            $row[] = $list->kode_produk;
            $row[] = $list->nama_produk;
            $row[] = $list->qty;
            $row[] = $list->stok_system;
            $data[] = $row;
        }
        
        $output = array("data" => $data);
        return response()->json($output);
    }



   
    public function store(Request $request){
        
        $unit = $request->unit;

        try {
            
            DB::beginTransaction();
            
            $data = ProdukSo::where('unit',$unit)->where('status',1)->get();

            foreach ($data as $value) {
                                
                $kode = Uuid::uuid4()->getHex();
                $kode_unik = substr($kode,25);
                $kode_transaksi = "SO/-".$unit.$kode_unik;
                $tanggal = date('Y-m-d');
                
                $data_produk_detail = ProdukDetail::where('kode_produk',$value->kode_produk)->where('unit',$unit)->orderBy('tanggal_masuk','DESC')->first();
                
                $master_produk = Produk::where('kode_produk',$value->kode_produk)->where('unit',$unit)->first();

                if ($value->qty > $master_produk->stok) {
                    
                    $selisih = $value->qty - $value->stok_system;
                    
                    if ($data_produk_detail) {
                        
                        $harga_jual = $data_produk_detail->harga_jual_umum * $selisih;
                        $harga_beli = $data_produk_detail->harga_beli * $selisih;
                        $margin = abs($harga_jual - $harga_beli);
                        
                        $master_produk->stok += $selisih;
                        $master_produk->update();    
                        
                        $produk_selisih = new ProdukSelisih;
                        $produk_selisih->kode_produk = $data_produk_detail->kode_produk;
                        $produk_selisih->jumlah = $selisih;
                        $produk_selisih->harga_beli = $data_produk_detail->harga_beli;
                        $produk_selisih->harga_jual = $data_produk_detail->harga_jual_umum;
                        $produk_selisih->unit = $unit;
                        $produk_selisih->status = 1;
                        $produk_selisih->ket = 'lebih';
                        $produk_selisih->tanggal_so = $tanggal;
                        $produk_selisih->save();

                        $produk_detail_baru = new ProdukDetail;
                        $produk_detail_baru->kode_produk = $value->kode_produk;
                        $produk_detail_baru->nama_produk = $master_produk->nama_produk;
                        $produk_detail_baru->stok_detail = $selisih;
                        $produk_detail_baru->harga_beli = $data_produk_detail->harga_beli;
                        $produk_detail_baru->harga_jual_umum = $data_produk_detail->harga_jual_umum;
                        $produk_detail_baru->harga_jual_insan = $data_produk_detail->harga_jual_umum;
                        $produk_detail_baru->tanggal_masuk = $tanggal;
                        $produk_detail_baru->unit = $unit;
                        $produk_detail_baru->status = null;
                        $produk_detail_baru->expired_date = '2021-01-01';
                        $produk_detail_baru->no_faktur = null;
                        $produk_detail_baru->save();

                        // Persediaan Musawamah/Barang Dagang
                        $jurnal = new TabelTransaksi;
                        $jurnal->unit =  $unit; 
                        $jurnal->kode_transaksi = $kode_transaksi;
                        $jurnal->kode_rekening = 1482000;
                        $jurnal->tanggal_transaksi  = $tanggal;
                        $jurnal->jenis_transaksi  = 'Jurnal System';
                        $jurnal->keterangan_transaksi = 'Stok Opname '. $value->kode_produk;
                        $jurnal->debet = $harga_beli;
                        $jurnal->kredit = 0;
                        $jurnal->tanggal_posting = '';
                        $jurnal->keterangan_posting = '0';
                        $jurnal->id_admin = Auth::user()->id; 
                        $jurnal->save();
                        
                        // Selisih lebih barang
                        $jurnal = new TabelTransaksi;
                        $jurnal->unit =  $unit; 
                        $jurnal->kode_transaksi = $kode_transaksi;
                        $jurnal->kode_rekening = 2474000;
                        $jurnal->tanggal_transaksi  = $tanggal;
                        $jurnal->jenis_transaksi  = 'Jurnal System';
                        $jurnal->keterangan_transaksi = 'Stok Opname '. $value->kode_produk;
                        $jurnal->debet = 0;
                        $jurnal->kredit = $harga_beli;
                        $jurnal->tanggal_posting = '';
                        $jurnal->keterangan_posting = '0';
                        $jurnal->id_admin = Auth::user()->id; 
                        $jurnal->save();
                        

                    }else {
                        
                        $harga_jual = $master_produk->harga_jual * $selisih;
                        $harga_beli = $master_produk->harga_beli * $selisih;
                        $margin = abs($harga_jual - $harga_beli);
                        
                        $master_produk->stok += $selisih;
                        $master_produk->update();    

                        $produk_detail_baru = new ProdukDetail;
                        $produk_detail_baru->kode_produk = $value->kode_produk;
                        $produk_detail_baru->nama_produk = $master_produk->nama_produk;
                        $produk_detail_baru->stok_detail = $selisih;
                        $produk_detail_baru->harga_beli = $master_produk->harga_beli;
                        $produk_detail_baru->harga_jual_umum = $master_produk->harga_jual;
                        $produk_detail_baru->harga_jual_insan = $master_produk->harga_jual;
                        $produk_detail_baru->tanggal_masuk = $tanggal;
                        $produk_detail_baru->unit = $unit;
                        $produk_detail_baru->status = null;
                        $produk_detail_baru->expired_date = '2021-01-01';
                        $produk_detail_baru->no_faktur = null;
                        $produk_detail_baru->save();

                        $produk_selisih = new ProdukSelisih;
                        $produk_selisih->kode_produk = $master_produk->kode_produk;
                        $produk_selisih->jumlah = $selisih;
                        $produk_selisih->harga_beli = $master_produk->harga_beli;
                        $produk_selisih->harga_jual = $master_produk->harga_jual;
                        $produk_selisih->unit = $unit;
                        $produk_selisih->status = 1;
                        $produk_selisih->ket = 'lebih';
                        $produk_selisih->tanggal_so = $tanggal;
                        $produk_selisih->save();
                        
                        // Persediaan Musawamah/Barang Dagang
                        $jurnal = new TabelTransaksi;
                        $jurnal->unit =  $unit; 
                        $jurnal->kode_transaksi = $kode_transaksi;
                        $jurnal->kode_rekening = 1482000;
                        $jurnal->tanggal_transaksi  = $tanggal;
                        $jurnal->jenis_transaksi  = 'Jurnal System';
                        $jurnal->keterangan_transaksi = 'Stok Opname '. $value->kode_produk;
                        $jurnal->debet = $harga_beli;
                        $jurnal->kredit = 0;
                        $jurnal->tanggal_posting = '';
                        $jurnal->keterangan_posting = '0';
                        $jurnal->id_admin = Auth::user()->id; 
                        $jurnal->save();
                        
                        // Selisih lebih barang
                        $jurnal = new TabelTransaksi;
                        $jurnal->unit =  $unit; 
                        $jurnal->kode_transaksi = $kode_transaksi;
                        $jurnal->kode_rekening = 2474000;
                        $jurnal->tanggal_transaksi  = $tanggal;
                        $jurnal->jenis_transaksi  = 'Jurnal System';
                        $jurnal->keterangan_transaksi = 'Stok Opname '. $value->kode_produk;
                        $jurnal->debet = 0;
                        $jurnal->kredit = $harga_beli;
                        $jurnal->tanggal_posting = '';
                        $jurnal->keterangan_posting = '0';
                        $jurnal->id_admin = Auth::user()->id; 
                        $jurnal->save();
                        
                    }

                }else {
                    
                    $selisih = $value->stok_system - $value->qty;
               
                    $master_produk->stok -= $selisih;
                    $master_produk->update();    
               
                    produk:
                    $produk_detail = ProdukDetail::where('kode_produk',$value->kode_produk)
                        ->where('unit',$unit)
                        ->where('stok_detail','>','0')
                        ->orderBy('tanggal_masuk','ASC')
                        ->first();
                    
                    // buat variable stok toko dari column stok_detail dari table produk_detail
                    $stok_toko = $produk_detail->stok_detail;
                    
                    // jika qty penjualan == jumlah stok yang tersedia ditoko
                    if ($selisih == $stok_toko) {
                        
                        
                        $harga_jual = $produk_detail->harga_jual_umum * $produk_detail->stok_detail;
                        $harga_beli = $produk_detail->harga_beli * $produk_detail->stok_detail;
                        $margin = abs($harga_jual - $harga_beli);
                        
                        
                        $produk_selisih = new ProdukSelisih;
                        $produk_selisih->kode_produk = $produk_detail->kode_produk;
                        $produk_selisih->jumlah = $produk_detail->stok_detail;
                        $produk_selisih->harga_beli = $produk_detail->harga_beli;
                        $produk_selisih->harga_jual = $produk_detail->harga_jual_umum;
                        $produk_selisih->unit = $unit;
                        $produk_selisih->status = 1;
                        $produk_selisih->ket = 'kurang';
                        $produk_selisih->tanggal_so = $tanggal;
                        $produk_selisih->save();

                        $jurnal = new TabelTransaksi;
                        $jurnal->unit =  $unit; 
                        $jurnal->kode_transaksi = $kode_transaksi;
                        $jurnal->kode_rekening = 1969000;
                        $jurnal->tanggal_transaksi  = $tanggal;
                        $jurnal->jenis_transaksi  = 'Jurnal System';
                        $jurnal->keterangan_transaksi = 'Stok Opname ' . $value->kode_produk;
                        $jurnal->debet = $harga_beli;
                        $jurnal->kredit = 0;
                        $jurnal->tanggal_posting = '';
                        $jurnal->keterangan_posting = '0';
                        $jurnal->id_admin = Auth::user()->id; 
                        $jurnal->save();


                        $jurnal = new TabelTransaksi;
                        $jurnal->unit =  $unit; 
                        $jurnal->kode_transaksi = $kode_transaksi;
                        $jurnal->kode_rekening = 1482000;
                        $jurnal->tanggal_transaksi  = $tanggal;
                        $jurnal->jenis_transaksi  = 'Jurnal System';
                        $jurnal->keterangan_transaksi = 'Stok Opname ' . $value->kode_produk;
                        $jurnal->debet =0;
                        $jurnal->kredit = $harga_beli;
                        $jurnal->tanggal_posting = '';
                        $jurnal->keterangan_posting = '0';
                        $jurnal->id_admin = Auth::user()->id; 
                        $jurnal->save();
                        
                        $produk_detail->update(['stok_detail'=>0]);
                        
                    // jika selisih qty stok_opname dengan jumlah stok yang tersedia
                    }else {
                        
                        // mengurangi qty stok_opname dengan stok toko berdasarkan stok_detail(table produk_detail)
                        $stok = $selisih - $stok_toko;

                        // jika hasilnya lebih dari nol atau tidak minus, stok_detail tsb tidak memenuhi qty stok_opname dan harus ambil lagi record pada produk detail~
                        // ~ yang stok nya lebih dari nol

                        if ($stok >= 0) {
                                
                            $harga_jual = $produk_detail->harga_jual_umum * $produk_detail->stok_detail;
                            $harga_beli = $produk_detail->harga_beli * $produk_detail->stok_detail;
                            $margin = abs($harga_jual - $harga_beli);
                        
                            $produk_selisih = new ProdukSelisih;
                            $produk_selisih->kode_produk = $produk_detail->kode_produk;
                            $produk_selisih->jumlah = $produk_detail->stok_detail;
                            $produk_selisih->harga_beli = $produk_detail->harga_beli;
                            $produk_selisih->harga_jual = $produk_detail->harga_jual_umum;
                            $produk_selisih->unit = $unit;
                            $produk_selisih->status = 1;
                            $produk_selisih->ket = 'kurang';
                            $produk_selisih->tanggal_so = $tanggal;
                            $produk_selisih->save();

                            $jurnal = new TabelTransaksi;
                            $jurnal->unit =  $unit; 
                            $jurnal->kode_transaksi = $kode_transaksi;
                            $jurnal->kode_rekening = 1969000;
                            $jurnal->tanggal_transaksi  = $tanggal;
                            $jurnal->jenis_transaksi  = 'Jurnal System';
                            $jurnal->keterangan_transaksi = 'Stok Opname ' . $value->kode_produk;
                            $jurnal->debet = $harga_beli;
                            $jurnal->kredit = 0;
                            $jurnal->tanggal_posting = '';
                            $jurnal->keterangan_posting = '0';
                            $jurnal->id_admin = Auth::user()->id; 
                            $jurnal->save();
    
                            $jurnal = new TabelTransaksi;
                            $jurnal->unit =  $unit; 
                            $jurnal->kode_transaksi = $kode_transaksi;
                            $jurnal->kode_rekening = 1482000;
                            $jurnal->tanggal_transaksi  = $tanggal;
                            $jurnal->jenis_transaksi  = 'Jurnal System';
                            $jurnal->keterangan_transaksi = 'Stok Opname ' . $value->kode_produk;
                            $jurnal->debet =0;
                            $jurnal->kredit = $harga_beli;
                            $jurnal->tanggal_posting = '';
                            $jurnal->keterangan_posting = '0';
                            $jurnal->id_admin = Auth::user()->id; 
                            $jurnal->save();
                        
                            $produk_detail->update(['stok_detail'=>0]);
                        
                            // sisa qty stok_opname yang dikurangi stok toko yang harganya paling rendah
                            $selisih = $stok;

                            // mengulangi looping untuk mencari harga yang paling rendah
                            goto produk;
                        
                        // jika pengurangan qty stok_opname dengan stok toko hasilnya kurang dari 0 atau minus
                        }else if($stok < 0){
                  
                            $produk_detail->update(['stok_detail'=>abs($stok)]);
                                
                            $harga_jual = $produk_detail->harga_jual_umum * $selisih;
                            $harga_beli = $produk_detail->harga_beli * $selisih;
                            $margin = abs($harga_jual - $harga_beli);

                            
                            $produk_selisih = new ProdukSelisih;
                            $produk_selisih->kode_produk = $produk_detail->kode_produk;
                            $produk_selisih->jumlah = $selisih;
                            $produk_selisih->harga_beli = $produk_detail->harga_beli;
                            $produk_selisih->harga_jual = $produk_detail->harga_jual_umum;
                            $produk_selisih->unit = $unit;
                            $produk_selisih->status = 1;
                            $produk_selisih->ket = 'kurang';
                            $produk_selisih->tanggal_so = $tanggal;
                            $produk_selisih->save();

                            $jurnal = new TabelTransaksi;
                            $jurnal->unit =  $unit; 
                            $jurnal->kode_transaksi = $kode_transaksi;
                            $jurnal->kode_rekening = 1969000;
                            $jurnal->tanggal_transaksi  = $tanggal;
                            $jurnal->jenis_transaksi  = 'Jurnal System';
                            $jurnal->keterangan_transaksi = 'Stok Opname ' . $value->kode_produk;
                            $jurnal->debet = $harga_beli;
                            $jurnal->kredit = 0;
                            $jurnal->tanggal_posting = '';
                            $jurnal->keterangan_posting = '0';
                            $jurnal->id_admin = Auth::user()->id; 
                            $jurnal->save();

                            $jurnal = new TabelTransaksi;
                            $jurnal->unit =  $unit; 
                            $jurnal->kode_transaksi = $kode_transaksi;
                            $jurnal->kode_rekening = 1482000;
                            $jurnal->tanggal_transaksi  = $tanggal;
                            $jurnal->jenis_transaksi  = 'Jurnal System';
                            $jurnal->keterangan_transaksi = 'Stok Opname ' . $value->kode_produk;
                            $jurnal->debet =0;
                            $jurnal->kredit = $harga_beli;
                            $jurnal->tanggal_posting = '';
                            $jurnal->keterangan_posting = '0';
                            $jurnal->id_admin = Auth::user()->id; 
                            $jurnal->save();
                  
                        }
                    }

                }

                $value->status = 2;
                $value->update();

            }

            DB::commit();

        }catch(\Exception $e){
           
            DB::rollback();
            
            return back()->with(['error' => $e->getmessage()]);
    
        }

        return back()->with(['success' => 'Stok Opname Berahasil']);
    
    }


}

