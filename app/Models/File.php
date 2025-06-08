<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class File extends Model
{
    protected $fillable = [
        'filename',
        'type',
        'path',
        'size',
        'status',
        'task_id',
        'college_id',
        'department_id',
        'uploaded_by',
        'archived_at',
    ];

    public function task()
    {
        return $this->belongsTo(Task::class);
    }

    public function college()
    {
        return $this->belongsTo(College::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
