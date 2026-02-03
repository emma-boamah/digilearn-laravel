<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ComingSoonController extends Controller
{
    /**
     * Show the coming soon page
     */
    public function index()
    {
        return view('coming-soon');
    }
}
