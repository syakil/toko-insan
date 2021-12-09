<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;
use DB;
use App\Musawamah;
use App\Member;
use App\Branch;

class RestrukturController extends Controller
{
    protected $projects;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    
    public function __construct()
    {
        $this->middleware(function ($request, $next) {

            if (is_null(Auth::user())) {
                return redirect()->route('login')->withErrors(['Sesi Anda Telah Berakhir']);;
            }else{   
                $this->projects = Auth::user()->projects;
                return $next($request);
            }
            
        });
    }
    
    public function index(){

        return view('restruktur.index');

    }

    public function loadData(Request $request){

        $cari = $request['query'];
        
        $data = DB::table('musawamah')->select('id_member', 'Cust_Short_name')->where('id_member', 'LIKE', '%'.$cari.'%')->orWhere('Cust_Short_name', 'LIKE', '%'.$cari.'%')->limit('5')->get();
        $output = '<ul class="dropdown-menu" style="display:block; position:relative">';


        foreach($data as $row){

            $output .= '
            <li class="member_list"><a href="#">'.$row->id_member.' - '.$row->Cust_Short_name.'</a></li>
            ';

        }

        $output .= '</ul>';
        echo $output;

    }
}