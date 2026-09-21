<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Commande extends Model
{
    protected $fillable = [
        'user_id',
        'numero',
        'nom_client',
        'telephone_client',
        'mode',
        'date_recuperation',
        'heure_recuperation',
        'adresse_livraison',
        'sous_total',
        'prix_livraison',
        'total',
        'statut',
        'stock_decremente',
    ];

    protected function casts(): array
    {
        return [
            'sous_total' => 'integer',
            'prix_livraison' => 'integer',
            'total' => 'integer',
            'date_recuperation' => 'date',
            'stock_decremente' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Commande $commande) {
            if (empty($commande->numero)) {
                $commande->numero = 'TAF-'.now()->format('ymd').'-'.strtoupper(Str::random(4));
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function lignes(): HasMany
    {
        return $this->hasMany(CommandeLigne::class);
    }

    public function statutLabel(): string
    {
        return match ($this->statut) {
            'en_cours_de_livraison' => 'En cours de livraison',
            'livree' => 'Livrée',
            default => 'À prendre en compte',
        };
    }

    public function estALivrer(): bool
    {
        return $this->mode === 'livrer';
    }

    public function nombreArticles(): int
    {
        return (int) $this->lignes->sum('quantite');
    }

    public function sousTotalFormate(): string
    {
        return number_format($this->sous_total, 0, ',', ' ').' Ar';
    }

    public function totalFormate(): string
    {
        return number_format($this->total, 0, ',', ' ').' Ar';
    }
}