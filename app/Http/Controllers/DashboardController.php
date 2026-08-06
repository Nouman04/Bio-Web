<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Show the admin dashboard overview.
     */
    public function index()
    {
        return view('dashboard.index');
    }
}
