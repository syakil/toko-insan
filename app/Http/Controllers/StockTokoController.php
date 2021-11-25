<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;
use Ramsey\Uuid\Uuid;
use App\ProdukDetail;
use App\KartuStok;
use App\Produk;
use DB;
use App\StokOpnameParsial;
use App\ProdukSelisih;
use App\TabelTransaksi;

class StockTokoController extends Controller
{
    public function index(){
        return view('toko/stock');
    }


    public function listData(){
        $produk = Produk::where('unit', '=',  Auth::user()->unit)
                ->get();
        $no = 0;
        $data = array();
        
        foreach($produk as $list){
            $no ++;
            $row = array();
            $row[] = $no;
            $row[] = $list->kode_produk;
            $row[] = $list->nama_produk;       
            $row[] = $list->stok;
            $row[] = '<div class="btn-group">
            <a onclick="editForm('.$list->id_produk.')" class="btn btn-primary btn-sm"><i class="fa fa-pencil"></i></a></div>';
            $data[] = $row;
        }
        
        $output = array("data" => $data);
        return response()->json($output);
    }

    public function detail($id){

        $produk = ProdukDetail::where('kode_produk',$id)
                        ->where('unit', '=',  Auth::user()->unit)
                        ->get();
                        
        $nama = Produk::where('kode_produk',$id)->first();
        return view('toko/detail_stock',['produk'=>$produk,'nama'=>$nama]);
    }


    public function delete($id){
        
        // dd($id);
        $detail = ProdukDetail::where('id_produk_detail',$id)->first();
        $produk = Produk::where('kode_produk',$detail->kode_produk)
        ->where('unit',Auth::user()->unit)
        ->first();
        // dd($produk);
        $produk->stok = $produk->stok - $detail->stok_detail;
        $produk->update(); 
        
        
        $detail->delete();

        return back();

    }



    public function store(Request $request){
        $unit = Auth::user()->unit;
        // dd($unit);
        $produk_detail = new ProdukDetail;
        $produk_detail->kode_produk = $request->barcode;
        $produk_detail->nama_produk = $request->nama;
        $produk_detail->unit = Auth::user()->unit;
        $produk_detail->stok_detail = $request->stok;
        $produk_detail->expired_date = $request->tanggal;
        $produk_detail->save();

        $stok = ProdukDetail::where('kode_produk',$request->barcode)
                        ->where('unit',Auth::user()->unit)
                        ->sum('stok_detail');

        $update_stok = Produk::where('kode_produk',$request->barcode)
                            ->where('unit',Auth::user()->unit)
                            ->first();
        $update_stok->stok= $stok;
        $update_stok->update();
    
        return back();
    }

    public function edit($id){


        $produk = Produk::find($id);
        echo json_encode($produk);
      
    }

