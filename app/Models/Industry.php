<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Industry extends Model
{
    protected $fillable = ['name', 'description'];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    public function companies()
    {
        return $this->belongsToMany(Company::class);
    }
}
