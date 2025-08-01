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

    public function requirements()
    {
        return $this->hasMany(Requirement::class, 'assigned_to', 'name');
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }
}
