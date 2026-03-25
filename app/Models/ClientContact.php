<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClientContact extends Model
{
    protected $fillable = [
        'client_id', 'name', 'email', 'phone', 'position', 'is_primary'
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }
}
