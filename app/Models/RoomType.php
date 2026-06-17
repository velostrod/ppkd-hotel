<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RoomType extends Model
{
    protected $fillable = [
        'name',
        'description',
        'base_price',
        'capacity',
        'breakfast_included',
        'breakfast_price',
        'extra_bed_allowed',
        'extra_bed_price',
        'is_active',
    ];

    protected $casts = [
        'breakfast_included' => 'boolean',
        'extra_bed_allowed' => 'boolean',
        'is_active' => 'boolean',
        'base_price' => 'decimal:2',
        'breakfast_price' => 'decimal:2',
        'extra_bed_price' => 'decimal:2',
    ];

    public function rooms(): HasMany
    {
        return $this->hasMany(Room::class);
    }
}
