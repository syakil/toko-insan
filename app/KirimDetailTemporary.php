<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class KirimDetailTemporary extends Model
{
 
    protected $table = 'kirim_barang_detail_temporary';
    protected $primaryKey = 'id_pembelian_detail';

    protected $fillable = ['jumlah_terima'];
}
