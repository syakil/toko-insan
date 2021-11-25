<?php

namespace App\Http\Controllers;

use Route;
use Illuminate\Http\Request;
use App\Produk;
use App\ProdukDetail;
use App\ParamProduk;
use App\Branch;
use App\ParamStatus;
use App\TabelTransaksi;
use Auth;
use DB;

class PricingController extends Controller
{
    public function index(){
        $produk = Produk::leftJoin('param_status','param_status.id_status','=','produk.param_status')
                        ->where('produk.unit',Auth::user()->unit)
                        ->get();
        $fast = ParamStatus::where('keterangan','Fast Moving')->first();
        $medium = ParamStatus::where('keterangan','pesanan')->first();
        $slow = ParamStatus::where('keterangan','Slow Moving')->first();
        $no = 1;
        // dd($produk);
        return view('pricing.index',['produk'=>$produk,'no'=>$no,'fast'=>$fast,'medium'=>$medium,'slow'=>$slow]);
    }


    public function listData(){

        $produk = Produk::leftJoin('param_status','param_status.id_status','=','produk.param_status')
                        ->where('produk.unit',auth::user()->unit)
                        ->select('produk.kode_produk','produk.nama_produk','produk.unit','produk.harga_beli','produk.harga_jual','produk.harga_jual_insan','param_status.*')                        
                        ->get();


        $no = 0;
        $data = array();
        foreach($produk as $list){
            $no ++;
            $row = array();
            $row[] = $no;
            $row[] = $list->kode_produk;
            $row[] = $list->nama_produk;
            $row[] = $list->harga_beli;
            $row[] = round($list->harga_beli + ($list->harga_beli*$list->margin/100));
            $row[] = $list->harga_jual;
            $row[] = $list->harga_jual_insan;
            $row[] = $list->keterangan;
                        $row[] = '
            <a href="'. route('pricing.edit', $list->kode_produk).'" class="btn btn-sm btn-warning"><i class="fa fa-pencil"></i></a>
            <a href="'. route('pricing.promo', $list->kode_produk).'" class="btn btn-sm btn-success"><i class="fa fa-plus"></i></a>';;
            $data[] = $row;
        }
    
        $output = array("data" => $data);
        return response()->json($output);

    }

    public function edit($id)
    {
        $produk = Produk::leftJoin('param_status','param_status.id_status','=','produk.param_status')->where('kode_produk',$id)->where('unit',auth::user()->unit)->first();
        $param = ParamStatus::all();
        $no=1;
        return view('pricing.edit',['param'=>$param,'produk'=>$produk,'no'=>$no]);
    }


    public function tambah(){
        $param = ParamStatus::all();
        return view ('pricing.tambah',['param'=>$param]);
    }


    public function add(Request $request){
        
        
        $kode_gudang = Branch::where('kode_gudang',Auth::user()->unit)->get();

        foreach ($kode_gudang as $unit ) {
            
            $produk = new Produk;
            $produk->kode_produk = $request->kode_produk;
            $produk->nama_produk = $request->nama_produk;
            $produk->nama_struk = $request->nama_struk;
            $produk->merk = '';
            $produk->id_kategori = 0;
            $produk->diskon = 0;
            $produk->harga_beli = $request->harga_beli;
            $produk->harga_jual = $request->harga_jual;
            $produk->stok = 0;
            $produk->isi_satuan = 0;

            if ($request->harga_beli > $request->harga_jual) {
                $produk->promo = 1;
            }else {
                $produk->promo = 0;
            }
            
            $produk->satuan = '';
            $produk->param_status= 0;
            $produk->stok_mak = 0; 
            $produk->stok_min = 0;
            $produk->unit = $unit->kode_toko;
            $produk->harga_jual_member_insan = $request->harga_jual_insan;
            $produk->harga_jual_insan = $request->harga_jual_insan;
            $produk->harga_jual_pabrik = $request->harga_jual;
            $produk->save();
            
        }     

        return redirect('pricing/index');

    }


