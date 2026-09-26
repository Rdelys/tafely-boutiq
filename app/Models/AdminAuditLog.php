<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdminAuditLog extends Model
{
    protected $fillable = [
        'admin_id',
        'user_id',
        'action',
        'details',
    ];

    protected function casts(): array
    {
        return [
            'details' => 'array',
        ];
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(Admin::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function libelle(): string
    {
        return match ($this->action) {
            'suspension' => 'a suspendu le compte',
            'reactivation' => 'a réactivé le compte',
            'prolongation_essai' => 'a prolongé l\'essai',
            'prolongation_abonnement' => 'a prolongé l\'abonnement',
            'limite_produits_personnalisee' => 'a modifié la limite de produits',
            default => $this->action,
        };
    }
}