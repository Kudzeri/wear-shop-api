<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Address extends Model
{
    use HasFactory;
    protected $fillable = [
        'is_primary',
        'state',
        'city',
        'postal_code',
        'apartment'
    ];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'address_users')->withTimestamps();
    }
}
