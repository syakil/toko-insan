<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Produk;
use App\ProdukDetail;
use App\TabelTransaksi;
use Auth;
use App\Kategori;
use Yajra\Datatables\Datatables;
use PDF;
use DB;

class ProdukController extends Controller
{
    public function index()
    {
       $kategori = Kategori::all();      
       return view('produk.index', compact('kategori'));
    }

    public function listData()
    {
    
      $produk = DB::table('produk_detail')
      ->select('produk_detail.*','produk.kode_produk','produk.nama_produk','produk.harga_jual','produk.diskon','produk.stok','produk.harga_jual_member_insan','produk.id_produk','kategori.*')
      ->leftJoin('kategori', 'kategori.id_kategori', '=', 'produk_detail.id_kategori')
      ->leftjoin('produk','produk.kode_produk','=','produk_detail.kode_produk')
      ->orderBy('produk_detail.id_produk_detail', 'desc')
      ->get();
        $no = 0;
        $data = array();
        foreach($produk as $list){
            $no ++;
            $row = array();
            $row[] = "<input type='checkbox' name='id[]'' value='".$list->id_produk."'>";
            $row[] = $no;
            $row[] = $list->kode_produk;
            $row[] = $list->nama_produk;
            $row[] = $list->nama_kategori;
            $row[] = $list->unit;
            $row[] = "Rp. ".format_uang($list->harga_beli);
            $row[] = "Rp. ".format_uang($list->harga_jual);
            $row[] = "Rp. ".format_uang($list->harga_jual_member_insan);
            $row[] = $list->diskon."%";
            $row[] = $list->stok;
            $row[] = "<div class='btn-group'>
                    <a href='/toko-master/produk/update/".$list->id_produk."' class='btn btn-primary btn-sm'><i class='fa fa-pencil'></i></a>
                    <a onclick='deleteData(".$list->id_produk.")' class='btn btn-danger btn-sm'><i class='fa fa-trash'></i></a></div>";
            $data[] = $row;
        }
        
        return Datatables::of($data)->escapeColumns([])->make(true);
    }

    public function store(Request $request)
    {
        $jml = Produk::where('kode_produk', '=', $request['kode'])->count();
        if($jml < 1){
            $produk = new Produk;
            $produk->kode_produk            = $request['kode'];
            $produk->nama_produk            = $request['nama'];
            $produk->id_kategori            = $request['kategori'];
            $produk->merk                   = $request['merk'];
            $produk->harga_beli             = $request['harga_beli'];
            $produk->diskon                 = $request['diskon'];
            $produk->harga_jual             = $request['harga_jual'];
            $produk->harga_jual_member_insan= $request['harga_jual_insan'];
            $produk->harga_jual_pabrik      = $request['harga_jual_pabrik']; 
            $produk->stok                   = $request['stok'];
            $produk->save();

            // $harga_jual_awal = $request['harga_beli'] + ($request['harga_beli']*5/100);
            // $margin = $harga_jual_awal*10/100;

            $jurnal = new TabelTransaksi;
            $jurnal->unit =  Auth::user()->unit; 
            $jurnal->kode_transaksi = $id;
            $jurnal->kode_rekening = 1482000;
            $jurnal->tanggal_transaksi  = date('Y-m-d');
            $jurnal->jenis_transaksi  = 'Jurnal System';
            $jurnal->keterangan_transaksi = 'Kenaikan Harga' . ' ' . $id;
            $jurnal->debet = $margin;
            $jurnal->kredit = 0;
            $jurnal->tanggal_posting = '';
            $jurnal->keterangan_posting = '0';
            $jurnal->id_admin = Auth::user()->id; 
            $jurnal->save();

            $jurnal = new TabelTransaksi;
            $jurnal->unit =  Auth::user()->unit; 
            $jurnal->kode_transaksi = 112;
            $jurnal->kode_rekening = 1422000;
            $jurnal->tanggal_transaksi  = date('Y-m-d');
            $jurnal->jenis_transaksi  = 'Jurnal System';
            $jurnal->keterangan_transaksi = 'Kenaikan Harga' . ' ' . $id;
            $jurnal->debet =0;
            $jurnal->kredit =$margin;
            $jurnal->tanggal_posting = '';
            $jurnal->keterangan_posting = '0';
            $jurnal->id_admin = Auth::user()->id; 
            $jurnal->save();

            echo json_encode(array('msg'=>'success'));
        }else{
            echo json_encode(array('msg'=>'error'));
        }
    }

    public function edit($id)
    {
        $produk = Produk::find($id);
        $kategori = Kategori::all();

        if ($produk->harga_jual == 0) {
            // harga jual = harga_beli_gabungan + margin 10%
            $harga_jual = $produk->harga_beli + ($produk->harga_beli*10/100);
        }else {
            $harga_jual = $produk->harga_jual;
        }

        return view('produk.edit',['kategori'=>$kategori,'produk'=>$produk,'harga_jual'=>$harga_jual]);
    }

