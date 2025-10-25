<?php
namespace App\Models\Base;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserProfile extends Model
{
    public function educationLevel(): BelongsTo
    {
        return $this->belongsTo(EducationLevel::class, 'education_level_id', 'rayvarz_id');
    }

    public function workplace(): BelongsTo
    {
        return $this->belongsTo(Workplace::class, 'workplace_id', 'rayvarz_id');
    }

    public function workArea(): BelongsTo
    {
        return $this->belongsTo(WorkArea::class, 'work_area_id', 'rayvarz_id');
    }

    public function costCenter(): BelongsTo
    {
        return $this->belongsTo(CostCenter::class, 'cost_center_id', 'rayvarz_id');
    }

    public function jobPosition(): BelongsTo
    {
        return $this->belongsTo(JobPosition::class, 'job_position_id', 'rayvarz_id');
    }
}
