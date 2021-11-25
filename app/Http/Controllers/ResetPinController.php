<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Member;
use App\Branch;
use DB;
use Auth;


class ResetPinController extends Controller{
    

    public function index(){

        return view('reset_pin/index');

    }


    public function listData(){


        $data_member = DB::select('select * from member where unit in (select unit from branch where kode_gudang = '. Auth::user()->unit.')' );

        $data = array();

        foreach($data_member as $list){

            $row = array();
            $row[] = $list->UNIT;
            $row[] = $list->CODE_KEL;
            $row[] = $list->kode_member;
            $row[] = $list->ID_NUMBER;
            $row[] = $list->nama;
            $row[] = $list->BIRTH_DATE;
            $row[] = '<div class="btn-group">
                    <a href="'.route('reset_pin.reset',$list->kode_member).'" class="btn btn-warning btn-sm"><i class="fa fa-refresh"></i></a>
                    </div>';
            $data[] = $row;
      
        }

        
        $output = array("data" => $data);
        return response()->json($output);

    }

    public function reset($kode_member){

        try{
            
            DB::beginTransaction();

            $member = Member::where('kode_member',$kode_member)->first();
            $member->PIN = 0;
            $member->update();

            DB::commit();

        }catch(\Exception $e){
        
            DB::rollback();
            return back()->with(['error' => $e->getmessage()]);
        }

        return back()->with(['success' => 'PIN Berhasil Di Reset!']);
    }


}
