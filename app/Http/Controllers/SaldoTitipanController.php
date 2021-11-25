<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Musawamah;
use App\MusawamahDetail;
use App\ListToko;
use Auth;
use App\Branch;
use App\User;
use App\Member;
use DB;

class SaldoTitipanController extends Controller
{
    public function index(){

        $member = DB::table('member')->leftjoin('musawamah','musawamah.id_member','=','member.kode_member')->get();
        return view('saldo_titipan/index',compact('member'));

    }

    public function getData($member){

        $musawamah = DB::table('member')->leftjoin('musawamah','musawamah.id_member','=','member.kode_member')->select(DB::raw('Cust_Short_name,id_member,format(musawamah.Plafond,0) AS Plafond,format(os,0) as os ,format(angsuran,0) as angsuran,format(bulat,0) as tunggakan'))->where('id_member',$member)->first();
        echo json_encode($musawamah);
    }

    public function listDetail($member){

        $detail_transaksi = DB::table('list_toko')->select(DB::raw(
                'BUSS_DATE,KET,DEBIT,KREDIT'
            ))
            ->where('id_member',$member)
            ->get();
            // dd($detail_transaksi);

        $no = 0;
        $data = array();
        foreach ($detail_transaksi as $list) {
        $no++;
        $row = array();
            $row[] = $no;
            $row[] = tanggal_indonesia($list->BUSS_DATE);
            $row[] = $list->KET;
            $row[] = number_format($list->DEBIT);
            $row[] = number_format($list->KREDIT);
            $data[] = $row;
        }
        

        $output = array("data" => $data);
        return response()->json($output);
    

    }
     
    public function getTitipan($member){
        
        $titipan = DB::table('list_toko')->select(DB::raw(
                'Format(SUM(KREDIT - DEBIT),0) AS titipan'
                ))
                ->where('id_member',$member)
                ->first();

        echo json_encode($titipan);

    }



}
