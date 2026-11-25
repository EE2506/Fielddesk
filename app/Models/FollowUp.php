<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['policy_id', 'contact_id', 'due_at', 'completed_at', 'reason'])]
class FollowUp extends Model
{
    protected function casts(): array
    {
        return ['due_at' => 'immutable_datetime', 'completed_at' => 'immutable_datetime'];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function policy(): BelongsTo
    {
        return $this->belongsTo(Policy::class);
    }
}
