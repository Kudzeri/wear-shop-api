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
        'street',
        'house',
        'postal_code',
        'apartment'
    ];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'address_users')->withTimestamps();
    }

    public function getFullAddressAttribute(): string
    {
        return collect([
            $this->city,
            $this->street ? "ул. {$this->street}" : null,
            $this->house ? "д. {$this->house}" : null,
            $this->apartment ? "кв. {$this->apartment}" : null
        ])->filter()->join(', ');
    }
}
