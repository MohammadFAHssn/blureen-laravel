<?php
namespace App\Models\Base;

use Illuminate\Database\Eloquent\Model;

class UserProfile extends Model
{
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
