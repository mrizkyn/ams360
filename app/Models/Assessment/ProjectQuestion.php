<?php

namespace App\Models\Assessment;

use Illuminate\Database\Eloquent\Model;

class ProjectQuestion extends Model
{
    protected $connection = 'mysql';
    protected $guarded = [];

    public function keyBehavior(){
        return $this->belongsTo('App\Models\Assessment\KeyBehavior');
    }

    public function project(){
        return $this->belongsTo('App\Models\Assessment\Project');
    }
}
