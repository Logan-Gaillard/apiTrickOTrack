<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Avis extends Model
{
    protected $fillable = ['status', 'commentaire', 'user_id', 'place_id', 'alert_id'];

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function place() {
        return $this->belongsTo(Place::class);
    }

    public function alert() {
        return $this->belongsTo(Alert::class);
    }
}