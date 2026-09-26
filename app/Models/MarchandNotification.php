<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MarchandNotification extends Model
{
    protected $fillable = [
        'user_id',
        'type',
        'titre',
        'message',
        'lu_le',
    ];

    protected function casts(): array
    {
        return [
            'lu_le' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function estLue(): bool
    {
        return ! is_null($this->lu_le);
    }

    public function icone(): string
    {
        return match ($this->type) {
            'produit_bloque' => 'block',
            'produit_supprime' => 'delete',
            'produit_reactive' => 'check_circle',
            default => 'notifications',
        };
    }
}