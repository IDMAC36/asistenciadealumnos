<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PermissionRequest extends Model
{
    protected $fillable = [
        'nombre',
        'grado',
        'nivel',
        'motivo',
        'quien_solicita',
        'por_via',
        'estado',
        'created_by',
        'accepted_at',
        'accepted_by',
    ];

    protected function casts(): array
    {
        return [
            'accepted_at' => 'datetime',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function acceptor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'accepted_by');
    }

    public function scopePendiente($query)
    {
        return $query->where('estado', 'pendiente');
    }

    public function scopeAceptado($query)
    {
        return $query->where('estado', 'aceptado');
    }
}
