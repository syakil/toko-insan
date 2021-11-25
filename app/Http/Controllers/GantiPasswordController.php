<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Auth;
use DB;

class GantiPasswordController extends Controller
{
   public function index(){

    return view('ganti_password/index');

   }


   public function update(Request $request){

        $id = Auth::user()->id;
        $password_baru = $request->password_baru;
        $password_konf = $request->password_konf;
        $password_lama = Hash::make($request->password_lama);
        $password_hash = Hash::make($password_baru);

        $password_sekarang = DB::table('users')->where('id',$id)->first();

        if ($password_baru == $password_konf) {
            
            if (Hash::check($password_lama , $password_sekarang->password)) {
            
                return back()->with(['error' => 'Password Lama Salah' ]);
                
            }else {
                
                $user = DB::table('users')->where('id',$id)->update([
                    'password' => $password_hash
                    ]);
                    
                    return back()->with(['success' => 'Password Berhasil Di Ubah' ]);
                    
                }            
                
        }else {
                
            return back()->with(['error' => 'Konfirmasi Password Salah' ]);
       
        }

   }

   public function reset($id){

    $password_baru = 12345;
    $password_hash = Hash::make($password_baru);

    $user = DB::table('users')->where('id',$id)->update([
    'password' =>  $password_hash
    ]);

    return back()->with(['success' => 'Password Berhasil Di Reset' ]);

   }
}
