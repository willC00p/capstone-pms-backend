<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class FileController extends Controller
{
    public function serveImage($path)
    {
        $path = 'public/' . $path;
        
        if (!Storage::exists($path)) {
            return response()->json(['error' => 'Image not found'], 404);
        }

        $file = Storage::get($path);
        $type = Storage::mimeType($path);

        $response = response($file, 200)
            ->header('Content-Type', $type)
            ->header('Access-Control-Allow-Origin', '*')
            ->header('Access-Control-Allow-Methods', 'GET, OPTIONS')
            ->header('Access-Control-Allow-Headers', 'Origin, Content-Type, Accept');

        return $response;
    }
}
