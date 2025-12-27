<?php
namespace App\Models\Base;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class OrgChartNode extends Model
{
    protected $fillable = [
        'org_position_id',
        'org_unit_id',
        'parent_id',
        'user_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function orgPosition()
    {
        return $this->belongsTo(OrgPosition::class);
    }

    public function orgUnit()
    {
        return $this->belongsTo(OrgUnit::class);
    }

    public function parent()
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function childrenRecursive()
    {
        return $this->children()
            ->with(['user:id,first_name,last_name,personnel_code', 'childrenRecursive', 'orgPosition', 'orgUnit']);
    }

    public function parentRecursive()
    {
        return $this->parent()
            ->with(['user:id,first_name,last_name,personnel_code', 'parentRecursive', 'orgPosition', 'orgUnit']);
    }
}
