<?php

namespace App\Models\DB;

use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    protected $connection = 'mysql2';

    public function participant()
    {
        return $this->hasMany('App\Models\DB\Participant');
    }

    public function project(){
        return $this->hasMany('App\Models\DB\Project');
    }
}
