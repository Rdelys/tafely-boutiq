<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
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
    ];
}

    protected static function booted(): void
    {
        // Génère/actualise automatiquement un slug lisible à partir du nom de
        // la boutique (ex : "Varotry Boutique" -> "varotry-boutique"), tant
        // que le marchand n'a pas choisi un pseudo personnalisé. Le lien
        // public (/b/{slug}) reste donc lisible sans action de sa part.
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

    // "Test" ou "Actif payant" affiché dans le layout connecté
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

    // Identifiant utilisé dans le lien public : pseudo choisi par le
    // marchand, sinon slug auto-généré depuis le nom de la boutique,
    // sinon l'id en tout dernier recours.
    public function identifiantBoutique(): string
    {
        return $this->pseudo ?: ($this->slug ?: (string) $this->id);
    }

    // Lien public de la vitrine à partager (route "vitrine").
    public function lienBoutique(): string
    {
        return url('/b/'.$this->identifiantBoutique());
    }

    // Couleur d'accent réellement utilisée sur la vitrine : la couleur
    // personnalisée (code hex) si choisie, sinon la couleur du preset.
    public function couleurBoutique(): string
    {
        if ($this->boutique_couleur === 'perso' && $this->boutique_couleur_perso) {
            return $this->boutique_couleur_perso;
        }

        return \App\Http\Controllers\BoutiqueController::COULEURS[$this->boutique_couleur]
            ?? \App\Http\Controllers\BoutiqueController::COULEURS['bleu'];
    }

    // Essai gratuit de 30 jours à partir de la création du compte.
public function finEssaiLe(): \Carbon\Carbon
{
    return $this->created_at->copy()->addDays(30);
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

// Abonnement payant actif (indépendamment de la valeur brute de "status",
// au cas où la date d'expiration serait dépassée sans tâche planifiée).
public function abonnementActif(): bool
{
    if ($this->status !== 'active') {
        return false;
    }

    return is_null($this->abonnement_expire_le) || $this->abonnement_expire_le->isFuture();
}

public function limiteProduits(): int
{
    $base = $this->abonnementActif() ? 30 : 10;

    return $base + (int) $this->limite_produits_bonus;
}


}