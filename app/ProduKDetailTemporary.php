<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ProduKDetailTemporary extends Model{
    protected $table = 'produk_detail_temporary';
    
    protected $primaryKey = 'id_produk_detail';
    protected $fillable = ['id_produk_detail',
    'kode_produk',
    'id_kategori',
    'nama_produk',
    'stok_detail',
    'harga_beli',
    'expired_date',
    'unit'];

    public static function getId(){
        return $getId = DB::table('produk_detail')->orderBy('id_produk_detail','DESC')->take(1)->get();
    }

    
}
