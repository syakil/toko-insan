<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class Controller extends BaseController
{
public function __construct(){
ini_set('mac_execution_time',30000);
}
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
}
