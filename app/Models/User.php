<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, SoftDeletes;

    protected $table = 'usuarios';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'rol_id',
        'nombre',
        'email',
        'dni',
        'password',
        'debe_cambiar_password',
        'revoked_permissions',
        'estado',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'estado' => 'boolean',
            'debe_cambiar_password' => 'boolean',
            'revoked_permissions' => 'array',
            'password' => 'hashed',
        ];
    }

    /**
     * Relación con role
     */
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'rol_id');
    }

    /**
     * Relación muchos a muchos con permisos
     */
    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'user_permission', 'user_id', 'permission_id');
    }

    /**
     * Obtener todos los permisos del usuario (rol + individuales - revocados)
     */
    public function getAllPermissions()
    {
        // Permisos revocados
        $revokedIds = $this->revoked_permissions ?? [];
        
        // Permisos del rol
        $rolePermissions = $this->role->permissions ?? collect();
        
        // Permisos individuales del usuario
        $userPermissions = $this->permissions;
        
        // Combinar y eliminar duplicados
        $allPermissions = $rolePermissions->merge($userPermissions)->unique('id');
        
        // Filtrar permisos revocados
        return $allPermissions->filter(function($permission) use ($revokedIds) {
            return !in_array($permission->id, $revokedIds);
        })->values();
    }

    /**
     * Verificar si el usuario tiene un permiso
     */
    public function hasPermission($permission): bool
    {
        // Obtener permisos revocados
        $revokedIds = $this->revoked_permissions ?? [];
        
        // Buscar el permiso
        if (is_string($permission)) {
            $permissionObj = Permission::where('slug', $permission)->first();
            if (!$permissionObj) return false;
            
            // Si está revocado, retornar false
            if (in_array($permissionObj->id, $revokedIds)) {
                return false;
            }
            
            return $this->getAllPermissions()->where('slug', $permission)->isNotEmpty();
        }
        
        // Si está revocado, retornar false
        if (in_array($permission->id, $revokedIds)) {
            return false;
        }
        
        return $this->getAllPermissions()->where('id', $permission->id)->isNotEmpty();
    }
}
