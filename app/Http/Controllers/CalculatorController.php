<?php

namespace App\Http\Controllers;

use App\Models\Port;
use Illuminate\Http\Request;

class CalculatorController extends Controller
{
    public function index()
    {
        $calculator_data = Port::all();

        return $calculator_data;
    }
}
