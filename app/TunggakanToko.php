<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TunggakanToko extends Model
{
    protected $table = "tunggakan_toko";
    public $timestamps = false;
    protected $primaryKey = "REFF";
}
