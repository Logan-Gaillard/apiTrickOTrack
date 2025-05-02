<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Place extends Model
{
    protected $fillable = ['is_alert', 'pos', 'designation', 'is_house', 'is_event', 'adresse', 'id_user'];

    public function user() {
        return $this->belongsTo(User::class, 'id_user');
    }

    public function alerts() {
        return $this->hasMany(Alert::class);
    }

    public function avis() {
        return $this->hasMany(Avis::class);
    }
}