<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use App\Models\Concerns\BelongsToOrg;
use Illuminate\Auth\MustVerifyEmail;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail as MustVerifyEmailContract;

class Usuario extends Authenticatable implements MustVerifyEmailContract
{
    use HasApiTokens, HasFactory, Notifiable, HasUuid, MustVerifyEmail;

    protected $table = 'usuarios';
    protected $Keytype = 'string';
    public $incrementing = false;
    protected $fillable = [
        'org_id','rol_id','nombre','email','email_verified_at','password_hash','estado','is_superadmin'
    ];
    protected $hidden = ['password_hash','remember_token'];
    protected $casts = ['email_verified_at'=>'datetime'];
    public $timestamps = true;

    // Laravel espera "password" por defecto:
    public function getAuthPassword()
    {
        return $this->password_hash;
    }

    protected static function booted(): void
    {
        static::creating(function (self $user) {
            if (empty($user->id)) {
                $user->id = (string) Str::uuid();
            }
        });
    }

    public function organizacion()
    {
        return $this->belongsTo(Organizacion::class, 'org_id');
    }

    public function rol()
    {
        return $this->belongsTo(Rol::class, 'rol_id');
    }

    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new \App\Notifications\CustomResetPassword($token));
    }

    public function sendEmailVerificationNotification(): void
    {
        $this->notify(new \App\Notifications\CustomVerifyEmail());
    }

}
