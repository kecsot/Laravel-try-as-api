<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Deck extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
    ];

    public function cards(): HasMany
    {
        return $this->hasMany(Card::class);
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
