<?php

namespace App\Models\DB;

use Illuminate\Database\Eloquent\Model;

class ProjectParticipant extends Model
{
    protected $connection = 'mysql2';
    protected $table = "project_participations";

    public function participant()
    {
        return $this->belongsTo('App\Models\DB\Participant');
    }

    public function project()
    {
        return $this->belongsTo('App\Models\DB\Project');
    }

    public function projectParticipantValues(){
        return $this->hasMany('App\Models\DB\ProjectParticipantValue');
    }

    public function itemRecommendation(){
        return $this->belongsTo('App\Models\DB\ItemRecommendation');
    }
}
