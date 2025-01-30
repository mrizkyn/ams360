<?php

namespace App\Models\DB;

use Illuminate\Database\Eloquent\Model;

class TargetJobCompetency extends Model
{
    protected $connection = 'mysql2';
    protected $guarded = [];

    public function competency()
    {
        return $this->belongsTo('App\Models\DB\Competency');
    }
}
