<?php

namespace App\Models\Assessment;

use Illuminate\Database\Eloquent\Model;

class ProjectParticipant extends Model
{
    protected $connection = 'mysql';
    protected $guarded = [];

    public function participant(){
        return $this->belongsTo('App\Models\Assessment\Participant');
    }

    public function projectParticipantStatus(){
        return $this->hasMany('App\Models\Assessment\ProjectParticipantStatus');
    }

    public function project(){
        return $this->belongsTo('App\Models\Assessment\Project');
    }
}
