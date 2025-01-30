<?php

namespace App\Models\DB;

use Illuminate\Database\Eloquent\Model;

class TargetJob extends Model
{
    protected $connection = 'mysql2';
    protected $guarded = [];

    public function targetJobCompetency()
    {
        return $this->hasMany('App\Models\DB\TargetJobCompetency');
    }

    public function project(){
        return $this->hasMany('App\Models\DB\Project');
    }
}
