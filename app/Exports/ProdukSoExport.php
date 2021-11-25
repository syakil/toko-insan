<?php

namespace App\Exports;

use App\ProdukSo;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class ProdukSoExport implements FromView
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function view(): View{
        $data =  ProdukSO::all();

        dd($data);
    }
}