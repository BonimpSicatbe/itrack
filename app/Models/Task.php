<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'status',
        'created_by',
        'updated_by',
    ];

    public function files()
    {
        return $this->hasMany(File::class, 'task_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function target()
    {
        return $this->hasOne(TaskTarget::class);
    }

    // Optional: all users who should get the task (computed via departments/colleges)
    public function users()
    {
        return User::whereHas('department', function ($q) {
            $q->whereIn('id', $this->targets->where('target_type', 'department')->pluck('target_id'));
        })->orWhereHas('college', function ($q) {
            $q->whereIn('id', $this->targets->where('target_type', 'college')->pluck('target_id'));
        });
    }
}
