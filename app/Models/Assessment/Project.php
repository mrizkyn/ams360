<?php

namespace App\Models\Assessment;

use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    protected $connection = 'mysql';
    protected $guarded = [];

    public function company(){
        return $this->belongsTo('App\Models\Assessment\Company');
    }

    public function position(){
        return $this->belongsTo('App\Models\Assessment\Position');
    }

    public function projectParticipants(){
        return $this->hasMany('App\Models\Assessment\ProjectParticipant');
    }

    public function projectQuestions(){
        return $this->hasMany('App\Models\Assessment\ProjectQuestion');
    }
}
