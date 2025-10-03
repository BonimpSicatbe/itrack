<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RequirementType extends Model
{
    protected $fillable = ['name', 'parent_id', 'is_folder'];

    public function parent()
    {
        return $this->belongsTo(RequirementType::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(RequirementType::class, 'parent_id');
    }
}
