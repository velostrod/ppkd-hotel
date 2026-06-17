<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HousekeepingRequestItem extends Model
{
    protected $fillable = [
        'housekeeping_request_id',
        'item_name',
        'description',
        'is_done',
    ];

    protected $casts = [
        'is_done' => 'boolean',
    ];

    public function housekeepingRequest(): BelongsTo
    {
        return $this->belongsTo(HousekeepingRequest::class);
    }
}
