<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;

abstract class Controller
{
    // 让所有控制器可用 $this->authorize() / validate() 等
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
}
