<?php

namespace App\Exports;
use Maatwebsite\Excel\Concerns\FromCollection;
use App\Pabrik;
use App\Member;
use App\ListToko;
use DB;
use Auth;


class ReportBank implements FromCollection
{
    public function collection()
    {
        return D::all();
    }
}