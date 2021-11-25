<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
use Auth;
use App\PermohonanPembelian;
use App\Produk;
use App\Coa;
use App\Supplier;
use Ramsey\Uuid\Uuid;


class PermohonanPembelianController extends Controller{
    
    public function index(){

        $produk = Produk::where('unit',Auth::user()->unit)->get();
        // $rekening = Coa::get();
        return view('permohonan_pembelian.index',compact('produk'));

    }

    
    public function listData(){
   
        $permohonan = PermohonanPembelian::leftJoin('supplier', 'supplier.id_supplier', '=', 'permohonan_pembelian.id_supplier')
                                ->leftJoin('permohonan_status','permohonan_status.id_status','permohonan_pembelian.status_permohonan')
                                ->leftJoin('produk','produk.kode_produk','permohonan_pembelian.kode_produk')
                                ->select('permohonan_pembelian.*','produk.nama_produk','permohonan_status.keterangan','supplier.nama')
                                ->where('produk.unit',Auth::user()->unit)
                                ->where('permohonan_pembelian.unit',Auth::user()->unit)
                                ->get();
        $no = 0;
        $data = array();
        
        foreach($permohonan as $list){
        
            $no ++;
            $row = array();
            $row[] = tanggal_indonesia($list->created_at);
            $row[] = $list->kode_transaksi;
            $row[] = $list->nama;
            $row[] = $list->kode_produk;
            $row[] = $list->nama_produk;            
            $row[] = $list->jumlah;
            $row[] = $list->harga_beli;
            $row[] = "Rp. ".format_uang($list->total);
            $row[] = $list->keterangan_permohonan;
            
            switch ($list->status_permohonan) {
                case '1':
                    $row[] = '<span class="label label-warning">' . $list->keterangan . '</span>';
                    $row[] = '<div class="btn-group">
                    <a onclick="showDetail('.$list->id_permohonan.')" class="btn btn-warning btn-sm"><i class="fa fa-pencil"></i></a>
                    <a onclick="deleteData('.$list->id_permohonan.')" class="btn btn-danger btn-sm"><i class="fa fa-trash"></i></a>
                    </div>';
                    break;
                case '2':
                    $row[] = '<span class="badge badge-secondary">' . $list->keterangan . '</span>';
                    $row[] = '<div class="btn-group">
                    <a onclick="showDetail('.$list->id_permohonan.')" class="btn btn-warning btn-sm"><i class="fa fa-pencil"></i></a>
                    <a onclick="deleteData('.$list->id_permohonan.')" class="btn btn-danger btn-sm"><i class="fa fa-trash"></i></a>
                    </div>';
                    break;
                case '3':
                    $row[] = '<span class="label label-dark">' . $list->keterangan . '</span>';
                    $row[] = '<div class="btn-group">
                    </div>';
                    break;
                default:
                    $row[] = '<span class="label label-success">' . $list->keterangan . '</span>';
                    $row[] = '<div class="btn-group">
                    </div>';
                    break;
            }
                
            $data[] = $row;
        }

        $output = array("data" => $data);
        
        return response()->json($output);
 
    }

    public function tambah(){

        $supplier = Supplier::all();
        $produk = Produk::where('unit',Auth::user()->unit)->get();
        return view('permohonan_pembelian.tambah',compact('produk','supplier'));

    }


    public function autocomplete_produk(Request $request){
        
        if ($request->has('q')) {
            
            $cari = $request->q;

            $data = Produk::where('kode_produk', 'LIKE', '%'.$cari.'%')   
                        ->first();

            return response()->json($data);
        }
    
    }

    
    public function autocomplete_supplier(Request $request){
        
        if ($request->has('q')) {
            
            $cari = $request->q;

            $data = Supplier::where('nama', 'LIKE', '%'.$cari.'%')
                        ->first();

            return response()->json($data);
        }
    
    }

