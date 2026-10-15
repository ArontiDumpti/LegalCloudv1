<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    public const ROLES = [
        'administrador' => 'Administrador',
        'responsable_documental' => 'Responsable documental',
        'especialista' => 'Especialista / Analista',
        'consultor' => 'Usuario consultor',
    ];

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'cargo',
        'activo',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'activo' => 'boolean',
        ];
    }

    public function rolNombre(): string
    {
        return self::ROLES[$this->role] ?? $this->role;
    }

    public function esConsultor(): bool
    {
        return $this->role === 'consultor';
    }

    public function puedeVerUsuariosYAuditoria(): bool
    {
        return ! $this->esConsultor();
    }

    public function expedientesResponsable(): HasMany
    {
        return $this->hasMany(Expediente::class, 'responsable_id');
    }

    public function documentos(): HasMany
    {
        return $this->hasMany(Documento::class, 'subido_por');
    }

    public function consultas(): HasMany
    {
        return $this->hasMany(ConsultaRag::class);
    }

    public function notificaciones(): HasMany
    {
        return $this->hasMany(Notificacion::class);
    }
}
