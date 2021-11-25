<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Redirect;
use App\Pembelian;
use Auth;
use PDF;
use App\Supplier;
use App\Kategori;
use App\PembelianTemporary;
use App\PembelianTemporaryDetail;
use App\Produk;
use App\ProdukDetail;
use App\KartuStok;
use DB;
use Session;
use App\PembelianDetail;

class TerimaPoController extends Controller
{
    public function index(){

        $pembelian = PembelianTemporary::leftJoin('supplier','pembelian_temporary.id_supplier','=','supplier.id_supplier')
                            ->where('kode_gudang',Auth::user()->unit)
                            ->where('pembelian_temporary.status',1)
                            ->get();
        return view('terima_po.index', compact('pembelian')); 
    }

    public function listData(){
    
        $pembelian = Pembelian::select('pembelian.*','supplier.nama')->leftJoin('supplier', 'supplier.id_supplier', '=', 'pembelian.id_supplier')
                                ->where('kode_gudang',Auth::user()->unit)
                                ->where('pembelian.status',1)
                                ->orderBy('pembelian.id_pembelian', 'desc')
                                ->get();
        $no = 0;
        $data = array();
        foreach($pembelian as $list){
            $no ++;
            $row = array();
            $row[] = $no;
            $row[] = $list->id_pembelian_t;
            $row[] = tanggal_indonesia($list->created_at);
            $row[] = $list->nama;
            $row[] = $list->total_item;
            $row[] = $list->total_terima;
            
            switch ($list->status) {
                case '1':
                    $row[] = "<div class='btn-group'>
                    <a href='".route('terima_po.edit',$list->id_pembelian)."' class='btn btn-warning btn-sm'><i class='fa fa-pencil'></i></a>
                    </div>";
                    break;
                default:
                    $row[] = '<div class="btn-group">
                    <button class="btn btn-warning btn-sm" disabled><i class="fa fa-pencil" ></i></button>
                    </div>';
                    break;
            }
            $data[] = $row;
            
        }

        $output = array("data" => $data);
        return response()->json($output);
    }

    public function show($id){
    
        $detail = PembelianDetail::leftJoin('produk', 'produk.kode_produk', '=', 'pembelian_detail.kode_produk')
            ->where('id_pembelian', '=', $id)
            ->where('unit',Auth::user()->unit)
            ->get();
        $no = 0;
        $data = array();
        foreach($detail as $list){
        $no ++;
        $row = array();
        $row[] = $no;
        $row[] = $list->kode_produk;
        $row[] = $list->nama_produk;
        $row[] = "Rp. ".format_uang($list->harga_beli);
        $row[] = $list->jumlah;
        $row[] = $list->jumlah_terima;
        $row[] = $list->status_jurnal;
        $row[] = 'Rp.' . $list->sub_total;
        $data[] = $row;
        }

        $output = array("data" => $data);
        return response()->json($output);
    }


    public function cetak($id){

        $data['produk'] = DB::table('pembelian_detail','produk')
                            ->select('pembelian_detail.*','produk.kode_produk','produk.nama_produk')
                            ->leftJoin('produk','pembelian_detail.kode_produk','=','produk.kode_produk')
                            ->where('unit',Auth::user()->unit)
                            ->where('id_pembelian',$id)
                            ->get();

        $data['alamat'] = Pembelian::leftJoin('supplier','pembelian.id_supplier','=','supplier.id_supplier')
                                    ->leftJoin('branch','pembelian.kode_gudang','=','branch.kode_gudang')
                                    ->where('id_pembelian',$id)
                                    ->first();

        $data['nosurat'] = Pembelian::where('id_pembelian',$id)->get();
        $data['no'] =1;
        $pdf = PDF::loadView('terima_po.cetak_po', $data);
        
        return $pdf->stream('surat_jalan.pdf');
    
    }


    public function create($id){

        $temporary = PembelianTemporary::find($id);
        
        $pembelian = new Pembelian;
        $pembelian->id_pembelian_t = $temporary->id_pembelian;
        $pembelian->id_supplier = $temporary->id_supplier;     
        $pembelian->total_item = $temporary->total_item;     
        $pembelian->total_harga = 0;
        $pembelian->total_terima = 0;
        $pembelian->total_selisih = 0;
        $pembelian->total_harga_selisih = 0;
        $pembelian->total_harga_terima = 0;     
        $pembelian->diskon = 0;     
        $pembelian->bayar = 0;      
        $pembelian->jatuh_tempo = date('Y-m-d');
        $pembelian->kode_gudang = 0;    
        $pembelian->status = 0;
        $pembelian->tipe_bayar = 2;    
        $pembelian->id_user = Auth::user()->id;
        $pembelian->kode_gudang = Auth::user()->unit;
            
        $pembelian->save();

        session(['idpembelian' => $pembelian->id_pembelian]);
        session(['idtemporary' => $id]);
        session(['idsupplier' => $temporary->id_supplier]);

        return Redirect::route('terima_po_detail.index');      
    }

    public function edit($id){

        $pembelian_temporary = Pembelian::where('id_pembelian',$id)->first();

        session(['idpembelian' => $id]);
        session(['idtemporary' => $pembelian_temporary->id_pembelian_t]);
        session(['idsupplier' => $pembelian_temporary->id_supplier]);

        return Redirect::route('terima_po_detail.index');      

    }

    public function store(Request $request){
           
        try{

            DB::beginTransaction();

            $pembelian = Pembelian::find($request['idpembelian']);
            $pembelian->total_terima = $request['totalitem'];
            $pembelian->total_selisih = $pembelian->total_item - $request['totalitem'];
            $pembelian->total_harga = $request['total'];
            $pembelian->diskon = $request['diskon'];
            $pembelian->bayar = $request['bayar'];
            $pembelian->status = 1;
            $pembelian->update();

            $pembelian_temporary = PembelianTemporary::find($pembelian->id_pembelian_t);
            $pembelian_temporary->total_terima = $request['totalitem'];
            $pembelian_temporary->total_selisih = $pembelian_temporary->total_item - $request['totalitem'];
            $pembelian_temporary->total_harga = $request['total'];
            $pembelian_temporary->diskon = $request['diskon'];
            $pembelian_temporary->bayar = $request['bayar'];
            $pembelian_temporary->status = 2;
            $pembelian_temporary->update();

            DB::commit();

              $request->session()->forget('idtemporary');
              $request->session()->forget('idpembelian');
              $request->session()->forget('idsupplier');

            return Redirect::route('terima_po.index')->with((['success' => 'Terima Barang Berhasil Disimpan !']));      
        
        }catch(\Exception $e){
         
            DB::rollback();
            return back()->with(['error' => $e->getmessage()]);
    
        }
       
    }
    
    public function destroy($id){
        
        $pembelian = Pembelian::find($id);
        $pembelian->delete();

        $detail = PembelianDetail::where('id_pembelian', '=', $id)->get();
        foreach($detail as $data){
            $produk = Produk::where('kode_produk', '=', $data->kode_produk)->first();
            $produk->stok -= $data->jumlah;
            $produk->update();
            $data->delete();
        }
    }
}
