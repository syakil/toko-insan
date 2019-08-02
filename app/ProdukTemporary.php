<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ProdukTemporary extends Model
{
    protected $table = 'produk_temporary';
    protected $primaryKey = 'id_produk';
    protected $fillable =['stok'];
    public function kategori(){
        return $this->belongsTo('App\Kategori');
    }
}
