<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SupportMessage extends Model
{
    protected $fillable = [
        'support_ticket_id',
        'auteur_type',
        'admin_id',
        'message',
    ];

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(SupportTicket::class, 'support_ticket_id');
    }

    public function estDeLAdmin(): bool
    {
        return $this->auteur_type === 'admin';
    }
}