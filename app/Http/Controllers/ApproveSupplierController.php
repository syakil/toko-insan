<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
use App\Supplier;
use Auth;



class ApproveSupplierController extends Controller{
    
    public function index(){

        return view('approve_supplier.index');

    }

    public function listData(){

        $supplier = Supplier::where('status',1)->orderBy('nama', 'desc')->get();
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
            $row[] = "<div class='btn-group'>
                    <a href='".route('approve_supplier.approve',$list->id_supplier)."' class='btn btn-danger btn-sm'><i class='fa fa-gavel'></i> Approve</a></div>";
            $data[] = $row;
        }

        $output = array("data" => $data);
        return response()->json($output);

    }


    public function approve($id){


        try{

            DB::beginTransaction();

            Supplier::where('id_supplier',$id)->update(['status'=>0]);

            DB::commit();
            return back()->with(['success' => 'Supplier Berhasil Ditambahkan']);

        }catch(\Exception $e){
    
            DB::rollback();
            return back()->with(['error' => $e->getmessage()]);

        }


    }



}
