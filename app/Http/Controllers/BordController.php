<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class BordController extends Controller
{
    //
    public function index(){
        return Inertia::render('board/Index');
    }
}
