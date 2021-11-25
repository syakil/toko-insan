<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TabelTransaksi extends Model
{
    protected $table = 'tabel_transaksi_toko';
	protected $primaryKey = 'id_transaksi';
}
