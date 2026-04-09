<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function daily()
    {
        return response()->json(['message' => 'Daily report']);
    }

    public function monthly()
    {
        return response()->json(['message' => 'Monthly report']);
    }

    public function yearly()
    {
        return response()->json(['message' => 'Yearly report']);
    }

    public function custom(Request $request)
    {
        return response()->json(['message' => 'Custom report']);
    }

    public function exportPdf()
    {
        return response()->json(['message' => 'PDF export']);
    }

    public function exportExcel()
    {
        return response()->json(['message' => 'Excel export']);
    }
}
