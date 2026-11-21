<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderStatusLog extends Model
{
    protected $fillable = ['user_id', 'status', 'catatan'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
