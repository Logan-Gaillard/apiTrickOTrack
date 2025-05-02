<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    protected $fillable = ['content', 'contact_id'];

    public function contact() {
        return $this->belongsTo(Contact::class);
    }
}