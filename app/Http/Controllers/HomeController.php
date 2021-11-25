<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;

use App\Setting;
use App\Kategori;
use App\Produk;
use App\Supplier;
use App\Member;
use App\Penjualan;
use App\ParamTgl;
use DB;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
  public function index(){
    
    $setting = Setting::find(1);

    $awal = date('Y-m-d', mktime(0,0,0, date('m'), 1, date('Y')));
    $akhir = date('Y-m-d');

    $tanggal = $awal;
    $data_tanggal = array();
    $data_pendapatan = array();
    $users = DB::table('branch')->where('kode_gudang',auth::user()->unit)->get();
    $unit = array();
    
    foreach($users as $key){
      $unit[] = $key->kode_toko;
    }

    $users_id = DB::table('users')->whereIn('unit',$unit)->get();
    $id = array();
    
    foreach($users_id as $list){
      $id[]=$list->id;
    }
    
    while(strtotime($tanggal) <= strtotime($akhir)){ 
    
      $data_tanggal[] = (int)substr($tanggal,8,2);
        
      $pendapatan = Penjualan::where('created_at', 'LIKE', "$tanggal%")->whereIn('id_user',$id)->sum('bayar');
      $data_pendapatan[] = (int) $pendapatan;
      $tanggal = date('Y-m-d', strtotime("+1 day", strtotime($tanggal)));
    
    }
        
    $kategori = Kategori::count();
    $produk = Produk::where('unit',Auth::user()->unit)->count();
    $supplier = Supplier::count();
    $member = Member::whereIn('unit',$unit)->count();

    $cek_param = ParamTgl::where('nama_param_tgl','tanggal_transaksi')->where('unit',Auth::user()->id)->count();

    if ($cek_param) {
    
      $cek = 1;
      $tgl = ParamTgl::where('nama_param_tgl','tanggal_transaksi')->where('unit',Auth::user()->id)->first();
      $param_tgl = tanggal_indonesia($tgl->param_tgl);
    
    }else{
    
      $cek = 0;
      $tgl = '2000-01-01';
      $param_tgl = tanggal_indonesia($tgl);
    
    }

    session(['tanggal' => $param_tgl]);
        
    $user= DB::select('select DATEDIFF(CURDATE(), updated_at) as jumlah_hari from users where id = '.Auth::user()->id.'');
      
    foreach ($user as $list ) {
      $jumlah_hari = $list->jumlah_hari;
    }

    if ($jumlah_hari >= 30) {
      return redirect()->route('ganti_password.index');
    }


    if(Auth::user()->level == 1) return view('home.admin', compact('kategori', 'param_tgl','cek','produk', 'supplier', 'member', 'awal', 'akhir', 'data_pendapatan', 'data_tanggal'));
    elseif(Auth::user()->level == 3) return view('home.po', compact('kategori', 'param_tgl','cek','produk', 'supplier'));
    elseif(Auth::user()->level == 2) return view('home.kasir', compact('kategori', 'param_tgl','cek','produk', 'supplier','setting'));
    elseif(Auth::user()->level == 4) return view('home.gudang', compact('kategori', 'param_tgl','cek','produk', 'supplier','setting'));
    elseif(Auth::user()->level == 5) return view('home.spvs', compact('param_tgl','cek','produk', 'setting'));
    elseif(Auth::user()->level == 6) return view('home.price', compact('kategori', 'param_tgl','cek','produk','setting'));
    elseif(Auth::user()->level == 7) return view('home.it',compact('param_tgl','cek'));
    elseif(Auth::user()->level == 8) return view('home.admin_purchasing',compact('param_tgl','cek'));        
    elseif(Auth::user()->level == 9) return view('home.finance', compact('kategori', 'param_tgl','cek','produk','setting'));
    elseif(Auth::user()->level == 10) return view('home.spvs_gudang', compact('kategori', 'param_tgl','cek','produk','setting'));
    else return view('/login', compact('setting'));
        
  }

  public function store(Request $request){

    $param_tanggal = new ParamTgl;
    $param_tanggal->param_tgl = $request->tanggal;
    $param_tanggal->nama_param_tgl = 'tanggal_transaksi';
    $param_tanggal->unit = Auth::user()->id;
    $param_tanggal->save();

    return redirect()->back();

  }

    public function update(Request $request, $id){

      $param_tanggal  = ParamTgl::where('nama_param_tgl','tanggal_transaksi')->where('unit',Auth::user()->id)->first();
      // dd($param_tanggal);
      $param_tanggal->param_tgl = $request->tanggal;
      $param_tanggal->update();

      return redirect()->back();

    }
}

