<?php

namespace App\Http\Controllers\Warehouse;

use App\Http\Controllers\Controller;

class MinMaxController extends Controller
{
    public function index()
    {
        return view('warehouse.stock-control.minmax.index');
    }
}