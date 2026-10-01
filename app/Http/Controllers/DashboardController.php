<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        // Owner & Admin → Executive Summary
        if ($user->hasAnyRole(['Owner', 'Admin'])) {
            return view('dashboard.owner');
        }

        if ($user->hasRole('Manager')) {
            return view('dashboard.manager');
        }

        if ($user->hasRole('Sales')) {
            return view('dashboard.sales');
        }

        if ($user->hasRole('PPIC')) {
            return view('dashboard.ppic');
        }

        if ($user->hasRole('Purchasing')) {
            return view('dashboard.purchasing');
        }

        if ($user->hasRole('Warehouse')) {
            return view('dashboard.warehouse');
        }

        if ($user->hasRole('Produksi')) {
            return view('dashboard.produksi');
        }

        if ($user->hasRole('QC')) {
            return view('dashboard.qc');
        }

        if ($user->hasRole('Finance')) {
            return view('dashboard.finance');
        }

        // Fallback
        return view('dashboard.default');
    }
}