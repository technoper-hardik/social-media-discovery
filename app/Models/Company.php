<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    protected $fillable = [
        'parent_id',
        'name',
        'website',
        'status',
        'description',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    public function handles()
    {
        return $this->hasMany(Handle::class);
    }

    public function brands()
    {
        return $this->hasMany(Brand::class);
    }

    public function companySubsidiaries()
    {
        return $this
            ->hasMany(Company::class, 'parent_id', 'id')
            ->with([
                'companySubsidiaries',
                'industries',
                'aliases',
                'categories',
                'brands',
                'products',
            ]);
    }

    public function subsidiaries()
    {
        return $this->hasMany(Company::class, 'parent_id', 'id');
    }

    public function aliases()
    {
        return $this->hasMany(CompanyAlias::class);
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class, CompanyCategory::class);
    }

    public function industries()
    {
        return $this->belongsToMany(Industry::class);
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }
}
