<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SupportTicket extends Model
{
    protected $fillable = [
        'user_id',
        'sujet',
        'statut',
        'nouveau_pour_admin',
        'nouveau_pour_marchand',
    ];

    protected function casts(): array
    {
        return [
            'nouveau_pour_admin' => 'boolean',
            'nouveau_pour_marchand' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(SupportMessage::class)->orderBy('created_at');
    }

    public function estOuvert(): bool
    {
        return $this->statut === 'ouvert';
    }

    public function marquerLuAdmin(): void
    {
        if ($this->nouveau_pour_admin) {
            $this->update(['nouveau_pour_admin' => false]);
        }
    }

    public function marquerLuMarchand(): void
    {
        if ($this->nouveau_pour_marchand) {
            $this->update(['nouveau_pour_marchand' => false]);
        }
    }
}