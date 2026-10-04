<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Visite extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = ['visiteur', 'chemin', 'type', 'source'];
}