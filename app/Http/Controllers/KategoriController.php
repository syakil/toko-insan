<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Kategori;

class KategoriController extends Controller
{
   public function index()
   {
      return view('kategori.index'); 
   }

   public function listData()
   {
   
      $kategori = Kategori::orderBy('id_kategori', 'desc')->get();
      $no = 0;
      $data = array();
      foreach($kategori as $list){
         $no ++;
         $row = array();
         $row[] = $no;
         $row[] = $list->nama_kategori;
         $row[] = $list->expired . ' bulan';
         $row[] = '<div class="btn-group">
                  <a onclick="editForm('.$list->id_kategori.')" class="btn btn-primary btn-sm"><i class="fa fa-pencil"></i></a>';
         $data[] = $row;
      }

      $output = array("data" => $data);
      return response()->json($output);
   }

   public function store(Request $request){

      $kategori = new Kategori;
      $kategori->nama_kategori = $request['nama'];
      $kategori->expired = $request['expired'];
      $kategori->save();

      return back();
   }

   public function edit($id){
     $kategori = Kategori::find($id);
     echo json_encode($kategori);
   }

   public function update(Request $request, $id){
      $kategori = Kategori::find($id);
      $kategori->nama_kategori = $request['nama'];
      $kategori->expired = $request['expired'];
      $kategori->update();
   }

   public function destroy($id)
   {
      $kategori = Kategori::where('id_kategori',$id)->delete();
      return back();
   }
}
