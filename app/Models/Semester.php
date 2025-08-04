<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Semester extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'start_date',
        'end_date',
        'is_active'
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_active' => 'boolean'
    ];

    public static function getActiveSemester()
    {
        return self::where('is_active', true)->first();
    }

    public static function getPreviousSemester()
    {
        return self::where('is_active', false)
            ->orderBy('end_date', 'desc')
            ->first();
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeDuring($query, $date)
    {
        return $query->where('start_date', '<=', $date)
                    ->where('end_date', '>=', $date);
    }

    public function setActive()
    {
        // Deactivate all semesters first
        self::query()->update(['is_active' => false]);
        
        // Activate this semester
        $this->update(['is_active' => true]);
        
        return $this;
    }
}