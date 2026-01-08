<?php
namespace App\Models\Base;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserProfile extends Model
{
    protected $fillable = [
        'user_id',
        'national_code',
        'gender',
        'father_name',
        'birth_place',
        'birth_date',
        'mobile_number',
        'marital_status',
        'employment_date',
        'start_date',
        'education_level_id',
        'workplace_id',
        'work_area_id',
        'cost_center_id',
        'job_position_id',
    ];

    public function educationLevel()
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
