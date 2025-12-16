<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ReportController extends Controller
{
    /**
     * Display a listing of available reports.
     */
    public function index()
    {
        return view('reports.index');
    }

    /**
     * Generate PDF report.
     */
    public function generatePDF(Request $request)
    {
        // Placeholder for future report generation
        // This will be implemented when reservation and shift reports are ready
        return redirect()->route('reports.index')
            ->with('info', 'Los reportes estarán disponibles próximamente.');
    }
}
