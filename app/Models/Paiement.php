<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Paiement extends Model
{
    protected $fillable = [
        'user_id', 'type', 'reference', 'montant', 'statut',
        'papi_transaction_id', 'papi_payment_method', 'meta', 'paye_le',
    ];

    protected function casts(): array
    {
        return [
            'montant' => 'integer',
            'meta' => 'array',
            'paye_le' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function estPaye(): bool
    {
        return $this->statut === 'paye';
    }

    public function montantFormate(): string
    {
        return number_format($this->montant, 0, ',', ' ').' Ar';
    }
}