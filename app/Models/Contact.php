<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['policy_id', 'submission_id', 'outcome', 'note'])]
class Contact extends Model
{
    public function policy(): BelongsTo
    {
        return $this->belongsTo(Policy::class);
    }
}
