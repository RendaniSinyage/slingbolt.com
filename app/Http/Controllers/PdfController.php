<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Browsershot\Browsershot;

class PdfController extends Controller
{
    public function generatePdf(Request $request)
    {
        $url = $request->input('url');

        if (!$url) {
            return response()->json(['error' => 'URL is required'], 400);
        }

        $pdf = Browsershot::url($url)->pdf();

        return response($pdf)
            ->header('Content-Type', 'application/pdf');
    }
}
