<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class User extends Authenticatable
{
    use Notifiable;use HasFactory;

    protected $fillable = [
    'email', 'pseudo', 'nom', 'prenom', 'nom_boutique', 'logo', 'adresse',
    'nif', 'stat', 'status', 'abonnement_expire_le', 'limite_produits_bonus',
    'telephone', 'email_notification', 'email_notification_secondaire',
    'boutique_theme', 'boutique_couleur', 'boutique_couleur_perso', 'boutique_description',
];

    protected $hidden = [
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'abonnement_expire_le' => 'date',
            'essai_jusquau' => 'date',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (User $user) {
            if (! empty($user->pseudo)) {
                return;
            }

            if (! $user->isDirty('nom_boutique') && ! empty($user->slug)) {
                return;
            }

            $base = Str::slug($user->nom_boutique ?? '') ?: 'boutique';
            $slug = $base;
            $i = 1;

            while (
                static::where('slug', $slug)
                    ->when($user->exists, fn ($q) => $q->where('id', '!=', $user->id))
                    ->exists()
            ) {
                $slug = $base.'-'.$i++;
            }

            $user->slug = $slug;
        });
    }

    public function statusLabel(): string
{
    if ($this->abonnementActif()) {
        return 'Actif payant';
    }

    return $this->essaiExpire() ? 'Essai expiré' : 'Gratuit (essai)';
}

    public function hasPseudo(): bool
    {
        return ! empty($this->pseudo);
    }

    public function produits(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Produit::class);
    }

    public function commandes(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Commande::class);
    }

    public function paiements(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Paiement::class);
    }

    public function identifiantBoutique(): string
    {
        return $this->pseudo ?: ($this->slug ?: (string) $this->id);
    }

    public function lienBoutique(): string
    {
        return url('/b/'.$this->identifiantBoutique());
    }

    public function couleurBoutique(): string
    {
        if ($this->boutique_couleur === 'perso' && $this->boutique_couleur_perso) {
            return $this->boutique_couleur_perso;
        }

        return \App\Http\Controllers\BoutiqueController::COULEURS[$this->boutique_couleur]
            ?? \App\Http\Controllers\BoutiqueController::COULEURS['bleu'];
    }

    public function finEssaiLe(): \Carbon\Carbon
    {
        $base = $this->created_at->copy()->addDays(30);

        if ($this->essai_jusquau && $this->essai_jusquau->greaterThan($base)) {
            return $this->essai_jusquau->copy();
        }

        return $base;
    }


public function essaiExpire(): bool
{
    return ! $this->abonnementActif() && now()->greaterThan($this->finEssaiLe());
}

public function joursRestantsEssai(): int
{
    if ($this->abonnementActif()) {
        return 0;
    }

    return max(0, (int) now()->diffInDays($this->finEssaiLe(), false));
}

public function abonnementActif(): bool
{
    if ($this->status !== 'active') {
        return false;
    }

    return is_null($this->abonnement_expire_le) || $this->abonnement_expire_le->isFuture();
}

    public function limiteProduits(): int
    {
        if (! is_null($this->limite_produits_personnalisee)) {
            return (int) $this->limite_produits_personnalisee;
        }

        $base = $this->abonnementActif() ? 30 : 10;

        return $base + (int) $this->limite_produits_bonus;
    }

    public function estSuspendu(): bool
    {
        return (bool) $this->suspendu;
    }

    // ---- Scopes pour le filtrage admin (calculés en SQL, pas en PHP) ----

    public function scopeAbonnementActif(Builder $query): Builder
    {
        return $query->where('status', 'active')
            ->where(function ($q) {
                $q->whereNull('abonnement_expire_le')
                  ->orWhere('abonnement_expire_le', '>=', now()->toDateString());
            });
    }

    public function scopeAbonnementInactif(Builder $query): Builder
    {
        return $query->where(function ($q) {
            $q->where('status', '!=', 'active')
              ->orWhere(function ($q2) {
                  $q2->where('status', 'active')
                     ->whereNotNull('abonnement_expire_le')
                     ->where('abonnement_expire_le', '<', now()->toDateString());
              });
        });
    }

    public function scopeEssaiExpire(Builder $query): Builder
    {
        return $query->abonnementInactif()->where('created_at', '<=', now()->subDays(30));
    }

    public function scopeEnEssai(Builder $query): Builder
    {
        return $query->abonnementInactif()->where('created_at', '>', now()->subDays(30));
    }

    public function notificationsMarchand(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(MarchandNotification::class);
    }

    public function supportTickets(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(SupportTicket::class);
    }
}