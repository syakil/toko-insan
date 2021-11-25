<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Redirect;
use DB;
use Auth;
use Ramsey\Uuid\Uuid;
use App\TabelTransaksi;
use App\Branch;
use App\Produk;
use App\ProdukDetail;
use App\KartuStok;
use App\StokOpnameParsial;
use App\ProdukSelisih;



class ApproveStokOpnameParsialGudangController extends Controller
{
    public function index(){

        return view('approve_stok_opname_parsial_gudang.index');

    }


    public function listData(){

        $data_unit = Branch::groupBy('kode_gudang')->get();
        $kode_branch = array();

        foreach ($data_unit as $value) {
            $kode_branch[] = $value->kode_gudang;

        }   

        $data_so = StokOpnameParsial::select('branch.nama_toko','produk.nama_produk','stok_opname_parsial.*')
        ->leftJoin('branch','branch.kode_toko','stok_opname_parsial.unit')
        ->leftJoin('produk','produk.kode_produk','stok_opname_parsial.kode_produk')
        ->whereIn('stok_opname_parsial.unit',$kode_branch)
        ->where('produk.unit',3000)
        ->where('stok_opname_parsial.status',2)
        ->get();

        $data = array();
        $no = 1;

        foreach($data_so as $list){

            $stok_toko = Produk::where('kode_produk',$list->kode_produk)->where('unit',$list->unit)->first();

            $row = array();
            $row[] = $no++;
            $row[] = tanggal_indonesia($list->tanggal_so);
            $row[] = $list->nama_toko;
            $row[] = $list->kode_produk;
            $row[] = $list->nama_produk;
            $row[] = $stok_toko->stok;
            $row[] = $list->qty;

            if ($list->qty > $stok_toko->stok) {
                $row[] = '<span class="label label-warning">Fisik Lebih</span>';
            }elseif ($list->qty < $stok_toko->stok) {
                $row[] = '<span class="label label-danger">Fisik Kurang</span>';
            }else{
                $row[] = '<span class="label label-success">Sesuai</span>';
            }

            $data[] = $row;

        }

        $output = array("data" =>$data);
        return response()->json($output);

    }


    public function store(Request $request){

        try {
            
            DB::beginTransaction();
            
            $data_unit = Branch::groupBy('kode_gudang')->get();
            $kode_branch = array();
    
            foreach ($data_unit as $value) {
                
                $kode_branch[] = $value->kode_gudang;
                
            }   
    

            $data = StokOpnameParsial::whereIn('unit',$kode_branch)->where('status',2)->get();

            foreach ($data as $value) {
                                
                $unit = $value->unit;
                $kode = Uuid::uuid4()->getHex();
                $kode_unik = substr($kode,25);
                $kode_transaksi = "SO/-".$unit.$kode_unik;
                $tanggal = date('Y-m-d');
                
                $data_produk_detail = ProdukDetail::where('kode_produk',$value->kode_produk)->where('unit',$unit)->orderBy('tanggal_masuk','DESC')->first();
                
                $master_produk = Produk::where('kode_produk',$value->kode_produk)->where('unit',$unit)->first();

                $value->status = 3;
                $value->stok_system = $master_produk->stok;
                $value->update();
                
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
                        $jurnal->debet = $harga_jual;
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
                        
                        // PMYD-PYD Musawamah
                        $jurnal = new TabelTransaksi;
                        $jurnal->unit =  $unit; 
                        $jurnal->kode_transaksi = $kode_transaksi;
                        $jurnal->kode_rekening = 1483000;
                        $jurnal->tanggal_transaksi  = $tanggal;
                        $jurnal->jenis_transaksi  = 'Jurnal System';
                        $jurnal->keterangan_transaksi = 'Stok Opname '. $value->kode_produk;
                        $jurnal->debet = 0;
                        $jurnal->kredit = $margin;
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

                    $kartu_stok = new KartuStok;
                    $kartu_stok->buss_date = $tanggal;
                    $kartu_stok->kode_produk = $master_produk->kode_produk;
                    $kartu_stok->masuk = $selisih;
                    $kartu_stok->keluar = 0;
                    $kartu_stok->status = 'so_lebih';
                    $kartu_stok->kode_transaksi = $kode_transaksi;
                    $kartu_stok->unit = $value->unit;
                    $kartu_stok->save();

                }elseif($value->qty < $master_produk->stok) {
                    
                    $selisih = $value->stok_system - $value->qty;

                    $kartu_stok = new KartuStok;
                    $kartu_stok->buss_date = $tanggal;
                    $kartu_stok->kode_produk = $master_produk->kode_produk;
                    $kartu_stok->masuk = 0;
                    $kartu_stok->keluar = $selisih;
                    $kartu_stok->status = 'so_kurang';
                    $kartu_stok->kode_transaksi = $kode_transaksi;
                    $kartu_stok->unit = $value->unit;
                    $kartu_stok->save();
                    
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


            }

            DB::commit();

        }catch(\Exception $e){
           
            DB::rollback();
            
            return back()->with(['error' => $e->getmessage()]);
    
        }

        return back()->with(['success' => 'Stok Opname Berahasil']);
    
    }
}
