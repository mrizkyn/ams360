<?php

namespace App\Models\Assessment;

use Illuminate\Database\Eloquent\Model;

class DevelopmentActivity extends Model
{
    protected $connection = 'mysql';

    public function competence()
    {
        return $this->belongsTo('App\Models\Assessment\Competency');
    }
}
