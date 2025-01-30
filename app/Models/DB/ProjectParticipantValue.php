<?php

namespace App\Models\DB;

use Illuminate\Database\Eloquent\Model;

class ProjectParticipantValue extends Model
{
    protected $connection = 'mysql2';

    public function projectParticipant(){
        return $this->belongsTo('App\Models\DB\ProjectParticipant');
    }

    public function competency(){
        return $this->belongsTo('App\Models\DB\Competency');
    }
}
