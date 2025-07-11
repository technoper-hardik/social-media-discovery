<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SocialAccount extends Model
{
    protected $fillable = [
        'handle_id',
        'platform',
        'url',
        'name',
        'bio',
        'profile_image',
        'verified',
        'verification_type',
        'official_account',
        'account_type',
    ];

    protected $casts = [
        'verified' => 'boolean',
        'official_account' => 'boolean',
    ];

    public function handle(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Handle::class);
    }
}
