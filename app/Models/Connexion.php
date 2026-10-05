<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Connexion extends Model
{
    public const UPDATED_AT = null;

    protected $table = 'connexions';

    protected $fillable = ['user_id', 'ip', 'source', 'nouveau_compte'];

    protected function casts(): array
    {
        return ['nouveau_compte' => 'boolean'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}