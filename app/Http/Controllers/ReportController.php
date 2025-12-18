<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function index()
    {
        // Mengembalikan view 'qc_report'
        return view('qc_report');
    }

    public function showDashboard()
    {
        return view('inspector.dashboard');
    }

    public function create()
    {
        return view('qc_report');
    }

    public function store(Request $request)
    {
        // Logika penyimpanan data (placeholder)
        return redirect()->back()->with('success', 'Data stored');
    }
}