    public function store(Request $request){

        try {
            
            DB::beginTransaction();

                $uuid=Uuid::uuid4()->getHex();
                $rndm=substr($uuid,25);
                $unit = Auth::user()->unit;
                $kode_transaksi="PR/-".$unit.$rndm;

                $check = PermohonanPembelian::where('kode_produk',$request->kode)->where('status_permohonan',1)->where('unit',Auth::user()->unit)->first();
                
                if ($check) {
                    return back()->with(['error' => 'Permohonan Produk Sudah Ada!']);        
                }

                $permohonan = new PermohonanPembelian;
                $permohonan->unit = $unit;
                $permohonan->kode_transaksi = $kode_transaksi;
                $permohonan->kode_produk = $request->kode;
                $permohonan->id_supplier = $request->supplier;
                $permohonan->jumlah = $request->jumlah;
                $permohonan->harga_beli = $request->harga;
                $permohonan->total = $request->jumlah * $request->harga;
                $permohonan->status_permohonan = 1;
                $permohonan->save();


            DB::commit();

            return redirect()->route('permohonan_pembelian.index')->with(['success' => 'Permohonan Berhasil Di buat !']);

        }catch(\Exception $e){
         
            DB::rollback();
            return back()->with(['error' => $e->getmessage()]);
    
        }

    }


    public function edit($id){

        $permohonan = PermohonanPembelian::leftJoin('supplier', 'supplier.id_supplier', '=', 'permohonan_pembelian.id_supplier')
                                ->leftJoin('permohonan_status','permohonan_status.id_status','permohonan_pembelian.status_permohonan')
                                ->leftJoin('produk','produk.kode_produk','permohonan_pembelian.kode_produk')
                                ->select('permohonan_pembelian.*','produk.nama_produk','permohonan_status.keterangan','supplier.nama')
                                ->where('produk.unit',Auth::user()->unit)
                                ->where('permohonan_pembelian.id_permohonan',$id)
                                ->first();
        
        echo json_encode($permohonan);
      
    }

    public function update(Request $request){

        try {
            
            DB::beginTransaction();

                $permohonan = PermohonanPembelian::where('id_permohonan',$request->id)->first();
                $permohonan->harga_beli = $request->harga;
                $permohonan->jumlah = $request->jumlah;
                $permohonan->update();

            DB::commit();

            return redirect()->route('permohonan_pembelian.index')->with(['success' => 'Permohonan Berhasil Di Ubah !']);

        }catch(\Exception $e){
            
            DB::rollback();
            return back()->with(['error' => $e->getmessage()]);
    
        }
    
    }

    public function index_approval(){

        return view('permohonan_pembelian.index_approval');

    }
    
    public function listApproval(){

        $permohonan = PermohonanPembelian::leftJoin('supplier', 'supplier.id_supplier', '=', 'pembelian.id_supplier')
                                        ->leftJoin('permohonan_status','permohonan_status.id_status','permohonan_pembelian.status_permohonan')
                                        ->leftJoin('produk','produk.kode_produk','permohonan_pembelian.kode_produk')
                                        ->where('produk.unit',Auth::user()->unit)
                                        ->where('permohonan_pembelian.unit',Auth::user()->unit)
                                        ->where('status_permohonan',1)
                                        ->get();

        $no = 0;
        $data = array();

        foreach($permohonan as $list){

            $no ++;
            $row = array();
            $row[] = $list->kode_transaksi;
            $row[] = tanggal_indonesia($list->created_at);
            $row[] = $list->nama;
            $row[] = $list->kode_produk;
            $row[] = $list->nama_produk;            
            $row[] = $list->jumlah;
            $row[] = $list->harga_beli;
            $row[] = "Rp. ".format_uang($list->harga_beli * $list->jumlah);
            $row[] = $list->keterangan_permohonan;


            $data[] = $row;
        }

        $output = array("data" => $data);

        return response()->json($output);

    }

    
    public function approval(Request $request,$id){
        
        dd($id);        
        
    }
    
    public function destroy($id){
        
        PermohonanPembelian::where('id_permohonan',$id)->delete();

    }
}
