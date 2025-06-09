<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AboutController extends Controller
{
    public function index()
    {
        $partners = [1, 2, 3, 4, 5, 6]; // Partner image IDs
        
        return view('about', compact('partners'));
    }
}