    public function tambah(Request $request){

        $id = $request->id;
        $jumlah = $request->jumlah;
        $unit = Auth::user()->unit;
        $tanggal = date('Y-m-d');
        
        $kode = Uuid::uuid4()->getHex();
        $kode_unik = substr($kode,25);
        $kode_transaksi = "SO/-".$unit.$kode_unik;

        $produk = Produk::find($id);

        $stok_sekarang = $produk->stok;

        if ($jumlah < $stok_sekarang) {

            return back()->with(['error' => 'Qty tidak boleh kurang dari stok sekarang!']);
        
        }else {
            
            try{

                DB::beginTransaction();

                $data_produk_detail = ProdukDetail::where('kode_produk',$produk->kode_produk)->where('unit',Auth::user()->unit)->first();

                $selisih = $jumlah - $stok_sekarang;
                
                $kartu_stok = new KartuStok;
                $kartu_stok->buss_date = date('Y-m-d');
                $kartu_stok->kode_produk = $produk->kode_produk;
                $kartu_stok->masuk = $selisih;
                $kartu_stok->keluar = 0;
                $kartu_stok->status = 'stok_tambah';
                $kartu_stok->kode_transaksi = $kode_transaksi;
                $kartu_stok->unit = Auth::user()->unit;
                $kartu_stok->save();
                
                $stok_opname_parsial = new StokOpnameParsial;
                $stok_opname_parsial->stok_system = $produk->stok;
                $stok_opname_parsial->kode_produk = $produk->kode_produk;
                $stok_opname_parsial->unit = Auth::user()->unit;
                $stok_opname_parsial->status = 1;
                $stok_opname_parsial->save();
    
                    if ($data_produk_detail) {
                        
                        $harga_jual = $data_produk_detail->harga_jual_umum * $selisih;
                        $harga_beli = $data_produk_detail->harga_beli * $selisih;
                        $margin = abs($harga_jual - $harga_beli);
                        
                        $produk->stok += $selisih;
                        $produk->update();    
                        
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
                        $produk_detail_baru->kode_produk = $produk->kode_produk;
                        $produk_detail_baru->nama_produk = $produk->nama_produk;
                        $produk_detail_baru->stok_detail = $selisih;
                        $produk_detail_baru->harga_beli = $data_produk_detail->harga_beli;
                        $produk_detail_baru->harga_jual_umum = $data_produk_detail->harga_jual_umum;
                        $produk_detail_baru->harga_jual_insan = $data_produk_detail->harga_jual_umum;
                        $produk_detail_baru->tanggal_masuk = $tanggal;
                        $produk_detail_baru->unit = $unit;
                        $produk_detail_baru->status = null;
                        $produk_detail_baru->expired_date = null;
                        $produk_detail_baru->no_faktur = null;
                        $produk_detail_baru->save();

                        // Persediaan Musawamah/Barang Dagang
                        $jurnal = new TabelTransaksi;
                        $jurnal->unit =  $unit; 
                        $jurnal->kode_transaksi = $kode_transaksi;
                        $jurnal->kode_rekening = 1482000;
                        $jurnal->tanggal_transaksi  = $tanggal;
                        $jurnal->jenis_transaksi  = 'Jurnal System';
                        $jurnal->keterangan_transaksi = 'Stok Opname '. $produk->kode_produk;
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
                        $jurnal->keterangan_transaksi = 'Stok Opname '. $produk->kode_produk;
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
                        $jurnal->keterangan_transaksi = 'Stok Opname '. $produk->kode_produk;
                        $jurnal->debet = 0;
                        $jurnal->kredit = $margin;
                        $jurnal->tanggal_posting = '';
                        $jurnal->keterangan_posting = '0';
                        $jurnal->id_admin = Auth::user()->id; 
                        $jurnal->save();
                        

                    }else {
                        
                        $harga_jual = $produk->harga_jual * $selisih;
                        $harga_beli = $produk->harga_beli * $selisih;
                        $margin = abs($harga_jual - $harga_beli);
                        
                        $produk->stok += $selisih;
                        $produk->update();    

                        $produk_detail_baru = new ProdukDetail;
                        $produk_detail_baru->kode_produk = $produk->kode_produk;
                        $produk_detail_baru->nama_produk = $produk->nama_produk;
                        $produk_detail_baru->stok_detail = $selisih;
                        $produk_detail_baru->harga_beli = $produk->harga_beli;
                        $produk_detail_baru->harga_jual_umum = $produk->harga_jual;
                        $produk_detail_baru->harga_jual_insan = $produk->harga_jual;
                        $produk_detail_baru->tanggal_masuk = $tanggal;
                        $produk_detail_baru->unit = $unit;
                        $produk_detail_baru->status = null;
                        $produk_detail_baru->expired_date = '2021-01-01';
                        $produk_detail_baru->no_faktur = null;
                        $produk_detail_baru->save();

                        $produk_selisih = new ProdukSelisih;
                        $produk_selisih->kode_produk = $produk->kode_produk;
                        $produk_selisih->jumlah = $selisih;
                        $produk_selisih->harga_beli = $produk->harga_beli;
                        $produk_selisih->harga_jual = $produk->harga_jual;
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
                        $jurnal->keterangan_transaksi = 'Stok Opname '. $produk->kode_produk;
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
                        $jurnal->keterangan_transaksi = 'Stok Opname '. $produk->kode_produk;
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
                        $jurnal->keterangan_transaksi = 'Stok Opname '. $produk->kode_produk;
                        $jurnal->debet = 0;
                        $jurnal->kredit = $margin;
                        $jurnal->tanggal_posting = '';
                        $jurnal->keterangan_posting = '0';
                        $jurnal->id_admin = Auth::user()->id; 
                        $jurnal->save();
                        
                    }



                DB::commit();
                return back()->with(['success' => 'Stok Berhasil di Tambah !']);

            }catch(\Exception $e){
                
                DB::rollback();
                return back()->with(['error' => $e->getmessage()]);

            }
        }

    }
    

}


