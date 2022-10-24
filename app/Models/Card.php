<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Card extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'deck_id',
    ];

    public function deck(): BelongsTo
    {
        return $this->belongsTo(Deck::class);
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
