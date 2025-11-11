<?php

namespace App\Http\Controllers\HSE;

use Throwable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Response;
use Illuminate\Validation\ValidationException;
use App\Services\HSE\HealthCertificateUserService;
use App\Http\Requests\HSE\CreateHealthCertificateUserRequest;

class HealthCertificateUserController
{
    /**
     * @var HealthCertificateUserService
     */
    protected $healthCertificateUserService;

    /**
     * HealthCertificateUserController constructor
     *
     * @param HealthCertificateUserService $healthCertificateUserService
     */
    public function __construct(HealthCertificateUserService $healthCertificateUserService)
    {
        $this->healthCertificateUserService = $healthCertificateUserService;
    }

    /**
     * Store a new HealthCertificateUser
     *
     * @param Request $request
     * @param CreateHealthCertificateUserRequest $request
     * @return JsonResponse
     */
    public function store(CreateHealthCertificateUserRequest $request)
    {
        try {
            $results = [];
            $skipped = [];

            foreach ($request->codes as $code) {
                $res = $this->healthCertificateUserService->createHealthCertificateUser([
                    'code' => $code,
                    'health_certificate_id' => $request->health_certificate_id,
                ]);

                // user not found (skipped)
                if (isset($res['reason'])) {
                    $skipped[] = $res;
                    continue;
                }

                // user exists (already in health certificate) → ignore silently
                if (!$res) {
                    continue;
                }

                // newly created
                $results[] = $res;
            }

            $payload = [
                'created' => $results,
                'skipped' => $skipped,
                'message' => 'لیست کاربران، با موفقیت بروزرسانی شد',
                'status' => 200,
            ];

            return response()->json($payload, $payload['status']);
        } catch (ValidationException $e) {
            $payload = [
                'errors' => $e->errors(),
                'message' => 'اطلاعات وارد شده معتبر نیست.',
                'status' => 422,
                'code' => 'VALIDATION_ERROR',
            ];

            return response()->json($payload, $payload['status']);
        } catch (Throwable $e) {
            $payload = [
                'error' => $e->getMessage(),
                'status' => 500,
            ];

            return response()->json($payload, $payload['status']);
        }
    }

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

        $fileName = basename($filePath);

        // Use response()->download to force attachment with appropriate headers
        // Add explicit content-type (mime_content_type requires fileinfo PHP extension which is present in most PHP installs).
        $mimeType = @mime_content_type($filePath) ?: 'application/octet-stream';

        return response()->download($filePath, $fileName, [
            'Content-Type' => $mimeType,
        ]);
    }
}
