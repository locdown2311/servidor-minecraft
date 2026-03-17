<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Server extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'plan_id',
        'name',
        'status',
        'port',
        'ftp_port',
        'ftp_user',
        'ftp_password',
        'container_id',
        'ftp_container_id',
        'minecraft_version',
        'server_type',
        'motd',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function isRunning(): bool
    {
        return $this->status === 'running';
    }

    public function isStopped(): bool
    {
        return $this->status === 'stopped';
    }

    public function isExpired(): bool
    {
        return $this->status === 'expired' || ($this->expires_at && $this->expires_at->isPast());
    }

    public function getAddressAttribute(): string
    {
        return config('app.server_ip', '127.0.0.1') . ':' . $this->port;
    }

    public function getFtpAddressAttribute(): string
    {
        return config('app.server_ip', '127.0.0.1') . ':' . $this->ftp_port;
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'running' => 'green',
            'provisioning', 'pending' => 'yellow',
            'stopped' => 'gray',
            'expired' => 'orange',
            'error' => 'red',
            default => 'gray',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'running' => 'Online',
            'provisioning' => 'Provisionando',
            'pending' => 'Pendente',
            'stopped' => 'Parado',
            'expired' => 'Expirado',
            'error' => 'Erro',
            default => $this->status,
        };
    }
}
