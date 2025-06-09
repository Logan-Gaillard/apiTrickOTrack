<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable;

    protected $fillable = [
        'nickname',
        'email',
        'password',
        'is_admin',
        'latitude',
        'longitude',
        'last_update',
        'last_connexion',
        'date_last_position',
        'last_activity',
        'created_at',
        'updated_at',
    ];

    protected $hidden = [
        'password'
    ];

    public function places() {
        return $this->hasMany(Place::class, 'id_user');
    }

    public function alerts() {
        return $this->hasMany(Alert::class);
    }

    public function avis() {
        return $this->hasMany(Avis::class);
    }

    public function contactsEmis() {
        return $this->hasMany(Contact::class, 'id_emetteur');
    }

    public function contactsRecus() {
        return $this->hasMany(Contact::class, 'id_recepteur');
    }
}