<?php

namespace App\Models\Assessment;

use Illuminate\Database\Eloquent\Model;

class ProjectParticipantRepondent extends Model
{
    protected $connection = 'mysql';
    protected $guarded = [];

    public function projectParticipant(){
        return $this->belongsTo('App\Models\Assessment\ProjectParticipant');
    }
}
