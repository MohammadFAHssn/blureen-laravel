<?php
namespace App\Http\Controllers\Base;

use App\Http\Requests\Base\UploadBulkAvatarsRequest;
use App\Services\Base\FileService;

class FileController
{
    protected FileService $fileService;

    public function __construct(FileService $fileService)
    {
        $this->fileService = $fileService;
    }

    public function uploadBulkAvatars(UploadBulkAvatarsRequest $request)
    {
        return response()->json(['data' => $this->fileService->uploadBulkAvatars($request)]);
    }
}
