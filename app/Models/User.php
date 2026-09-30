<?php

namespace App\Models;

use App\Support\Settings;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

class User extends Authenticatable
{
    use Notifiable;
    use HasFactory;

    protected $fillable = [
        'email', 'pseudo', 'nom', 'prenom', 'nom_boutique', 'logo', 'adresse',
        'nif', 'stat', 'status', 'abonnement_expire_le', 'limite_produits_bonus',
        'telephone', 'email_notification', 'email_notification_secondaire',
        'boutique_theme', 'boutique_couleur', 'boutique_couleur_perso', 'boutique_description',
        // Localisation exacte de la boutique (Google Maps)
        'latitude', 'longitude', 'localisation_libelle',
        // Champs gérés par l'admin
        'suspendu', 'suspendu_raison', 'suspendu_le',
        'essai_jusquau', 'limite_produits_personnalisee',
        'boutique_validee_le',
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
            'suspendu' => 'boolean',
            'suspendu_le' => 'datetime',
            'boutique_validee_le' => 'datetime',
            'latitude' => 'float',
            'longitude' => 'float',
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

    // ---- Localisation de la boutique ----

    public function aLocalisation(): bool
    {
        return ! is_null($this->latitude) && ! is_null($this->longitude);
    }

    /**
     * Lien qui ouvre la boutique sur Google Maps (ou l'appli Maps du téléphone).
     */
    public function lienGoogleMaps(): ?string
    {
        if (! $this->aLocalisation()) {
            return null;
        }

        return 'https://www.google.com/maps/search/?api=1&query='.$this->latitude.','.$this->longitude;
    }

    /**
     * Lien qui lance directement l'itinéraire vers la boutique.
     */
    public function lienItineraire(): ?string
    {
        if (! $this->aLocalisation()) {
            return null;
        }

        return 'https://www.google.com/maps/dir/?api=1&destination='.$this->latitude.','.$this->longitude;
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

    /**
     * Temps restant avant la fin de l'abonnement, en mois et jours.
     * Null si l'abonnement n'est pas actif ou n'a pas de date de fin.
     *
     * @return array{mois: int, jours: int}|null
     */
    public function dureeRestanteAbonnement(): ?array
    {
        if (! $this->abonnementActif() || is_null($this->abonnement_expire_le)) {
            return null;
        }

        $ecart = now()->startOfDay()->diff($this->abonnement_expire_le->copy()->startOfDay());

        return [
            'mois' => ($ecart->y * 12) + $ecart->m,
            'jours' => $ecart->d,
        ];
    }

    /**
     * Ex : "1 mois et 20 jours", "3 mois", "12 jours".
     */
    public function dureeRestanteLabel(): ?string
    {
        $duree = $this->dureeRestanteAbonnement();

        if (is_null($duree)) {
            return null;
        }

        $parties = [];

        if ($duree['mois'] > 0) {
            $parties[] = $duree['mois'].' mois';
        }

        if ($duree['jours'] > 0) {
            $parties[] = $duree['jours'].' jour'.($duree['jours'] > 1 ? 's' : '');
        }

        return $parties ? implode(' et ', $parties) : 'moins d\'un jour';
    }

    // ---- Offre de lancement ----

    public function boutiqueValidee(): bool
    {
        return ! is_null($this->boutique_validee_le);
    }

    /**
     * Éligible si le compte fait partie des N premiers inscrits
     * (N réglé dans les Paramètres admin).
     */
    public function estEligibleOffreLancement(): bool
    {
        $places = Settings::offreLancementPlaces();

        if ($places <= 0) {
            return false;
        }

        return static::query()
            ->orderBy('id')
            ->limit($places)
            ->pluck('id')
            ->contains($this->id);
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