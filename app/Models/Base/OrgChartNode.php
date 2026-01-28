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
    ];

    public function users()
    {
        return $this->belongsToMany(
            User::class,
            'org_chart_node_users',
            'org_chart_node_id',
            'user_id'
        )
            // ->wherePivot('role', 'primary')
            ->select('users.id', 'users.first_name', 'users.last_name', 'users.personnel_code');
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
            ->with([
                'users',
                'childrenRecursive',
                'orgPosition',
                'orgUnit',
            ]);
    }

    public function parentRecursive()
    {
        return $this->parent()
            ->with([
                'users',
                'parentRecursive',
                'orgPosition',
                'orgUnit',
            ]);
    }
}
