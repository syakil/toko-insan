<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Kirim extends Model
{
    protected $table='kirim_barang';
    protected $primaryKey = 'id_pembelian';
}