<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanyAlias extends Model
{
    protected $fillable = ['company_id', 'name'];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}
