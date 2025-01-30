<?php

namespace App\Models\DB;

use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    protected $connection = 'mysql2';

    public function targetJob(){
        return $this->belongsTo('App\Models\DB\TargetJob');
    }

    public function company(){
        return $this->belongsTo('App\Models\DB\Company');
    }

    public function projectParticipant(){
        return $this->hasMany('App\Models\DB\ProjectParticipant');
    }

    public function projectCompetenceStandarts(){
        return $this->hasMany('App\Models\DB\ProjectCompetenceStandart');
    }
}
