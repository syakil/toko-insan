<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
use Auth;
use App\Supplier;
use App\PembelianTemporary;
use App\Kirim;

class SupplierController extends Controller
{
   public function index()
   {
      return view('supplier.index'); 
   }

   public function listData(){
   
      $supplier = Supplier::orderBy('id_supplier', 'desc')->get();
      $no = 0;
      $data = array();
      foreach($supplier as $list){
         $no ++;
         $row = array();
         $row[] = $no;
         $row[] = $list->nama;
         $row[] = $list->alamat_supplier;
         $row[] = $list->telepon;
         $row[] = $list->pic;
         $row[] = $list->norek;
         $row[] = $list->bank;
         $row[] = $list->metode_bayar;
         $row[] = '<div class="btn-group">
                  <a onclick="editForm('.$list->id_supplier.')" class="btn btn-primary btn-sm"><i class="fa fa-pencil"></i></a>
                  <a href="'. route('supplier.delete',$list->id_supplier).'" class="btn btn-danger btn-sm"><i class="fa fa-trash"></i></a></div>';
         $data[] = $row;
      }

      $output = array("data" => $data);
      return response()->json($output);
     
   }

   public function tambah(Request $request)
   {
      try {

         DB::beginTransaction();

         $supplier = new Supplier;
         $supplier->nama   = $request['nama'];
         $supplier->alamat_supplier = $request['alamat'];
         $supplier->telepon = $request['telepon'];
         $supplier->norek = $request['norek'];
         $supplier->nama_rek = $request['nama_rek'];
         $supplier->bank = $request['bank'];
         $supplier->pic = $request['pic'];
         $supplier->metode_bayar = $request['metode'];
         $supplier->status = 1;
         $supplier->save();
         
         DB::commit();
         return back()->with(['success' => 'Supplier Berhasil di Tambah !']);

      }catch(\Exception $e){
         
         DB::rollback();
         return back()->with(['error' => $e->getmessage()]);
 
      }
   
   }

   public function edit($id){

     $supplier = Supplier::find($id);
     echo json_encode($supplier);
   
   }

   public function update_supplier(Request $request, $id){

      try {
         
         DB::beginTransaction();

         $supplier = Supplier::find($id);
         $supplier->nama = $request['nama'];
         $supplier->alamat_supplier = $request['alamat'];
         $supplier->telepon = $request['telepon'];
         $supplier->norek = $request['norek'];
         $supplier->nama_rek = $request['nama_rek'];
         $supplier->bank = $request['bank'];
         $supplier->pic = $request['pic'];
         $supplier->metode_bayar = $request['metode'];
         $supplier->status = 1;
         $supplier->update();
      
         DB::commit();
         return back()->with(['success' => 'Supplier Berhasil di Ubah !']);

      }catch(\Exception $e){
         
         DB::rollback();
         return back()->with(['error' => $e->getmessage()]);

      }
   }

   public function delete($id){


      try {
         
         DB::beginTransaction();

         $cek_pembelian = PembelianTemporary::where('id_supplier',$id)->first();

         $cek_kirim = Kirim::where('id_supplier',$id)->first();

         if ($cek_pembelian) {
            return back()->with(['error' => 'Ada History Transaksi !']);   
         }

         if ($cek_kirim) {
            return back()->with(['error' => 'Ada History Transaksi !']);   
         }

         $supplier = Supplier::find($id);
         $supplier->delete();
   
         DB::commit();
         return back()->with(['success' => 'Supplier Berhasil di Hapus !']);

      }catch(\Exception $e){
            
         DB::rollback();
         return back()->with(['error' => $e->getmessage()]);

      }
   
   }
}
