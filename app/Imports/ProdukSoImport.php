<?php

namespace App\Imports;

use App\ProdukSo;
use Maatwebsite\Excel\Concerns\ToModel;

class ProdukSoImport implements ToModel
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        dd($row);
        // return new Siswa([
        //     'nama' => $row[1],
        //     'nis' => $row[2], 
        //     'alamat' => $row[3], 
        // ]);
    }
}