<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TaskTarget extends Model
{
    protected $fillable = ['task_id', 'target_type', 'target_id'];

    public function task()
    {
        return $this->belongsTo(Task::class);
    }

    public function target()
    {
        return $this->morphTo(null, 'target_type', 'target_id');
    }
}
