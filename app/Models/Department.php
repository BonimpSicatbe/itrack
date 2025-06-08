<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    protected $fillable = ['name', 'college_id'];

    public function college()
    {
        return $this->belongsTo(College::class);
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function taskTargets()
    {
        return $this->morphMany(TaskTarget::class, 'target', 'target_type', 'target_id');
    }

    public function files()
    {
        return $this->hasMany(File::class);
    }
    public function tasks()
    {
        return $this->hasMany(Task::class);
    }
}
