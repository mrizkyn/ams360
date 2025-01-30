<?php

namespace App\Models\Assessment;

use Illuminate\Database\Eloquent\Model;

class Position extends Model
{
    protected $connection = 'mysql';
    protected $guarded = [];

    public function participants(){
        return $this->hasMany('App\Models\Assessment\Participant');
    }
}