    public function update(Request $request){

        try {

            DB::beginTransaction();
            $produk = ProdukDetail::where('kode_produk',$request['id'])->where('unit',Auth::user()->unit)->where('stok_detail','>',0)->first();
            if($produk){
                $produk = ProdukDetail::where('kode_produk',$request['id'])->where('unit',Auth::user()->unit)->where('stok_detail','>',0)->first(); 
                $produk->harga_jual_umum = $request['harga_jual']; 
                $produk->harga_jual_insan = $request['harga_jual']; 
                $produk->status = 2;
                $produk->update();
            }else{
                return redirect('pricing/index')->with(['error' => 'Stok Di Gudang kosong']);
            }
                
            DB::commit();
        }catch(\Exception $e){
            DB::rollback();
            return redirect('pricing/index')->with(['error' => $e->getmessage()]);
        
        }


          return redirect('pricing/index')->with(['success' => 'Perubahan Harga Berhasil']);;
    
    }

    public function show(Request $request){
        
        $param = ParamStatus::where('keterangan','Fast Moving')->first();
        $param->margin = $request->fast;
        $param->update();
        
        $param = ParamStatus::where('keterangan','Medium Moving')->first();
        $param->margin = $request->medium;
        $param->update();

        $param = ParamStatus::where('keterangan','Slow Moving')->first();
        $param->margin = $request->slow;
        $param->update();

        return redirect('pricing/index');
    
    }
    
    public function tambah_promo($id){

        $produk = Produk::where('kode_produk',$id)->where('unit',Auth::user()->unit)->first();

        return view('pricing/tambah_promo',compact('produk'));
    }

