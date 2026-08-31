<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Produit extends Model
{
    protected $table = 'produits';

    protected $fillable = [
        'user_id',
        'nom',
        'description',
        'prix',
        'image',
        'stock',
        'livraison',
        'prix_livraison',
    ];

    protected function casts(): array
    {
        return [
            'prix' => 'integer',
            'prix_livraison' => 'integer',
            'stock' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function aLivraison(): bool
    {
        return $this->livraison === 'payante';
    }

    public function prixFormate(): string
    {
        return number_format($this->prix, 0, ',', ' ').' Ar';
    }
}