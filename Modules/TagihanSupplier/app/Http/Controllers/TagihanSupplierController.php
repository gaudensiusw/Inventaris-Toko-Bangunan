<?php

namespace Modules\TagihanSupplier\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class TagihanSupplierController extends Controller
{
    public function index()
    {
        return view('tagihansupplier::index');
    }
}