    public function update_promo(Request $request){

        $kode_produk = $request['kode_produk'];
        $kode_promo = $request['kode_promo'];
        $nama_produk = $request['nama_produk'];
        $struk = $request['nama_struk'];
        $harga_beli = $request->harga_beli;
        $harga_jual = $request->harga_jual_insan;
        $harga_jual_insan = $request->harga_jual_insan;
        $qty_promo = $request->stok;
        $qty = $request->stok;
        $units = Branch::get();
        
        $stok_lama = Produk::where('kode_produk',$kode_produk)->where('unit',Auth::user()->unit)->first();   
        $stok_detail = ProdukDetail::where('kode_produk',$kode_produk)->where('unit',Auth::user()->unit)->sum('stok_detail');

        if ($stok_detail < $qty) {

            return back()->with(['error' => 'Stock '. $stok_lama->kode_produk . ' Kurang']);

        }


        produk:
        $produk_detail_lama = ProdukDetail::where('kode_produk',$kode_produk)
        ->where('unit',Auth::user()->unit)
        ->where('stok_detail','>','0')
        ->orderBy('tanggal_masuk','ASC')
        ->first();
      
        // buat variable stok toko dari column stok_detail dari table produk_detail
        $stok_detail_lama = $produk_detail_lama->stok_detail;
      
        // jika qty promo == jumlah stok yang tersedia ditoko
        if ($qty_promo == $stok_detail_lama) {
            
            $produk_detail_lama->update(['stok_detail'=>0]);

            $produk_detail = new ProdukDetail;
            $produk_detail->nama_produk = $nama_produk;
            $produk_detail->kode_produk = $kode_promo;
            $produk_detail->stok_detail = $stok_detail_lama;
            $produk_detail->harga_beli = $harga_beli;
            $produk_detail->harga_jual_umum = $harga_jual;
            $produk_detail->harga_jual_insan = $harga_jual;
            $produk_detail->expired_date = $produk_detail_lama->expired_date;
            $produk_detail->tanggal_masuk = date('Y-m-d');
            $produk_detail->unit = Auth::user()->unit;
            $produk_detail->status = null;
            $produk_detail->no_faktur = $produk_detail_lama->no_faktur;
            $produk_detail->save();
            
        // jika selisih qty penjualan dengan jumlah stok yang tersedia
        }else {
            
            // mengurangi qty promo dengan stok toko berdasarkan stok_detail(table produk_detail)
            $stok_sisa = $qty_promo - $stok_detail_lama;

            // jika hasilnya lebih dari nol atau tidak minus, stok_detail tsb tidak memenuhi qty penjualan dan harus ambil lagi record pada produk detail~
            // ~ yang stok nya lebih dari nol

            if ($stok_sisa >= 0) {

                // update produk_detail->stok_detail menjadi nol berdasarkan $produk_detail 
                $produk_detail_lama->update(['stok_detail'=>0]);

                $produk_detail = new ProdukDetail;
                $produk_detail->nama_produk = $nama_produk;
                $produk_detail->kode_produk = $kode_promo;
                $produk_detail->stok_detail = $stok_detail_lama;
                $produk_detail->harga_beli = $harga_beli;
                $produk_detail->harga_jual_umum = $harga_jual;
                $produk_detail->harga_jual_insan = $harga_jual;
                $produk_detail->expired_date = $produk_detail_lama->expired_date;
                $produk_detail->tanggal_masuk = date('Y-m-d');
                $produk_detail->unit = Auth::user()->unit;
                $produk_detail->status = null;
                $produk_detail->no_faktur = $produk_detail_lama->no_faktur;
                $produk_detail->save();

                $qty_promo = $stok_sisa;
                // mengulangi looping untuk mencari harga yang paling rendah
                goto produk;
                    
            // jika pengurangan qty promo dengan stok toko hasilnya kurang dari 0 atau minus
            }else if($stok_sisa < 0){

                // update stok_detail berdasar sisa pengurangan qty promo dengan stok toko hasilnya kurang dari 0 atau minus
                $produk_detail_lama->update(['stok_detail'=>abs($stok_sisa)]);
                
                $produk_detail = new ProdukDetail;
                $produk_detail->nama_produk = $nama_produk;
                $produk_detail->kode_produk = $kode_promo;
                $produk_detail->stok_detail = $qty_promo;
                $produk_detail->harga_beli = $harga_beli;
                $produk_detail->harga_jual_umum = $harga_jual;
                $produk_detail->harga_jual_insan = $harga_jual;
                $produk_detail->expired_date = $produk_detail_lama->expired_date;
                $produk_detail->tanggal_masuk = date('Y-m-d');
                $produk_detail->unit = Auth::user()->unit;
                $produk_detail->status = null;
                $produk_detail->no_faktur = $produk_detail_lama->no_faktur;
                $produk_detail->save();
            }    
        }

        if($stok_lama->stok < $qty){
            
            return back()->with(['error' => 'Stock '. $list->kode_produk . ' Kurang']);
        
        }else {

            $stok_lama->stok -= $qty;
            $stok_lama->update();
        
        }
        
        $cek = Produk::where('kode_produk',$kode_promo)->where('unit',Auth::user()->unit)->first();
        
        if ($cek){

            $stok_baru = Produk::where('kode_produk',$kode_promo)->where('unit',Auth::user()->unit)->first();
            $stok_baru->stok += $qty;
            $stok_baru->update();
            
            if ($stok_baru->harga_beli > $stok_baru->harga_jual) {
                
                $stok_baru->promo = 1;
                $stok_baru->update();
            
            }else {

                $stok_baru->promo = 2;
                $stok_baru->update();
                
            }

        }else{

            foreach ($units as $unit ) {

                $produk = new Produk;
                $produk->kode_produk = $kode_promo;
                $produk->nama_produk = $nama_produk;
                $produk->nama_struk = $struk;
                $produk->merk = '';
                $produk->id_kategori = 0;
                $produk->diskon = 0;
                $produk->harga_beli = $harga_beli;
                $produk->harga_jual = $harga_jual;
                $produk->stok = 0;
                $produk->isi_satuan = 0;

                if ($harga_beli > $harga_jual) {
                    $produk->promo = 1;
                }else {
                    $produk->promo = 2;
                }

                $produk->satuan = '';
                $produk->param_status= 0;
                $produk->stok_mak = 0; 
                $produk->stok_min = 0;
                $produk->unit = $unit->kode_toko;
                $produk->harga_jual_member_insan = $harga_jual_insan;
                $produk->harga_jual_insan = $harga_jual_insan;
                $produk->harga_jual_pabrik = $harga_jual;
                $produk->save();
        
            }
            
            
            $stok_baru = Produk::where('kode_produk',$kode_promo)->where('unit',Auth::user()->unit)->first();
            $stok_baru->stok += $qty;
            $stok_baru->update();
                
        }
        
        return redirect('pricing/index');
    }
}