    public function update(Request $request, $id)
    {
        $produk = Produk::where('kode_produk',$id)->first();

        
        if($produk->harga_jual == 0){
            
            $margin = $request['harga_jual'] - $produk->harga_beli;

            $jurnal = new TabelTransaksi;
            $jurnal->unit = Auth::user()->unit; 
            $jurnal->kode_transaksi = $id;
            $jurnal->kode_rekening = 1482000;
            $jurnal->tanggal_transaksi  = date('Y-m-d');
            $jurnal->jenis_transaksi  = 'Jurnal System';
            $jurnal->keterangan_transaksi = 'Harga Baru' . ' ' . $id;
            $jurnal->debet = $margin;
            $jurnal->kredit = 0;
            $jurnal->tanggal_posting = '';
            $jurnal->keterangan_posting = '0';
            $jurnal->id_admin = Auth::user()->id; 
            $jurnal->save();

            $jurnal = new TabelTransaksi;
            $jurnal->unit =  Auth::user()->unit; 
            $jurnal->kode_transaksi = $id;
            $jurnal->kode_rekening = 1422000;
            $jurnal->tanggal_transaksi  = date('Y-m-d');
            $jurnal->jenis_transaksi  = 'Jurnal System';
            $jurnal->keterangan_transaksi = 'Harga Baru' . ' ' . $id;
            $jurnal->debet =0;
            $jurnal->kredit =$margin;
            $jurnal->tanggal_posting = '';
            $jurnal->keterangan_posting = '0';
            $jurnal->id_admin = Auth::user()->id; 
            $jurnal->save();

        }elseif ($produk->harga_jual < $request['harga_jual']) {
            
            $margin = $request['harga_jual'] - $produk->harga_jual;

            $jurnal = new TabelTransaksi;
            $jurnal->unit = Auth::user()->unit; 
            $jurnal->kode_transaksi = $id;
            $jurnal->kode_rekening = 1482000;
            $jurnal->tanggal_transaksi  = date('Y-m-d');
            $jurnal->jenis_transaksi  = 'Jurnal System';
            $jurnal->keterangan_transaksi = 'Perubahan Harga' . ' ' . $id;
            $jurnal->debet = $margin;
            $jurnal->kredit = 0;
            $jurnal->tanggal_posting = '';
            $jurnal->keterangan_posting = '0';
            $jurnal->id_admin = Auth::user()->id; 
            $jurnal->save();

            $jurnal = new TabelTransaksi;
            $jurnal->unit =  Auth::user()->unit; 
            $jurnal->kode_transaksi = $id;
            $jurnal->kode_rekening = 1422000;
            $jurnal->tanggal_transaksi  = date('Y-m-d');
            $jurnal->jenis_transaksi  = 'Jurnal System';
            $jurnal->keterangan_transaksi = 'Perubahan Harga' . ' ' . $id;
            $jurnal->debet =0;
            $jurnal->kredit =$margin;
            $jurnal->tanggal_posting = '';
            $jurnal->keterangan_posting = '0';
            $jurnal->id_admin = Auth::user()->id; 
            $jurnal->save();

        }elseif ($produk->harga_jual > $request['harga_jual']) {
            $margin = $request['harga_jual'] - $produk->harga_jual;
            
            $jurnal = new TabelTransaksi;
            $jurnal->unit = Auth::user()->unit; 
            $jurnal->kode_transaksi = $id;
            $jurnal->kode_rekening = 1422000;
            $jurnal->tanggal_transaksi  = date('Y-m-d');
            $jurnal->jenis_transaksi  = 'Jurnal System';
            $jurnal->keterangan_transaksi = 'Perubahan Harga' . ' ' . $id;
            $jurnal->debet = abs($margin);
            $jurnal->kredit = 0;
            $jurnal->tanggal_posting = '';
            $jurnal->keterangan_posting = '0';
            $jurnal->id_admin = Auth::user()->id; 
            $jurnal->save();

            $jurnal = new TabelTransaksi;
            $jurnal->unit =  Auth::user()->unit; 
            $jurnal->kode_transaksi = $id;
            $jurnal->kode_rekening = 1482000;
            $jurnal->tanggal_transaksi  = date('Y-m-d');
            $jurnal->jenis_transaksi  = 'Jurnal System';
            $jurnal->keterangan_transaksi = 'Perubahan Harga' . ' ' . $id;
            $jurnal->debet =0;
            $jurnal->kredit =abs($margin);
            $jurnal->tanggal_posting = '';
            $jurnal->keterangan_posting = '0';
            $jurnal->id_admin = Auth::user()->id; 
            $jurnal->save();
        
        }else{
            
        $kategori = Kategori::all();
            return view('produk.index', compact('kategori'));
        }
        
            
        // dd($produk->nama_produk);
        $produk = Produk::where('kode_produk',$id)->get();
        foreach($produk as $produk_all){
        $produk_all->nama_produk    = $request['nama'];
        $produk_all->id_kategori    = $request['kategori'];
        $produk_all->merk          = $request['merk'];
        $produk_all->harga_beli      = $request['harga_beli'];
        $produk_all->diskon       = $request['diskon'];
        $produk_all->harga_jual    = $request['harga_jual'];
        $produk_all->harga_jual_member_insan      = $request['harga_jual_insan'];
        $produk_all->harga_jual_pabrik      = $request['harga_jual_pabrik']; 
        $produk_all->update();
        }
        
        $kategori = Kategori::all();
        return view('produk.index', compact('kategori'));
    
    }

    public function destroy($id)
    {
        $produk = Produk::find($id);
        $produk->delete();
    }

    public function deleteSelected(Request $request)
    {
        foreach($request['id'] as $id){
            $produk = Produk::find($id);
            $produk->delete();
        }
    }

    public function printBarcode(Request $request)
    {
        $dataproduk = array();
        foreach($request['id'] as $id){
            $produk = Produk::find($id);
            $dataproduk[] = $produk;
        }
        $no = 1;
        $pdf = PDF::loadView('produk.barcode', compact('dataproduk', 'no'));
        $pdf->setPaper('a4', 'potrait');      
        return $pdf->stream();
    }
}
