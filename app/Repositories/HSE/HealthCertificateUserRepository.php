<?php

namespace App\Repositories\HSE;

use App\Models\HSE\HealthCertificateUser;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class HealthCertificateUserRepository
{
    /**
     * Create or replace a HealthCertificateUser's image for a given user.
     */
    public function createOrReplaceForUser(
        int $healthCertificateId,
        int $userId,
        int $year,
        UploadedFile $file
    ): HealthCertificateUser {
        // Store new image
        $path = $file->store("healthCertificates/{$year}", 'public');

        // Find existing record for this health certificate + user
        $existing = HealthCertificateUser::where('health_certificate_id', $healthCertificateId)
            ->where('user_id', $userId)
            ->first();

        if ($existing) {
            // Delete old image if exists
            if ($existing->image && Storage::disk('public')->exists($existing->image)) {
                Storage::disk('public')->delete($existing->image);
            }

            $existing->update([
                'edited_by' => Auth::id(),
                'status' => 1,
                'image' => $path,
            ]);

            return $existing->fresh();
        }

        // Create new record
        return HealthCertificateUser::create([
            'health_certificate_id' => $healthCertificateId,
            'user_id' => $userId,
            'uploaded_by' => Auth::id(),
            'status' => 1,
            'image' => $path,
        ]);
    }
}
