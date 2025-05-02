<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{
    protected $fillable = ['id_emetteur', 'id_recepteur'];

    public function messages() {
        return $this->hasMany(Message::class);
    }
}