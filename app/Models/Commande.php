<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Commande extends Model
{
    public const SOURCE_EN_LIGNE = 'en_ligne';
    public const SOURCE_BOUTIQUE = 'boutique';

    protected $fillable = [
        'user_id',
        'numero',
        'source',
        'nom_client',
        'telephone_client',
        'mode',
        'date_recuperation',
        'heure_recuperation',
        'adresse_livraison',
        'sous_total',
        'prix_livraison',
        'total',
        'mode_paiement',
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
            if (empty($commande->source)) {
                $commande->source = self::SOURCE_EN_LIGNE;
            }

            if (empty($commande->numero)) {
                // TAF-... pour les commandes en ligne, BTQ-... pour les ventes en boutique
                $prefixe = $commande->source === self::SOURCE_BOUTIQUE ? 'BTQ' : 'TAF';
                $commande->numero = $prefixe.'-'.now()->format('ymd').'-'.strtoupper(Str::random(4));
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

    // ---- Scopes ----

    public function scopeEnLigne(Builder $query): Builder
    {
        return $query->where('source', self::SOURCE_EN_LIGNE);
    }

    public function scopeBoutique(Builder $query): Builder
    {
        return $query->where('source', self::SOURCE_BOUTIQUE);
    }

    // ---- Helpers ----

    public function estVenteBoutique(): bool
    {
        return $this->source === self::SOURCE_BOUTIQUE;
    }

    public function estALivrer(): bool
    {
        return $this->mode === 'livrer';
    }

    public function nomClientAffiche(): string
    {
        return $this->nom_client ?: 'Client de passage';
    }

    public function statutLabel(): string
    {
        return match ($this->statut) {
            'en_cours_de_livraison' => 'En cours de livraison',
            'livree' => 'Livrée',
            default => 'À prendre en compte',
        };
    }

    public function modePaiementLabel(): string
    {
        return match ($this->mode_paiement) {
            'especes' => 'Espèces',
            'mvola' => 'MVola',
            'orange_money' => 'Orange Money',
            'autre' => 'Autre',
            default => '—',
        };
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