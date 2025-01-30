<?php

namespace App\Models\DB;

use Illuminate\Database\Eloquent\Model;

class DbPosition extends Model
{
    protected $connection = 'mysql2';
    protected $fillable = [
        'name'
    ];

    public function participant()
    {
        return $this->hasOne('App\Models\DB\Participant');
    }
}
