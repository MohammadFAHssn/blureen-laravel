<?php

namespace App\Http\Controllers\HSE;

use App\Models\HSE\HealthCertificateUser;
use App\Models\HSE\HealthCertificate;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
class HealthCertificateUserController
{
    public function getImage(Request $request)
    {
        $user = Auth::user();
        $year = $request->query('year');
        $healthCertificate = HealthCertificate::where('year', $year)->first();
        $healthCertificateUser = HealthCertificateUser::where('user_id', $user->id)->where('health_certificate_id', $healthCertificate->id)->first();
        return $healthCertificateUser->image;
    }

    public function downloadImage(Request $request)
    {
        $user = Auth::user();
        $year = $request->query('year');

        $record = HealthCertificateUser::query()
            ->join('health_certificates as hc', 'hc.id', '=', 'health_certificates_users.health_certificate_id')
            ->where('health_certificates_users.user_id', $user->id)
            ->when($year, fn($q) => $q->where('hc.year', $year))
            ->orderByDesc('hc.year')
            ->orderByDesc('health_certificates_users.updated_at')
            ->select('health_certificates_users.*', 'hc.year')
            ->first();

        if (!$record || empty($record->image)) {
            return response()->json(null, 404);
        }

        $relative = $record->image;  // relative to 'public' disk
        if (!Storage::disk('public')->exists($relative)) {
            return response()->json(null, 404);
        }

        // Absolute filesystem path for response()->download()
        $absolute = Storage::disk('public')->path($relative);
        $downloadName = basename($relative);
        $mime = @mime_content_type($absolute) ?: 'application/octet-stream';

        return response()->download($absolute, $downloadName, [
            'Content-Type' => $mime,
        ]);
    }
}
