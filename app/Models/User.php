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
        'email',
        'pseudo',
        'nom',
        'prenom',
        'nom_boutique',
        'logo',
        'adresse',
        'nif',
        'stat',
        'status',
        'telephone',
        'email_notification',
        'email_notification_secondaire',
        'boutique_theme',
        'boutique_couleur',
        'boutique_couleur_perso',
        'boutique_description',
    ];

    protected $hidden = [
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
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
        return match ($this->status) {
            'active' => 'Actif payant',
            'test' => 'Test',
            default => 'Gratuit',
        };
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
}