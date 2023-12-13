<?php

namespace App\Models\OldModels;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubgroupMonitorDate extends Model
{
    use HasFactory;

    protected $table = 'subgroups_monitor_dates';

    protected $connection = 'old';

protected $fillable = [
        'monitor_id',
        'date',
        'hour',
        'subgroup_id',
    ];

    public function monitor()
    {
        return $this->belongsTo(User::class, 'monitor_id');
    }

    public function subgroup()
    {
        return $this->belongsTo(CourseGroupsSubgroups2::class, 'subgroup_id');
    }

    public function toArray()
    {
        $subgroupArray = [
            'id' => $this->id,
            'date' => $this->date,
            'hour' => $this->hour,
            'monitor_id' => $this->monitor_id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at
        ];


        $subgroupArray['monitor'] = $this->monitor;
        $subgroupArray['monitor']['image'] = $this->monitor ? $this->monitor->getImageUrl() : '';


        return $subgroupArray;
    }

    public function scopeBySubgroupId($query, $subgroupId)
    {
        if ($subgroupId !== null) {
            return $query->where('subgroup_id', $subgroupId);
        }
        return $query;
    }

    public function scopeByDate($query, $date)
    {
        if ($date !== null) {
            return $query->whereDate('date', $date);
        }
        return $query;
    }

    public function scopeByHour($query, $hour)
    {
        if ($hour !== null) {
            return $query->where('hour', $hour);
        }
        return $query;
    }
}
