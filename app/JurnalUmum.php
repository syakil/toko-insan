<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class JurnalUmum extends Model
{
    // tambah field status di table jurnal_umum
    protected $table = 'jurnal_umum';
    protected $primaryKey = 'id_jurnal';

    
    public static function getId(){
        return $getId = DB::table('jurnal_umum')->orderBy('id_jurnal','DESC')->take(1)->get();
    }
}
