<?php
namespace App\Models\Base;

use Illuminate\Database\Eloquent\Model;

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

    public function workplace()
    {
        return $this->belongsTo(Workplace::class, 'workplace_id', 'rayvarz_id');
    }

    public function workArea()
    {
        return $this->belongsTo(WorkArea::class, 'work_area_id', 'rayvarz_id');
    }

    public function costCenter()
    {
        return $this->belongsTo(CostCenter::class, 'cost_center_id', 'rayvarz_id');
    }

    public function jobPosition()
    {
        return $this->belongsTo(JobPosition::class, 'job_position_id', 'rayvarz_id');
    }
}
