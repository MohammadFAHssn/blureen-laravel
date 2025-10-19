<?php

namespace App\Http\Controllers\HSE;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Response;

class HealthCertificateUserController
{
    /**
     * Get URL for latest image (any format) or image of selected year for HealthCertificateUser
     */
    public function getImage(Request $request): JsonResponse
    {
        $user = Auth::user();
        $personnelCode = $user->personnel_code;
        $year = $request->query('year');

        $basePath = storage_path('app/public/healthCertificates');

        // Determine folder
        if ($year) {
            $folder = $basePath . '/' . $year;
        } else {
            $folders = File::directories($basePath);
            rsort($folders);  // latest year first
            $folder = $folders[0] ?? null;
        }

        if (!$folder || !File::isDirectory($folder)) {
            return response()->json(null, 404);
        }

        // Check for any supported image extension
        $extensions = ['jpg', 'jpeg', 'png'];
        $filePath = null;

        foreach ($extensions as $ext) {
            $path = $folder . '/' . $personnelCode . '.' . $ext;
            if (File::exists($path)) {
                $filePath = $path;
                break;
            }
        }

        if (!$filePath) {
            return response()->json(null, 404);
        }

        // Return public URL
        $fileName = basename($filePath);
        $url = asset('storage/healthCertificates/' . basename($folder) . '/' . $fileName);

        return response()->json($url);
    }

    public function downloadImage(Request $request)
    {
        $user = Auth::user();
        $personnelCode = $user->personnel_code;
        $year = $request->query('year');

        $basePath = storage_path('app/public/healthCertificates');

        // Determine folder
        if ($year) {
            $folder = $basePath . '/' . $year;
        } else {
            $folders = File::directories($basePath);
            rsort($folders);  // latest year first
            $folder = $folders[0] ?? null;
        }

        if (!$folder || !File::isDirectory($folder)) {
            return response()->json(null, 404);
        }

        // Check for any supported image extension
        $extensions = ['jpg', 'jpeg', 'png'];
        $filePath = null;

        foreach ($extensions as $ext) {
            $path = $folder . '/' . $personnelCode . '.' . $ext;
            if (File::exists($path)) {
                $filePath = $path;
                break;
            }
        }

        if (!$filePath) {
            return response()->json(null, 404);
        }

        // Return public URL

        $file = File::get($filePath);
        $type = File::mimeType($filePath);
        $response = Response::make($file, 200);
        $response->header('Content-Type', $type);
        return response()->download($filePath);
    }
}
