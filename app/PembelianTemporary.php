<?php
namespace App;
use Illuminate\Database\Eloquent\Model;


class PembelianTemporary extends Model
{
    protected $table = 'pembelian_temporary';
    protected $primaryKey = 'id_pembelian';
    
    protected $fillable = ['status'];
}