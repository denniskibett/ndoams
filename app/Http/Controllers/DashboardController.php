<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Marriage;

class DashboardController extends Controller
{
    public function index()
    {
        $recentMarriages = Marriage::latest()->take(5)->get();
        return view('dashboard', compact('recentMarriages'));
    }
}