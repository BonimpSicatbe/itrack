<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class College extends Model
{
    protected $fillable = ['name', 'acronym'];

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function departments()
    {
        return $this->hasMany(Department::class);
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
