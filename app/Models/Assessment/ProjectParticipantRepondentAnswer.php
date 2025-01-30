<?php

namespace App\Models\Assessment;

use Illuminate\Database\Eloquent\Model;

class ProjectParticipantRepondentAnswer extends Model
{
    protected $connection = 'mysql';
    protected $guarded = [];

    public function keyBehavior(){
        return $this->belongsTo('App\Models\Assessment\KeyBehavior');
    }

    public function projectParticipantRepondent(){
        return $this->belongsTo('App\Models\Assessment\ProjectParticipantRepondent');
    }
}
