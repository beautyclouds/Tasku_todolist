<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Inertia\Inertia;

class BordController extends Controller
{
    //
    public function index(){
        return Inertia::render('board/Index');
    }
}
