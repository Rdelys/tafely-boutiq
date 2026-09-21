<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    protected $model = User::class;

    public function definition(): array
    {
        return [
            'pseudo' => fake()->unique()->userName(),
            'nom' => fake()->lastName(),
            'prenom' => fake()->firstName(),
            'nom_boutique' => fake()->company(),

            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'remember_token' => Str::random(10),

            'logo' => null,
            'adresse' => null,
            'status' => 'test',

            'email_notification' => true,
            'email_notification_secondaire' => null,

            'boutique_theme' => null,
            'boutique_couleur' => 'bleu',
            'boutique_couleur_perso' => null,
            'boutique_description' => null,
        ];
    }
}