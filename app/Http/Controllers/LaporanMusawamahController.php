<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
use Auth;
use App\Musawamah;
use App\Member;
use App\MusawamahDetail;

class LaporanMusawamahController extends Controller
{
    public function index(){
        return view('laporan_musawamah/index');
    }

    public function listData(){
        $data_member = DB::table('musawamah','member','list_toko')
        ->selectRaw('SUM(list_toko.KREDIT) as KREDIT,SUM(list_toko.DEBIT) as DEBIT')         
        ->leftJoin('member','member.kode_member','=','musawamah.id_member')
                    ->leftJoin('list_toko','list_toko.id_member','=','musawamah.id_member')
                                         ->groupBy('list_toko.id_member')
                    ->where('musawamah.unit','3002')
                    ->get();
        // dd($data_member);
        $no = 0;
        $data = array();
        foreach($data_member as $list){
            // dd($list);
        $no ++;
        $row = array();
        $row[] = $no;
        $row[] = $list->unit;
        $row[] = $list->kode_member;
        $row[] = $list->nama;
        $row[] = $list->Plafond;
        $row[] = $list->os;
        $row[] = $list->angsuran;
        $row[] = $list->DEBIT - $list->KREDIT;
        $row[] = $list->tgl_akad;
        $data[] = $row;
        }
        //   dd($data);
        $output = array("data" => $data);
        return response()->json($output);
    }       
}
