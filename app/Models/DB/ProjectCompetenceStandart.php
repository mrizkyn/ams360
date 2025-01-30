<?php

namespace App\Models\DB;

use Illuminate\Database\Eloquent\Model;

class ProjectCompetenceStandart extends Model
{
    protected $connection = 'mysql2';
    protected $table = "project_competence_standart";

    public function competency(){
        return $this->belongsTo('App\Models\DB\Competency', 'competence_id', 'id');
    }
}
