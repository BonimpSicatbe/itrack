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

    public function requirements()
    {
        return $this->hasMany(Requirement::class, 'assigned_to', 'name');
    }

    public function departments()
    {
        return $this->hasMany(Department::class);
    }
}
