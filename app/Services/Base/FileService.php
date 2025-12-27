<?php
namespace App\Services\Base;

use App\Exceptions\CustomException;
use App\Models\Base\File;
use App\Models\User;
use App\Repositories\Base\FileRepository;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FileService
{
    protected FileRepository $fileRepository;

    protected array $config = [
        'max_file_size' => 2097152, // 2MB
        'allowed_extensions' => [],
        'allowed_mime_types' => [],
    ];

    //  if changed, sync this with UploadBulkAvatarsRequest rules
    protected array $collections = [
        'avatar' => [
            'max_file_size' => 2097152, // 2MB
            'allowed_extensions' => ['jpg', 'jpeg', 'png'],
            'allowed_mime_types' => ['image/jpeg', 'image/png'],
            'disk' => 'public',
            'visibility' => 'public',
            'single' => true,
        ],
        'default' => [
            'max_file_size' => 2097152, // 2MB
            'allowed_extensions' => [],
            'allowed_mime_types' => [],
            'disk' => 'local',
            'visibility' => 'private',
            'single' => true,
        ],
    ];

    public function __construct(FileRepository $fileRepository)
    {
        $this->fileRepository = $fileRepository;
    }

    public function upload(UploadedFile $uploadedFile, string $collection = 'default', ?Model $fileable = null): File
    {
        $collectionConfig = $this->getCollectionConfig($collection);

        $this->validateFile($uploadedFile, $collectionConfig);

        // if the collection allows only a single file, delete existing files
        if ($fileable && ($collectionConfig['single'] ?? false)) {
            $this->deleteExistingFiles($fileable, $collection);
        }

        $storedName = $this->generateUniqueFileName($uploadedFile);

        $path = $this->generatePath($collection, $storedName);

        $disk = $collectionConfig['disk'] ?? 'local';
        Storage::disk($disk)->put($path, file_get_contents($uploadedFile));

        $metadata = $this->extractMetadata($uploadedFile);

        $fileData = [
            'original_name' => $uploadedFile->getClientOriginalName(),
            'stored_name' => $storedName,
            'path' => $path,
            'disk' => $disk,
            'mime_type' => $uploadedFile->getMimeType(),
            'extension' => strtolower($uploadedFile->getClientOriginalExtension()),
            'size' => $uploadedFile->getSize(),
            'collection' => $collection,
            'visibility' => $collectionConfig['visibility'] ?? 'private',
            'uploaded_by' => Auth::id(),
            'metadata' => $metadata,
        ];

        if ($fileable) {
            $fileData['fileable_type'] = get_class($fileable);
            $fileData['fileable_id'] = $fileable->getKey();
        }

        return File::create($fileData);
    }

    protected function validateFile(UploadedFile $file, array $config): void
    {
        // check file size
        $maxSize = $config['max_file_size'] ?? $this->config['max_file_size'];
        if ($file->getSize() > $maxSize) {
            $maxSizeMB = $maxSize / 1048576;
            throw new CustomException("حداکثر حجم مجاز فایل {$maxSizeMB} مگابایت است.", 422);
        }

        // check file extension
        $extension = strtolower($file->getClientOriginalExtension());
        $allowedExtensions = $config['allowed_extensions'] ?? $this->config['allowed_extensions'];
        if (!empty($allowedExtensions) && !in_array($extension, $allowedExtensions)) {
            $allowed = implode(', ', $allowedExtensions);
            throw new CustomException("پسوند فایل مجاز نیست. پسوندهای مجاز: {$allowed}", 422);
        }

        // check mime type
        $mimeType = $file->getMimeType();
        $allowedMimeTypes = $config['allowed_mime_types'] ?? $this->config['allowed_mime_types'];
        if (!empty($allowedMimeTypes) && !in_array($mimeType, $allowedMimeTypes)) {
            throw new CustomException('نوع فایل مجاز نیست.', 422);
        }
    }

    protected function generateUniqueFileName(UploadedFile $file): string
    {
        $extension = strtolower($file->getClientOriginalExtension());

        return Str::uuid() . '.' . $extension;
    }

    protected function generatePath(string $collection, string $fileName): string
    {
        $datePath = now()->format('Y/m');

        return "files/{$collection}/{$datePath}/{$fileName}";
    }

    protected function extractMetadata(UploadedFile $file): array
    {
        $metadata = [];

        // If the file is an image
        if (str_starts_with($file->getMimeType(), 'image/')) {
            // $imageSize = @getimagesize($file->path());
            // if ($imageSize) {
            //     $metadata['width'] = $imageSize[0];
            //     $metadata['height'] = $imageSize[1];
            // }
        }

        return $metadata;
    }

    protected function getCollectionConfig(string $collection): array
    {
        return $this->collections[$collection] ?? $this->collections['default'];
    }

    protected function deleteExistingFiles(Model $fileable, string $collection): void
    {
        $existingFiles = $this->fileRepository->getByFileable(
            get_class($fileable),
            $fileable->getKey(),
            $collection
        );

        foreach ($existingFiles as $file) {
            $this->fileRepository->forceDelete($file);
        }
    }

    public function uploadBulkAvatars($request): array
    {
        $uploadedFiles = $request->file('files');

        $results = [
            'total' => count($uploadedFiles),
            'success_count' => 0,
            'success' => [],
            'failed_count' => 0,
            'failed' => [],
        ];

        foreach ($uploadedFiles as $uploadedFile) {
            $originalName = $uploadedFile->getClientOriginalName();
            $personnelCode = pathinfo($originalName, PATHINFO_FILENAME);

            try {
                $user = User::where('personnel_code', $personnelCode)->first();

                if (!$user) {
                    $results['failed'][] = [
                        'file' => $originalName,
                        'reason' => "کاربر با کد پرسنلی {$personnelCode} یافت نشد.",
                    ];
                    $results['failed_count']++;

                    continue;
                }

                $this->upload($uploadedFile, 'avatar', $user);

                $results['success'][] = [
                    'file' => $originalName,
                ];
                $results['success_count']++;
            } catch (\Exception $e) {
                $results['failed'][] = [
                    'file' => $originalName,
                    'reason' => $e->getMessage(),
                ];
                $results['failed_count']++;
            }
        }

        return $results;
    }
}
