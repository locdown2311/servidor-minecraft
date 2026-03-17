<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Plan extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'ram_mb',
        'max_players',
        'price_monthly',
        'description',
        'features',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'features' => 'array',
            'is_active' => 'boolean',
            'price_monthly' => 'decimal:2',
        ];
    }

    public function servers(): HasMany
    {
        return $this->hasMany(Server::class);
    }

    public function getRamLabelAttribute(): string
    {
        return $this->ram_mb >= 1024
            ? round($this->ram_mb / 1024, 1) . ' GB'
            : $this->ram_mb . ' MB';
    }
}
